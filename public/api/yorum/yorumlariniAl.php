<?php
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Core\Logger;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["uuid", "limit", "eskiTarih"];
$postData = Utils::getPostData($zorunluKeyler);

$adamId = $postData["uuid"];
$limit = $postData["limit"];
$eskiTarih = $postData["eskiTarih"];

if(!Utils::validateUUIDv4($adamId) || !is_int($limit) || $limit <= 0){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

try {
    $eskiTarihObj = new DateTime($eskiTarih);
} catch (Exception $e) {
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

// başlayak artık

$yu = new YemekUzmani(null);
$adam = $yu->kullaniciAl($adamId);
if($adam === null){
    OutputManager::error("Böyle birisi yok sen kimin yorumlarını alıyosun lan denyo?");
    die();
}

$yorumlar = $yu->adaminYorumlariniAl($adamId, $limit, $eskiTarihObj);
foreach($yorumlar as &$yorum){
    $yorum["yazarKullaniciAdi"] = $adam["kullaniciAdi"];
}

// direkt yazıverelim
OutputManager::outputJSON($yorumlar);
