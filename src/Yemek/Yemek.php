<?php
namespace Yemek;

use Core\Utils;

class Yemek {
    /**
     * Gayet açık bir ismi var.
     * 
     * @var string db yolu
     */
    const dbPath = DB_DIR . "/database.db";

    const yemekYokMenu = "Daha bu tarihteki yemekle ilgili elimize bir bilgi geçmedi oğlum!";

    /**
     * Verilen tarihteki yemeği alır.
     * 
     * @param string $tarih yyyy-mm-dd formatında tarih
     * 
     * @return array Yemek hakkında ne varsa döner.
     */
    public static function yemekAl($tarih): array {
        if(!Utils::isIsoDate($tarih)){
            Utils::buAdamBiseylerYapmayaCalisiyo();
        }
        
        $pdo = new \PDO("sqlite:" . self::dbPath);

        $sql = "SELECT * FROM yemek WHERE tarih = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tarih]); 
        $row = $stmt->fetch();
        if($row === false){
            return [
                "menu" => self::yemekYokMenu,
                "tarih" => $tarih,
                "puan" => 0,
                "puansayisi" => 0
            ];
        }

        return [
            "menu" => $row["menu"],
            "tarih" => $row["tarih"],
            "puan" => $row["puan"],
            "puansayisi" => $row["puansayisi"]
        ];
    }

    /**
     * Verilen tarihteki yorumları alır.
     * 
     * @param string $tarih yyyy-mm-dd formatında tarih
     * 
     * @return array Yorumların listesi döner, yorum yoksa boş liste döner.
     */
    public static function yorumlariAl($tarih): array {
        if(!Utils::isIsoDate($tarih)){
            Utils::buAdamBiseylerYapmayaCalisiyo();
        }

        $pdo = new \PDO("sqlite:" . self::dbPath);

        $gunBaslangic = (new DateTime($tarih))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $gunBitis = (new DateTime($tarih))->modify('+1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s');

        $sql = "SELECT * FROM yorumlar WHERE zaman >= ? AND zaman < ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$gunBaslangic, $gunBitis]); 
        $rows = $stmt->fetch();
        if($row === false){
            return [];
        }

        $yorumlar = [];
        foreach($rows as $row){
            $yorumlar[] = [
                "uuid" => $row["uuid"],

                "yazaruuid" => $row["yazaruuid"],

                "ustyorumid" => $row["ustyorumid"],

                "yorum" => $row["yorum"],
                "puan" => $row["puan"],
                "herkeseacik" => $row["herkeseacik"],

                "like" => $row["like"],
                "dislike" => $row["dislike"],

                "kaldirildi" => $row["kaldirildi"],
                "zaman" => $row["zaman"]
            ];
        }

        return $yorumlar;
    }

    /**
     * Adam yemeğe güzel bi puan verdiyse 10, vermediyse 0 döndürür.
     * Şaka şaka, adam puan verdiyse puan, vermediyse null döndürür.
     * 
     * @param string $adamId adamın uuid
     * @param string $yorumId yorumun uuid
     * 
     * @return ?int Adamın verdiği puan, vermediyse null.
     */
    public static function adaminYemegeVerdigiPuaniAl($uuid): ?int {

    }

    /**
     * Adam yoruma puan verdiyse up->true down->false, vermediyse null döndürür.
     * 
     * @param string $adamId adamın uuid
     * @param string $yorumId yorumun uuid
     * 
     * @return ?bool Adam like filan attıysa onlar, atmadıysa null.
     */
    public static function adaminYorumaVerdigiPuaniAl($adamId, $yorumId): ?bool {

    }
}