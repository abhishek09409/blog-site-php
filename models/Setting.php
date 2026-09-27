<?php

declare(strict_types=1);

class Setting
{
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $existing = db()->fetch('SELECT id FROM settings WHERE setting_key = ?', [$key]);
            if ($existing) {
                db()->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
            } else {
                db()->insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
            }
        }
    }
}
