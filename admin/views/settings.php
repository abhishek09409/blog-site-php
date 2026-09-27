<form class="panel stack" method="post" action="/admin/settings" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <div class="form-grid">
        <label>Site name<input name="site_name" required value="<?= e(old('site_name', setting('site_name'))) ?>"><?= field_error('site_name') ?></label>
        <label>Primary color<input name="primary_color" value="<?= e(old('primary_color', setting('primary_color', '#F05A2A'))) ?>"><?= field_error('primary_color') ?></label>
        <label>Email<input name="email" value="<?= e(old('email', setting('email'))) ?>"></label>
        <label>Phone<input name="phone" value="<?= e(old('phone', setting('phone'))) ?>"></label>
        <label class="span-2">Description<textarea name="site_description" rows="3"><?= e(old('site_description', setting('site_description'))) ?></textarea></label>
        <label class="span-2">Address<input name="address" value="<?= e(old('address', setting('address'))) ?>"></label>
        <label>Facebook<input name="facebook" value="<?= e(old('facebook', setting('facebook'))) ?>"></label>
        <label>Instagram<input name="instagram" value="<?= e(old('instagram', setting('instagram'))) ?>"></label>
        <label>X / Twitter<input name="twitter" value="<?= e(old('twitter', setting('twitter'))) ?>"></label>
        <label>LinkedIn<input name="linkedin" value="<?= e(old('linkedin', setting('linkedin'))) ?>"></label>
        <label>YouTube<input name="youtube" value="<?= e(old('youtube', setting('youtube'))) ?>"></label>
        <label class="span-2">Footer text<textarea name="footer_text" rows="3"><?= e(old('footer_text', setting('footer_text'))) ?></textarea></label>
        <label>Google Analytics ID<input name="ga_id" value="<?= e(old('ga_id', setting('ga_id'))) ?>" placeholder="G-XXXX"><?= field_error('ga_id') ?></label>
        <label>Search Console verification<input name="gsc_verification" value="<?= e(old('gsc_verification', setting('gsc_verification'))) ?>"><?= field_error('gsc_verification') ?></label>
        <label>Default SEO title<input name="seo_title" value="<?= e(old('seo_title', setting('seo_title'))) ?>"></label>
        <label>Default SEO description<input name="seo_description" value="<?= e(old('seo_description', setting('seo_description'))) ?>"></label>
        <label>Logo<input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp"></label>
        <label>Favicon<input type="file" name="favicon" accept="image/jpeg,image/png,image/gif,image/webp"></label>
    </div>
    <button class="btn" type="submit">Save settings</button>
</form>
