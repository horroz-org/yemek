var yemekCache = [];

const yorumDerinlikRem = 2;
var kullanici = null;
var suAnkiTarih;

var cevapVerilenYorumId = null; // şu anda cevap veriyorsa id, dümdüz yorum yazıyorsa null

async function basla(){
    uiAyarla();
    await authBak();

    const tParam = getQueryParam("t");
    if(tParam !== null && isIsoDate(tParam)){
        suAnkiTarih = new Date(tParam);
    }
    else{
        suAnkiTarih = new Date();
    }

    var simdiIsoDate = isoDate(suAnkiTarih); // yyyy-mm-dd
    herseyiGoster(simdiIsoDate);
}

// tarih yyyy-mm-dd olacak
function herseyiGoster(tarih){
    if(tarih in yemekCache){
        if ("error" in yemekCache[tarih]) {
            uiYemekYok();
        }
        else {
            var yemek = yemekCache[tarih].yemek;
            var yorumlar = yemekCache[tarih].yorumlar;

            uiYemekVar();

            yemekGoster(yemek);
            yorumlariGoster(yorumlar, Siralama.varsayilan);
        }
    }
    else{
        yemekBilgiAlGoster(tarih);
    }
}

// cache yenilemek için başka yerlerde kullanacaz diye ayırdım bunu herseyiGoster'den
function yemekBilgiAlGoster(tarih){
    topluAl(tarih).then(bilgiler => {
        if("error" in bilgiler){
            uiYemekYok();
        }
        else{
            var yemek = bilgiler.yemek;
            var yorumlar = bilgiler.yorumlar;
            
            uiYemekVar();

            yemekGoster(yemek);
            yorumlariGoster(yorumlar, Siralama.varsayilan);
        }

        yemekCache[tarih] = bilgiler;
    });
}

function uiYemekYok(){
    var tarihElement = document.getElementById("yemektarih");
    var menuElement = document.getElementById("menu");
    var puanWrapperElement = document.querySelector(".puan-wrapper");
    var butonGridElement = document.querySelector(".butongrid");
    var pyBilgiElement = document.querySelector(".puan-yorum-bilgi");
    var yorumYazButon = document.getElementById("yorumyazbuton");

    const formatter = new Intl.DateTimeFormat("tr-TR", { dateStyle: 'long' });
    tarihElement.textContent = formatter.format(suAnkiTarih);

    menuElement.textContent = "Bilmiyoruz.";
    puanWrapperElement.style.display = "none";
    butonGridElement.style.display = "none";
    pyBilgiElement.style.display = "none";
    yorumYazButon.style.display = "none";

    var yorumlarListe = document.getElementById("yorumlar-liste");
    yorumlarListe.innerHTML = "";
}

function uiYemekVar(){
    var puanWrapperElement = document.querySelector(".puan-wrapper");
    var butonGridElement = document.querySelector(".butongrid");
    var pyBilgiElement = document.querySelector(".puan-yorum-bilgi");
    var yorumYazButon = document.getElementById("yorumyazbuton");

    puanWrapperElement.style.removeProperty("display");
    butonGridElement.style.removeProperty("display");
    pyBilgiElement.style.removeProperty("display");
    yorumYazButon.style.removeProperty("display");
}

function yemekGoster(yemek){
    var tarihElement = document.getElementById("yemektarih");
    var menuElement = document.getElementById("menu");
    var puanElement = document.getElementById("puan");
    var puanSayisiElement = document.getElementById("degerlendirme-sayisi");

    // tarih
    const formatter = new Intl.DateTimeFormat("tr-TR", { dateStyle: 'long' });
    tarihElement.textContent = formatter.format(new Date(yemek.tarih));

    menuElement.textContent = yemek.menu;
    // 4.66666667 gibi sayılar gelmesin diye tek basamağa yuvarlamaktayız
    puanElement.textContent = puanTrunc(yemek.puan);
    puanSayisiElement.textContent = yemek.puanSayisi;

    document.querySelectorAll(".puan-secildi").forEach(function(buton){
        buton.classList.remove("puan-secildi");
    });
    if(yemek.verilenPuan != null){
        var puanButon = document.getElementById("puan" + yemek.verilenPuan);
        puanButon.classList.add("puan-secildi");
    }
}

function yorumlariGoster(yorumlar, siralama){
    var yorumSayisiElement = document.getElementById("yorum-sayisi");
    yorumSayisiElement.textContent = yorumlar.length;

    var yorumlarListe = document.getElementById("yorumlar-liste");
    yorumlarListe.innerHTML = "";

    // Yorumları sıralamak için
    // ağaç yapacağız idir
    var tree = yorumTreeYap(yorumlar);
    yorumSirala(tree, siralama);

    yorumlariEkle(tree);

    yorumUiEventAyarla();
}

function yorumTreeYap(yorumlar) {
    var uuidMap = new Map();

    yorumlar.forEach(function (yorum) {
        yorum.children = [];
        uuidMap.set(yorum.uuid, yorum);
    });

    yorumlar.forEach(yorum => {
        if (yorum.ustYorumId !== null) {
            var parent = uuidMap.get(yorum.ustYorumId);
            parent.children.push(yorum);
        }
    });

    var root = [];
    yorumlar.forEach(yorum => {
        if (yorum.ustYorumId === null) {
            root.push(yorum);
        }
    });

    return root;
}

class Siralama {
    static varsayilan(a, b) { return Siralama.enYuksekOy(a, b); }
    static enYuksekOy(a, b) { return (b.like - b.dislike) - (a.like - a.dislike); }
    static enYeni(a, b) { return new Date(b.zaman) - new Date(a.zaman); }
    static enEski(a, b) { return new Date(a.zaman) - new Date(b.zaman); }
};

function yorumSirala(tree, siralama) {
    tree.sort(siralama);
    tree.forEach(function (yorum) {
        if (yorum.children.length > 0) {
            yorumSirala(yorum.children, siralama);
        }
    });
}

function yorumlariEkle(tree, derinlik = 0) {
    tree.forEach(function (yorum) {
        yorumEkle(yorum, derinlik);
        if (yorum.children.length > 0) {
            yorumlariEkle(yorum.children, derinlik + 1);
        }
    });
}

function yorumEkle(yorum, derinlik = 0){
    const template = document.getElementById("yorum-template");
    const clone = template.content.cloneNode(true);

    clone.querySelector(".yorumkutu").id = yorum.uuid;
    clone.querySelector(".yorum-yazar").textContent = yorum.yazarKullaniciAdi;
    clone.querySelector(".yorum-yazar").href = "/profil/?u=" + yorum.yazarKullaniciAdi;
    clone.querySelector(".yorum-tarih").textContent = zamanFarki(new Date(), new Date(yorum.zaman));
    clone.querySelector(".yorum-metin").textContent = yorum.yorum;
    clone.querySelector(".vote-sayi").textContent = yorum.like - yorum.dislike;
    if(yorum.bizimkininOyu !== null){
        if(yorum.bizimkininOyu){
            clone.querySelector(".upvote").classList.add("vote-secildi");
        }
        else {
            clone.querySelector(".downvote").classList.add("vote-secildi");
        }
    }

    if(kullanici !== null && yorum.yazarUuid === kullanici.uuid){
        var sikayetButon = clone.querySelector(".sikayet-buton");
        sikayetButon.classList.remove("sikayet-buton");
        sikayetButon.classList.add("sil-buton");
        sikayetButon.textContent = "-";
    }

    clone.querySelector(".yorumkutu").style.marginLeft = (derinlik * yorumDerinlikRem) + "rem";

    document.getElementById("yorumlar-liste").appendChild(clone);
}

function sonrakiYemek(){
    suAnkiTarih = new Date(suAnkiTarih.getTime() + 86400000); // 1 gün ms
    var suAnkiIsoDate = isoDate(suAnkiTarih);
    setQueryParam("t", suAnkiIsoDate);
    herseyiGoster(suAnkiIsoDate);
}

function oncekiYemek() {
    suAnkiTarih = new Date(suAnkiTarih.getTime() - 86400000); // 1 gün ms
    var suAnkiIsoDate = isoDate(suAnkiTarih);
    setQueryParam("t", suAnkiIsoDate);
    herseyiGoster(isoDate(suAnkiTarih));
}

function cevapVer(yorumId){
    cevapVerilenYorumId = yorumId;
    yorumFormAc(true);
}

function sikayetEtEvent(yorumId) {
    alert("yorumu şikayet ettiniz tebrikler " + yorumId);
}

async function yorumSilEvent(yorumId) {
    if(!confirm("Yorumu silmek istediğinize emin misiniz?")){
        return;
    }

    var sonuc = await yorumSil(yorumId);
    if(sonuc === null){
        alert("Kötü şeyler oluverdi.");
        return;
    }
    
    // yorum silindi, cache'den de sil, yeniden yükle
    var yemekTarih = isoDate(suAnkiTarih);
    yemekCache[yemekTarih].yorumlar.forEach((yorum, index) => {
        if(yorum.uuid === yorumId){
            yemekCache[yemekTarih].yorumlar.splice(index, 1);
            return;
        }
    });

    yorumlariGoster(yemekCache[yemekTarih].yorumlar, Siralama.varsayilan);
}

function yorumYaziKontrol(yazi){
    return true;
}

async function yorumGonderEvent() {
    var yorumYaziElement = document.getElementById("yorum-yazi");
    var herkeseAcikElement = document.getElementById("herkese-acik-checkbox");

    var yemekTarih = isoDate(suAnkiTarih);
    var yorumYazi = yorumYaziElement.value.trim();
    var herkeseAcik = herkeseAcikElement.checked;
    var ustYorumId = cevapVerilenYorumId;

    if(!yorumYaziKontrol(yorumYazi)){
        // normalde hata mesajını kırmızıyla yorumun oraya yazacam sonra hallederim ama onu
        alert("Yorumunuz değişik.");
        return;
    }

    var gonderilecekYorum = {
        yemekTarih: yemekTarih,
        yorum: yorumYazi,
        herkeseAcik: herkeseAcik,
        ustYorumId: ustYorumId
    };

    var sonuc = await yorumYap(gonderilecekYorum);
    if(sonuc === null){
        // hata çıktı
        alert("Kötü şeyler oluverdi.");
        return;
    }

    // formu kapatak
    yorumFormKapat();

    yemekCache[yemekTarih].yorumlar.push(sonuc);
    yorumlariGoster(yemekCache[yemekTarih].yorumlar, Siralama.varsayilan);
    document.getElementById(sonuc.uuid).scrollIntoView({ behavior: "smooth" });
}

function yorumUiEventAyarla(){
    document.querySelectorAll('.yorumkutu').forEach(yorumkutu => {
        yorumkutu.querySelector(".cevap-buton").addEventListener("click", () => {
            cevapVer(yorumkutu.id);
        });

        var sikayetButon = yorumkutu.querySelectorAll(".sikayet-buton");
        if(sikayetButon.length > 0){
            sikayetButon[0].addEventListener("click", () => {
                sikayetEtEvent(yorumkutu.id);
            });
        }
        else{
            var silButon = yorumkutu.querySelector(".sil-buton");
            silButon.addEventListener("click", async () => {
                await yorumSilEvent(yorumkutu.id);
            });
        }

        yorumkutu.querySelectorAll(".vote-ok").forEach(voteOk => {
            voteOk.addEventListener("click", async () => {
                if(kullanici === null){
                    window.location.href = "/giris/";
                    return;
                }

                var yorumUuid = yorumkutu.id;
                var likeDislike = voteOk.classList.contains("upvote");
                
                if(voteOk.classList.contains("vote-secildi")){
                    // seçiliye bastıysa silsin

                    voteOk.classList.remove("vote-secildi");
                    
                    var guncelOylar = await yorumOySil(yorumUuid);
                    if(guncelOylar === null){
                        // hata çıktı, eski haline getir
                        voteOk.classList.add("vote-secildi");
                    }
                    else{
                        yorumOyGuncelle(yorumUuid, null, guncelOylar);
                    }
                }
                else {
                    // yeni oy veriyoz / değiştiriyoz

                    var seciliOk = yorumkutu.querySelectorAll(".vote-secildi");
                    if(seciliOk.length > 0){
                        // önceki seçiliyi kaldır
                        seciliOk[0].classList.remove("vote-secildi");
                    }

                    voteOk.classList.add("vote-secildi");

                    var guncelOylar = await yorumOyVer(yorumUuid, likeDislike);
                    if (guncelOylar === null) {
                        // hata çıktı, eski haline getir
                        voteOk.classList.remove("vote-secildi");
                        if (seciliOk.length > 0) {
                            seciliOk[0].classList.add("vote-secildi");
                        }
                    }
                    else {
                        yorumOyGuncelle(yorumUuid, likeDislike, guncelOylar);
                    }
                }
            });
        });
    });
}

function puanGuncelle(puan, puanSayisi, verilenPuan){
    var duzgunPuan = puanTrunc(puan);

    var puanElement = document.getElementById("puan");
    var puanSayisiElement = document.getElementById("degerlendirme-sayisi");

    puanElement.textContent = duzgunPuan;
    puanSayisiElement.textContent = puanSayisi;

    // cache
    yemekCache[isoDate(suAnkiTarih)].yemek.puan = duzgunPuan;
    yemekCache[isoDate(suAnkiTarih)].yemek.puanSayisi = puanSayisi;
    yemekCache[isoDate(suAnkiTarih)].yemek.verilenPuan = verilenPuan;
}

function yorumOyGuncelle(yorumUuid, likeDislike, oylar){
    var yorumkutu = document.getElementById(yorumUuid);

    yorumkutu.querySelector(".vote-sayi").textContent = oylar.like - oylar.dislike;

    // cache
    yemekCache[isoDate(suAnkiTarih)].yorumlar.forEach(yorum => {
        if(yorum.uuid === yorumUuid){
            yorum.like = oylar.like;
            yorum.dislike = oylar.dislike;
            yorum.bizimkininOyu = likeDislike;
            return;
        }
    });
}

function yorumFormAc(cevapMi = false){
    var formlarElement = document.querySelector(".ekran-formlar");
    var yorumFormElement = document.getElementById("yorum-form");
    var yorumInputBaslikElement = document.getElementById("yorum-input-baslik");
    var yorumYaziElement = document.getElementById("yorum-yazi");

    yorumYaziElement.value = "";

    formlarElement.style.removeProperty("display");
    yorumFormElement.style.removeProperty("display");
    
    yorumInputBaslikElement.textContent = cevapMi ? "Cevabınız:" : "Yorumunuz:";

    yorumYaziElement.focus();
}

function yorumFormKapat(){
    var formlarElement = document.querySelector(".ekran-formlar");
    var yorumFormElement = document.getElementById("yorum-form");

    formlarElement.style.display = "none";
    yorumFormElement.style.display = "none";
}

function uiAyarla(){
    document.getElementById("yorumyazbuton").addEventListener("click", () => {
        cevapVerilenYorumId = null;
        yorumFormAc(false);
    });

    document.getElementById("form-kapat-buton").addEventListener("click", () => {
        yorumFormKapat();
    });

    document.getElementById("sagyemekok").addEventListener("click", () => {
        sonrakiYemek();
    });

    document.getElementById("solyemekok").addEventListener("click", () => {
        oncekiYemek();
    });

    document.getElementById("yorum-gonder-buton").addEventListener("click", async () => {
        await yorumGonderEvent();
    });

    document.querySelectorAll('.puanbuton').forEach(puanbuton => {
        puanbuton.addEventListener("click", async () => {
            if(kullanici == null){
                window.location.href = "/giris/";
                return;
            }

            if(puanbuton.classList.contains("puan-secildi")){
                // seçildiyse sil

                puanbuton.classList.remove("puan-secildi");
                var sonuc = await yemekPuaniSil(isoDate(suAnkiTarih));
                if (sonuc === null) {
                    // ters gitti
                    puanbuton.classList.add("puan-secildi");
                }
                else{
                    // keyfimin kahyası gelmiş, güncel edelim
                    puanGuncelle(sonuc.puan, sonuc.puanSayisi, null);
                }
            }
            else{
                // yeni puan ekliyoruz veya değiştiriyoruz
                // adam seçili olmayan butona bastı

                var seciliButon = document.querySelectorAll(".puan-secildi");
                if(seciliButon.length > 0){
                    seciliButon[0].classList.remove("puan-secildi");
                }

                puanbuton.classList.add("puan-secildi");
                var basilanPuan = parseInt(puanbuton.textContent);
                var sonuc = await yemegePuanVer(basilanPuan, isoDate(suAnkiTarih));
                if (sonuc === null) {
                    // hata çıktı, eski haline getir
                    puanbuton.classList.remove("puan-secildi");
                    if (seciliButon.length > 0) {
                        seciliButon[0].classList.add("puan-secildi");
                    }
                }
                else{
                    // herşey güzel, güncel puanlar geldi onları güncelleyek
                    puanGuncelle(sonuc.puan, sonuc.puanSayisi, basilanPuan);
                }
            }
        });
    });

    yorumUiEventAyarla();
}

window.addEventListener("load", basla);