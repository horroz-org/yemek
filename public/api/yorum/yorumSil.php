<?php
/**
 * JSON şöyle gelecek:
 * {
 *     "yorumUuid": "d71876a5-87f4-4ebd-9022-e1cc4b5972cc",
 * }
 * 
 * Sildikten sonra ne döndürsün
 * bişey döndürmesin status ok filan yazsın
 * döndürecek bişey yok çünkü
 * 
 * yada direkt silinen yorumu döndürsün
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
    OutputManager::error("Ulan sen manyak mısın? Yılmadın mı? Sen kafayı mı yedin? Yeter be? Yetti?");
    die();
}

$yu = new YemekUzmani($bizimki);
$silinenYorum = $yu->yorumSil($yorumUuid);

if($silinenYorum === null){
    OutputManager::error("Sen çooook ama çok kötü şeyler yapmışsın. (Şaka)");
    die();
}

OutputManager::outputJSON($silinenYorum);
