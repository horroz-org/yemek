<?php
/**
 * Json şöyle gelecek:
 * {
 *     "yorumUuid": "c52cb05d-6c6d-4f23-ace3-b6af796ebe0a",
 *     "like": true
 * }
 * 
 * true->like, false->dislike
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["yorumUuid", "like"];
$postData = Utils::getPostData($zorunluKeyler);

$yorumUuid = $postData["yorumUuid"];
$like = $postData["like"];

if(!Utils::validateUUIDv4($yorumUuid) || !is_bool($like)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$bizimki = Auth::bizimkiKim();
if($bizimki === null){
    OutputManager::error("Vallahi artık diyecek bir şey bulamıyorum. Şımarık.");
    die();
}

$yu = new YemekUzmani($bizimki);
$guncelOylar = $yu->yorumOyVer($yorumUuid, $like);

if($guncelOylar === null){
    OutputManager::error("İnanılmaz. (Şaka şaka)");
    die();
}

OutputManager::outputJSON($guncelOylar);
