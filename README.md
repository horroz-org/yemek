# [yemek.horroz.org](https://yemek.horroz.org)
[Horroz.org](https://horroz.org) yemek değerlendirme ana bilgisayar sistemleri San. ve Tic. Ltd. Şti. gururla sunar.

Daha açmadık, açıyoz yavaş yavaş.

## Ben bunu nası çalıştıracam kendim?
Şöyle efenim:
```bash
composer install

cp .env.example .env
# şimdi .env'i düzenil eyin

php scripts/kur.php

# gerekli yemek alma scriptini çalıştır iyin ve yemekleri koyun
php scripts/yemekAlHacettepe.php yemekler.json
php scripts/yemekKoy.php yemekler.json

# apache/nginx vs. uğraşmak istemez iseniz,
# kısacık denemek için çalıştırıyor iseniz böyle yapın, 
# diğer türlü siz bilirsiniz
cd public
php -S 0.0.0.0:8080
```

### İşçileri crontab'a ekleyin:
- ``workers/prestij/prestijGuncelle.php`` -> 2-3 günde bir iyi ama siz bilirsiniz. [Zamanı ayarlamadan önce dosyanın başını okuyun](workers/prestij/prestijGuncelle.php), ondan sonra karar verin.

- ``scripts/yemekAl...php`` ve ``scripts/yemekKoy.php`` -> bunları da ayarlarsınız, otomatik alıp koyar rahatçana.

## Neler kaldı
[Bak bakalım sen de yardım edersin belki](TODO.md)

## Bağış
Acilen [horroz.org'a yardımda bulunun](https://wiki.horroz.org/wiki/Horrozpedi:Bağış), batıyoruz. Şaka şaka. Ayağımı şu köşeye sileyim de ses çıkarmayın. Kim getirttirdi sizi buraya? Gelmeyin. (Galmayın.)
