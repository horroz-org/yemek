class Siralama {
    varsayilan(a, b) { return this.enYuksekOy(a, b);}
    enYuksekOy(a, b) { return (b.like - b.dislike) - (a.like - a.dislike); }
    enYeni(a, b) { return new Date(b.tarih) - new Date(a.tarih); }
    enEski(a, b) { return new Date(a.tarih) - new Date(b.tarih);}
};

const apiUrl = window.location.origin + "/api/";

var yemekCache = [];
var yorumCache = [];

const yorumDerinlikRem = 2;

function basla(){
    // var bugunTarih = new Date().toISOString().split('T')[0]; // yyyy-mm-dd
    herseyiGoster("2025-01-01");
}

// tarih yyyy-mm-dd olacak
function herseyiGoster(tarih){
    var bilgiler = topluAl(tarih);
    var yemek = bilgiler.yemek;
    var yorumlar = bilgiler.yorumlar;

    yemekGoster(yemek);
    yorumlariGoster(yorumlar, Siralama.varsayilan);
}

function topluAl(tarih){
    return apiGet("yemek/toplual.php?tarih=" + tarih);
}

function yorumOyla(id, begenBool){
    // api
    return [];
}

function yemekGoster(yemek){
    var tarihElement = document.getElementById("yemektarih");
    var menuElement = document.getElementById("menu");
    var puanElement = document.getElementById("puan");
    var puanSayisiElement = document.getElementById("degerlendirme-sayisi");

    tarihElement.textContent = yemek.tarih;
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


    /** 
     * burası bütün yorumları sıralamakta idir
     * ama her yorum aynı yükseklikte değil idir
     * aynı grupta hiç değil idir
     * ona göre sıralayacağız ve ekleyeceğiz idir
     * 
     *    root
     *     /\
     *    A  E                
     *   / \  \
     *  C   B  F
     *   \
     *    D
     * 
     * ağaç yapacağız idir
     */

    var tree = yorumTreeYap(yorumlar);
    yorumSirala(tree, siralama);

    yorumlariEkle(tree);
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

function yorumSirala(tree, siralama){
    tree.sort(siralama);
    tree.forEach(function(yorum) {
        if(yorum.children.length > 0){
            yorumSirala(yorum.children, siralama);
        }
    });
}

function yorumlariEkle(tree, derinlik = 0) {
    tree.forEach(function (yorum) {
        console.log("Yorum Ekle:", yorum, derinlik);
        yorumEkle(yorum.uuid, yorum.yazarKullaniciAdi, yorum.zaman, yorum.yorum, yorum.like - yorum.dislike, yorum.adaminOyu, derinlik);
        if (yorum.children.length > 0) {
            console.log("Alt Yorumlar:", yorum.children);
            yorumlariEkle(yorum.children, derinlik + 1);
        }
    });
}

function yorumEkle(id, yazar, tarih, metin, puan, oyBegeni, derinlik = 0){
    const template = document.getElementById("yorum-template");
    const clone = template.content.cloneNode(true);

    clone.querySelector(".yorumkutu").id = id;
    clone.querySelector(".yorum-yazar").textContent = yazar;
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

function alertPopup(message){
    alert(message);
}

function apiGet(endpoint) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", apiUrl + endpoint, false);
    
    try {
        xhr.send();
        if (xhr.status === 200) {
            return JSON.parse(xhr.response);
        } else {
            throw new Error("API Hatası: " + xhr.status);
        }
    } catch (error) {
        throw new Error("Hata.");
    }
}

function apiPost(endpoint, data) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", apiUrl + endpoint, false);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.responseType = "json";
    
    try {
        xhr.send(JSON.stringify(data));
        if (xhr.status === 200) {
            return JSON.parse(xhr.response);
        } else {
            throw new Error("API Hatası: " + xhr.status);
        }
    } catch (error) {
        throw new Error("Hata.");
    }
}

window.addEventListener("load", basla);