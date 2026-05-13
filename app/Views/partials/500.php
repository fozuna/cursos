<div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-xl rounded-3xl border border-rose-200 bg-white p-10 text-center shadow-soft dark:border-rose-900/60 dark:bg-slate-900">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-rose-600">500</p>
        <h1 class="mt-4 text-3xl font-semibold">Falha ao processar a requisicao</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-300">
            <?= e($message ?? 'Erro interno no sistema.') ?>
        </p>
        <a href="<?= e(url('/')) ?>" class="mt-8 inline-flex rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
            Retornar ao sistema
        </a>
    </div>
</div>
