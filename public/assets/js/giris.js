// auth.js'ye taşırım belki kullanici'yı sonra 
var kullanici = null;

// query parametresi ?r= falan filan
// redirParam'ı direk window.location.href = yapacaz
// öyle
var redirParam = null;

async function basla(){
    uiAyarla();
    await authBak();

    redirParam = getQueryParam("r");

    if(kullanici !== null){
        var gkfkElement = document.getElementById("giris-kayit-form-kutu");
        gkfkElement.innerHTML = "Zaten giriş yapmışsın.";
        gkfkElement.style.textAlign = "center";
        gkfkElement.innerHTML += "<a href='/'>Ana sayfaya git</a>";
    }
}

function formHataYaz(mesaj){
    document.getElementById("hata-mesaji").textContent = mesaj;
}

function bilgileriKontrolEt(kullaniciAdi, sifre){
    if(kullaniciAdi === "" || sifre === ""){
        formHataYaz("Boş kullanıcı adı şifre mi olur dangalak?");
        return false;
    }

    formHataYaz("");
    return true;
}

function uiAyarla() {
    document.getElementById("giris-kayit-buton").addEventListener("click", async function () {
        if (this.classList.contains("kapali-buton")) {
            return;
        }
        this.classList.add("kapali-buton");

        var kullaniciAdiElement = document.getElementById("kullanici-adi-input");
        var sifreElement = document.getElementById("sifre-input");

        var kullaniciAdi = kullaniciAdiElement.value.trim();
        var sifre = sifreElement.value.trim();

        if(!bilgileriKontrolEt(kullaniciAdi, sifre)){
            return;
        }

        var tokenInfo = await girisYap(kullaniciAdi, sifre);
        if(tokenInfo === null){
            formHataYaz("Değişik bir şeyler oldu, sıçtık.");
            this.classList.remove("kapali-buton");
            return;
        }

        // hata verdiyse hatayı yazak kırmızıylan
        if("error" in tokenInfo){
            formHataYaz(tokenInfo.error);
            this.classList.remove("kapali-buton");
            return;
        }

        setCookie("YEMEK_SESSION", tokenInfo.token, tokenInfo.expiration);

        var gidilenYer = "/";
        if(redirParam !== null){
            gidilenYer = redirParam;
        }
        window.location.href = gidilenYer;
    });

    document.getElementById("sifre-goster-buton").addEventListener("click", function () {
        var sifreInputElement = document.getElementById("sifre-input");
        if (sifreInputElement.type == "text") {
            sifreInputElement.type = "password";
            this.textContent = "göster";
        }
        else {
            sifreInputElement.type = "text";
            this.textContent = "gizle";
        }
    });
}

window.addEventListener("load", basla);