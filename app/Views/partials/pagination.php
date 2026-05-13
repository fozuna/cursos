<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="mt-5 flex flex-wrap items-center gap-2">
        <?php for ($pageNumber = 1; $pageNumber <= (int) $pagination['pages']; $pageNumber++): ?>
            <?php
            $query = $query ?? [];
            $query['page'] = $pageNumber;
            $pageUrl = $baseUrl . '?' . http_build_query(array_filter($query, static fn ($value) => $value !== '' && $value !== 0 && $value !== null));
            ?>
            <a href="<?= e($pageUrl) ?>" class="rounded-full px-4 py-2 text-sm <?= $pageNumber === (int) $pagination['page'] ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200' ?>">
                <?= $pageNumber ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
