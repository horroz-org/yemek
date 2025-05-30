var kullanici = null;

// şu an profiline baktığımız kullanıcı
var gosterilenKullanici = null;

const yorumAlmaLimit = 20;
var suAnkiEskiTarih = new Date();

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

    await profilAyarla(gosterilenKullanici);
}

async function profilAyarla(kullaniciBilgi) {
    var uuid = kullaniciBilgi.uuid;
    var kullaniciAdi = kullaniciBilgi.kullaniciAdi;
    var rutbe = kullaniciBilgi.rutbe;
    var prestij = kullaniciBilgi.prestij;

    // isim falan yerine koy
    document.getElementById("kullanici-adi-kutu").textContent = kullaniciAdi;
    document.getElementById("rutbe-kutu").textContent = rutbe;
    document.getElementById("prestij-kutu").textContent = prestij;

    // yorumları al koy
    var yorumlarListe = await yorumlariniAl(uuid, yorumAlmaLimit, isoDate(suAnkiEskiTarih));
    yorumlarListe.forEach(yorum => {
        yorumEkle(yorum);
    });
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

    document.getElementById("profil-yorumlar-liste").appendChild(clone);
}

function uiAyarla() {

}

window.addEventListener("load", basla);