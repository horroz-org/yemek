<?php
/**
 * JSON şöyle gelecek:
 * {
 *     "yemekTarih": "2025-01-01",
 *     "yorum": "Selamın aleyküm.",
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

$zorunluKeyler = ["yemekTarih", "yorum", "ustYorumId"];
$postData = Utils::getPostData($zorunluKeyler);

$yemekTarih = $postData["yemekTarih"];
$yorum = $postData["yorum"];
$ustYorumId = $postData["ustYorumId"];

if(!Utils::isIsoDate($yemekTarih) || !is_string($yorum) || ($ustYorumId !== null && !is_string($ustYorumId))){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

// mixteyp
if(time() < strtotime($yemekTarih)){
    OutputManager::error("Senin zaman makinan mı var ulan dingil?");
    die();
}

// yoruma da bakak
if(!Utils::yorumKontrol($yorum)){
    OutputManager::error("Bu nası yorum oğlum? Manyak mısın sen? Kafayı mı yedin?");
    die();
}

$bizimki = Auth::bizimkiKim();
if($bizimki === null){
    OutputManager::error("Ulan adama bak hem giriş yapmamış hem de yorum yazacakmış. Şımarık.");
    die();
}

$yu = new YemekUzmani($bizimki);
$guncelYorum = $yu->yorumYaz($yemekTarih, $yorum, $ustYorumId);

// zaten bizimki yorum yazdı, onun adını hemen koyalım
$guncelYorum["yazarKullaniciAdi"] = $bizimki["kullaniciAdi"];
// zaten daha yeni yazdı oğlum ne ara beğenecek?
$guncelYorum["bizimkininOyu"] = null;

if($guncelYorum === null){
    OutputManager::error("Sen kötü şeyler yapmışsın.");
    die();
}

OutputManager::outputJSON($guncelYorum);
