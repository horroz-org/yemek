<?php
namespace Core;

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

    public static function isDebugModeOn(){
        if(php_sapi_name() === "cli"){
            return true;
        }
        else{
            return \Core\Dotenv::getValue("DEBUG_MODE");
        }
    }

    public static function isIsoDate($str){
        $regex = "^\d{4}-([0][1-9]|1[0-2])-([0][1-9]|[1-2]\d|3[01])$";
        return preg_match($regex, $date);
    }
}