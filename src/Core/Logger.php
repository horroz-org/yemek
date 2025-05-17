<?php
namespace Core;

class Logger {
    private const validLevels = ['ERROR', 'INFO', 'WARNING', 'DEBUG'];

    public static function log($message, $level = "INFO"){
        $level = strtoupper($level);
        if (!in_array($level, self::validLevels)) {
            throw new \InvalidArgumentException("Invalid log level: $level");
        }

        $date = new \DateTime();
        $logFile = BASE_PATH . "/logs/" . $date->format("Y-m-d") . ".log";
        
        $dateString = $date->format('Y-m-d H:i:s.v');
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? "CLI";

        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? $_SERVER['SCRIPT_NAME'];
        $requestUrl = $_SERVER['REQUEST_URI'] ?? "";
        // Strip query string, e.g. ?adnan=bey
        if (false !== $pos = strpos($requestUrl, '?')) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }
        $requestUrl = rawurldecode($requestUrl);

        $logString = "[$dateString] [$level] [$clientIp]\n";
        $logString .= "$httpMethod $requestUrl\n";
        $logString .= $message . "\n\n";

        file_put_contents($logFile, $logString, FILE_APPEND | LOCK_EX);
    }

    public static function error($message) { self::log($message, "ERROR"); }
    public static function info($message) { self::log($message, "INFO"); }
    public static function warning($message) { self::log($message, "WARNING"); }
    public static function debug($message) { self::log($message, "DEBUG"); }
}