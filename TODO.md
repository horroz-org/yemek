# Önemli şeyler
- [X] Utils.php:34 sakııııııın ama sakın unutma
- [X] Mail::dogrulamaGonder($eposta)
- [X] YemekUzmani::kullaniciEkle($kullaniciAdi, $eposta, $sifre)
- [X] Utils::kullaniciAdiKontrol
- [X] Utils::epostaKontrol ve Utils::sifreKontrol test etme
- [X] epostaDogrula.php
- [X] api php dosyalarının adlarını deve yapacaz (deve cüce oyunu)
- [X] Profil
    - [X] yorum/yorumlariniAl.php
    - [X] hesap/kullaniciAl.php
    - [X] yorum kısmında daha fazla göster tuşu olacak falan filan
    - [X] yoruma gite basınca yoruma gidecek idir
    - [X] rütbe
- [X] query string kontrolünü getQueryData()'ya geçir her yerde
- [X] yemekKoy.php
- [X] sksdb scraper
    - [X] site bok gibi olduğu için bazı özel durumlar var onları ayarlamak lazım (yapalı kaç gün oldu unuttum özel durum mözel durum, sonra hallederiz bunu yapıldı diyorum)
- [X] prestij güncellemesi (workers/prestij/prestijGuncelle.php'ye bakın, orda başta yazdığım şey hala var. hatta prestij güncellemesini 3 günde bire kadar çıkarabilirim onun için, bakarız.)
- [X] yorum textarea Devamını göster tuşu olacak (gerek kalmadı yorumlar zaten az satır olacak ama yine de yapılsa iyi olur)
- [X] yorum kontrol (sunucu kontrolde bazı sabitler var onları bi şekilde .env'e taşırız belki)
- [X] yorum textarea kalan karakterler sağ altta yazacak
- [X] kalori yazsın (çok iyi durmuyo azcık boşluk olsa iyi olur bence bi el atın)
- [X] rütbeyi neden db'de tutuyoz ulan biz? hesaplarız her zaman işte kısaca? ledırbordda da hesaplayıp buluruz ulan niye tutuyoz biz bunu? manyak mıyız? (sildim)
- [X] örnek .env
- [X] db'de isim değeriyle ilgili bişeyler yapak, silelim bence (silmeyip bunu işaretlemişim, şimdi sildim)
- [X] herkeseAcik bokundan kurtulduk
- [X] yorum kutusunda puanları görecekler (renkler biraz kötü ama olsun idare eder)
- [X] zaman makinesi olayları (yapmıştık heralde ama yine bakalım çok önemli çünkü) (şaka yaptım o kadar önemli değil ama olsun) (şimdi baktım kalmamış heralde)
    - [X] puanSil'de kalmış bi tane
- [X] captcha bokundan kurtuluyoruz
- [X] profilde adminlerin ismi kırmızı yazsın
- [X] topbarda kullanıcı adına tıklayınca küçük menü (veya çıkış yap butonu) (menüyle uğraşmadım çıkış butonunu koyuver idim)
    - [X] hatta github butonu yerine küçük logo olsun topbar kalabalık olmasın, ledırbord da koyacaz çünkü daha
- [X] şikayet
- [X] profil html title
- [X] yorumu silince direkt silinmeyecek, kaldırılacak, birisi yorumları alırken de kaldırıldı filan diye dönecek metni, ama uuid aynı olacak
- [X] kaldırılan yorumlarda oy verilmese iyi olabilir ama fark etmez
- [X] girişte şifreyi göster tuşu (kayıtta zaten vardı)

# Daha önemsiz şeyler
- [X] TemplateManager (sooper iş) (html'ler php'ye geçecek) (template ettik her şeyi, yorumlara gerek yoktu ama olsun, abarttık)
- [ ] belki profilde açıklama olabilir
- [ ] eposta doğrulama .env'de isteğe bağlı olsun, açıp kapatıl isin
- [ ] ledırbord
- [ ] admin panel
- [ ] yemek tarihi seçme
- [ ] mobile hoverli şeylere basınca takılı kalıyo
- [ ] sayfada herşey yüklenene kadar bomboş olsun, ortada yükleniyor filan yazsın belki
- [ ] çıkışta da redir koyalım

# Değişik şeyler için skriptler
- [ ] yeni kullanıcı aç
- [ ] falan filan

# İlerde yaparız belki
- [ ] her sayfayı index.php'ye bağlayak, ordan route yapak, mis olur
- [ ] Core More ayırak bi ara, composer paketi yapak, sonra da kullanırız