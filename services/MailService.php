<?php

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public static function sendInvoice($email, $filePath)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'admin@ravatraacademy.id';
            $mail->Password = 'Gzzzl/6dU';

            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('admin@ravatraacademy.id', 'Ravatra Academy');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Invoice Pembelian';
            $mail->Body = '
                <h3>Terima kasih atas pembelian Anda</h3>
                <p>Invoice terlampir pada email ini.</p>
            ';

            $mail->addAttachment($filePath);

            $mail->send();

            return true;

        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            throw new Exception("Gagal kirim email: " . $mail->ErrorInfo);
        }
    }
}