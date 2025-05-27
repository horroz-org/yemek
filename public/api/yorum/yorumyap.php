<?php
/**
 * JSON şöyle gelecek:
 * {
 *     "yemekTarih": "2025-01-01",
 *     "yorum": "Selamın aleyküm.",
 *     "herkeseAcik": true,
 *     "ustYorumId": null
 * }
 * 
 * Yorum yazınca yorumu döndürüyo
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["yemekTarih", "yorum", "herkeseAcik", "ustYorumId"];
$postData = Utils::getPostData($zorunluKeyler);

$yemekTarih = $postData["yemekTarih"];
$yorum = $postData["yorum"];
$herkeseAcik = $postData["herkeseAcik"];
$ustYorumId = $postData["ustYorumId"];

if(!Utils::isIsoDate($yemekTarih) || !is_string($yorum) || !is_bool($herkeseAcik) || ($ustYorumId !== null && !is_string($ustYorumId))){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$bizimki = Auth::bizimkiKim();
if($bizimki === null){
    OutputManager::error("Ulan adama bak hem giriş yapmamış hem de yorum yazacakmış. Şımarık.");
    die();
}

$yu = new YemekUzmani($bizimki);
$guncelYorum = $yu->yorumYaz($yemekTarih, $yorum, $herkeseAcik, $ustYorumId);

// zaten bizimki yorum yazdı, onun adını hemen koyalım
$guncelYorum["yazarKullaniciAdi"] = $bizimki["kullaniciAdi"];
// zaten daha yeni yazdı oğlum ne ara beğenecek?
$guncelYorum["bizimkininOyu"] = null;

if($guncelYorum === null){
    OutputManager::error("Sen kötü şeyler yapmışsın.");
    die();
}

OutputManager::outputJSON($guncelYorum);
