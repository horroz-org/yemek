var yemekCache = [];

const yorumDerinlikRem = 2;
var kullanici;
var suAnkiTarih;


function basla(){
    uiAyarla();
    authBak();

    suAnkiTarih = new Date("2025-01-01");

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
    var kullaniciAdiElement = document.getElementById("kullanici-adi");
    kullaniciAdiElement.textContent = "Giriş Yap";
    kullaniciAdiElement.href = "/giris.html";
}

function adamAyarla(kullanici){
    var kullaniciAdiElement = document.getElementById("kullanici-adi");
    kullaniciAdiElement.textContent = kullanici.kullaniciAdi;
    kullaniciAdiElement.href = "/profil.html";
}

// tarih yyyy-mm-dd olacak
function herseyiGoster(tarih){
    if(tarih in yemekCache){
        var yemek = yemekCache[tarih].yemek;
        var yorumlar = yemekCache[tarih].yorumlar;

        yemekGoster(yemek);
        yorumlariGoster(yorumlar, Siralama.varsayilan);
    }
    else{
        topluAl(tarih).then(bilgiler => {
            var yemek = bilgiler.yemek;
            var yorumlar = bilgiler.yorumlar;

            yemekGoster(yemek);
            yorumlariGoster(yorumlar, Siralama.varsayilan);

            yemekCache[tarih] = bilgiler;
        });
    }
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
    clone.querySelector(".yorum-yazar").href = "/profil.html?u=" + yazar;
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
    herseyiGoster(isoDate(suAnkiTarih));
}

function oncekiYemek() {
    suAnkiTarih = new Date(suAnkiTarih.getTime() - 86400000); // 1 gün ms
    herseyiGoster(isoDate(suAnkiTarih));
}

async function yemegePuanVer(puan){
    // api
    alert("tebrikler " + puan + " puan verdiniz");
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
            await yemegePuanVer(parseInt(puanbuton.textContent));
        });
    });

    document.querySelectorAll('.yorumkutu').forEach(yorumkutu => {
        yorumkutu.querySelector(".cevap-buton").addEventListener("click", async () => {
            yemegePuanVer(parseInt(puanbuton.textContent));
        });
    });
}

window.addEventListener("load", basla);