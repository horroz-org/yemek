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
    return date.toISOString().split('T')[0];
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