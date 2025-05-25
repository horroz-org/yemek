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
            return false;
        }

        $dataJson = $base62->decode($parts[0]);
        try{
            $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return false;
        }

        if((new \DateTime())->getTimestamp() > $data["exp"]){
            return false;
        }
        
        $clientSignature = bin2hex($base62->decode($parts[1]));
        $expectedSignature = hash_hmac("sha3-256", $dataJson, $secret);
        return hash_equals($clientSignature, $expectedSignature) ? $data : false;
    }

    public static function generateToken($kullanici, $expiration = "+1 year"){
        $base62 = new \Tuupola\Base62;
        $secret = Dotenv::getValue("AUTH_SECRET");

        $data = [
            "uid" => $kullanici["uuid"],
            "exp" => (new \DateTime())->modify($expiration)->getTimestamp()
        ];
        $dataJson = json_encode($data);
        
        return $base62->encode($dataJson) . "." . $base62->encode(hash_hmac("sha3-256", $dataJson, $secret, true));
    }

    public static function giriliKullaniciyiAl(){
        if(!isset($_COOKIE["YEMEK_SESSION"])){
            return null;
        }
        
        $token = $_COOKIE["YEMEK_SESSION"];
        $tokenData = self::verifyToken($token);
        if($tokenData === false){
            return null;
        }
        
        $yu = new YemekUzmani(false); // anonim çünkü kontrol noktasındayız oğlum
        $kullanici = $yu->kullaniciAlGuvenli($tokenData["uid"]);

        if($kullanici === null){
            return null;
        }
        
        return $kullanici;
    }
}