function getCookie(name) {
    const value = document.cookie
        .split('; ')
        .find(row => row.startsWith(name + '='))
        ?.split('=')[1] || null;

    return value ? decodeURIComponent(value) : null;
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

function alertPopup(message) {
    alert(message);
}