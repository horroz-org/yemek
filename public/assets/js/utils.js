function getCookie(name) {
    const value = document.cookie
        .split('; ')
        .find(row => row.startsWith(name + '='))
        ?.split('=')[1] || null;

    return value ? decodeURIComponent(value) : null;
}

function setCookie(name, value, exp) {
    let expires = "expires=" + (new Date(exp)).toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

function deleteCookie(name){
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function isoDate(date){
    const pad = n => n.toString().padStart(2, '0');

    const Y = date.getFullYear();
    const m = pad(date.getMonth() + 1);
    const d = pad(date.getDate());

    return `${Y}-${m}-${d}`;
}

function isIsoDate(text){
    const regex = /^\d{4}-([0][1-9]|1[0-2])-([0][1-9]|[1-2]\d|3[01])$/;
    return regex.test(text);
}

function setQueryParam(parameter, value){
    const url = new URL(window.location.href);
    url.searchParams.set(parameter, value);
    window.history.pushState(null, '', url.toString());
}

function getQueryParam(parameter){
    const paramsString = window.location.search;
    const searchParams = new URLSearchParams(paramsString);
    if(searchParams.has(parameter)){
        return searchParams.get(parameter);
    }

    return null;
}

// mesela https://bok.net/pro.php?ikizkenar=28 ise
// path /pro.php?ikizkenar=28 olur
function getUrlPath(){
    // incelediğim keadarı ile böyle
    return window.location.pathname + window.location.search;
}

function alertPopup(message) {
    alert(message);
}

// ödünç alındı: https://www.media-division.com/easy-human-readable-date-difference/
function zamanFarki(date1, date2) {
    if (!(date1 instanceof Date && date2 instanceof Date))
        throw new RangeError('Invalid date arguments');

    const timeIntervals = [31536000, 2628000, 604800, 86400, 3600, 60, 1];
    const intervalNames = ['yıl', 'ay', 'hafta', 'gün', 'saat', 'dakika', 'saniye'];
    const diff = Math.abs(date2.getTime() - date1.getTime()) / 1000;
    const index = timeIntervals.findIndex(i => (diff / i) >= 1);
    if(index === -1){
        return "şimdi";
    }

    const n = Math.floor(diff / timeIntervals[index]);
    const interval = intervalNames[index];
    return n.toString() + " " + interval + " önce";
}

function puanTrunc(puan, basamakSayisi = 1){
    var onUzeriBasamakSayisi = 10 ** basamakSayisi;
    return Math.trunc(puan * onUzeriBasamakSayisi) / onUzeriBasamakSayisi;
}

function giriseGit(){
    window.location.href = "/giris/?r=" + encodeURIComponent(getUrlPath());
}

function htmlEscape(str){
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function yorumIsle(yorum){
    var metin = yorum;

    metin = htmlEscape(metin);

    // etiketleri <a> yapacaz,
    // mesela @pro
    // <a href='/profil/?u=pro'>@pro</a>
    // olacak
    var regex = /@([a-zA-Z0-9_]{3,})/g;
    metin = metin.replace(regex, (match, kullanici) => {
        return "<a href='/profil/?u=" + kullanici + "'>" + match + "</a>";
    });

    return metin;
}

// klasik dediğim Y-m-d H:i:s
// anasını siktiğimin javascriptinde tarih formatlamak için düzgün bişey yok
function klasikTarihSaatFormat(date){
    const pad = n => n.toString().padStart(2, '0');

    const Y = date.getFullYear();
    const m = pad(date.getMonth() + 1);
    const d = pad(date.getDate());

    const H = pad(date.getHours());
    const i = pad(date.getMinutes());
    const s = pad(date.getSeconds());

    return `${Y}-${m}-${d} ${H}:${i}:${s}`;
}

// sunucuda tarafında da var bu
// orayı değiştirirken burayı da değiştirin
// yada burayı değiştirirken orayı da değiştirin
// anladınız siz
//
// sabitleri direkt Utils.php'den kıpırdım, merak etmeyin.
const minimumYorumKarakterSayisi = 3;
const maksimumYorumKarakterSayisi = 400;
const maksimumYorumSatirSayisi = 4;
const izinVerilenYorumKarakterleri = "abcçdefgğhıijklmnoöpqrsştuüvwxyzABCÇDEFGĞHIİJKLMNOÖPQRSŞTUÜVWXYZ01234567890_.,!?-:;\"'()[]{}<>@#&$%*+=/\\|^~`\n ";
function yorumKontrol(yorum){
    // maks min karakter
    if (minimumYorumKarakterSayisi > yorum.length || yorum > maksimumYorumKarakterSayisi) {
        return "Yorumun çok uzun oğlum. " + minimumYorumKarakterSayisi + "-" + maksimumYorumKarakterSayisi + " karakter arası lazım.";
    }

    for (let i = 0; i < yorum.length; i++) {
        const harf = yorum[i];
        if (!izinVerilenYorumKarakterleri.includes(harf)) {
            return "Yorumunda kötü karakterler var: " + harf;
        }
    }

    // maksimum satır sayısı
    const satirlar = yorum.split('\n');
    if (satirlar.length > maksimumYorumSatirSayisi) {
        return "Maksimum " + maksimumYorumSatirSayisi + " satır yazabilirsin.";
    }

    return true;
}

/**
 * Mesela factor 7/10'sa 10 midpoint var 7. alıyoz
 * palete ihtiyacım yoktu böyle yaptım
 * 
 * "rgb(...)" diye döndürüyo
 */
function colInterpolate(rgb1, rgb2, factor) {
    const r = Math.round(rgb1[0] + (rgb2[0] - rgb1[0]) * factor);
    const g = Math.round(rgb1[1] + (rgb2[1] - rgb1[1]) * factor);
    const b = Math.round(rgb1[2] + (rgb2[2] - rgb1[2]) * factor);
    return `rgb(${r}, ${g}, ${b})`;
}