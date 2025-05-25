<?php
namespace Core;

class OutputManager{
    public static function error($message, $statusCode = 400){
        if($statusCode != 0 && $statusCode != null){
            http_response_code($statusCode);
        }
        
        self::outputJSON([
            "error" => $message
        ]);
    }

    public static function info($message, $statusCode = 200){
        if($statusCode != 0 && $statusCode != null){
            http_response_code($statusCode);
        }
        
        self::outputJSON([
            "info" => $message
        ]);
    }

    public static function outputJSON($obj){
        header("Content-Type: application/json");
        echo json_encode($obj);
    }

    public static function outputFile($mimeType, $downloadFilename, $filePath){
        if(!file_exists($filePath)){
            throw new \Exception("File not found.");
        }

        header("Content-Type: $mimeType");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filePath));
        header("Accept-Ranges: bytes");
        header("Content-Disposition: attachment; filename=\"$downloadFilename\"");

        ob_clean();
        flush();

        readfile($filePath);

        die();
    }

    public static function outputPlain($text){
        header("Content-Type: text/plain; charset=UTF-8");
        echo $text;
    }

    public static function handleCORS($path){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
    }
}