<?php
require_once dirname(__DIR__, 2) . "/src/init.php";
use \Core\TemplateManager as TM;
?>
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Horroz.org Yemek Kayıt</title>
        <link rel="stylesheet" href="/assets/css/stylesheet.css">
        <script src="/assets/js/kayit.js"></script>
        <script src="/assets/js/auth.js"></script>
        <script src="/assets/js/api.js"></script>
        <script src="/assets/js/utils.js"></script>
    </head>
    <body>
        <!-- topbar -->
        <?php TM::print("topbar") ?>

        <div class="giris-kayit-layout">
            <fieldset id="giris-kayit-form-kutu">
                <legend>Kayıt</legend>

                <label for="kullanici-adi-input" class="font-kalin">Kullanıcı Adı:</label>
                <input type="text" id="kullanici-adi-input" placeholder="Boşluk içeremez.">

                <label for="eposta-input" class="font-kalin">E-posta:</label>
                <input type="text" id="eposta-input" placeholder="Okul maili zorunlu.">

                <label for="sifre-input" class="font-kalin">Şifre:</label>
                <div class="sifre-input-wrapper">
                    <input type="password" id="sifre-input" placeholder="Büyük küçük harf, rakam, en az 6 hane.">
                    <div id="sifre-goster-buton" class="buton">göster</div>
                </div>

                <div class="kayit-kabul-wrapper">
                    <input type="checkbox" id="kabul-ediyorum" checked>
                    <label for="kabul-ediyorum">Şunu bunu kabul ediyorum.</label>
                </div>

                <div id="hata-mesaji"></div>

                <div id="giris-kayit-buton-wrapper">
                    <div id="giris-kayit-buton" class="buton">Kayıt Ol</div>
                </div>
            </fieldset>
        </div>
    </body>        
</html>