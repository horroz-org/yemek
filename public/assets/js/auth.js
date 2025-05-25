function girisYapildiMi() {
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
        const xhr = apiGet("hesap/girisKontrol.php");
        if(xhr.status === 200){
            return JSON.parse(xhr.response);
        }
        else{
            return false;
        }
    } catch{
        // vah vah
        return false;
    }
}
