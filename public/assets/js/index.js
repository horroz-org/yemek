var yemekCache = [];

const yorumDerinlikRem = 2;
var kullanici;
var suAnkiTarih;


function basla(){
    uiAyarla();
    authBak();

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

/**
 * Bakalım giriş yapmış mı
 * yapmamışsa cevap yaz yorum yaz yemeğe puan verme yoruma oy verme report butonları khapaly olacak
 * aslında report açık olabilir ama her yorum için 1 kere tutarım dbde
 * ve uyarı çıkar eğer giriş yapmadan rapor ederseniz ip'niz kaydedilir diye
 * 
 * giriş yapmışsa da kullanıcı adını sağ üste yazdıracağım ben
*/
function authBak(){
    girisYapildiMi().then(userData => {
        if (userData === false) {
            anonimAyarla();
            return;
        }

        kullanici = userData;
        adamAyarla(userData);
    });
}

function anonimAyarla(){
    kullanici = null;
    var kullaniciAdiElement = document.getElementById("kullanici-adi");
    kullaniciAdiElement.textContent = "Giriş Yap";
    kullaniciAdiElement.href = "/giris/";
}

function adamAyarla(kullanici){
    var kullaniciAdiElement = document.getElementById("kullanici-adi");
    kullaniciAdiElement.textContent = kullanici.kullaniciAdi;
    kullaniciAdiElement.href = "/profil/";
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
    puanWrapperElement.style.visibility = "hidden";
    butonGridElement.style.visibility = "hidden";
    pyBilgiElement.style.visibility = "hidden";
    yorumYazButon.style.visibility = "hidden";
}

function uiYemekVar(){
    var puanWrapperElement = document.querySelector(".puan-wrapper");
    var butonGridElement = document.querySelector(".butongrid");
    var pyBilgiElement = document.querySelector(".puan-yorum-bilgi");
    var yorumYazButon = document.getElementById("yorumyazbuton");

    puanWrapperElement.style.removeProperty("visibility");
    butonGridElement.style.removeProperty("visibility");
    pyBilgiElement.style.removeProperty("visibility");
    yorumYazButon.style.removeProperty("visibility");
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
    puanElement.textContent = yemek.puan;
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
    varsayilan(a, b) { return this.enYuksekOy(a, b); }
    enYuksekOy(a, b) { return (b.like - b.dislike) - (a.like - a.dislike); }
    enYeni(a, b) { return new Date(b.tarih) - new Date(a.tarih); }
    enEski(a, b) { return new Date(a.tarih) - new Date(b.tarih); }
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
        yorumEkle(yorum.uuid, yorum.yazarKullaniciAdi, yorum.zaman, yorum.yorum, yorum.like - yorum.dislike, yorum.bizimkininOyu, derinlik);
        if (yorum.children.length > 0) {
            yorumlariEkle(yorum.children, derinlik + 1);
        }
    });
}

function yorumEkle(id, yazar, tarih, metin, puan, oyBegeni, derinlik = 0){
    const template = document.getElementById("yorum-template");
    const clone = template.content.cloneNode(true);

    clone.querySelector(".yorumkutu").id = id;
    clone.querySelector(".yorum-yazar").textContent = yazar;
    clone.querySelector(".yorum-yazar").href = "/profil/?u=" + yazar;
    clone.querySelector(".yorum-tarih").textContent = tarih;
    clone.querySelector(".yorum-metin").textContent = metin;
    clone.querySelector(".vote-sayi").textContent = puan;
    if(oyBegeni !== null){
        if(oyBegeni){
            clone.querySelector(".upvote").classList.add("vote-secildi");
        }
        else {
            clone.querySelector(".downvote").classList.add("vote-secildi");
        }
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
    alert("yoruma cevap verdiniz tebrikler " + yorumId);
}

function sikayetEt(yorumId) {
    alert("yorumu şikayet ettiniz tebrikler " + yorumId);
}

function yorumUiEventAyarla(){
    document.querySelectorAll('.yorumkutu').forEach(yorumkutu => {
        yorumkutu.querySelector(".cevap-buton").addEventListener("click", () => {
            cevapVer(yorumkutu.id);
        });

        yorumkutu.querySelector(".sikayet-buton").addEventListener("click", () => {
            sikayetEt(yorumkutu.id);
        });

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
    var puanElement = document.getElementById("puan");
    var puanSayisiElement = document.getElementById("degerlendirme-sayisi");

    puanElement.textContent = puan;
    puanSayisiElement.textContent = puanSayisi;

    // cache
    yemekCache[isoDate(suAnkiTarih)].yemek.puan = puan;
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

function uiAyarla(){
    document.querySelector(".topbar-logovebaslik").addEventListener("click", () => {
        window.location.href = "/";
    });

    document.getElementById("yorumyazbuton").addEventListener("click", () => {
        alert("yorum yazdınız tebrikler");
    });

    document.getElementById("sagyemekok").addEventListener("click", () => {
        sonrakiYemek();
    });

    document.getElementById("solyemekok").addEventListener("click", () => {
        oncekiYemek();
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