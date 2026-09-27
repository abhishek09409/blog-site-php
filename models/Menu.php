<?php

declare(strict_types=1);

class Menu
{
    public static function all(): array
    {
        $menus = db()->fetchAll('SELECT * FROM menus ORDER BY id ASC');
        foreach ($menus as &$menu) {
            $menu['items'] = self::items((string) $menu['location']);
        }
        return $menus;
    }

    public static function items(string $location): array
    {
        return db()->fetchAll(
            'SELECT i.* FROM menu_items i
             INNER JOIN menus m ON m.id = i.menu_id
             WHERE m.location = ? AND i.parent_id IS NULL
             ORDER BY i.sort_order ASC, i.id ASC',
            [$location]
        );
    }

    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM menus WHERE id = ?', [$id]);
    }

    public static function addItem(int $menuId, string $label, string $url, int $sort): int
    {
        return db()->insert('menu_items', [
            'menu_id' => $menuId,
            'label' => $label,
            'url' => $url,
            'sort_order' => $sort,
            'parent_id' => null,
        ]);
    }

    public static function updateItem(int $id, string $label, string $url, int $sort): void
    {
        db()->update('menu_items', [
            'label' => $label,
            'url' => $url,
            'sort_order' => $sort,
        ], 'id = ?', [$id]);
    }

    public static function deleteItem(int $id): void
    {
        db()->delete('menu_items', 'id = ?', [$id]);
    }
}
