<div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-soft dark:border-slate-800 dark:bg-slate-900">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-700">404</p>
        <h1 class="mt-4 text-3xl font-semibold">Rota nao encontrada</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-300">
            O caminho <?= e($path ?? '/') ?> nao esta disponivel nesta aplicacao.
        </p>
        <a href="<?= e(url('/')) ?>" class="mt-8 inline-flex rounded-full bg-brand-700 px-5 py-3 text-sm font-semibold text-white">
            Voltar ao dashboard
        </a>
    </div>
</div>
