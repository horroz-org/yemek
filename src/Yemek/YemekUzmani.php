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
     * Bizim adam kim? Biz kime çalışıyoruz oğlum?
     * null'sa anonim, değilse bizimki varmış yani gerçekmiş, anladın?
     * 
     * @var ?array
     */
    private $bizimki = null;

    /**
     * Konstrüktör.
     * 
     * @param ?array $bizimki Daha önce de dediğim gibi, sen kime çalışıyorsun? Bizimkine çalışıyorsun
     * null ise anonim, değilse klasik kullanıcı.
     * Anonim burayı istiyorsa kullanabilir ama sadece görür elleyemez.
     */
    public function __construct($bizimki = null) {
        if(!file_exists(self::dbPath)){
            http_response_code(500);
            OutputManager::outputPlain("Ulan kurmamışsın sen bunu? He? Şunu çalıştır: scripts/kur.php");
            die();
        }

        $this->bizimki = $bizimki;
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
        if($rows === false){
            return [];
        }

        $yorumlar = [];
        foreach($rows as $row){
            $yorumlar[] = [
                "uuid" => $row["uuid"],

                "yazarUuid" => $row["yazarUuid"],

                "ustYorumId" => $row["ustYorumId"],

                "yorum" => $row["yorum"],
                "adaminYemekPuani" => $row["adaminYemekPuani"],
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
        if($adamId === null){
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
     * Bizimki yemeğe güzel bi puan verdiyse 10, vermediyse 0 döndürür.
     * Şaka şaka, adam puan verdiyse puan, vermediyse null döndürür.
     * 
     * @param string $yemekTarih yemegin tarihi
     * 
     * @return ?int Bizimkinin verdiği puan, vermediyse null.
     */
    public function bizimkininYemegeVerdigiPuaniAl($yemekTarih): ?int {
        if($this->bizimki === null){
            return null;
        }
        return $this->adaminYemegeVerdigiPuaniAl($this->bizimki["uuid"], $yemekTarih);
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
        if($adamId === null){
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

    /**
     * Bizimki yoruma oy verdiyse up->true down->false, vermediyse null döndürür.
     * 
     * @param string $yorumId yorumun uuid
     * 
     * @return ?bool Bizimki like filan attıysa onlar, atmadıysa null.
     */
    public function bizimkininYorumaVerdigiOyuAl($yorumId): ?bool {
        if($this->bizimki === null){
            return null;
        }

        return $this->adaminYorumaVerdigiOyuAl($this->bizimki["uuid"], $yorumId);
    }

    /**
     * Adamın bilgilerini alır.
     * 
     * @param string $adamId adamın uuid
     * 
     * @return ?array Adamın bilgileri, yoksa nulliye.
     */
    public function kullaniciAl($adamId): ?array {
        $sql = "SELECT * FROM kullanicilar WHERE uuid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adamId]);
        $row = $stmt->fetch();
        if($row === false){
            return null;
        }

        return [
            "uuid" => $row["uuid"],
            "kullaniciAdi" => $row["kullaniciAdi"],
            "isim" => $row["isim"],
            "hash" => $row["hash"],
            "email" => $row["email"],
            "prestij" => $row["prestij"],
            "rutbe" => $row["rutbe"],
            "katilmaTarihi" => $row["katilmaTarihi"],
            "admin" => $row["admin"]
        ];
    }

    /**
     * Bizimkinin bilgilerini alır.
     * 
     * @return ?array Bizimkinin bilgileri, yoksa null.
     */
    public function bizimkiniAl(): ?array {
        if($this->bizimki === null){
            return null;
        }

        return $this->kullaniciAl($this->bizimki["uuid"]);
    }

    /**
     * Güvenli şekilde al, sadece şu bilgiler olacak:
     * uuid, kullaniciAdi, isim, prestij, rutbe, katilmaTarihi, admin
     * 
     * şunlar yok:
     * hash, email
     * 
     * @param string $adamId adamın uuid
     * 
     * @return ?array Adamın bilgileri, yoksa null.
     */
    public function kullaniciAlGuvenli($adamId): ?array {
        $kullaniciData = $this->kullaniciAl($adamId);
        if($kullaniciData === null){
            return null;
        }
        
        unset($kullaniciData["hash"]);
        unset($kullaniciData["email"]);
        return $kullaniciData;
    }
}