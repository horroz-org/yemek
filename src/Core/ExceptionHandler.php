<?php
namespace Core;

class ExceptionHandler{
    private function __construct(){}
    
    public static function exceptionHandler(\Throwable $exception){
        $error = "Internal server error.";
        $debug = "Exception at " . $exception->getFile() . ":" . $exception->getLine()  . " -> " . $exception->getMessage();
        if(\Core\Utils::isDebugModeOn()){
            $error = $debug;
        }
        
        if(php_sapi_name() === 'cli'){
            echo("Hata meydana geldi, kayıtlara geçti. Açıklama:\n$error");
        }
        else{
            \Core\OutputManager::error($error, 500);
        }

        \Core\Logger::error($debug);
        
        die();
    }

    public static function apply(){
        set_exception_handler([self::class, 'exceptionHandler']);
    }
}


