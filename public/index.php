<?php
require_once dirname(__DIR__, 1) . "/src/init.php";
use \Core\TemplateManager as TM;
?>
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Horroz.org Yemek</title>
        <link rel="stylesheet" href="/assets/css/stylesheet.css">
        <script src="/assets/js/index.js"></script>
        <script src="/assets/js/auth.js"></script>
        <script src="/assets/js/api.js"></script>
        <script src="/assets/js/utils.js"></script>
    </head>
    <body>
        <div class="index-layout">
            <!-- topbar -->
            <?php TM::print("topbar") ?>

            <div class="anakutu">
                <div class="ickutu">
                    <div class="kocadiv border ortalayandiv">
                        <div id="yemektarih"></div>
                        <div id="sagyemekok"><i class="mid-arrow right"></i></div>
                        <div id="solyemekok"><i class="mid-arrow left"></i></div>
                        <div id="menu"></div>
                        <div class="puan-wrapper">
                            <div id="puan">0</div>
                            <div id="puanbolen">/10</div>
                        </div>
                        <div class="butongrid">
                            <div class="butonlar">
                                <div id="puan1" class="puanbuton">1</div>
                                <div id="puan2" class="puanbuton">2</div>
                                <div id="puan3" class="puanbuton">3</div>
                                <div id="puan4" class="puanbuton">4</div>
                                <div id="puan5" class="puanbuton">5</div>
                            </div>
                            <div class="butonlar">
                                <div id="puan6" class="puanbuton">6</div>
                                <div id="puan7" class="puanbuton">7</div>
                                <div id="puan8" class="puanbuton">8</div>
                                <div id="puan9" class="puanbuton">9</div>
                                <div id="puan10" class="puanbuton">10</div>
                            </div>
                        </div>

                        <div class="yorum-menu">
                            <div class="puan-yorum-bilgi"><div id="degerlendirme-sayisi">0</div>değerlendirme ve <div id="yorum-sayisi">0</div>yorum</div>
                            <div id="yorumyazbuton" class="buton">
                                Yorum Yaz
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ickutu">
                    <div id="yorumlar-liste"></div>
                </div>
            </div>
        </div>

        <!-- Yorum template -->
        <?php TM::print("yorum") ?>

        <!-- ekranın ortasında çıkacak olan formlar. yorum yazma/cevap verme-->
        <div class="ekran-formlar" style="display: none;">
            <div id="yorum-form" style="display: none;">
                <div id="form-kapat-buton">x</div>
                <div class="yorum-form-ust">
                    <div id="yorum-input-baslik">Yorumunuz:</div>
                    <textarea id="yorum-yazi" placeholder="Vah vah..."></textarea>
                    <div id="yorum-yazi-karaktersayi"></div>
                </div>
                <div id="yorum-form-hata"></div>
                <div class="yorum-form-alt">
                    <div id="yorum-gonder-buton" class="buton">Gönder</div>
                </div>
            </div>
        </div>
    </body>
</html>