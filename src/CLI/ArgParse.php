<?php

/**
 * Sooper.
 * Şunları alıyo şöyle:
 * --sinan=abi
 * --git_buradan
 * 
 * Şöyle veriyo:
 * key->sinan, value->abi diye
 * veya key->git_buradan, value->null
 * 
 * @param array $argv direkt $argv veriyon
 * 
 * @return array argüman listesi, key value ayrılmış şekilde
 */
function parseCliArgs($argv){
    $args = $argv;
    array_shift($args);

    $argumanlar = [];

    // bunun için regex öğrendim
    $regex = "/--([^=\s]+)(?:=(.+))?/";
    foreach ($args as $arg) {
        $gruplar = [];
        if(preg_match($regex, $arg, $gruplar)){
            $argumanlar[$gruplar[1]] = isset($gruplar[2]) ? $gruplar[2] : null;
        }
    }

    return $argumanlar;
}

/**
 * Zorunlu olanları filan hallet, yaz ekrana bişey bişey falan
 * 
 * @param array $argv argv direkt
 * @param array $zorunluKeyler zorunlu keyler işte
 * 
 * @return array argüman listesi
 */
function argHallet($argv, $zorunluKeyler){
    $args = parseCliArgs($argv);
    foreach ($zorunluKeyler as $key) {
        if(!array_key_exists($key, $args)){
            echo "--$key veya --$key=\"...\" filan vermen lazım oğlum.\n";
            die();
        }
    }

    return $args;
}