<?php
define("BASE_PATH", dirname(__DIR__));
define("DB_DIR", BASE_PATH . "/database");

require_once BASE_PATH . "/vendor/autoload.php";

Core\ExceptionHandler::apply();
