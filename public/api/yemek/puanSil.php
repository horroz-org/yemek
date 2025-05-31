<?php
/**
 * JSON body var bunda, puanVer gibi ama puan yok.
 * {
 *     "tarih": "2025-01-01"
 * }
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$zorunluKeyler = ["tarih"];
$postData = Utils::getPostData($zorunluKeyler);

$tarih = $postData["tarih"];

if(!Utils::isIsoDate($tarih)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$bizimki = Auth::bizimkiKim();
if($bizimki === null){
    OutputManager::error("Ulan sen nesin? Hem giriş yapmamış, hem de puan silmeye çalışıyo denyoya bak?");
    die();
}

$yu = new YemekUzmani($bizimki);
$guncelPuan = $yu->yemekPuanSil($tarih);

if($guncelPuan === null){
    OutputManager::error("Kötü kötü şeyler getirdiler buralara emi?");
    die();
}

OutputManager::outputJSON($guncelPuan);
