<?php
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["tarih"];
$qData = Utils::getQueryData($zorunluKeyler);

$tarih = $qData["tarih"];
if(!Utils::isIsoDate($tarih)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$kullanici = Auth::bizimkiKim();

$yu = new YemekUzmani($kullanici);

$yemek = $yu->yemekAl($tarih);
if($yemek === null){
    OutputManager::error("Yemek yok hocam.");
    die();
}

$yemek["verilenPuan"] = $yu->bizimkininYemegeVerdigiPuaniAl($tarih);

$yorumlar = $yu->yorumlariAl($tarih);

if($yorumlar === null){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

foreach($yorumlar as &$yorum){
    $yorum["bizimkininOyu"] = $yu->bizimkininYorumaVerdigiOyuAl($yorum["uuid"]);
    $yorum["yazarKullaniciAdi"] = $yu->kullaniciAl($yorum["yazarUuid"])["kullaniciAdi"];
}

OutputManager::outputJSON([
    "yemek" => $yemek,
    "yorumlar" => $yorumlar
]);


