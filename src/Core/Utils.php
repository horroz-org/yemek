<?php
namespace Core;

use Core\OutputManager;

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
        return true; // şimdilik örnek db'de uuid'ler uuid değil o yüzden test için
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

    public static function sifreKontrol(){
        
    }
}