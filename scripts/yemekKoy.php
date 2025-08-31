<?php
require_once __DIR__ . "/../src/init.php";
require_once BASE_PATH . "/src/CLI/Utils.php";

use Yemek\YemekUzmani;

if($argc != 2){
    echo "Şöyle kullanacaksın:\nphp scripts/yemekKoy.php yemekler.json\n";
    die();
}

$jsonPath = $argv[1];
if(!file_exists($jsonPath)){
    echo "Dosya yok sen mal mısın?\n";
    die();
}

$yemekler = json_decode(file_get_contents($jsonPath), true);

echo ":: Yemekleri cebimize koyuyoz.\n";
$yu = new YemekUzmani(null);
foreach ($yemekler as $yemek) {
    try{
        if($yu->yemekKoy($yemek)){
            echo "-> Cebe atıldı: " . $yemek["tarih"] . "\n";
        }
        else{
            echo "-> Noluyo ulan? Bişeyler oldu az önce:" . $yemek["tarih"] . "\n";
            die();
        }
    }
    catch (\Throwable $th){
        echo "-> Atlandı.\n";
    }
}

echo "\n";
echo ":: Tamamdır.\n";