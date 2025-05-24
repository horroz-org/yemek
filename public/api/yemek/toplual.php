<?php

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;

if(!isset($_GET["tarih"])){
    OutputManager::error("Tarih niye vermedin?");
    die();
}

$tarih = trim($_GET["tarih"]);
if(!Utils::isIsoDate($_GET["tarih"])){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$anonim = false; //Auth::control();
$adaminId = "pro-uuid"; //Auth::adaminIdAl();

$yu = new YemekUzmani($anonim);

$yemek = $yu->yemekAl($tarih);
$yemek["verilenPuan"] = $yu->adaminYemegeVerdigiPuaniAl($adaminId, $tarih);

$yorumlar = $yu->yorumlariAl($tarih);
foreach($yorumlar as &$yorum){
    $yorum["adaminOyu"] = $yu->adaminYorumaVerdigiOyuAl($adaminId, $yorum["uuid"]);
    $yorum["yazarKullaniciAdi"] = $yu->kullaniciAl($yorum["yazarUuid"])["kullaniciAdi"];
}

OutputManager::outputJSON([
    "yemek" => $yemek,
    "yorumlar" => $yorumlar
]);


