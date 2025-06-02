<?php
/**
 * Başka projemden aldım, biraz değiştirdim, jwt gibi yaptım.
 */

namespace Yemek;

use Core\Utils;
use Core\Dotenv;
use Core\OutputManager;

class Auth {
    public static function verifyToken($token){
        $base62 = new \Tuupola\Base62;
        $secret = Dotenv::getValue("AUTH_SECRET");
        
        $parts = explode(".", $token);
        if(count($parts) != 2){
            return null;
        }

        $dataJson = $base62->decode($parts[0]);
        try{
            $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if((new \DateTime())->getTimestamp() > $data["exp"]){
            return null;
        }
        
        $clientSignature = bin2hex($base62->decode($parts[1]));
        $expectedSignature = hash_hmac("sha3-256", $dataJson, $secret);
        return hash_equals($clientSignature, $expectedSignature) ? $data : null;
    }

    /**
     * Giriş tokeni oluşturmaktadır.
     * 
     * @param array $kullanici Klasik kullanıcı işte, ["uuid"] filan var biliyosun sen onu
     * @param \DateTime $expiration Tokenin ölüm zamanı (DateTime tipinde olacak)
     * 
     * @return string token
     */
    public static function generateToken($kullanici, $expiration){
        $base62 = new \Tuupola\Base62;
        $secret = Dotenv::getValue("AUTH_SECRET");
        
        $data = [
            "uid" => $kullanici["uuid"],
            "exp" => $expiration->getTimestamp()
        ];
        $dataJson = json_encode($data);
        
        return $base62->encode($dataJson) . "." . $base62->encode(hash_hmac("sha3-256", $dataJson, $secret, true));
    }

    public static function generateMailToken($eposta, $expiration){
        $base62 = new \Tuupola\Base62;
        $secret = Dotenv::getValue("EPOSTA_SECRET");
        
        $data = [
            "eposta" => $eposta,
            "exp" => $expiration->getTimestamp()
        ];
        $dataJson = json_encode($data);
        
        return $base62->encode($dataJson) . "." . $base62->encode(hash_hmac("sha3-256", $dataJson, $secret, true));
    }

    public static function verifyMailToken($token){
        $base62 = new \Tuupola\Base62;
        $secret = Dotenv::getValue("EPOSTA_SECRET");
        
        $parts = explode(".", $token);
        if(count($parts) != 2){
            return null;
        }

        $dataJson = $base62->decode($parts[0]);
        try{
            $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if((new \DateTime())->getTimestamp() > $data["exp"]){
            return null;
        }
        
        $clientSignature = bin2hex($base62->decode($parts[1]));
        $expectedSignature = hash_hmac("sha3-256", $dataJson, $secret);
        return hash_equals($clientSignature, $expectedSignature) ? $data : null;
    }

    /**
     * Bizimki kim? Kim giriş yapmış şu an? Bizimki nerelerde? Ne içiyor nereler ide?
     * 
     * @return ?array Bizimki giriş yapmışsa kimdir onu döndürür, giriş yapmamışsa anonim yani null.
     */
    public static function bizimkiKim(){
        if(!isset($_COOKIE["YEMEK_SESSION"])){
            return null;
        }
        
        $token = $_COOKIE["YEMEK_SESSION"];
        $tokenData = self::verifyToken($token);
        if($tokenData === null){
            return null;
        }
        
        $yu = new YemekUzmani(null); // anonim çünkü kontrol noktasındayız oğlum
        $kullanici = $yu->kullaniciAl($tokenData["uid"]);
        
        return $kullanici;
    }
}