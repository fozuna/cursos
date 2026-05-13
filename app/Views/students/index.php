<?php
$activeNav = $activeNav ?? 'students';
$studentForm = $editingStudent ?? [];
require base_path('app/Views/partials/admin_nav.php');
?>

<main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <?php require base_path('app/Views/partials/flash.php'); ?>

    <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Cadastro</p>
                    <h2 class="mt-2 text-2xl font-semibold"><?= $editingStudent ? 'Editar aluno' : 'Novo aluno' ?></h2>
                </div>
                <?php if ($editingStudent): ?>
                    <a href="<?= e(url('/alunos')) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Novo cadastro</a>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= e(url('/alunos/salvar')) ?>" class="mt-6 space-y-4">
                <input type="hidden" name="student_id" value="<?= (int) ($studentForm['id'] ?? 0) ?>">
                <label class="field-card">
                    <span>Nome completo</span>
                    <input type="text" name="full_name" value="<?= e((string) ($studentForm['full_name'] ?? '')) ?>" class="field-input" required>
                </label>
                <label class="field-card">
                    <span>E-mail</span>
                    <input type="email" name="email" value="<?= e((string) ($studentForm['email'] ?? '')) ?>" class="field-input">
                </label>
                <label class="field-card">
                    <span>Telefone</span>
                    <input type="text" name="phone" value="<?= e((string) ($studentForm['phone'] ?? '')) ?>" class="field-input" data-phone-mask placeholder="(00) 00000-0000">
                </label>
                <label class="field-card">
                    <span>CPF</span>
                    <input type="text" name="cpf" value="<?= e((string) ($studentForm['cpf'] ?? '')) ?>" class="field-input" data-cpf-mask placeholder="000.000.000-00">
                </label>
                <button type="submit" class="inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white dark:bg-white dark:text-slate-950">
                    <?= $editingStudent ? 'Salvar alteracoes' : 'Cadastrar aluno' ?>
                </button>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Base de alunos</p>
                    <h2 class="mt-2 text-2xl font-semibold">Consulta e manutencao</h2>
                </div>
                <form method="GET" action="<?= e(url('/alunos')) ?>" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar por nome, e-mail, telefone ou CPF" class="field-input min-w-[280px]">
                    <button type="submit" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold dark:border-slate-700">Buscar</button>
                </form>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="<?= e(url('/alunos/exportar/csv' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Exportar CSV</a>
                <a href="<?= e(url('/alunos/exportar/pdf' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Exportar PDF</a>
            </div>

            <div class="mt-6 overflow-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="pb-3 pr-4">Nome</th>
                            <th class="pb-3 pr-4">E-mail</th>
                            <th class="pb-3 pr-4">Telefone</th>
                            <th class="pb-3 pr-4">CPF</th>
                            <th class="pb-3">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <?php foreach ($students['items'] as $student): ?>
                            <tr>
                                <td class="py-4 pr-4 font-medium"><?= e($student['full_name']) ?></td>
                                <td class="py-4 pr-4"><?= e((string) ($student['email'] ?? '')) ?></td>
                                <td class="py-4 pr-4"><?= e((string) ($student['phone'] ?? '')) ?></td>
                                <td class="py-4 pr-4"><?= e((string) ($student['cpf'] ?? '')) ?></td>
                                <td class="py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?= e(url('/alunos?edit=' . (int) $student['id'])) ?>" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold dark:border-slate-700">Editar</a>
                                        <form method="POST" action="<?= e(url('/alunos/excluir/' . (int) $student['id'])) ?>" onsubmit="return confirm('Deseja remover este aluno?');">
                                            <button type="submit" class="rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 dark:border-rose-900/60 dark:text-rose-300">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $pagination = $students;
            $baseUrl = url('/alunos');
            $query = ['search' => $search];
            require base_path('app/Views/partials/pagination.php');
            ?>
        </div>
    </section>
</main>
