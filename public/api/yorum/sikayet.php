<?php
/**
 * JSON şöyle gelecek:
 * {
 *     "yorumUuid": "2025-01-01"
 * }
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["yorumUuid"];
$postData = Utils::getPostData($zorunluKeyler);

$yorumUuid = $postData["yorumUuid"];

if(!Utils::validateUUIDv4($yorumUuid)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$bizimki = Auth::bizimkiKim();
if($bizimki === null){
    OutputManager::error("Ulan adama bak hem giriş yapmamış hem de şikayetçi oluyor. Bi giriş yap ta sonra şikayet edersin. Ama ciddiyse destek@horroz.org'a yaz, biz hallederiz.");
    die();
}

$yu = new YemekUzmani($bizimki);

// yorum var mı peki?
$yorum = $yu->yorumBilgisiAl($yorumUuid);
if($yorum === null){
    OutputManager::error("Böyle bi yorum yok. Sen şeytan mısın?");
    die();
}

// bakalım şikayet etmiş mi
if($yu->sikayetAl($yorumUuid) !== null){
    OutputManager::error("Zaten şikayet etmişsin.");
    die();
}

// artık şikayet edebilir
$sikayetBilgi = $yu->sikayetEt($yorumUuid);
if($sikayetBilgi === null){
    OutputManager::error("Sen kötü şeyler yapmışsın.");
    die();
}

OutputManager::outputJSON($sikayetBilgi);
