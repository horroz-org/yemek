var kullanici = null;

async function basla() {
    uiAyarla();
    await authBak();

    if (kullanici !== null) {
        var gkfkElement = document.getElementById("giris-kayit-form-kutu");
        gkfkElement.innerHTML = "Zaten giriş yapmışsın.";
        gkfkElement.style.textAlign = "center";
        gkfkElement.innerHTML += "<a href='/'>Ana sayfaya git</a>";
    }
}

function formMesajYaz(mesaj, hata = true) {
    var hataMesajiElement = document.getElementById("hata-mesaji");
    hataMesajiElement.style.color = hata ? "red" : "green";
    hataMesajiElement.textContent = mesaj;
}

function bilgileriKontrolEt(kullaniciAdi, eposta, sifre, kabulEdiyorum) {
    if (kullaniciAdi === "" || sifre === "" || eposta === "" || !kabulEdiyorum) {
        formMesajYaz("Sen bunları bomboş bırakmaya utanmıyor musun?");
        return false;
    }

    formMesajYaz("");
    return true;
}

function uiAyarla() {
    document.getElementById("giris-kayit-buton").addEventListener("click", async function () {
        if (this.classList.contains("kapali-buton")) {
            return;
        }
        this.classList.add("kapali-buton");

        var kullaniciAdiElement = document.getElementById("kullanici-adi-input");
        var epostaElement = document.getElementById("eposta-input");
        var sifreElement = document.getElementById("sifre-input");
        var kabulEdiyorumElement = document.getElementById("kabul-ediyorum");

        var kullaniciAdi = kullaniciAdiElement.value.trim();
        var eposta = epostaElement.value.trim();
        var sifre = sifreElement.value.trim();
        var kabulEdiyorum = kabulEdiyorumElement.checked; // client side olsun sadece

        if (!bilgileriKontrolEt(kullaniciAdi, eposta, sifre, kabulEdiyorum)) {
            this.classList.remove("kapali-buton");
            return;
        }

        var kayitInfo = await kayitOl(kullaniciAdi, eposta, sifre);
        if (kayitInfo === null) {
            formMesajYaz("Değişik bir şeyler oldu, sıçtık.");
            this.classList.remove("kapali-buton");
            return;
        }

        // hata verdiyse hatayı yazak kırmızıylan
        if ("error" in kayitInfo) {
            formMesajYaz(kayitInfo.error);
            this.classList.remove("kapali-buton");
            return;
        }

        if("info" in kayitInfo){
            // yok efendim mailinize doğrulama geldi yok efendim 5 dk sonra isterseniz yeniden kod alabilirsiniz giriş kısmından
            // falan filan
            var gkfkElement = document.getElementById("giris-kayit-form-kutu");
            gkfkElement.style.textAlign = "center";
            gkfkElement.innerHTML = kayitInfo.info;
            gkfkElement.innerHTML += "<a href='/giris/'>Giriş Yap</a>";
            return;
        }

        formMesajYaz("Bu ne oğlum? Git birine de ki, böyle bi mesaj yazdı de. Harbiden de ama ciddiyim.");

        this.classList.remove("kapali-buton");
    });
}

window.addEventListener("load", basla);