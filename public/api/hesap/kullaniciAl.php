<?php
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Core\Logger;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["kullaniciAdi"];
$qData = Utils::getQueryData($zorunluKeyler);

$kullaniciAdi = $qData["kullaniciAdi"];

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
$sunlariVer = ["uuid", "kullaniciAdi", "prestij", "katilmaTarihi", "admin"];

$sonData = [];
foreach ($sunlariVer as $key) {
    $sonData[$key] = $adam[$key];
}

// rütbeyi ayarlıyak
$sonData["rutbe"] = Utils::rutbeYaziAl($adam["prestij"]);

OutputManager::outputJSON($sonData);