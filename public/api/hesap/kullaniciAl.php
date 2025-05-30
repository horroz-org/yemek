<?php
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Core\Logger;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["kullaniciAdi"];
$postData = Utils::getQueryData($zorunluKeyler);

$kullaniciAdi = $postData["kullaniciAdi"];

// alıverdirdirelim
$yu = new YemekUzmani(null);
$adam = $yu->kullaniciAlParametreIle("kullaniciAdi", $kullaniciAdi);
if($adam === null){
    OutputManager::error("Böyle birisi yokuleyn?");
    die();
}

// sadece yüzeysel alıyoruz, şunları alalım
// admin vermesek te olur aslında ama belki ilerde
// bu kullanıcı yönetici haklarına sahiptir filan
// yazabiliriz profile, kalsın o yüzden
$sunlariVer = ["uuid", "kullaniciAdi", "prestij", "rutbe", "katilmaTarihi", "admin"];

$sonData = [];
foreach ($sunlariVer as $key) {
    $sonData[$key] = $adam[$key];
}

OutputManager::outputJSON($sonData);