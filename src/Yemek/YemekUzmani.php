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
     * @return ?array Yemek hakkında ne varsa döner, yemek varsa tabi. Yoksa null.
     */
    public function yemekAl($tarih): ?array {
        if(!Utils::isIsoDate($tarih)){
            Utils::buAdamBiseylerYapmayaCalisiyo();
        }

        $sql = "SELECT * FROM yemek WHERE tarih = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tarih]); 
        $row = $stmt->fetch(); // 1 row olacak zaten
        $stmt->closeCursor();
        if($row === false){
            return null;
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
            return null;
        }

        $gunBaslangic = (new \DateTime($tarih))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $gunBitis = (new \DateTime($tarih))->modify('+1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s');

        $sql = "SELECT * FROM yorumlar WHERE zaman >= ? AND zaman < ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$gunBaslangic, $gunBitis]);

        $rows = $stmt->fetchAll();
        $stmt->closeCursor();
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
        $stmt->closeCursor();
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
        $stmt->closeCursor();
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
        $stmt->closeCursor();
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

    /**
     * Bizimkinin yemeğe puan vermesini sağlıyoruz.
     * 
     * @param int $yemekTarih Ne zaman yedin bre oğlum? Breh breh.
     * @param int $puan Kaç puan verdin bre oğlum? İboya söyleme ha.
     */
    public function yemegePuanVer($yemekTarih, $puan){
        if($this->bizimki === null){
            return null;
        }

        // burada upsert olacak ama nasıl yapılıyo bilmiyom
        // baktım azıcık bissürü şey var yeni eski bol keseden
        // o yüzden varsa update, yoksa insert => toplam 2 işlemde halledecez
        // atomik oluyo heralde o internettekiler ama olsun 5-10 ms götümüze giriversin dimi?
        //
        // Güncelleme: transaction'a aldım hepsini, götümüz rahat olsun

        try{
            $this->pdo->beginTransaction();

            // bu yemekAl kısmını da transaction'un içine soktum
            // olur heralde
            $yemek = $this->yemekAl($yemekTarih);
            if($yemek === null){
                $this->pdo->rollBack();
                return null;
            }

            $sql = "SELECT * FROM puanlar WHERE kullaniciId = ? AND tarih = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->bizimki["uuid"], $yemekTarih]);
            $row = $stmt->fetch();
            $stmt->closeCursor();
            
            $eskiPuan = null;
            if($row !== false){
                // varmış, update.
                $sql = "UPDATE puanlar SET puan = ? WHERE kullaniciId = ? AND tarih = ?";
                $eskiPuan = $row["puan"];
            }
            else{
                // yokmuş, insert.
                $sql = "INSERT INTO puanlar (puan, kullaniciId, tarih) VALUES (?, ?, ?)";
            }
            $stmt = $this->pdo->prepare($sql);
            $kontrol = $stmt->execute([$puan, $this->bizimki["uuid"], $yemekTarih]);
    
            if($kontrol === false){
                // yemeğe puan veremiyoruz -> update/insert yapamıyoruz
                // neden? bilmem. puan, kullaniciId, tarih filan yanlış olabilir belki
                \Core\Logger::warning("Burada garip şeyler oluyor, yemegePuanVer puan ekleme kısmı.\npuan: $puan\kullaniciId: bizimki (".$this->bizimki["uuid"].")\ntarih: $yemekTarih");
                
                $this->pdo->rollBack();
                return null;
            }
    
            // burda da yemeğin ortalamasını değiştirmeye geldik,
            // değiştirme:
            // ortalama = (ortalama * kac_kisi + yeni_puan - eski_puan) / kac_kisi
            // ekleme:
            // ortalama = (ortalama * kac_kisi + yeni_puan) / (kac_kisi + 1)
            // ama kac_kisi = 1 ise 0 olacak falan filan sql'de if mif gerekecek diye
            // hiç uğraşmıyorum, 5-10 ms daha götüme sokuyorum
            //
            // Güncelleme: transaction'a aldım hepsini, götümüz rahat olsun

            $eskiOrtalama = $yemek["puan"];
            $kacKisi = $yemek["puanSayisi"];
            $yeniOrtalama = 0;
            if($eskiPuan === null){
                // ilk defa giriyoruz bu işe
                $yeniOrtalama = ($eskiOrtalama * $kacKisi + $puan) / ($kacKisi + 1);
                $kacKisi += 1;
            }
            else{
                // yıllardır içindeyiz bu bokun
                $yeniOrtalama = $eskiOrtalama + ($puan - $eskiPuan) / $kacKisi;
            }
    
            $sql = "UPDATE yemek SET puan = ?, puanSayisi = ? WHERE tarih = ? RETURNING puan, puanSayisi";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$yeniOrtalama, $kacKisi, $yemekTarih]);

            $guncelPuan = $stmt->fetch();
            $stmt->closeCursor();

            if($guncelPuan === false){
                // yemeğin puanlarını güncelleyemiyoruz.
                // puan ve puanSayisi bir garip olabilir, loglara yazsın
                \Core\Logger::warning("Burada garip şeyler oluyor, yemegePuanVer.\npuan: $yeniOrtalama\npuanSayisi: $kacKisi\ntarih: $yemekTarih");
                
                $this->pdo->rollBack();
                return null;
            }

            $this->pdo->commit();
        } catch(\PDOException $e){
            \Core\Logger::error("yemegePuanVer PDOException\n" . $e->getMessage());
            return null;
        }

        return [
            "puan" => $guncelPuan["puan"],
            "puanSayisi" => $guncelPuan["puanSayisi"]
        ];
    }

    /**
     * Bizimki verdiği puandan vazgeçmiş, silmek istiyormuş breh breh.
     * 
     * @param int $yemekTarih Ne zaman yedin bre oğlum?
     * 
     * @return ?array Başardıysak güncel puan ve kaç kişi, başaramadıysak null.
     */
    public function yemekPuanSil($yemekTarih){
        if($this->bizimki === null){
            return null;
        }

        // önce eski puanı alacaz hatırlayacaz, sonra silecez
        // select + delete 2 işlem olacak
        // bunu tekte yapan vardır kesin ama ben bilmiyorum
        // sonra da yemeğin ortalamasını filan değiştiricez
        // bunda da select + update 2 işlem
        //
        // buldum, DELETE ... RETURNING *; yapınca silinen rowlar geliyormuş

        try{
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("DELETE FROM puanlar WHERE kullaniciId = ? AND tarih = ? RETURNING *");
            $stmt->execute([$this->bizimki["uuid"], $yemekTarih]);
            $eskiData = $stmt->fetch();
            $stmt->closeCursor();
    
            if($eskiData === false){
                // sen var ya sen çok akıllısın heralde?
                $this->pdo->rollBack();
                return null;
            }
    
            $stmt = $this->pdo->prepare("
            UPDATE yemek
            SET 
                puan = 
                    CASE 
                        WHEN puanSayisi = 1 THEN 0
                        ELSE (puan * puanSayisi - :puan) / (puanSayisi - 1)
                    END,
                puanSayisi = puanSayisi - 1
            WHERE tarih = :tarih
            RETURNING puan, puanSayisi
            ");
            $stmt->execute(['puan' => $eskiData["puan"], 'tarih' => $yemekTarih]);
    
            $guncelPuan = $stmt->fetch();
            $stmt->closeCursor();

            if($guncelPuan === false){
                \Core\Logger::warning("Burada garip şeyler oluyor, yemegePuanVer.\npuan: $yeniOrtalama\npuanSayisi: $kacKisi\ntarih: $yemekTarih");
                $this->pdo->rollBack();
                return null;
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            \Core\Logger::error("yemegePuanVer PDOException\n" . $e->getMessage());
            $this->pdo->rollBack();
            return null;
        }

        return [
            "puan" => $guncelPuan["puan"],
            "puanSayisi" => $guncelPuan["puanSayisi"]
        ];
    }
}