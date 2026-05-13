<?php if (!empty($flash)): ?>
    <section class="admin-card <?= ($flash['type'] ?? '') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100' : 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-100' ?>">
        <p class="text-sm font-semibold"><?= e($flash['message'] ?? '') ?></p>
    </section>
<?php endif; ?>
