<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="mt-5 flex flex-wrap items-center gap-2">
        <?php for ($pageNumber = 1; $pageNumber <= (int) $pagination['pages']; $pageNumber++): ?>
            <?php
            $query = $query ?? [];
            $query['page'] = $pageNumber;
            $pageUrl = $baseUrl . '?' . http_build_query(array_filter($query, static fn ($value) => $value !== '' && $value !== 0 && $value !== null));
            ?>
            <a href="<?= e($pageUrl) ?>" class="action-button <?= $pageNumber === (int) $pagination['page'] ? 'action-button--primary' : '' ?>">
                <?= $pageNumber ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
