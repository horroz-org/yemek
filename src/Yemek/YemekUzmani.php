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
    public $pdo = null;

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

        $sql = "SELECT * FROM yemekler WHERE tarih = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tarih]); 
        $row = $stmt->fetch(); // 1 row olacak zaten
        $stmt->closeCursor();
        if($row === false){
            return null;
        }

        return [
            "tarih" => $row["tarih"],
            "menu" => $row["menu"],
            "kalori" => $row["kalori"],
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
    public function yorumlariAl($tarih): ?array {
        if(!Utils::isIsoDate($tarih)){
            return null;
        }

        $sql = "SELECT * FROM yorumlar WHERE yemekTarih = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tarih]);
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

                "like" => $row["like"],
                "dislike" => $row["dislike"],

                "kaldirildi" => $row["kaldirildi"],
                
                "yemekTarih" => (new \DateTime($row["yemekTarih"]))->format('Y-m-d'), // emin olalım
                "zaman" => (new \DateTime($row["zaman"]))->format('Y-m-d H:i:s') // emin olalım
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
        return $this->kullaniciAlParametreIle("uuid", $adamId);
    }

    /**
     * Parametre verilen adamın bilgilerini alır.
     * 
     * @param string $param parametre
     * @param string $deger parametrenin değeri
     * 
     * @return ?array Adamın bilgileri, yoksa null.
     */
    public function kullaniciAlParametreIle($param, $deger): ?array {
        // injection olmaz ama yine de
        $kabulEdilenParametreler = ["uuid", "kullaniciAdi", "email"];
        if(!in_array($param, $kabulEdilenParametreler)){
            \Core\Logger::warning("Nasıl oldu bu oğlum? (kullaniciAlParametreIle)\nparametre: $param");
            return null;
        }

        $sql = "SELECT * FROM kullanicilar WHERE $param = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$deger]);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if($row === false){
            return null;
        }

        return [
            "uuid" => $row["uuid"],
            "kullaniciAdi" => $row["kullaniciAdi"],
            "hash" => $row["hash"],
            "email" => $row["email"],
            "emailDogrulandi" => $row["emailDogrulandi"],
            "dogrulamaNeZamanGonderdik" => $row["dogrulamaNeZamanGonderdik"],
            "prestij" => $row["prestij"],
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
     * uuid, kullaniciAdi, isim, prestij, katilmaTarihi, admin
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
     * 
     * @return ?array Başarıyla puan verdiysek güncel puan ve kaç kişi, başaramadıysak null.
     */
    public function yemegePuanVer($yemekTarih, $puan): ?array{
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
    
            $sql = "UPDATE yemekler SET puan = ?, puanSayisi = ? WHERE tarih = ? RETURNING puan, puanSayisi";
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
            $this->pdo->rollBack();
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
    public function yemekPuanSil($yemekTarih): ?array {
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
            UPDATE yemekler
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
                \Core\Logger::warning("Burada garip şeyler oluyor, yemekPuanSil.\nguncelPuan: $guncelPuan\ntarih: $yemekTarih");

                $this->pdo->rollBack();
                return null;
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            \Core\Logger::error("yemekPuanSil PDOException\n" . $e->getMessage());
            return null;
        }

        return [
            "puan" => $guncelPuan["puan"],
            "puanSayisi" => $guncelPuan["puanSayisi"]
        ];
    }

    /**
     * Uuid verilen yorumun bilgilerini al
     * 
     * @param string $yorumUuid yorumun uuid'si
     * 
     * @return ?array Yorum varsa bilgiler, yoksa null.
     */
    public function yorumBilgisiAl($yorumUuid){
        $sql = "SELECT * FROM yorumlar WHERE uuid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$yorumUuid]);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if($row === false){
            return null;
        }

        return [
            "uuid" => $row["uuid"],

            "yazarUuid" => $row["yazarUuid"],

            "ustYorumId" => $row["ustYorumId"],

            "yorum" => $row["yorum"],
            "adaminYemekPuani" => $row["adaminYemekPuani"],

            "like" => $row["like"],
            "dislike" => $row["dislike"],

            "kaldirildi" => $row["kaldirildi"],
            
            "yemekTarih" => (new \DateTime($row["yemekTarih"]))->format('Y-m-d'), // emin olalım
            "zaman" => (new \DateTime($row["zaman"]))->format('Y-m-d H:i:s') // emin olalım
        ];
    }

    /**
     * Adam yemeğe yorum yazmak istiyor, bu fonksiyonla yazdırıyoruz.
     * 
     * @param string $yemekTarih Yemeğin tarihi.
     * @param string $yorum Yorum metni.
     * @param ?string $ustYorumId Yorum başka yoruma cevapsa üst yorumun uuid, değilse null.
     * 
     * @return ?array Yorumun bilgileri (oluşturulmuş uuid filan), hata olduysa null.
     */
    public function yorumYaz($yemekTarih, $yorum, $ustYorumId = null): ?array {
        if($this->bizimki === null){
            return null;
        }

        // önce yorumda değişik karakterler filan kullanmış mı değişik şeyler yapmış mı
        // bi kontrol edelim
        // aslında gerek yok, sonra yaparız
        // burayı okuyan varsa kendisi yapsın pr atsın, ben mi yapacam her şeyi?
        //
        // Güncelleme: yorumYap.php'de kontrol ediyoz, kontrol fonksiyonu Utils'de.

        // yemek varsa insert, bu kadar.

        if($this->yemekAl($yemekTarih) === null){
            return null;
        }

        // üst yorum var mı yoksa adam bizi kandırmaya mı çalışıyor
        if($ustYorumId !== null && $this->yorumBilgisiAl($ustYorumId) === null){
            // vay vay sen çok akıllısın he?
            return null;
        }

        $yorumUuid = Utils::generateUUIDv4();
        $simdi = (new \DateTime())->format('Y-m-d H:i:s');

        $sql = "INSERT INTO yorumlar (uuid, yazarUuid, ustYorumId, yorum, adaminYemekPuani, like, dislike, kaldirildi, yemekTarih, zaman) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $kontrol = $stmt->execute([$yorumUuid, $this->bizimki["uuid"], $ustYorumId, $yorum, $this->bizimkininYemegeVerdigiPuaniAl($yemekTarih), 0, 0, 0, $yemekTarih, $simdi]);

        if($kontrol === false){
            // yorum yazamadık, neden? bilmem. yemeğin tarihi, yazarUuid filan yanlış olabilir belki
            \Core\Logger::warning("Burada garip şeyler oluyor, yorumYaz.\nyemekTarih: $yemekTarih\nyorum: $yorum\nustYorumId: $ustYorumId");
            return null;
        }

        return [
            "uuid" => $yorumUuid,
            "yazarUuid" => $this->bizimki["uuid"],
            "ustYorumId" => $ustYorumId,

            "yorum" => $yorum,

            "adaminYemekPuani" => $this->bizimkininYemegeVerdigiPuaniAl($yemekTarih),

            "like" => 0,
            "dislike" => 0,
            "kaldirildi" => 0,

            "yemekTarih" => $yemekTarih,
            "zaman" => $simdi
        ];
    }

    /**
     * Yorumu tamamen her şeyiyle silmek isteyenlere.
     * 
     * @param string $yorumUuid Yorumun uuid'si
     * 
     * @return ?array Silinen yorumun bilgileri, hata olduysa null.
     */
    public function yorumSilTamamen($yorumUuid): ?array {
        if($this->bizimki === null){
            return null;
        }

        // önce yorum var mı yok mu kontrol edelim
        // ayrıca bizimki mi yazmış yoksa başkası mı yazmış
        $yorum = $this->yorumBilgisiAl($yorumUuid);
        if($yorum === null || $yorum["yazarUuid"] !== $this->bizimki["uuid"]){
            return null;
        }

        // siliyoz tamamen
        $sql = "DELETE FROM yorumlar WHERE uuid = ?";
        $stmt = $this->pdo->prepare($sql);
        $kontrol = $stmt->execute([$yorumUuid]);

        if($kontrol === false){
            \Core\Logger::warning("Burada garip şeyler oluyor, yorumSil.\nyorumUuid: $yorumUuid");
            return null;
        }

        // zaten aynı bilgiler
        return $yorum;
    }

    /**
     * Bizimki yorumu silmek istemiş, utanmış.
     * 
     * @param string $yorumUuid Yorumun uuid'si
     * 
     * @return ?array Kaldırılan yorumun bilgileri, hata olduysa null.
     */
    public function yorumKaldir($yorumUuid): ?array {
        if($this->bizimki === null){
            return null;
        }

        // önce yorum var mı yok mu kontrol edelim
        // bizimki mi yazmış ayrıca, öyleyse kaldırabilir yoksa siktirsin gitsin yeter artık
        $yorum = $this->yorumBilgisiAl($yorumUuid);
        if($yorum === null || $yorum["yazarUuid"] !== $this->bizimki["uuid"]){
            return null;
        }

        // kaldırıyoz bu sefer
        $sql = "UPDATE yorumlar SET kaldirildi = 1 WHERE uuid = ?";
        $stmt = $this->pdo->prepare($sql);
        $kontrol = $stmt->execute([$yorumUuid]);

        if($kontrol === false){
            \Core\Logger::warning("Burada garip şeyler oluyor, yorumSil.\nyorumUuid: $yorumUuid");
            return null;
        }

        // kaldırıldı dışında aynı bilgiler
        $yorum["kaldirildi"] = 1;
        return $yorum;
    }

    /**
     * Bizimki oy verecekmiş bak hele. Sen o yaşlara geldin mi evladım?
     * Ülkenin geleceği için oy verme yaşına ulaştın imi?
     * 
     * @param string $yorumUuid Yorumun uuid'si.
     * @param bool $likeDislike like->true, dislike->false vereceksiniz.
     * 
     * @return ?array Yorumun güncel like dislike sayıları, hata olduysa null.
     */
    public function yorumOyVer($yorumUuid, $likeDislike){
        // yemegePuanVer'den kopyalıyorum direkt, değiştirecem.
        // vazgeçtim karışık geldi, hem burda ortalamayla filan uğraşmıyoruz

        // ne oluyo bilmiyorum ama likeDislike json'da false geliyo
        // sonra yazdırmaya çalışınca "" oluyor, null mu oluyor ne oluyorsa
        // bilmiyorum, db'ye bile 0 diye değil de boş string gibi bişeyle
        // geçiyor çok saçma
        // çözümünü bilen var mı?
        // şimdilik şöyle olsun
        // 
        // Yapılacak: bunu diğer bool alan yerlere de yap
        $likeDislike = $likeDislike ? 1 : 0;

        if($this->bizimki === null){
            return null;
        }

        try{
            $this->pdo->beginTransaction();

            // yine upsert yapacaz
            $bizimkininEskiOyu = $this->bizimkininYorumaVerdigiOyuAl($yorumUuid);

            if($bizimkininEskiOyu === $likeDislike){
                // zaten aynı oyu vermiş, sen mal mısın oğlum?
                $this->pdo->rollBack();
                return null;
            }

            if($bizimkininEskiOyu === null){
                // oy vermemiş, insert
                $sql = "INSERT INTO likedislike (like, zaman, kullaniciId, yorumId) VALUES (?, ?, ?, ?) RETURNING *";
            }
            else{
                // oy vermiş, update
                $sql = "UPDATE likedislike SET like = ?, zaman = ? WHERE kullaniciId = ? AND yorumId = ? RETURNING *";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$likeDislike, (new \DateTime())->format("Y-m-d H:i:s"), $this->bizimki["uuid"], $yorumUuid]);
            $guncelOy = $stmt->fetch();
            $stmt->closeCursor();

            if($guncelOy === false){
                // oy veremedik, neden? asla bilemem oğlum aslan. sen bişeyler mi yaptın bu telefona?
                // ne indirdin sen bunlar ne cillop gibi? internetten ne türlü şeyler indirdin sen bakayım?
                \Core\Logger::warning("Burada garip şeyler oluyor, yorumOyVer oy verme.\nyorumUuid: $yorumUuid\nlikeDislike: $likeDislike");
                
                $this->pdo->rollBack();
                return null;
            }

            // şimdi de yorumu güncelleyecez
            $likeDiff = 0;
            $dislikeDiff = 0;
            if($bizimkininEskiOyu === null){
                // direkt koyuyoruz
                if($likeDislike){
                    $likeDiff = 1;
                }
                else{
                    $dislikeDiff = 1;
                }
            }
            else if($bizimkininEskiOyu !== $likeDislike){
                // eski oyunu siliyoruz, sıfırlıyoruz, yeni oyu koyuyoruz
                // defterde tablo çizdim ona göre
                if($likeDislike){
                    $likeDiff = 1;
                    $dislikeDiff = -1;
                }
                else{
                    $likeDiff = -1;
                    $dislikeDiff = 1;
                }
            }
            
            $sql = 
            "UPDATE yorumlar
            SET 
                like = like + ?,
                dislike = dislike + ?
            WHERE uuid = ? RETURNING like, dislike
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$likeDiff, $dislikeDiff, $yorumUuid]);
            $guncelYorumOylari = $stmt->fetch();
            $stmt->closeCursor();

            if($guncelYorumOylari === false){
                // işte anladın sen yukarıda 100 tane var be abi neden olduğunu asla bilemeyiz falan filan

                \Core\Logger::warning("Burada garip şeyler oluyor, yorumOyVer yorumlar tablo güncelleme.\nyorumUuid: $yorumUuid\nlikeDislike: $likeDislike");

                $this->pdo->rollBack();
                return null;
            }

            $this->pdo->commit();
        } catch(\PDOException $e){
            $this->pdo->rollBack();
            \Core\Logger::error("yemegePuanVer PDOException\n" . $e->getMessage());
            return null;
        }

        return [
            "like" => $guncelYorumOylari["like"],
            "dislike" => $guncelYorumOylari["dislike"]
        ];
    }

    /**
     * Bizimki oyunu silmek istiyor, yanlış basmıştır heralde, dimi?
     * 
     * @param string $yorumUuid Yorumun uuid'si
     * 
     * @return ?array Güncel oy bilgileri, aslında verecek bişey yok ama olsun, yorumId veririz en kötü.
     */
    public function yorumOySil($yorumUuid){
        if($this->bizimki === null){
            return null;
        }

        try {
            $this->pdo->beginTransaction();

            // önce oy vermiş mi vermemiş mi kontrol edelim
            $bizimkininEskiOyu = $this->bizimkininYorumaVerdigiOyuAl($yorumUuid);

            if($bizimkininEskiOyu === null){
                // vermemiş, neyi siliyosun oğlum sen?
                return null;
            }

            // siliyoruz
            $sql = "DELETE FROM likedislike WHERE kullaniciId = ? AND yorumId = ?";
            $stmt = $this->pdo->prepare($sql);
            $kontrol = $stmt->execute([$this->bizimki["uuid"], $yorumUuid]);
            if($kontrol === false){
                // oy niye silinmedi Aslan? mal mal şeyler oluyor (olmaz heralde ama olsun)
                \Core\Logger::warning("Burada garip şeyler oluyor, yorumOySil silme.\nyorumUuid: $yorumUuid");

                return null;
            }

            // yorumu güncelliyoz
            $sql = 
            "UPDATE yorumlar
            SET 
                like = like - CASE WHEN ? THEN 1 ELSE 0 END,
                dislike = dislike - CASE WHEN ? THEN 1 ELSE 0 END
            WHERE uuid = ? RETURNING like, dislike
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$bizimkininEskiOyu, !$bizimkininEskiOyu, $yorumUuid]);
            $guncelYorumOylari = $stmt->fetch();
            $stmt->closeCursor();

            if($guncelYorumOylari === false){
                \Core\Logger::warning("Burada garip şeyler oluyor, yorumOySil yorumlar tablo güncelleme.\nyorumUuid: $yorumUuid");

                $this->pdo->rollBack();
                return null;
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            \Core\Logger::error("yorumOySil PDOException\n" . $e->getMessage());
            return null;
        }

        return [
            "like" => $guncelYorumOylari["like"],
            "dislike" => $guncelYorumOylari["dislike"]
        ];
    }

    /**
     * Yeni kullanıcı ekliyoz.
     * 
     * @param string $kullaniciAdi kullanıcı adı
     * @param string $eposta eposta
     * @param string $sifre şifre
     * @param string $dogrulamaNeZamanGonderdik doğrulama kodunu ne zaman gönderdik? (hiç göndermediysen null)
     * 
     * @return ?array Eklenen kullanıcının bilgileri
     */
    public function kullaniciEkle($kullaniciAdi, $eposta, $sifre, $dogrulamaNeZamanGonderdik = null){
        $yeniKul = [
            "uuid" => Utils::generateUUIDv4(),
            "kullaniciAdi" => $kullaniciAdi,
            "hash" => password_hash($sifre, PASSWORD_BCRYPT),
            "email" => $eposta,
            "emailDogrulandi" => 0,
            "dogrulamaNeZamanGonderdik" => $dogrulamaNeZamanGonderdik,
            "prestij" => 0,
            "katilmaTarihi" => (new \DateTime())->format("Y-m-d H:i:s"),
            "admin" => 0
        ];

        $sql = "INSERT INTO kullanicilar (uuid, kullaniciAdi, hash, email, emailDogrulandi, dogrulamaNeZamanGonderdik, prestij, katilmaTarihi, admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $kontrol = $stmt->execute([$yeniKul["uuid"], $yeniKul["kullaniciAdi"], $yeniKul["hash"], $yeniKul["email"], $yeniKul["emailDogrulandi"], $yeniKul["dogrulamaNeZamanGonderdik"], $yeniKul["prestij"], $yeniKul["katilmaTarihi"], $yeniKul["admin"]]);
    
        if($kontrol === false){
            \Core\Logger::error("Oğlum yeni adam ekleyemedik.\n" . print_r($yeniKul, true));
            return null;
        }

        return $yeniKul;
    }

    /**
     * Epostayı doğrulandı olarak işaretliyoz. (Artık hesabına girebilir.)
     * (Vallahi bilerek bu kadar uzun isim koydum fonksiyona)
     * 
     * @param string $eposta eposta
     * 
     * @return bool tamam mı değil mi
     */
    public function epostaDogrulandiOlarakIsaretle($eposta){
        $sql = "UPDATE kullanicilar SET emailDogrulandi = ? WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([true, $eposta]); // inşallah bool döndürüyodur (öyleymiş)
    }

    /**
     * Adama şimdi kod göndermişiz, dogrulamaNeZamanGonderdik = şimdi yapacaz.
     * 
     * @param string $adamId adamın uuid
     * 
     * @return bool tamam mı değil mi
     */
    public function adamaSimdiDogrulamaGonderdik($adamId){
        $simdi = new \DateTime();
        $sql = "UPDATE kullanicilar SET dogrulamaNeZamanGonderdik = ? WHERE uuid = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$simdi->format("Y-m-d H:i:s"), $adamId]);
    }

    /**
     * Adamın yorumlarının listesini al. (yeniden eskiye)
     * Adam var mı yok mu bunu çağırmadan önce kontrol et, burda kontrol yok.
     * (Adam yoksa [] döner aslında ama olsun)
     * 
     * @param string $adamId adamın uuid
     * @param int $limit ikk kaç tanesini alalım
     * @param ?\DateTime $eskiTarih hangi tarihten eski yorumlar alınsın?
     * mesela 2025 verdiyse sadece o tarihten önce yapılmış yorumları alır.
     * (profil kısmındaki 'daha fazla göster' kısmı için)
     * null ise şimdi demek
     * 
     * @return array yorumların listesi
     */
    public function adaminYorumlariniAl($adamId, $limit, $eskiTarih = null){
        $eskiTarihObj = ($eskiTarih === null) ? new \DateTime() : $eskiTarih;
        $eskiTarihStr = $eskiTarih->format("Y-m-d H:i:s");

        // daha eskiler lazım, zaman <= ? değil de zaman < ? yaptım
        $sql = "SELECT * FROM yorumlar WHERE yazarUuid = ? AND zaman < ? AND kaldirildi = 0 ORDER BY datetime(zaman) DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adamId, $eskiTarihStr, $limit]);
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();

        $yorumlar = [];
        foreach($rows as $row){
            $yorumlar[] = [
                "uuid" => $row["uuid"],

                "yazarUuid" => $row["yazarUuid"],

                "ustYorumId" => $row["ustYorumId"],

                "yorum" => $row["yorum"],
                "adaminYemekPuani" => $row["adaminYemekPuani"],

                "like" => $row["like"],
                "dislike" => $row["dislike"],

                // zaten kaldırılmışları seçmiyoruz bile
                // "kaldirildi" => $row["kaldirildi"],
                
                "yemekTarih" => (new \DateTime($row["yemekTarih"]))->format('Y-m-d'), // emin olalım
                "zaman" => (new \DateTime($row["zaman"]))->format('Y-m-d H:i:s') // emin olalım
            ];
        }

        return $yorumlar;
    }

    /**
     * yemekKoy.php için
     * 
     * @param array $yemek yemek data
     * 
     * @return bool oldu mu olmadı mı
     */
    public function yemekKoy($yemek){
        $sql = "INSERT INTO yemekler (tarih, menu, kalori, puan, puanSayisi) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$yemek["tarih"], $yemek["menu"], $yemek["kalori"], 0, 0]);
    }

    /**
     * Bizimkinin şikayetini alalım bakalım neler demiş.
     * 
     * @param string $yorumUuid yorumun uuid'si
     * 
     * @return ?array varsa direkt veriyoz, yoksa null
     */
    public function sikayetAl($yorumUuid){
        if($this->bizimki === null){
            return null;
        }

        $sql = "SELECT * FROM sikayetler WHERE sikayetciId = ? AND yorumId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->bizimki["uuid"], $yorumUuid]);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if($row === false){
            return null;
        }

        return [
            "sikayetciId" => $row["sikayetciId"],
            "yorumId" => $row["yorumUuid"],
            "zaman" => (new \DateTime($row["zaman"]))->format('Y-m-d H:i:s') // emin oluyoruz her zamanki gibi
        ];
    }

    /**
     * Şikayetçi olmuş bizimki.
     * 
     * @param string $yorumUuid yorumun uuid'si
     * 
     * @return ?array şikayet bilgileri, sıkıntı olduysa null
     */
    public function sikayetEt($yorumUuid){
        if($this->bizimki === null){
            return null;
        }

        $sql = "INSERT INTO sikayetler (sikayetciId, yorumId, zaman) VALUES (?, ?, ?) RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->bizimki["uuid"], $yorumUuid, (new \DateTime())->format('Y-m-d H:i:s')]);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if($row === false){
            return null;
        }

        return [
            "sikayetciId" => $row["sikayetciId"],
            "yorumId" => $row["yorumId"],
            "zaman" => (new \DateTime($row["zaman"]))->format('Y-m-d H:i:s') // emin oluyoruz her zamanki gibi
        ];
    }
}