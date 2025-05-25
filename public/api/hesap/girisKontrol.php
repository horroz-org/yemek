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

try {
    $kullanici = Auth::girisYapiliMi();
} catch (\Throwable $th) {
    OutputManager::error($th->getMessage(), 401);
    die();
}

OutputManager::outputJSON($kullanici);
