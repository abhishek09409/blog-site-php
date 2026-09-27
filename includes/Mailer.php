<?php

declare(strict_types=1);

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $dir = base_path('storage/logs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $entry = "----\nTo: {$to}\nSubject: {$subject}\n{$body}\n";
        file_put_contents($dir . '/mail.log', $entry, FILE_APPEND | LOCK_EX);
        if (!config('app.mail_enabled')) {
            return true;
        }
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        return mail($to, $subject, $body, $headers);
    }
}
