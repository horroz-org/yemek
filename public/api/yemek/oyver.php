<?php
require_once dirname(__DIR__, 3) . "/src/init.php";

use Core\Utils;
use Core\OutputManager;
use Yemek\YemekUzmani;
use Yemek\Auth;

$inputJSON = file_get_contents('php://input');
$body = json_decode($inputJSON, TRUE);