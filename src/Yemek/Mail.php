<?php
namespace Yemek;

use Core\Utils;
use Core\Dotenv;
use Core\OutputManager;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail {
    public static function mailGonder($alici, $konu, $metin, $gondericiIsim = "Horroz.org Dağıtım Merkezi"){
        $mail = new PHPMailer(true);

        try {
            $smtpHost = Dotenv::getValue("SMTP_HOST");
            $smtpUsername = Dotenv::getValue("SMTP_USERNAME");
            $smtpPassword = Dotenv::getValue("SMTP_PASSWORD");
            
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // türkçe karakterler sıkıntı çıkarmasın
            $mail->CharSet = "UTF-8";
            $mail->Encoding = "base64";

            $mail->setFrom($smtpUsername, $gondericiIsim);
            $mail->addAddress($alici);

            $mail->isHTML(true);
            $mail->Subject = $konu;
            $mail->Body = $metin;
        
            $mail->send();
        } catch (Exception $e) {
            \Core\Logger::error("Mail gönderemedik.\n$alici");
            return false;
        }

        return true;
    }

    public static function dogrulamaGonder($eposta){
        $exp = (new \DateTime())->modify("+2 hours");
        $token = Auth::generateMailToken($eposta, $exp);

        // urlencode gerek yok aslında base62 olduğundan ama nokta var içinde 1 tane içimiz rahat olsun
        $link = Dotenv::getValue("APP_URL") . "/dogrula/?t=" . urlencode($token);
        $mailBody = "Hesabını açtık, bi tek linke basman kaldı. Linke de bastın mı tamamsın.<br><a href='$link'>Bas buraya</a><br><br>Basamıyorsan direkt şu linki kopyala filan:<br>$link";
        
        $kontrol = self::mailGonder($eposta, "Yemek Hesabını Doğrula", $mailBody);
        return $kontrol;
    }
}