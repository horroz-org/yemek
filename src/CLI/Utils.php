<?php

function soru($soru, $default = ""){
    echo($soru . ($default ? " ($default): " : ": "));
    $input = trim(fgets(STDIN));
    return $input === "" ? $default : $input;
}

function yesno($soru, $yesDefault){
    echo($soru . ($yesDefault ? " [Y/n]: " : " [y/N]: "));
    $input = strtolower(trim(fgets(STDIN)));
    if($input === ""){
        return $yesDefault;
    }
    else if($input == "y"){
        return true;
    }
    else if($input == "n"){
        return false;
    }
    else{
        return yesno($soru, $yesDefault);
    }
}