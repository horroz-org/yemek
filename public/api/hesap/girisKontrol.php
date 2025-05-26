<?php
/**
 * Adam giriş yapmış mı yapmamış mı kontrol eder.
 * Giriş yapmışsa 200 OK adamın bilgileri döndürür, yapmamışsa 401 Unauthorized döndürür.
 * 
 * Cookie "YEMEK_SESSION" hmac olacak, detaylar Auth.php'de
 * Adamın bilgiler
 * uuid -> uid
 * kullaniciAdi -> uname
 * exp -> session bitiş tarihi
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\Auth;
use Yemek\YemekUzmani;

// Buradan sonrası aslında Auth.php'deydi ama Exception filan vardı mesajlar için
// O yüzden bu burda kalsın şimdilik, belki geri taşırız sonra

if(!isset($_COOKIE["YEMEK_SESSION"])){
    OutputManager::error("Sen ne iş?", 401);
    die();
}

$token = $_COOKIE["YEMEK_SESSION"];
$tokenData = Auth::verifyToken($token);
if($tokenData === false){
    OutputManager::error("Yaş yetmiş, iş bitmiş.", 401);
    die();
}

$yu = new YemekUzmani(false); // anonim çünkü kontrol noktasındayız oğlum
$kullanici = $yu->kullaniciAlGuvenli($tokenData["uid"]);

if($kullanici === null){
    OutputManager::error("Bloks.", 401);
    die();
}

OutputManager::outputJSON($kullanici);
