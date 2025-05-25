const apiUrl = window.location.origin + "/api/";

function apiGet(endpoint) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", apiUrl + endpoint, false);

    try {
        xhr.send();
        return xhr;
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
        return xhr;
    } catch (error) {
        throw new Error("Hata.");
    }
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

class Siralama {
    varsayilan(a, b) { return this.enYuksekOy(a, b); }
    enYuksekOy(a, b) { return (b.like - b.dislike) - (a.like - a.dislike); }
    enYeni(a, b) { return new Date(b.tarih) - new Date(a.tarih); }
    enEski(a, b) { return new Date(a.tarih) - new Date(b.tarih); }
};

function yorumSirala(tree, siralama) {
    tree.sort(siralama);
    tree.forEach(function (yorum) {
        if (yorum.children.length > 0) {
            yorumSirala(yorum.children, siralama);
        }
    });
}

function topluAl(tarih) {
    const xhr = apiGet("yemek/toplual.php?tarih=" + tarih);
    try{
        return JSON.parse(xhr.response);
    } catch{
        throw new Error("Fop.");
    }
}

function yorumOyla(id, begenBool) {
    return apiPost("yorum/oyla.php", {
        id: id,
        begen: begenBool
    });
}
