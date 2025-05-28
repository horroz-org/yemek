<?php
/**
 * {
 *     "kullaniciAdi": "Adam Öldür 1903",
 *     "sifre": "easlanMex34"
 * }
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;
use Yemek\Mail;

// dakika
const dogrulamaCooldown = 5;

$zorunluKeyler = ["kullaniciAdi", "sifre"];
$postData = Utils::getPostData($zorunluKeyler);

$kullaniciAdi = $postData["kullaniciAdi"];
$sifre = $postData["sifre"];

if(!is_string($kullaniciAdi) || !is_string($sifre)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$bizimki = Auth::bizimkiKim();
if($bizimki !== null){
    if($bizimki["emailDogrulandi"]){
        OutputManager::error("Ulan sen giriş yapmışsın? Kimi kandırıyon?");
        die();
    }
    // email doğrulanmamış

    $dogrulamaZamani = strtotime($bizimki["dogrulamaNeZamanGonderdik"]);
    $simdi = time();

    $zdiff = $simdi - $dogrulamaZamani;

    // doğrulama mail cooldown geçmiş mi?
    if($zdiff >= dogrulamaCooldown * 60){
        // geçmiş, yeniden gönder

        Mail::dogrulamaGonder($bizimki);

        OutputManager::error("Doğrulama kodunu yine gönderdik, mailine baksana oğlum.");
        die();
    }
    else{
        // geçmemiş, sabırsıza küfür et

        OutputManager::error("Mailine kod göndermiştik, girmemişsin. $zdiff saniye sonra yine dene, o zaman yine gönderelim hala gelmediyse.");
        die();
    }
}

// şimdi düzgün giriş yapabiliriz artık
// istenen adamı alalım
$yu = new YemekUzmani(false);
$adam = $yu->kullaniciAlAdIle($kullaniciAdi);
if($adam === null){
    OutputManager::error("Lan böyle birisi yok?");
    die();
}

$beklenenHash = $adam["hash"];
if(!password_verify($sifre, $beklenenHash)){
    OutputManager::error("Yanlış şifre.");
    die();
}

$expiration = (new \DateTime())->modify("+1 year");
$token = Auth::generateToken($adam, $expiration);

OutputManager::outputJSON([
    "token" => $token,
    "expiration" => $expiration->format("Y-m-d H:i:s") // timestamp döndürelim hadi bi farklılık olsun
]);
