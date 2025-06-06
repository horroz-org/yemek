<?php

if(setcookie("YEMEK_SESSION", "", 1, "/")) {
    header("Location: /");
} else {
    echo "Oğlum?";
    die();
}