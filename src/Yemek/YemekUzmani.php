<?php
namespace Yemek;

use Core\Utils;
use Core\OutputManager;

class YemekUzmani {
    /**
     * Gayet açık bir ismi var.
     * 
     * @var string db yolu
     */
    const dbPath = DB_DIR . "/database.db";

    /**
     * O tarihte yemek yazılmamışsa bu yazacak menü yerine.
     * 
     * @var string moops
     */
    const yemekYokMenu = "Daha bu tarihteki yemekle ilgili elimize bir bilgi geçmedi oğlum!";

    /**
     * PDO
     * 
     * @var \PDO
     */
    private $pdo = null;

    /**
     * Adam anonim mi?
     * 
     * @var bool
     */
    private $anonim;

    /**
     * Konstrüktör.
     * 
     * @param bool $anonim Anonim adam mı istiyor bunu? İstiyorsa kullanabilir ama sadece görür elleyemez.
     */
    public function __construct($anonim = false) {
        if(!file_exists(self::dbPath)){
            http_response_code(500);
            OutputManager::outputPlain("Ulan kurmamışsın sen bunu? He? Şunu çalıştır: scripts/kur.php");
            die();
        }

        $this->anonim = $anonim;
        $this->pdo = new \PDO("sqlite:" . self::dbPath);
    }

    /**
     * Verilen tarihteki yemeği alır.
     * 
     * @param string $tarih yyyy-mm-dd formatında tarih
     * 
     * @return array Yemek hakkında ne varsa döner.
     */
    public function yemekAl($tarih): array {
        if(!Utils::isIsoDate($tarih)){
            Utils::buAdamBiseylerYapmayaCalisiyo();
        }

        $sql = "SELECT * FROM yemek WHERE tarih = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tarih]); 
        $row = $stmt->fetch(); // 1 row olacak zaten
        if($row === false){
            return [
                "menu" => self::yemekYokMenu,
                "tarih" => $tarih,
                "puan" => 0,
                "puanSayisi" => 0
            ];
        }

        return [
            "menu" => $row["menu"],
            "tarih" => $row["tarih"],
            "puan" => $row["puan"],
            "puanSayisi" => $row["puanSayisi"]
        ];
    }

    /**
     * Verilen tarihteki yorumları alır.
     * 
     * @param string $tarih yyyy-mm-dd formatında tarih
     * 
     * @return array Yorumların listesi döner, yorum yoksa boş liste döner.
     */
    public function yorumlariAl($tarih): array {
        if(!Utils::isIsoDate($tarih)){
            Utils::buAdamBiseylerYapmayaCalisiyo();
        }

        $gunBaslangic = (new \DateTime($tarih))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $gunBitis = (new \DateTime($tarih))->modify('+1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s');

        $sql = "SELECT * FROM yorumlar WHERE zaman >= ? AND zaman < ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$gunBaslangic, $gunBitis]);

        $rows = $stmt->fetchAll();
        if($row === false){
            return [];
        }

        $yorumlar = [];
        foreach($rows as $row){
            $yorumlar[] = [
                "uuid" => $row["uuid"],

                "yazarUuid" => $row["yazarUuid"],

                "ustYorumId" => $row["ustYorumId"],

                "yorum" => $row["yorum"],
                "adaminYemekPuani" => $row["puan"],
                "herkeseAcik" => $row["herkeseAcik"],

                "like" => $row["like"],
                "dislike" => $row["dislike"],

                "kaldirildi" => $row["kaldirildi"],
                "zaman" => (new \DateTime($row["zaman"]))->format('Y-m-d H:i:s'), // emin olalım
            ];
        }

        return $yorumlar;
    }

    /**
     * Adam yemeğe güzel bi puan verdiyse 10, vermediyse 0 döndürür.
     * Şaka şaka, adam puan verdiyse puan, vermediyse null döndürür.
     * 
     * @param string $adamId adamın uuid
     * @param string $yemekTarih yemegin tarihi
     * 
     * @return ?int Adamın verdiği puan, vermediyse null.
     */
    public function adaminYemegeVerdigiPuaniAl($adamId, $yemekTarih): ?int {
        if($this->anonim){
            return null;
        }

        $sql = "SELECT * FROM puanlar WHERE kullaniciId = ? AND tarih = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adamId, $yemekTarih]);
        $row = $stmt->fetch();
        if($row === false){
            return null;
        }

        return $row["puan"];
    }

    /**
     * Adam yoruma oy verdiyse up->true down->false, vermediyse null döndürür.
     * 
     * @param string $adamId adamın uuid
     * @param string $yorumId yorumun uuid
     * 
     * @return ?bool Adam like filan attıysa onlar, atmadıysa null.
     */
    public function adaminYorumaVerdigiOyuAl($adamId, $yorumId): ?bool {
        if($this->anonim){
            return null;
        }
        
        $sql = "SELECT * FROM likedislike WHERE kullaniciId = ? AND yorumId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adamId, $yorumId]);
        $row = $stmt->fetch();
        if($row === false){
            return null;
        }

        return $row["like"];
    }
}