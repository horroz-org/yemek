<?php
require_once dirname(__DIR__, 2) . "/src/init.php";
use \Core\TemplateManager as TM;
use \Core\OutputManager;
use \Yemek\Auth;

$bizimki = Auth::bizimkiKim();

if($bizimki === null){
    OutputManager::error("Oğlum sen daha giriş yapmamışsın bi de adminim mi diyosun sen?");
    die();    
}

if(!$bizimki["admin"]){
    OutputManager::error("Git admini çağır da gel. Sen giremezsin.");
    die();
}
?>
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Denetim Masası</title>
        <link rel="stylesheet" href="/assets/css/stylesheet.css">
        <link rel="stylesheet" href="/assets/css/admin.css">
        <script src="/assets/js/admin.js"></script>
        <script src="/assets/js/api.js"></script>
        <script src="/assets/js/utils.js"></script>
    </head>
    <body>
        <div id="admin-anadiv">
            <div id="admin-sidebar">
                <div class="asb-buton asb-secili" id="sb-kullanici">Kullanıcı Yönetimi</div>
                <div class="asb-buton" id="sb-yemek">Yemek Yönetimi</div>
                <div class="asb-buton" id="sb-sikayet">Şikayetler</div>
                <div class="asb-buton" id="sb-log">Seyir Defteri</div>
            </div>
            <div class="admin-aktif-ekran"></div>
        </div>

        <!-- şablonlar -->
        <?php TM::print("admin/kullanici-yonetim") ?>
        <?php TM::print("admin/yemek-yonetim") ?>
        <?php TM::print("admin/sikayet-yonetim") ?>
        <?php TM::print("admin/log-yonetim") ?>
    </body> 
</html>