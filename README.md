# [yemek.horroz.org](https://yemek.horroz.org)
[Horroz.org](https://horroz.org) yemek değerlendirme ana bilgisayar sistemleri San. ve Tic. Ltd. Şti. gururla sunar.

## Ben bunu nası çalıştıracam kendim?
Şöyle efenim:
```bash
composer install

cd public
php -S 0.0.0.0:8080
```

## Neler kaldı
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
    - [ ] rütbe
- [X] query string kontrolünü getQueryData()'ya geçir her yerde
- [X] yemekKoy.php
- [X] sksdb scraper
    - [ ] site bok gibi olduğu için bazı özel durumlar var onları ayarlamak lazım
- [ ] yorum textarea kalan karakterler sağ altta yazacak
- [ ] yorum textarea Devamını göster tuşu olacak
- [ ] kalori yazsın
- [ ] şikayet
- [ ] yorum kontrol
- [ ] prestij güncellemesi
- [ ] zaman makinesi olayları (yapmıştık heralde ama yine bakalım çok önemli çünkü) (şaka yaptım o kadar önemli değil ama olsun)
    - [X] puanSil'de kalmış bi tane 
- [ ] topbarda kullanıcı adına tıklayınca küçük menü

## Bağış
Acilen [horroz.org'a yardımda bulunun](https://wiki.horroz.org/wiki/Horrozpedi:Bağış), batıyoruz. Şaka şaka. Ayağımı şu köşeye sileyim de ses çıkarmayın. Kim getirttirdi sizi buraya? Gelmeyin. (Galmayın.)
