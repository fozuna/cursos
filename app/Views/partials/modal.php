<?php
$modalId = $modalId ?? 'modal';
$modalTitle = $modalTitle ?? 'Detalhes';
$modalDescription = $modalDescription ?? '';
$modalBody = $modalBody ?? '';
$modalFooter = $modalFooter ?? '';
?>
<div
    id="<?= e($modalId) ?>"
    class="modal-backdrop"
    data-modal
    data-modal-id="<?= e($modalId) ?>"
    aria-hidden="true"
>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="<?= e($modalId) ?>-title" <?php if ($modalDescription !== ''): ?>aria-describedby="<?= e($modalId) ?>-description"<?php endif; ?>>
        <div class="modal-card__header">
            <div>
                <h2 id="<?= e($modalId) ?>-title" class="modal-card__title"><?= e($modalTitle) ?></h2>
                <?php if ($modalDescription !== ''): ?>
                    <p id="<?= e($modalId) ?>-description" class="modal-card__body"><?= e($modalDescription) ?></p>
                <?php endif; ?>
            </div>
            <button type="button" class="action-button" data-modal-close aria-label="Fechar modal">Fechar</button>
        </div>
        <div class="modal-card__body">
            <?= $modalBody ?>
        </div>
        <?php if ($modalFooter !== ''): ?>
            <div class="modal-card__footer">
                <?= $modalFooter ?>
            </div>
        <?php endif; ?>
    </div>
</div>
