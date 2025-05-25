<?php
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

if(!isset($_GET["tarih"])){
    OutputManager::error("Tarih niye vermedin?");
    die();
}

$tarih = trim($_GET["tarih"]);
if(!Utils::isIsoDate($_GET["tarih"])){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$kullanici = Auth::bizimkiKim();

$yu = new YemekUzmani($kullanici);

$yemek = $yu->yemekAl($tarih);
$yemek["verilenPuan"] = $yu->bizimkininYemegeVerdigiPuaniAl($tarih);

$yorumlar = $yu->yorumlariAl($tarih);
foreach($yorumlar as &$yorum){
    $yorum["bizimkininOyu"] = $yu->bizimkininYorumaVerdigiOyuAl($yorum["uuid"]);
    $yorum["yazarKullaniciAdi"] = $yu->kullaniciAl($yorum["yazarUuid"])["kullaniciAdi"];
}

OutputManager::outputJSON([
    "yemek" => $yemek,
    "yorumlar" => $yorumlar
]);


