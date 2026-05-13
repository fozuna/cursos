<?php
$activeNav = $activeNav ?? 'courses';
$courseForm = $editingCourse ?? [];
$programValues = [
    'program_syllabus' => (string) ($courseForm['program_syllabus'] ?? $programSectionDefaults['syllabus']),
    'program_objectives' => (string) ($courseForm['program_objectives'] ?? $programSectionDefaults['objectives']),
    'program_modules' => (string) ($courseForm['program_modules'] ?? $programSectionDefaults['modules']),
    'program_methodology' => (string) ($courseForm['program_methodology'] ?? $programSectionDefaults['methodology']),
    'program_evaluation' => (string) ($courseForm['program_evaluation'] ?? $programSectionDefaults['evaluation']),
];
require base_path('app/Views/partials/admin_nav.php');
?>

<main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <?php require base_path('app/Views/partials/flash.php'); ?>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Cadastro</p>
                    <h2 class="mt-2 text-2xl font-semibold"><?= $editingCourse ? 'Editar curso' : 'Novo curso' ?></h2>
                </div>
                <?php if ($editingCourse): ?>
                    <a href="<?= e(url('/cursos')) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Novo cadastro</a>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= e(url('/cursos/salvar')) ?>" class="mt-6 space-y-4">
                <input type="hidden" name="course_id" value="<?= (int) ($courseForm['id'] ?? 0) ?>">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="field-card">
                        <span>Nome do curso</span>
                        <input type="text" name="name" value="<?= e((string) ($courseForm['name'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Carga horaria</span>
                        <input type="number" name="workload_hours" value="<?= e((string) ($courseForm['workload_hours'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Data de inicio</span>
                        <input type="date" name="start_date" value="<?= e((string) ($courseForm['start_date'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Data de termino</span>
                        <input type="date" name="end_date" value="<?= e((string) ($courseForm['end_date'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Instrutor</span>
                        <input type="text" name="instructor_name" value="<?= e((string) ($courseForm['instructor_name'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Instituicao executora</span>
                        <input type="text" name="institution_name" value="<?= e((string) ($courseForm['institution_name'] ?? $company['name'])) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Prefixo do certificado</span>
                        <input type="text" name="certificate_prefix" value="<?= e((string) ($courseForm['certificate_prefix'] ?? 'CERT')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300" <?= !isset($courseForm['is_active']) || (int) $courseForm['is_active'] === 1 ? 'checked' : '' ?>>
                        <span>Curso ativo para matriculas e certificados</span>
                    </label>
                </div>

                <div class="grid gap-4">
                    <label class="field-card">
                        <span><?= e($programSectionLabels['syllabus']) ?></span>
                        <textarea name="program_syllabus" rows="3" class="field-input min-h-28" required><?= e($programValues['program_syllabus']) ?></textarea>
                    </label>
                    <label class="field-card">
                        <span><?= e($programSectionLabels['objectives']) ?></span>
                        <textarea name="program_objectives" rows="3" class="field-input min-h-28" required><?= e($programValues['program_objectives']) ?></textarea>
                    </label>
                    <label class="field-card">
                        <span><?= e($programSectionLabels['modules']) ?></span>
                        <textarea name="program_modules" rows="5" class="field-input min-h-32" required><?= e($programValues['program_modules']) ?></textarea>
                    </label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="field-card">
                            <span><?= e($programSectionLabels['methodology']) ?></span>
                            <textarea name="program_methodology" rows="4" class="field-input min-h-28" required><?= e($programValues['program_methodology']) ?></textarea>
                        </label>
                        <label class="field-card">
                            <span><?= e($programSectionLabels['evaluation']) ?></span>
                            <textarea name="program_evaluation" rows="4" class="field-input min-h-28" required><?= e($programValues['program_evaluation']) ?></textarea>
                        </label>
                    </div>
                </div>

                <button type="submit" class="inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white dark:bg-white dark:text-slate-950">
                    <?= $editingCourse ? 'Salvar alteracoes' : 'Cadastrar curso' ?>
                </button>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Catalogo</p>
                    <h2 class="mt-2 text-2xl font-semibold">Cursos cadastrados</h2>
                </div>
                <form method="GET" action="<?= e(url('/cursos')) ?>" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar curso, instrutor ou instituicao" class="field-input min-w-[280px]">
                    <button type="submit" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold dark:border-slate-700">Buscar</button>
                </form>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="<?= e(url('/cursos/exportar/csv' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Exportar CSV</a>
                <a href="<?= e(url('/cursos/exportar/pdf' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Exportar PDF</a>
            </div>

            <div class="mt-6 space-y-4">
                <?php foreach ($courses['items'] as $course): ?>
                    <article class="rounded-3xl border border-slate-200 p-5 dark:border-slate-700">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold"><?= e($course['name']) ?></h3>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e($course['instructor_name']) ?> | <?= e($course['institution_name']) ?></p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e(format_date_br((string) $course['start_date'])) ?> a <?= e(format_date_br((string) $course['end_date'])) ?> | <?= (int) $course['workload_hours'] ?>h</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-2 text-xs <?= (int) $course['is_active'] === 1 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' ?>">
                                    <?= (int) $course['is_active'] === 1 ? 'Ativo' : 'Inativo' ?>
                                </span>
                                <a href="<?= e(url('/cursos?edit=' . (int) $course['id'])) ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Editar</a>
                                <form method="POST" action="<?= e(url('/cursos/excluir/' . (int) $course['id'])) ?>" onsubmit="return confirm('Deseja remover este curso?');">
                                    <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 dark:border-rose-900/60 dark:text-rose-300">Excluir</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm dark:bg-slate-950/50">
                                <p class="font-semibold">Matriculas ativas</p>
                                <p class="mt-1 text-slate-500 dark:text-slate-400"><?= (int) ($course['active_enrollments'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm dark:bg-slate-950/50">
                                <p class="font-semibold">Matriculas concluidas</p>
                                <p class="mt-1 text-slate-500 dark:text-slate-400"><?= (int) ($course['completed_enrollments'] ?? 0) ?></p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php
            $pagination = $courses;
            $baseUrl = url('/cursos');
            $query = ['search' => $search];
            require base_path('app/Views/partials/pagination.php');
            ?>
        </div>
    </section>
</main>
