<?php
/**
 * {
 *     "kullaniciAdi": "Adam Öldür 1903",
 *     "eposta": "kafakesme@hacettepe.edu.tr"
 *     "sifre": "easlanMex34"
 * }
 */

require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Core\Dotenv;
use Yemek\YemekUzmani;
use Yemek\Auth;
use Yemek\Mail;

// dakika
const dogrulamaCooldown = 5;

$zorunluKeyler = ["kullaniciAdi", "eposta", "sifre"];
$postData = Utils::getPostData($zorunluKeyler);

$kullaniciAdi = $postData["kullaniciAdi"];
$eposta = $postData["eposta"];
$sifre = $postData["sifre"];

if(!is_string($kullaniciAdi) || !is_string($eposta) || !is_string($sifre)){
    Utils::buAdamBiseylerYapmayaCalisiyo();
}

// bizim mal giriş yapmış mı diye bakalım
$bizimki = Auth::bizimkiKim();
if($bizimki !== null){
    OutputManager::error("Zaten giriş yapmışsın ki sen?");
    die();
}

// her şeyi bi kontrol edelim
$kullaniciAdiKontrol = Utils::kullaniciAdiKontrol($kullaniciAdi);
$epostaKontrol = Utils::epostaKontrol($eposta);
$sifreKontrol = Utils::sifreKontrol($sifre);
if(!$kullaniciAdiKontrol){
    OutputManager::error("Kullanıcı adın " . Dotenv::getValue("MIN_KULLANICI_ADI") . "-" . Dotenv::getValue("MAX_KULLANICI_ADI") . " karakter uzunluğunda olsun; sadece a-z, A-Z, 0-9, ve _ karakterlerini içersin.");
    die();
}
if(!$epostaKontrol){
    OutputManager::error("Eposta kötü. Okul epostası olması lazım oğlum.");
    die();
}
if(!$sifreKontrol){
    OutputManager::error("Şifrenin " . Dotenv::getValue("MIN_SIFRE") . "-" . Dotenv::getValue("MAX_SIFRE") . " karakterli olması, en az bi büyük ve bi küçük karakter bi de rakam içermesi lazım.");
    die();
}

// böyle birisi var mı diye bakalım
$yu = new YemekUzmani(null);
$adam = $yu->kullaniciAlParametreIle("kullaniciAdi", $kullaniciAdi);
if($adam !== null){
    OutputManager::error("Bu isim alınmış.");
    die();
}

$adam = $yu->kullaniciAlParametreIle("email", $eposta);
if($adam !== null){
    OutputManager::error("Bu eposta daha önce kullanılmış???");
    die();
}

// bunların hepsini trenzekşına bağlayak
try{
    $yu->pdo->beginTransaction();

    // yeni kullanıcı oluşturak
    // doğrulamayı ne zaman gönderdik (birazdan)
    $dogrulamaZamani = (new \DateTime())->format("Y-m-d H:i:s");
    $yeniKullanici = $yu->kullaniciEkle($kullaniciAdi, $eposta, $sifre, $dogrulamaZamani);
    if($yeniKullanici === null){
        // valla bişeyler çok fena ters gitmiş
        // OutputManager::error("Çüş. Sen mi yaptın bunu?");

        throw new \Exception("Bişeyler çooooook fena ters gitti oğlum.");
    }
    
    // doğrulamayı atalım hemencecik
    $kontrol = Mail::dogrulamaGonder($eposta);
    if(!$kontrol){
        // zaten Mail::mailGonder'de loglandığı için loglamayalım yine.
        // catch'de loglanacak yine
        throw new \Exception("Maili gönderemedik oğlum.");
    }

    $yu->pdo->commit();
} catch (\Exception $e) {
    $yu->pdo->rollBack();

    \Core\Logger::error("Kayıtta sıkıntı oldu, ama geri çektik sıkıntı yok.\n" . $e->getMessage() . "\n" . print_r($postData, true));
    OutputManager::error("Kayıtta sıkıntı oldu, birine söyle bunu. Sonra yine denersin.");
    
    die();
}

OutputManager::info("Kayıt yaptık. E-postana gelen linke bas, hesabını doğrula. Mail gelmediyse spama bak, hala yoksa 5 dk sonra giriş kısmından yeniden girmeye çalış, orda yeniden kod gelir.");
