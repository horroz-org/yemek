class Siralama {
    varsayilan(a, b) { return this.enYuksekOy(a, b);}
    enYuksekOy(a, b) { return b.puan - a.puan; }
    enYeni(a, b) { return new Date(b.tarih) - new Date(a.tarih); }
    enEski(a, b) { return new Date(a.tarih) - new Date(b.tarih);}
};

const apiUrl = window.location.origin + "/api/";

var yemekCache = [];
var yorumCache = [];

function basla(){
    var bugunTarih = new Date().toISOString().split('T')[0]; // yyyy-mm-dd
    // herseyiGoster(bugunTarih);
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
    var yorumlarListe = document.getElementById("yorumlar-liste");
    yorumlarListe.innerHTML = "";

    yorumlar.sort(siralama);
    yorumlar.forEach(function(yorum){
        yorumEkle(yorum.id, yorum.yazar, yorum.tarih, yorum.metin, yorum.like - yorum.dislike, yorum.adaminOyu);
    });
}

function yorumEkle(id, yazar, tarih, metin, puan, oyBegeni){
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

    document.getElementById("yorumlar-liste").appendChild(clone);
}

function alertPopup(message){
    alert(message);
}

function apiGet(endpoint) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", apiUrl + endpoint, false);
    xhr.responseType = "json";
    
    try {
        xhr.send();
        if (xhr.status === 200) {
            return xhr.response;
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
            return xhr.response;
        } else {
            throw new Error("API Hatası: " + xhr.status);
        }
    } catch (error) {
        throw new Error("Hata.");
    }
}

window.addEventListener("load", basla);