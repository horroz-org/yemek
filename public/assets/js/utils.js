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

function alertPopup(message) {
    alert(message);
}