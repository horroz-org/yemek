function basla() {
    tokenParam = getQueryParam("t");

    if (tokenParam === null) {
        alert("Token yok, sen neler içtin oğlum? Nerelere gldin sen buralara geldin buralara.");
        window.location.href = "/";
        return;
    }
    
    var href = "/api/hesap/epostaDogrula.php?t=" + tokenParam;
    document.body.innerHTML = "Yönlendiriyoz.<br>Yönlenmediysen <a href='" + href + "'>buraya</a> bas.";

    window.location.href = href;
}

window.addEventListener("load", basla);