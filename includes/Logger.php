<?php

declare(strict_types=1);

class Logger
{
    public static function write(string $message, string $file = 'app.log'): void
    {
        $dir = base_path('storage/logs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $line = '[' . date('c') . '] ' . $message . PHP_EOL;
        file_put_contents($dir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function exception(Throwable $e): void
    {
        $previous = $e->getPrevious();
        $message = $e::class . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        if ($previous) {
            $message .= ' | caused by ' . $previous::class . ': ' . $previous->getMessage();
        }
        $message .= PHP_EOL . $e->getTraceAsString();
        self::write($message);
    }
}
