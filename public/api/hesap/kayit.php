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
$kullaniciAdiKontrol = Utils::kullaniciAdiKontrol($eposta);
$epostaKontrol = Utils::epostaKontrol($eposta);
$sifreKontrol = Utils::sifreKontrol($sifre);
if(!$kullaniciAdiKontrol || !$epostaKontrol || !$sifreKontrol){
    OutputManager::error("Kullanıcı adında, epostanda veya şifrende fena bir yanlışlık var.");
    die();
}

// böyle birisi var mı diye bakalım
$adam = $yu->kullaniciAlAdIle($kullaniciAdi);
if($adam !== null){
    OutputManager::error("Bu isim alınmış.");
    die();
}

// yeni kullanıcı oluşturak
$yu = new YemekUzmani(false);
$yeniKullanici = $yu->kullaniciEkle($kullaniciAdi, $eposta, $sifre);
if($yeniKullanici === null){
    // valla bişeyler çok fena ters gitmiş
    OutputManager::error("Çüş. Sen mi yaptın bunu?");
    \Core\Logger::error("Kullanıcı eklenemedi.\n$kullaniciAdi\n$eposta\n$sifre");
    die();
}

// doğrulamayı atalım hemencecik
Mail::dogrulamaGonder($email);

OutputManager::info("Kayıt yaptık. E-postana gelen linke bas, hesabını doğrula. Mail gelmediyse spama bak, hala yoksa 5 dk sonra giriş kısmından yeniden denersin.");
