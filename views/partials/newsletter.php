<?php
$compact = $compact ?? false;
$fieldId = 'email-' . bin2hex(random_bytes(3));
?>
<form class="newsletter-form<?= $compact ? ' is-compact' : '' ?>" action="/newsletter" method="post" data-newsletter>
    <?= Csrf::field() ?>
    <label class="sr-only" for="<?= e($fieldId) ?>">Email address</label>
    <input id="<?= e($fieldId) ?>" type="email" name="email" placeholder="Email address" required maxlength="190" autocomplete="email">
    <button class="btn btn-primary" type="submit">Subscribe</button>
    <p class="form-note" data-newsletter-note></p>
</form>
