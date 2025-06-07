<?php
/**
 * Json şöyle gelecek:
 * {
 *     "yorumUuid": "c52cb05d-6c6d-4f23-ace3-b6af796ebe0a",
 * }
 * 
 * true->like, false->dislike
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
    OutputManager::error("Sen ama iyice şımardın, kaç kere uyardım seni. Şebek herif, rezil herifsin sen.");
    die();
}

$yu = new YemekUzmani($bizimki);

// bakalım öyle bi yorum var mı
$yorumBilgi = $yu->yorumBilgisiAl($yorumUuid);
if($yorumBilgi === null || $yorumBilgi["kaldirildi"] === 1){
    OutputManager::error("Bu yorumu kaldırmışlar oğlum, dağa kaldırmışlar oğlum...");
    die();
}

// artık silebilirsin
$guncelOylar = $yu->yorumOySil($yorumUuid);
if($guncelOylar === null){
    OutputManager::error("Aynanılmaz. (Xops)");
    die();
}

OutputManager::outputJSON($guncelOylar);
