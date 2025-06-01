<?php
require_once __DIR__ . "/../src/init.php";
require_once BASE_PATH . "/src/CLI/Utils.php";

if($argc != 2){
    echo "Şöyle kullanacaksın:\nphp scripts/yemekAlHacettepe.php yemekler.json\n";
    die();
}

$ciktiPath = $argv[1];
if(file_exists($ciktiPath)){
    $yn = yesno("-> Zaten böyle bi dosya var, üstüne yazak mı?", true);
    if($yn){
        echo "\n";
        unlink($ciktiPath);
    }
    else{
        die(":: Tmm.");
    }
}

$gridUrl = "https://sksdb.hacettepe.edu.tr/new/grid.php";

// bu ne amınakoyayım
$yemekParam = "qbapuL6kmaScnHaup8DEm1B8maqturW8haidnI+sq8F/gY1fiZWdnKShq8bTlaOZXq+mwWjLzJyPlpmcpbm1kNORopmYXI22tLzHXKmVnZykwafFhImVnZWipbq0f8qRnJ+ioF6go7/OoplWqKSltLa805yVj5agnsGmkNORopmYXam2qbi+o5mqlXRt";

$html = file_get_contents($gridUrl . "?parameters=" . urlencode($yemekParam));

// php de aslında DOMDocument filan diye parser var, xpath filan çalışıyo
// ama html bok gibi olduğu için işimize yaramaz
// böyle yapacaz mecbur
$listeCol = explode("<div class=\"icerik_baslik\">", $html, 2)[1];
$menuBok = trim(explode("</div>", $listeCol, 3)[1]);

// her yemeğin içine alerjen listesi diye koymuşlar onu silelim
// <p></p> içindeki her şey yani
$menuBok = preg_replace("/<p>[\s\S]*?<\/p>/", "", $menuBok);

// her yemeği <hr> ile ayırmışlar
$yemeklerPis = explode("<hr>", $menuBok);
// en başta da bi tane <hr> var, [0] boş oldu onu gönderelim
array_shift($yemeklerPis);

// temizcene
$yemekler = [];

// şimdi teker teker alalım.
foreach($yemeklerPis as $yemekPis){
    $parcalarPis = explode("<br>", $yemekPis);
    $parcalar = [];
    foreach ($parcalarPis as $parca) {
        $parcalar[] = trim($parca);
    }

    // <br> ile ayırdıktan sonra sırasıyla şunlar var:
    // tarih, kalori, "Menü:", yemek, yemek, yemek, yemek, yıldızlı yemek

    // tarih format örnek: 30.05.2025 Cuma
    $tarihPisStr = explode(" ", $parcalar[0])[0];
    // yyyy-mm-dd yaptık
    $tarih = DateTime::createFromFormat("d.m.Y", $tarihPisStr)->format("Y-m-d");

    // kalori format: "Kalori: 35"
    $kalori = intval(explode(" ", $parcalar[1])[1]);

    $menuStr = "";
    for($i = 3; $i <= 7; $i++){
        $suAnki = $parcalar[$i];
        // parantezler içindeki kaloriler bok gibi yazılmış
        // bir de çok uzun oluyor her satır
        // o yüzden silecem onları
        // (alerji şeyleri kalacak merak etmeyin)
        // hatta ayrıca parantez içindeki yemek açıklamalarını da sikelim pardon silelim
        $suAnki = trim(preg_replace("/\([0-9 a-z\*]*?\)/", "", $suAnki));
        $menuStr .= $suAnki;

        if($i != 7){
            $menuStr .= empty($suAnki) ? "" : "\n";
        }
    }

    $yemekler[] = [
        "tarih" => $tarih,
        "menu" => $menuStr,
        "kalori" => $kalori
    ];
}

file_put_contents($ciktiPath, json_encode($yemekler));

echo ":: Yemekleri aldık:\n";
echo "-> İlk: " . $yemekler[0]["tarih"] . "\n";
echo "-> Son: " . end($yemekler)["tarih"] . "\n";
echo "\n";
echo "-> Cebimize koyalım dersen:\n";
echo "-> php scripts/yemekKoy.php $ciktiPath\n";
echo "\n";
echo ":: Hadi baybay.\n";
