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
use Core\Dotenv;
use Core\Logger;
use Yemek\YemekUzmani;
use Yemek\Auth;
use Yemek\Mail;

$dogrulamaCooldown = Dotenv::getValue("DOGRULAMA_COOLDOWN");

$zorunluKeyler = ["kullaniciAdi", "sifre"];
$postData = Utils::getPostData($zorunluKeyler);

$kullaniciAdi = $postData["kullaniciAdi"];
$sifre = $postData["sifre"];

if(!is_string($kullaniciAdi) || !is_string($sifre)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

$bizimki = Auth::bizimkiKim();
if($bizimki !== null){
    OutputManager::error("Ulan sen giriş yapmışsın? Kimi kandırıyon?");
    die();
}

// şimdi düzgün giriş yapabiliriz artık
// istenen adamı alalım
$yu = new YemekUzmani(null);
$adam = $yu->kullaniciAlParametreIle("kullaniciAdi", $kullaniciAdi);
if($adam === null){
    OutputManager::error("Lan böyle birisi yok?");
    die();
}

//Eemail doğrulanmış mı?
if(!$adam["emailDogrulandi"]){
    // email doğrulanmamış

    $dogrulamaZamani = strtotime($adam["dogrulamaNeZamanGonderdik"]);
    $simdi = time();

    $zdiff = $simdi - $dogrulamaZamani;

    // doğrulama mail cooldown geçmiş mi?
    if($zdiff >= $dogrulamaCooldown * 60){
        // geçmiş, yeniden gönder

        if(!Mail::dogrulamaGonder($adam["email"])){
            // zaten Mail::mailGonder'de loglanıyor, şimdi loga gerek yok
            OutputManager::error("Fena sıkıntı olmuş galiba, bunu birine söyle.");
            die();
        }
        if(!$yu->adamaSimdiDogrulamaGonderdik($adam["uuid"])){
            Logger::error("adamaSimdiDogrulamaGonderdik sıkıntı oldu.\n" . print_r($adam, true));
            OutputManager::error("Fena sıkıntı olmuş galiba, bunu birine söyle.");
            die();
        }

        OutputManager::error("Doğrulama kodunu yine gönderdik, mailine baksana oğlum.");
        die();
    }
    else{
        // geçmemiş, sabırsıza küfür et
        $kalanSure = 60 * $dogrulamaCooldown - $zdiff;
        OutputManager::error("Mailine kod göndermiştik, girmemişsin. $kalanSure saniye sonra yine dene, o zaman yine gönderelim hala gelmediyse.");
        die();
    }
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
