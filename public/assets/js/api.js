const apiUrl = window.location.origin + "/api/";

async function apiGet(endpoint) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", apiUrl + endpoint, true);

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(xhr);
            } else {
                reject(new Error(`HTTP Error: ${xhr.status}`));
            }
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
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(xhr);
            } else {
                reject(new Error(`HTTP Error: ${xhr.status}`));
            }
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

async function topluAl(tarih) {
    const xhr = await apiGet("yemek/toplual.php?tarih=" + tarih);
    try{
        return JSON.parse(xhr.response);
    } catch{
        throw new Error("Fop.");
    }
}

async function yorumOyla(id, begenBool) {
    return await apiPost("yorum/oyla.php", {
        id: id,
        begen: begenBool
    });
}
