<?php
namespace Core;

use Core\OutputManager;
use Core\Dotenv;

class Utils {
    public static function generateUUIDv7() {
        static $last_timestamp = 0;
        $unixts_ms = intval(microtime(true) * 1000);
        if ($last_timestamp >= $unixts_ms) {
            $unixts_ms = $last_timestamp + 1;
        }
        $last_timestamp = $unixts_ms;
        $data = random_bytes(10);
        $data[0] = chr((ord($data[0]) & 0x0f) | 0x70); // set version
        $data[2] = chr((ord($data[2]) & 0x3f) | 0x80); // set variant
        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(
                str_pad(dechex($unixts_ms), 12, '0', \STR_PAD_LEFT) .
                    bin2hex($data),
                4
            )
        );
    }

    public static function generateUUIDv4() {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // rfc 4122
    
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // https://stackoverflow.com/questions/136505/searching-for-uuids-in-text-with-regex
    public static function validateUUIDv4($str){
        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        return preg_match($regex, $str);
    }

    // bu utils.php nin tamamını diğer projemden koparmıştım, bunu da koparayım modern çağa atlayalım efendiler
    public static function getPostData($checkKeys = [], $defaults = []) {
        $postBody = file_get_contents("php://input");
        if (!json_validate($postBody)) {
            OutputManager::error("Bu nasıl json oğlum?", 400);
            die();
        }

        $json = json_decode($postBody, true);
        $finalData = [];
        if (!empty($checkKeys)) {
            foreach ($checkKeys as $key) {
                if (!array_key_exists($key, $json)) {
                    OutputManager::error("'$key' lazım efendiler?", 400);
                    die();
                }
                $finalData[$key] = $json[$key];
            }
        }

        // varsayilanlari da ekle
        foreach ($defaults as $key => $value) {
            if (array_key_exists($key, $json)) {
                $finalData[$key] = $json[$key];
            } else {
                $finalData[$key] = $value;
            }
        }

        return $finalData;
    }

    // getPostData'yı azıcık çekiştirdim
    public static function getQueryData($checkKeys = [], $defaults = []) {
        $finalData = [];
        if (!empty($checkKeys)) {
            foreach ($checkKeys as $key) {
                if (!array_key_exists($key, $_GET)) {
                    OutputManager::error("'$key' lazım efendiler?", 400);
                    die();
                }
                $finalData[$key] = $_GET[$key];
            }
        }

        // varsayilanlari da ekle
        foreach ($defaults as $key => $value) {
            if (array_key_exists($key, $_GET)) {
                $finalData[$key] = $_GET[$key];
            } else {
                $finalData[$key] = $value;
            }
        }

        return $finalData;
    }

    public static function isDebugModeOn(){
        if(php_sapi_name() === "cli"){
            return true;
        }
        else{
            return \Core\Dotenv::getValue("DEBUG_MODE");
        }
    }

    public static function isIsoDate($str){
        $regex = "/^\d{4}-([0][1-9]|1[0-2])-([0][1-9]|[1-2]\d|3[01])$/";
        return preg_match($regex, $str);
    }

    public static function buAdamBiseylerYapmayaCalisiyo(){
        OutputManager::error("Sen çok mu akıllısın he?");
        die();
    }

    // değişik karakterler falan filan var mı diye bakacaz
    // Minimum 3 harfli olsun
    // 2025-05-29 20:40 tamamen değiştiriyorum
    // boşluk moşluk olmasın
    // salak salak şeyler yaparlar kesin
    const izinVerilenKullaniciAdiKarakterleri = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_";
    public static function kullaniciAdiKontrol($kullaniciAdi){
        if(strlen($kullaniciAdi) < 3){
            return false;
        }

        foreach (str_split($kullaniciAdi) as $harf) {
            if(!str_contains(self::izinVerilenKullaniciAdiKarakterleri, $harf)){
                return false;
            }
        }

        return true;
    }

    // ceayet açık
    public static function epostaKontrol($eposta){
        if(filter_var($eposta, FILTER_VALIDATE_EMAIL)){
            $parcalar = explode('@', $eposta, 2);
            $domain = $parcalar[1];

            return $domain === Dotenv::getValue("EPOSTA_DOMAIN");
        }

        return false;
    }

    // küçük harf büyük harf rakam olacak
    // en az 8 hane olacak
    // vazgeçtim en az 6 hane olsun
    public static function sifreKontrol($str){
        return (strlen($str) >= 6) && preg_match('/[A-Z]/', $str) && preg_match('/[a-z]/', $str) && preg_match('/[0-9]/', $str);
    }
}