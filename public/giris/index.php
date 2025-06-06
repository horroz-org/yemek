<?php
require_once dirname(__DIR__, 2) . "/src/init.php";
use \Core\TemplateManager as TM;
?>
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Horroz.org Yemek Giriş</title>
        <link rel="stylesheet" href="/assets/css/stylesheet.css">
        <script src="/assets/js/giris.js"></script>
        <script src="/assets/js/auth.js"></script>
        <script src="/assets/js/api.js"></script>
        <script src="/assets/js/utils.js"></script>
    </head>
    <body>
        <!-- topbar -->
        <?php TM::print("topbar") ?>
        
        <div class="giris-kayit-layout">
            <fieldset id="giris-kayit-form-kutu">
                <legend>Giriş</legend>

                <label for="kullanici-adi-input">Kullanıcı Adı:</label>
                <input type="text" id="kullanici-adi-input" placeholder="Pro363">

                <label for="sifre-input">Şifre:</label>
                <input type="password" id="sifre-input" placeholder="........">

                <div id="hata-mesaji"></div>

                <div id="giris-kayit-buton-wrapper">
                    <div id="giris-kayit-buton" class="buton">Giriş Yap</div>
                </div>
            </fieldset>
        </div>
    </body>        
</html>