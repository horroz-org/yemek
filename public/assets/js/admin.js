function basla() {
    uiAyarla();

    ekranDegistir("kullanici");
}

function ekranDegistir(isim) {
    document.querySelector(".admin-aktif-ekran").remove();
    
    let div = document.createElement("div");
    div.classList.add("admin-aktif-ekran");
    
    let sayfaAdi = isim;
    const clone = document.getElementById("at-" + sayfaAdi).content.cloneNode(true);
    
    div.appendChild(clone);

    document.getElementById("admin-anadiv").appendChild(div);
}

function uiAyarla() {
    document.querySelectorAll(".asb-buton").forEach(buton => {
        buton.addEventListener("click", () => {
            try {
                ekranDegistir(buton.id.split("-")[1]);

                let secili = document.querySelector(".asb-secili");
                secili.classList.remove("asb-secili");

                buton.classList.add("asb-secili");
            } catch {}
        });
    });
}

window.addEventListener("load", basla);