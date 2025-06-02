<?php
// Dikkat:
//
// Şimdi prestijde şöyle bi sıkıntı var,
// eğer birisi yoruma oy verdi diyelim,
// sonra güncelleme oldu diyelim, adamın pretiji arttı/azaldı
// eğer güncellemeden sonra oyu değiştirirse veren kişi,
// adamın prestiji sonraki güncellemede yine artıcak/azalıcak
// bunu şu an engellemiycem çünkü prestij güncellemesi 24 saatte bir olacak,
// adam her gün arkadaşına söylese o bassa filan gün gün prestij kasabilir
// o da zaman alır uzun sürer o yüzden diyorum
//
// ama engellemek için şöyle bişey yapılabilir,
// likedislike tablosunda 2 tane o adamın o yoruma verdiği şey tutulur,
// oyunu sildiğini belirten bir şey de olması lazım,
// her güncellemede eğer adamın o yorum için 2 oy girdisi varsa
// öncekinden yola çıkarak en son girdi arasındaki fark hesaplanır
// ona göre prestij hesaplanır falan filan
// tam anlatamadım ama az önce düşünmüştüm mantıklı gelmişti
// mantıklı olmaya da bilir ama olsun
// bunun için işte bayağı bi oy sisteminin değişmesi lazım
// o yüzden asla uğraşamam
// değiştirmek isteyen pr atsın
// daha mantıklı bi önerisi olan da atabilir, kodu yazmak zorunda değil,
// sadece öneri diye issue atabilir
// bu kadar

require_once dirname(__DIR__, 2) . "/src/init.php";
require_once BASE_PATH . "/src/CLI/Utils.php";
require_once BASE_PATH . "/src/CLI/ArgParse.php";

use Core\Logger;
use Core\Utils;
use Yemek\YemekUzmani;

const sonGuncellemeDosya = __DIR__ . "/sonGuncelleme";

$yu = new YemekUzmani(null);

function basla(){
    global $argv;
    $args = argHallet($argv, []);

    if(array_key_exists("prestijSifirla", $args)){
        if(!prestijSifirla()){
            echo ":: Ulan sıfırlayamadık. Ne yaptınız?\n";
            die();
        }
        echo ":: Prestijler sıfırlandı.\n";
        die();
    }

    $sonGuncelleme = sonGuncellemeAl();
    if($sonGuncelleme === null){
        $sonGuncelleme = new \DateTime("1970-01-01 00:00:01");

        echo ":: İlk defa prestijler konulacak, son güncelleme yok.\n";
        echo ":: Prestijleri sıfırladık.\n\n";

        prestijSifirla();
    }
    
    $kontrol = prestijGuncelle($sonGuncelleme);

    if($kontrol === false){
        echo "Güncelleyemedik ulan. Neden? Bilmem.\n";
        die();
    }

    sonGuncellemeKaydet();

    echo "\n";
    echo ":: Tamamdır.\n";
    die();
}

/**
 * Son güncelleme tarihinden itibaren olan yorumların
 * layklarıyla eklenecek prestiji hesapla sonra da ekle
 * 
 * @param \DateTime $sonGuncelleme son güncelleme zamanı
 * 
 * @return bool oldu mu olmadı mı
 */
function prestijGuncelle($sonGuncelleme){
    global $yu;

    $sql = "UPDATE kullanicilar
    SET prestij = prestij + IFNULL((
        SELECT 
            SUM(
                CASE
                    WHEN likedislike.like = 1 THEN 1
                    WHEN likedislike.like = 0 THEN -1
                END
            )
        FROM likedislike
        JOIN yorumlar ON yorumlar.uuid = likedislike.yorumId
        WHERE yorumlar.yazarUuid = kullanicilar.uuid
            AND likedislike.zaman > :sonGuncelleme
            AND likedislike.kullaniciId != yorumlar.yazarUuid -- adam kendine oy veremez
    ), 0) RETURNING *";

    $stmt = $yu->pdo->prepare($sql);
    return $stmt->execute(["sonGuncelleme" => $sonGuncelleme->format("Y-m-d H:i:s")]);
}

/**
 * Prestijleri sıfırlıyoz, bu kadar.
 * 
 * @return bool oldu mu olmadı mı
 */
function prestijSifirla(){
    global $yu;

    $sql = "UPDATE kullanicilar SET prestij = 0, rutbe = 0";
    $stmt = $yu->pdo->prepare($sql);
    return $stmt->execute();
}

/**
 * Son güncellemeyi alak. Hiç güncelleme yoksa, dosya yoksa null.
 * 
 * @return ?\DateTime son güncelleme
 */
function sonGuncellemeAl(){
    if(!file_exists(sonGuncellemeDosya)){
        echo ":: Son güncelleme dosyası yokmuş, en baştan başlıyoruz.";
        // echo "-> En baştan başladığımız için herkesin prestijleri sıfırlanıyor.";

        // ilk başta yemekuzmanına ekleyecektim prestijSifirla diye
        // ama o class çok büyüdü artık
        // sadece burda kullanırız bi de o yüzden gerek yok
        // belki admin paneline ekleriz
        // hatta bu scripte komut argümanı ekleyelim --prestijSifirla diye
        // sıfırlayıp çıksın, onu bağlarız panele
        return null;
    }

    $tarih = new DateTime(trim(file_get_contents(sonGuncellemeDosya)));
    return $tarih;
}

function sonGuncellemeKaydet() {
    $simdi = (new \DateTime())->format("Y-m-d H:i:s");
    file_put_contents(sonGuncellemeDosya, $simdi);

    echo ":: Son güncelleme kaydedildi.\n";
    echo "-> Son güncelleme: $simdi\n";
}

basla();