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
    await herseyiGoster(simdiIsoDate);

    yorumaKaydir();
}

function yorumaKaydir(){
    if (window.location.hash) {
        const target = document.getElementById(window.location.hash.substring(1));
        if (target){
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

function hashSil(){
    history.replaceState(null, null, ' ');
}

// tarih yyyy-mm-dd olacak
async function herseyiGoster(tarih){
    if(tarih in yemekCache){
        if (yemekCache[tarih] === null) {
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
        await yemekBilgiAlGoster(tarih);
    }
}

// cache yenilemek için başka yerlerde kullanacaz diye ayırdım bunu herseyiGoster'den
async function yemekBilgiAlGoster(tarih){
    var bilgiler = await topluAl(tarih);

    if (bilgiler === null) {
        uiYemekYok();
    }
    else {
        var yemek = bilgiler.yemek;
        var yorumlar = bilgiler.yorumlar;

        uiYemekVar();

        yemekGoster(yemek);
        yorumlariGoster(yorumlar, Siralama.varsayilan);
    }

    yemekCache[tarih] = bilgiler;
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

    // çok iyi durmamakta, düzenlemeniz önerilir ama belki.
    menuElement.innerHTML = yemek.menu + "\n" + "<b>Kalori:</b> " + yemek.kalori;

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
    clone.querySelector(".yorum-metin").innerHTML = yorumIsle(yorum.yorum);
    clone.querySelector(".vote-sayi").textContent = yorum.like - yorum.dislike;
    if(yorum.bizimkininOyu !== null){
        if(yorum.bizimkininOyu){
            clone.querySelector(".upvote").classList.add("vote-secildi");
        }
        else {
            clone.querySelector(".downvote").classList.add("vote-secildi");
        }
    }

    if(yorum.adaminYemekPuani !== null){
        clone.querySelector(".yorum-yazar-yemek-puan").textContent = yorum.adaminYemekPuani + "/10";
        clone.querySelector(".yorum-yazar-yemek-puan").style.color = colInterpolate([200, 0, 0], [0, 200, 0], yorum.adaminYemekPuani / 10);
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

    hashSil();
}

function oncekiYemek() {
    suAnkiTarih = new Date(suAnkiTarih.getTime() - 86400000); // 1 gün ms
    var suAnkiIsoDate = isoDate(suAnkiTarih);
    setQueryParam("t", suAnkiIsoDate);
    herseyiGoster(isoDate(suAnkiTarih));

    hashSil();
}

function cevapVer(yorumId){
    cevapVerilenYorumId = yorumId;
    yorumFormAc(true);
}

async function sikayetEtEvent(yorumId) {
    if(!confirm("Emin misin oğlum?")){
        return;
    }

    var sikayetBilgi = await sikayetEt(yorumId);
    if("error" in sikayetBilgi){
        alert(sikayetBilgi.error);
        return;
    }

    alert("Yorumu şikayet ettiniz tebrikler.");
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

    if ("error" in sonuc) {
        alert(sonuc.error);
        return;
    }
    
    // yorum silindi, cache'den de sil, yeniden yükle
    var yemekTarih = isoDate(suAnkiTarih);
    yemekCache[yemekTarih].yorumlar.forEach((yorum) => {
        if(yorum.uuid === yorumId){
            yorum.yazarUuid = null;
            yorum.yazarKullaniciAdi = "[silindi]";
            yorum.yorum = "[silindi]";
        }
    });

    yorumlariGoster(yemekCache[yemekTarih].yorumlar, Siralama.varsayilan);
}

function yorumFormHataYaz(mesaj){
    document.getElementById("yorum-form-hata").textContent = mesaj;
}

async function yorumGonderEvent() {
    var yorumYaziElement = document.getElementById("yorum-yazi");

    var yemekTarih = isoDate(suAnkiTarih);
    var yorumYazi = yorumYaziElement.value.trim();
    var ustYorumId = cevapVerilenYorumId;

    // ya true ya da hata mesajı döndürüyo
    var kontrol = yorumKontrol(yorumYazi);
    if(kontrol !== true){
        // normalde hata mesajını kırmızıyla yorumun oraya yazacam sonra hallederim ama onu
        //
        // uzun zaman (2-3 gün) oldu şimdi yapalım
        yorumFormHataYaz(kontrol);
        return;
    }

    var gonderilecekYorum = {
        yemekTarih: yemekTarih,
        yorum: yorumYazi,
        ustYorumId: ustYorumId
    };

    var sonuc = await yorumYap(gonderilecekYorum);
    if(sonuc === null){
        // hata çıktı
        alert("Kötü şeyler oluverdi.");
        return;
    }

    if("error" in sonuc){
        yorumFormHataYaz(sonuc.error);
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
            if (kullanici == null) {
                giriseGit();
                return;
            }
            
            cevapVer(yorumkutu.id);
        });

        var sikayetButon = yorumkutu.querySelectorAll(".sikayet-buton");
        if(sikayetButon.length > 0){
            sikayetButon[0].addEventListener("click", async function () {
                if (kullanici == null) {
                    giriseGit();
                    return;
                }
                
                await sikayetEtEvent(yorumkutu.id);
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
                    giriseGit();
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
    yorumFormHataYaz("");

    var formlarElement = document.querySelector(".ekran-formlar");
    var yorumFormElement = document.getElementById("yorum-form");
    var yorumInputBaslikElement = document.getElementById("yorum-input-baslik");
    var yorumYaziElement = document.getElementById("yorum-yazi");
    var kalanKarakterSayisiElement = document.getElementById("yorum-yazi-karaktersayi");

    yorumYaziElement.value = "";
    kalanKarakterSayisiElement.textContent = "0/" + maksimumYorumKarakterSayisi;

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
        if (kullanici == null) {
            giriseGit();
            return;
        }

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

    // arrow fonksiyonlarda this yok
    document.getElementById("yorum-gonder-buton").addEventListener("click", async function () {
        if(this.classList.contains("kapali-buton")){
            return;
        }

        this.classList.add("kapali-buton");
        await yorumGonderEvent();
        this.classList.remove("kapali-buton");
    });

    document.querySelectorAll('.puanbuton').forEach(puanbuton => {
        puanbuton.addEventListener("click", async () => {
            if(kullanici == null){
                giriseGit();
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

    // yorum formundaki textarea
    document.getElementById("yorum-yazi").addEventListener("input", (e) => {
        var gonderButon = document.getElementById("yorum-gonder-buton");
        var kalanKarakterSayisiElement = document.getElementById("yorum-yazi-karaktersayi");

        var targetVal = e.target.value.trim();
        if (minimumYorumKarakterSayisi <= targetVal.length && targetVal.length <= maksimumYorumKarakterSayisi) {
            kalanKarakterSayisiElement.style.color = "var(--normal-color)";
            gonderButon.classList.remove("kapali-buton");
        }
        else {
            kalanKarakterSayisiElement.style.color = "var(--kotu-color)";
            gonderButon.classList.add("kapali-buton");
        }

        kalanKarakterSayisiElement.textContent = targetVal.length + " / " + maksimumYorumKarakterSayisi;
    });

    yorumUiEventAyarla();
}

window.addEventListener("load", basla);