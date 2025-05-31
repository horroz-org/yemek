<?php
/**
 * ?t ile gelecek token
 */
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\Logger;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;
use Yemek\Mail;

$zorunluKeyler = ["t"];
$qData = Utils::getQueryData($zorunluKeyler);

$token = $qData["t"];

$tokenData = Auth::verifyMailToken($token);
if($tokenData === null){

    die("Yine giriş yapmayı dene, yeniden mail gelsin sana.
    <br><b>Ya aşırı kötü şeyler oluyor (veya sen mal mal şeyler deniyosun şu anda), ya da linkin süresi geçmiş. (Süre 2 saat)</b>
    <br><br><a href='/giris/'>Giriş Sayfasına Git</a>");
}

$eposta = $tokenData["eposta"];

$bizimki = Auth::bizimkiKim();
if($bizimki !== null){
    die("Giriş yapmışsın ya oğlum? Niye yeniden buraya geliyosun? Sen manyak mısın?");
}

// şimdi hesabı doğrulayalım
$yu = new YemekUzmani(null);

$adam = $yu->kullaniciAlParametreIle("email", $eposta);
if($adam === null){
    // Adamın hesabı yok gelmiş buraya.
    Logger::error("Deyecek bir shey bulamyorum.\n$eposta");
    die("Hesabını mı sildirdin? Yoksa ilginç şeyler olmakta.");
}

if($adam["emailDogrulandi"]){
    // ulan zaten doğrulanmış ki?
    die("Senin hesabın zaten doğrulanmış, senin burda ne işin var bre deyyus?<br><a href='/giris/'>Giriş Yap</a>");
}

// sonunda geldik buralara
$kontrol = $yu->epostaDogrulandiOlarakIsaretle($eposta);
if($kontrol === false){
    Logger::error("İnanılmaz kötü şeyler olmakta. (epostaDogrula.php)\n" . print_r($tokenData, true));
    die("Gerçekten kötü kötü şeyler oldu az önce. (Olmamış ta olabilir, emin değilim.)
    <br>Büyük ihtimal sen hesabı açtın, 2 saat içinde hesabı sildirttin, sonra linke yine girdin, di mi?
    <br>(Eğer öyle yapmadıysan gerçekten kötü şeyler oluyordur kesin.)
    <br>Birine söyle bunu kötü kötü şeyler olmakta.");
}

header("Location: /giris/");
die("Tamamdır, giriş yap şimdi. <a href='/giris/'>Yönlendiriyom.</a>");
