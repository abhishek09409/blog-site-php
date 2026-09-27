<?php foreach ($menus as $menu): ?>
    <section class="panel">
        <h2><?= e($menu['name']) ?></h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Label</th><th>URL</th><th>Order</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($menu['items'] as $item): ?>
                    <tr>
                        <td colspan="4">
                            <form class="inline-form" method="post" action="/admin/menus">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                                <input name="label" value="<?= e($item['label']) ?>" required>
                                <input name="url" value="<?= e($item['url']) ?>" required>
                                <input name="sort_order" type="number" value="<?= (int) $item['sort_order'] ?>">
                                <button>Save</button>
                            </form>
                            <form method="post" action="/admin/menus/delete/<?= (int) $item['id'] ?>" data-confirm="Remove this link?"><?= Csrf::field() ?><button>Remove</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <form class="inline-form" method="post" action="/admin/menus">
            <?= Csrf::field() ?>
            <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
            <input name="label" placeholder="Label" required>
            <input name="url" placeholder="/about" required>
            <input name="sort_order" type="number" value="0">
            <button class="btn">Add link</button>
        </form>
    </section>
<?php endforeach; ?>
