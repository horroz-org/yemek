async function girisYapildiMi() {
    /** eğer cookie varsa api çağrısı yapalım
      * yoksa direkt false zaten ekstra api çağrısı olmasın
      * 
      * base62 denedim olmadı burda o yüzden direkt yapacam 
      */
    const token = getCookie("YEMEK_SESSION");
    if (token === null) {
        return false;
    }

    const parts = token.split(".");
    if(parts.length != 2){
        // vay vay
        return false;
    }

    try{
        const xhr = await apiGet("hesap/girisKontrol.php");
        if(xhr.status === 200){
            return xhr.response;
        }
        else{
            // sıkıntı çıkmasın
            deleteCookie("YEMEK_SESSION");
            
            return false;
        }
    } catch{
        // vah vah
        return false;
    }
}

/**
 * Bakalım giriş yapmış mı
 * yapmamışsa cevap yaz yorum yaz yemeğe puan verme yoruma oy verme report butonları khapaly olacak
 * aslında report açık olabilir ama her yorum için 1 kere tutarım dbde
 * ve uyarı çıkar eğer giriş yapmadan rapor ederseniz ip'niz kaydedilir diye
 * 
 * giriş yapmışsa da kullanıcı adını sağ üste yazdıracağım ben
*/
async function authBak() {
    var userData = await girisYapildiMi();
    if (userData === false) {
        anonimAyarla();
        return;
    }

    kullanici = userData;
    adamAyarla(userData);
}

function anonimAyarla() {
    kullanici = null;

    document.querySelectorAll(".topbar-kayit-giris-buton").forEach(function (element) {
        element.style.removeProperty("display");
    });

    document.querySelectorAll(".topbar-profil-butonlar").forEach(function (element) {
        element.style.display = "none";
    });
}

function adamAyarla(kullanici) {
    document.querySelectorAll(".topbar-kayit-giris-buton").forEach(function (element) {
        element.style.display = "none";
    });

    document.querySelectorAll(".topbar-profil-butonlar").forEach(function (element) {
        element.style.removeProperty("display");
    });

    document.querySelector(".topbar-kullanici-adi").textContent = kullanici.kullaniciAdi;
}