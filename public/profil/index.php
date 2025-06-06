<?php
require_once dirname(__DIR__, 2) . "/src/init.php";
use \Core\TemplateManager as TM;
?>
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Yemek Profili</title>
        <link rel="stylesheet" href="/assets/css/stylesheet.css">
        <script src="/assets/js/profil.js"></script>
        <script src="/assets/js/auth.js"></script>
        <script src="/assets/js/api.js"></script>
        <script src="/assets/js/utils.js"></script>
    </head>
    <body>
        <!-- topbar -->
        <?php TM::print("topbar") ?>
        
        <div class="profil-layout">
            <div id="kullanici-adi-kutu"></div>
            <div class="profil-attr-wrapper">Rütbe:<div id="rutbe-kutu"></div></div>
            <div class="profil-attr-wrapper">Prestij:<div id="prestij-kutu"></div></div>
            <div id="profil-yorumlar-liste"></div>
            <div id="devamini-goster" class="buton">Devamını Göster</div>
        </div>

        <!-- Profil yorum template -->
        <?php TM::print("profil-yorum") ?>
    </body> 
</html>