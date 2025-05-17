<?php
namespace Core;

class Dotenv{
    public static function getValue($key){
        $envdata = parse_ini_file(BASE_PATH . "/.env");
        if(!array_key_exists($key, $envdata)){
            throw new \Exception("Environment property '$key' not found.");
        }
        return $envdata[$key];
    }

    public static function keyExists($key){
        $envdata = parse_ini_file(BASE_PATH . "/.env");
        return array_key_exists($key, $envdata);
    }
}