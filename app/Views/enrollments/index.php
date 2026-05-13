<?php
$activeNav = $activeNav ?? 'enrollments';
require base_path('app/Views/partials/admin_nav.php');
?>

<main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <?php require base_path('app/Views/partials/flash.php'); ?>

    <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Nova matricula</p>
                    <h2 class="mt-2 text-2xl font-semibold">Matricular varios alunos</h2>
                </div>
                <form method="POST" action="<?= e(url('/matriculas/salvar')) ?>" class="mt-6 space-y-4">
                    <label class="field-card">
                        <span>Curso</span>
                        <select name="course_id" class="field-input" required>
                            <option value="">Selecione um curso</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= (int) $course['id'] ?>" <?= (int) $filters['course_id'] === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field-card">
                        <span>Status inicial</span>
                        <select name="status" class="field-input">
                            <option value="active">Ativa</option>
                            <option value="completed">Concluida</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="field-card">
                            <span>Data de matricula</span>
                            <input type="datetime-local" name="enrolled_at" value="<?= e(date('Y-m-d\TH:i')) ?>" class="field-input" required>
                        </label>
                        <label class="field-card">
                            <span>Data de conclusao</span>
                            <input type="datetime-local" name="completed_at" class="field-input">
                        </label>
                    </div>
                    <div class="rounded-3xl border border-slate-200 p-4 dark:border-slate-700">
                        <p class="text-sm font-semibold">Alunos</p>
                        <div class="mt-4 max-h-72 space-y-3 overflow-auto">
                            <?php foreach ($students as $student): ?>
                                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                                    <input type="checkbox" name="student_ids[]" value="<?= (int) $student['id'] ?>" class="mt-1 h-4 w-4 rounded border-slate-300">
                                    <span class="text-sm">
                                        <span class="block font-medium"><?= e($student['full_name']) ?></span>
                                        <span class="text-slate-500 dark:text-slate-400"><?= e((string) ($student['email'] ?? $student['cpf'] ?? 'Sem identificador')) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white dark:bg-white dark:text-slate-950">Criar matriculas</button>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-xl font-semibold">Relatorio por curso</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Selecione um curso no filtro ao lado para visualizar os alunos vinculados.</p>
                <?php if ($courseReport !== []): ?>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($courseReport as $item): ?>
                            <div class="rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                                <p class="font-medium"><?= e($item['full_name']) ?></p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($item['status']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Consulta</p>
                        <h2 class="mt-2 text-2xl font-semibold">Matriculas registradas</h2>
                    </div>
                    <form method="GET" action="<?= e(url('/matriculas')) ?>" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <input type="text" name="search" value="<?= e((string) $filters['search']) ?>" placeholder="Buscar aluno ou curso" class="field-input">
                        <select name="status" class="field-input">
                            <option value="">Todos os status</option>
                            <?php foreach (['active' => 'Ativa', 'completed' => 'Concluida', 'cancelled' => 'Cancelada'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="course_id" class="field-input">
                            <option value="0">Todos os cursos</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= (int) $course['id'] ?>" <?= (int) $filters['course_id'] === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="student_id" class="field-input">
                            <option value="0">Todos os alunos</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= (int) $student['id'] ?>" <?= (int) $filters['student_id'] === (int) $student['id'] ? 'selected' : '' ?>><?= e($student['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold dark:border-slate-700">Filtrar</button>
                    </form>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="<?= e(url('/matriculas/exportar/csv?' . http_build_query(array_filter($filters, static fn ($value) => $value !== '' && $value !== 0)))) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Exportar CSV</a>
                    <a href="<?= e(url('/matriculas/exportar/pdf?' . http_build_query(array_filter($filters, static fn ($value) => $value !== '' && $value !== 0)))) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Exportar PDF</a>
                </div>

                <div class="mt-6 overflow-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="pb-3 pr-4">Aluno</th>
                                <th class="pb-3 pr-4">Curso</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 pr-4">Datas</th>
                                <th class="pb-3">Atualizar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php foreach ($enrollments['items'] as $item): ?>
                                <tr>
                                    <td class="py-4 pr-4 font-medium"><?= e($item['full_name']) ?></td>
                                    <td class="py-4 pr-4"><?= e($item['course_name']) ?></td>
                                    <td class="py-4 pr-4"><?= e($item['status']) ?></td>
                                    <td class="py-4 pr-4 text-slate-500 dark:text-slate-400">
                                        <?= e(format_date_br(substr((string) $item['enrolled_at'], 0, 10))) ?>
                                        <?php if (!empty($item['completed_at'])): ?>
                                            <br>Conclusao: <?= e(format_date_br(substr((string) $item['completed_at'], 0, 10))) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4">
                                        <form method="POST" action="<?= e(url('/matriculas/status/' . (int) $item['id'])) ?>" class="flex flex-col gap-2">
                                            <select name="status" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                                                <?php foreach (['active' => 'Ativa', 'completed' => 'Concluida', 'cancelled' => 'Cancelada'] as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= $item['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="date" name="completed_at" value="<?= !empty($item['completed_at']) ? e(substr((string) $item['completed_at'], 0, 10)) : '' ?>" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                                            <button type="submit" class="rounded-full bg-slate-950 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-950">Salvar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                $pagination = $enrollments;
                $baseUrl = url('/matriculas');
                $query = $filters;
                require base_path('app/Views/partials/pagination.php');
                ?>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-xl font-semibold">Relatorio por aluno</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Selecione um aluno no filtro para ver todos os cursos em que ele esta matriculado.</p>
                <?php if ($studentReport !== []): ?>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($studentReport as $item): ?>
                            <div class="rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                                <p class="font-medium"><?= e($item['course_name']) ?></p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($item['status']) ?> | <?= (int) $item['workload_hours'] ?>h</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
