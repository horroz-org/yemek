<?php
require_once __DIR__ . "/../src/init.php";
require_once BASE_PATH . "/src/CLI/Utils.php";

use \Core\Utils;

$dbPath = DB_DIR . "/database.db";

if(file_exists($dbPath)){
    $yn = yesno("-> Zaten veritabanı var, sil baştan mı olsun?", false);
    if($yn){
        unlink($dbPath);
    }
    else{
        echo(":: Tmm kolay gelsin o zaman."); echo("\n");
        die();
    }

    echo("\n");
}

main();

function main(){
    echo("Horroz.org Yemek Veritabanı Kurulum Sihirbazı'na hoş geldiniz oğlum!\n");
    if(!yesno("-> Hazır mısın?", true)){
        echo(":: Hazır ol da gel o zaman.\n");
        die();
    }
    echo("\n");

    global $pdo, $dbPath;
    $pdo = new PDO("sqlite:" . $dbPath);

    echo("Tablolar oluşturuluyor.\n");
    tablolariOlustur(); echo("\n");

    echo("Admin hesabı açılıyor.\n");
    adminAyarla(); echo("\n");

    echo("Hayırlı olsun.\n");
}

function tablolariOlustur(){
    global $pdo;

    // yemekler
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS yemek (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            menu TEXT NOT NULL,
            tarih DATE NOT NULL,
            puan REAL NOT NULL,
            puanSayisi INTEGER NOT NULL
        )
    ");
    echo(":: Tablo oluşturuldu: yemek\n");

    // puanlar
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS puanlar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kullaniciId TEXT NOT NULL,
            puan INTEGER NOT NULL,
            tarih DATE NOT NULL
        )
    ");
    echo(":: Tablo oluşturuldu: puanlar\n");

    // yorumlar
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS yorumlar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL,

            yazarUuid TEXT NOT NULL,

            ustYorumId TEXT,

            yorum TEXT NOT NULL,
            adaminYemekPuani INTEGER NOT NULL,
            herkeseAcik BOOLEAN NOT NULL,

            like INTEGER NOT NULL,
            dislike INTEGER NOT NULL,

            kaldirildi BOOLEAN NOT NULL,

            zaman DATETIME NOT NULL
        )
    ");
    echo(":: Tablo oluşturuldu: yorumlar\n");

    // layk/dislayk
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS likedislike (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kullaniciId TEXT NOT NULL,
            yorumId TEXT NOT NULL,
            like BOOLEAN NOT NULL
        )
    ");
    echo(":: Tablo oluşturuldu: likedislike\n");

    // kullanıcılar
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS kullanicilar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL,

            kullaniciAdi TEXT NOT NULL,
            isim TEXT NOT NULL,
            hash TEXT NOT NULL,
            email TEXT,

            prestij INTEGER NOT NULL,
            rutbe TEXT,

            katilmaTarihi DATETIME NOT NULL,
            admin BOOLEAN NOT NULL
        )
    ");
    echo(":: Tablo oluşturuldu: kullanicilar\n");
}

function adminAyarla(){
    global $pdo;

    $adminUUID = Utils::generateUUIDv4();
    $adminUsername = soru("-> Admin kullanıcı adı ne olsun?", "admin");
    $adminName = soru("-> İsmi ne olsun peki?", "Yönetici");
    $adminPass = soru("-> Şifre ne olsun abicim?", "aslanmax");
    $adminHash = password_hash($adminPass, PASSWORD_BCRYPT);
    $dateNow = date('Y-m-d H:i:s');
    $pdo->prepare("INSERT INTO kullanicilar (uuid, kullaniciAdi, isim, hash, prestij, rutbe, katilmaTarihi, admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$adminUUID, $adminUsername, $adminName, $adminHash, 0, "Pro", $dateNow, true]);
    echo(":: Admin eklendi."); echo("\n");
}
