const apiUrl = window.location.origin + "/api/";

async function apiGet(endpoint) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", apiUrl + endpoint, true);
        xhr.responseType = "json";
        
        xhr.onload = function () {
            resolve(xhr);
        };

        xhr.onerror = function () {
            reject(new Error("Internet hatası."));
        };

        xhr.ontimeout = function () {
            reject(new Error("Timeout."));
        };

        try {
            xhr.send();
        } catch (error) {
            reject(new Error("Ulen?"));
        }
    });
}

async function apiPost(endpoint, data) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", apiUrl + endpoint, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.responseType = "json";

        xhr.onload = function () {
            resolve(xhr);
        };

        xhr.onerror = function () {
            reject(new Error("Internet hatası."));
        };

        xhr.ontimeout = function () {
            reject(new Error("Timeout."));
        };

        try {
            xhr.send(JSON.stringify(data));
        } catch (error) {
            reject(new Error("Ulen?"));
        }
    });
}

async function topluAl(tarih) {
    const xhr = await apiGet("yemek/toplual.php?tarih=" + tarih);
    return xhr.status === 200 ? xhr.response : null;
}

async function yorumOyVer(yorumUuid, likeDislike) {
    var xhr = await apiPost("yorum/oyver.php", {
        yorumUuid: yorumUuid,
        like: likeDislike
    });

    return xhr.status === 200 ? xhr.response : null;
}

async function yorumOySil(yorumUuid) {
    var xhr = await apiPost("yorum/oysil.php", {
        yorumUuid: yorumUuid,
    });

    return xhr.status === 200 ? xhr.response : null;
}

async function yemegePuanVer(puan, tarih) {
    var xhr = await apiPost("yemek/puanver.php", {
        puan: puan,
        tarih: tarih
    });

    return xhr.status === 200 ? xhr.response : null;
}

async function yemekPuaniSil(tarih) {
    var xhr = await apiPost("yemek/puansil.php", {
        tarih: tarih
    });

    return xhr.status === 200 ? xhr.response : null;
}

async function yorumYap(yorumObj){
    var xhr = await apiPost("yorum/yorumyap.php", yorumObj);

    return xhr.status === 200 ? xhr.response : null;
}

async function yorumSil(yorumUuid) {
    var xhr = await apiPost("yorum/yorumsil.php", {
        yorumUuid: yorumUuid
    });

    return xhr.status === 200 ? xhr.response : null;
}

// cookie değerini veya error diye json veriyoz ya onu döndürür
async function girisYap(kullaniciAdi, sifre){
    var xhr = await apiPost("hesap/giris.php", {
        kullaniciAdi: kullaniciAdi,
        sifre: sifre
    });

    return (xhr.status === 200 || xhr.status === 400) ? xhr.response : null;
}

async function kayitOl(kullaniciAdi, eposta, sifre) {
    var xhr = await apiPost("hesap/kayit.php", {
        kullaniciAdi: kullaniciAdi,
        eposta: eposta,
        sifre: sifre
    });

    return (xhr.status === 200 || xhr.status === 400) ? xhr.response : null;
}

// profil görüntüleme fln. için kullanıcı bilgisi al
async function kullaniciAl(kullaniciAdi) {
    var xhr = await apiGet("hesap/kullaniciAl.php?kullaniciAdi=" + kullaniciAdi);
    return xhr.status === 200 ? xhr.response : null;
}

// adamın bütün yorumlarını al
// eskiTarih'in açıklaması YemekUzmani.php'de var
async function yorumlariniAl(uuid, limit, eskiTarih) {
    var xhr = await apiPost("yorum/yorumlariniAl.php", {
        uuid: uuid,
        limit: limit,
        eskiTarih: eskiTarih
    });

    return xhr.status === 200 ? xhr.response : null;
}