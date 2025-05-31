var kullanici = null;

// şu an profiline baktığımız kullanıcı
var gosterilenKullanici = null;

const yorumAlmaLimit = 20;
var suAnkiEskiTarih = new Date();
var devamiVar = true;

async function basla() {
    uiAyarla();
    await authBak();

    kullaniciParam = getQueryParam("u");

    if (kullaniciParam === null) {
        if(kullanici === null){
            giriseGit();
        }
        else{
            gosterilenKullanici = kullanici;
        }
    }
    else {
        gosterilenKullanici = await kullaniciAl(kullaniciParam);
        if(gosterilenKullanici === null){
            var layout = document.querySelector(".profil-layout");
            layout.style.justifyContent = "center";
            layout.style.marginTop = 0;
            layout.style.paddingTop = 0;
            layout.style.height = "100vh";
            layout.innerHTML = "Böyle birisi malesef yokmuş.";
            return;
        }
    }

    await profilAyarla();
}

async function profilAyarla() {
    // isim falan yerine koy
    document.getElementById("kullanici-adi-kutu").textContent = gosterilenKullanici.kullaniciAdi;
    document.getElementById("rutbe-kutu").textContent = gosterilenKullanici.rutbe;
    document.getElementById("prestij-kutu").textContent = gosterilenKullanici.prestij;

    // yorumları al koy
    await yorumlarinDevaminiKoy();

    if (!devamiVar) {
        document.getElementById("devamini-goster").style.display = "none";
    }
}

async function yorumlarinDevaminiKoy(){
    var yorumlarListe = await yorumlariniAl(gosterilenKullanici.uuid, yorumAlmaLimit, klasikTarihSaatFormat(suAnkiEskiTarih));
    yorumlarListe.forEach(yorum => {
        yorumEkle(yorum);
    });

    if(yorumlarListe.length === yorumAlmaLimit){
        suAnkiEskiTarih = new Date(yorumlarListe[yorumlarListe.length - 1].zaman);
    }
    else{
        devamiVar = false;
    }
}

// index.js'den alıverdim
function yorumEkle(yorum) {
    const template = document.getElementById("profil-yorum-template");
    const clone = template.content.cloneNode(true);

    clone.querySelector(".yorumkutu").id = yorum.uuid;
    clone.querySelector(".yorum-yazar").textContent = yorum.yazarKullaniciAdi;
    clone.querySelector(".yorum-yazar").href = "/profil/?u=" + yorum.yazarKullaniciAdi;
    clone.querySelector(".yorum-tarih").textContent = zamanFarki(new Date(), new Date(yorum.zaman));
    clone.querySelector(".yorum-metin").innerHTML = yorumIsle(yorum.yorum);
    clone.querySelector(".vote-sayi").textContent = yorum.like - yorum.dislike;

    // yoruma git butonu
    clone.querySelector(".yoruma-git-buton").href = "/?t=" + yorum.yemekTarih + "#" + yorum.uuid;

    document.getElementById("profil-yorumlar-liste").appendChild(clone);
}

function uiAyarla() {
    document.getElementById("devamini-goster").addEventListener("click", async function() {
        await yorumlarinDevaminiKoy();
        if(!devamiVar){
            this.style.display = "none";
        }
    });
}

window.addEventListener("load", basla);