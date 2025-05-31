<?php
/**
 * JSON body var bunda.
 * {
 *     "puan": 5,
 *     "tarih": "2025-01-01"
 * }
 * gibi.
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["puan", "tarih"];
$postData = Utils::getPostData($zorunluKeyler);

$puan = $postData["puan"];
$tarih = $postData["tarih"];

if(!is_int($puan) || !Utils::isIsoDate($tarih)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

// gelecekteki yemeğe nasıl puan verecen lan dingil?
if(time() < strtotime($tarih)){
    OutputManager::error("Senin zaman makinan mı var ulan?");
    die();
}

$bizimki = Auth::bizimkiKim();
if($bizimki === null){
    OutputManager::error("Ulan sen kendini ne sandın? Giriş yapmadan puan mu verilir denyo? Bunlarla uğraşacağına bi hesap açıver?");
    die();
}

$yu = new YemekUzmani($bizimki);
$guncelPuan = $yu->yemegePuanVer($tarih, $puan);

if($guncelPuan === null){
    OutputManager::error("Kötü kötü şeyler getirdiler buralara emi? Sen gerizekalı mısın oğlum?");
    die();
}

OutputManager::outputJSON($guncelPuan);
