<?php
require_once __DIR__ . "/init.php";

$pdo = new PDO("sqlite:" . DB_DIR . "/database.db");

// yemekler
$pdo->exec("
    CREATE TABLE IF NOT EXISTS yemek (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        yemek TEXT NOT NULL,
        kalori INTEGER NOT NULL,
        tarih DATE NOT NULL,
        puan INTEGER NOT NULL
    )
");

// kullanıcılar
$pdo->exec("
    CREATE TABLE IF NOT EXISTS kullanicilar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uuid TEXT NOT NULL,

        kuladi TEXT NOT NULL,
        hash TEXT NOT NULL,
        email TEXT NOT NULL,
        telno TEXT NOT NULL,
        isim TEXT NOT NULL,
        okulno TEXT NOT NULL,
        foto TEXT NOT NULL,

        katilmatarihi DATETIME NOT NULL,
        admin BOOLEAN NOT NULL
    )
");

// puanlar
$pdo->exec("
    CREATE TABLE IF NOT EXISTS puanlar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullaniciid TEXT NOT NULL,
        puan INTEGER NOT NULL,
        tarih DATE NOT NULL
    )
");

// yorumlar
$pdo->exec("
    CREATE TABLE IF NOT EXISTS yorumlar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uuid TEXT NOT NULL,

        yazaruuid TEXT NOT NULL,
        
        ustyorum TEXT NOT NULL,
        
        yorum TEXT NOT NULL,
        puan INTEGER NOT NULL,
        herkeseacik BOOLEAN NOT NULL,

        like INTEGER NOT NULL,
        dislike INTEGER NOT NULL,

        zaman DATETIME NOT NULL
    )
");

// layk/dislayk
$pdo->exec("
    CREATE TABLE IF NOT EXISTS likedislike (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullaniciid TEXT NOT NULL,
        yorumid TEXT NOT NULL,
        likemi BOOLEAN NOT NULL
    )
");
