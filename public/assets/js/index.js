const apiEndpoint = window.location.origin + "/api/";

function yorumEkle(id, sahip, tarih, metin, puan){
    const template = document.getElementById("yorum-template");
    const clone = template.content.cloneNode(true);

    clone.querySelector(".yorumkutu").id = id;
    clone.querySelector(".yorum-sahip").textContent = sahip;
    clone.querySelector(".yorum-tarih").textContent = tarih;
    clone.querySelector(".yorum-metin").textContent = metin;
    clone.querySelector(".vote-sayi").textContent = puan;

    document.getElementById("yorumlar-liste").appendChild(clone);
}

function yorumOyla(id, begenBool){
    // api gönder
}

// falan filan yarın bakacaz akşam akşam beni dellendirmeyin