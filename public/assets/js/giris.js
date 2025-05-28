// auth.js'ye taşırım belki kullanici'yı sonra 
var kullanici = null;

async function basla(){
    uiAyarla();
    await authBak();

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
    document.getElementById("giris-kayit-buton").addEventListener("click", async () => {
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
            return;
        }

        // hata verdiyse hatayı yazak kırmızıylan
        if("error" in tokenInfo){
            formHataYaz(tokenInfo.error);
            return;
        }

        setCookie("YEMEK_SESSION", tokenInfo.token, tokenInfo.expiration);

        window.location.href = "/";
    });
}

window.addEventListener("load", basla);