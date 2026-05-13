<section class="page-header">
    <div class="page-header__row">
        <div>
            <?php if (!empty($pageHeaderEyebrow)): ?>
                <p class="page-header__eyebrow"><?= e($pageHeaderEyebrow) ?></p>
            <?php endif; ?>
            <h1 class="page-header__title"><?= e($pageHeaderTitle ?? '') ?></h1>
            <?php if (!empty($pageHeaderSubtitle)): ?>
                <p class="page-header__subtitle"><?= e($pageHeaderSubtitle) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($pageHeaderActions)): ?>
            <div class="page-actions">
                <?= $pageHeaderActions ?>
            </div>
        <?php endif; ?>
    </div>
</section>
