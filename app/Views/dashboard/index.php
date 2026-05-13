<?php
$zipFile = $flash['zip_file'] ?? null;
$flashDetails = $flash['details'] ?? null;
$currentInputMode = $flash['input_mode'] ?? 'manual';
$institutionCnpj = (string) config('app.institution.cnpj');
$institutionLogo = asset((string) config('app.institution.logo_path'));
$programFields = $flash['program_fields'] ?? [];
$programValues = [
    'program_syllabus' => (string) ($programFields['program_syllabus'] ?? $programSectionDefaults['syllabus']),
    'program_objectives' => (string) ($programFields['program_objectives'] ?? $programSectionDefaults['objectives']),
    'program_modules' => (string) ($programFields['program_modules'] ?? $programSectionDefaults['modules']),
    'program_methodology' => (string) ($programFields['program_methodology'] ?? $programSectionDefaults['methodology']),
    'program_evaluation' => (string) ($programFields['program_evaluation'] ?? $programSectionDefaults['evaluation']),
];
?>
<div class="flex min-h-screen bg-slate-100 dark:bg-slate-950">
    <aside class="hidden w-80 flex-col border-r border-slate-200 bg-slate-950 px-8 py-10 text-white lg:flex">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">SaaS Premium</p>
            <h1 class="mt-3 text-3xl font-semibold"><?= e($company['name']) ?></h1>
            <p class="mt-3 text-sm text-slate-300">Gere certificados em lote com identidade corporativa, validacao publica e exportacao ZIP.</p>
        </div>

        <nav class="mt-12 space-y-3 text-sm">
            <a href="#dashboard" class="block rounded-2xl bg-white/10 px-4 py-3 font-medium">Dashboard</a>
            <a href="#geracao" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/5">Geracao</a>
            <a href="#preview" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/5">Preview</a>
            <a href="#historico" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/5">Historico</a>
            <a href="#validacao" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/5">Validacao</a>
        </nav>

        <div class="mt-auto rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Templates</p>
            <div class="mt-4 space-y-4">
                <?php foreach ($templates as $template): ?>
                    <div class="rounded-2xl border border-white/10 p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-medium"><?= e($template['name']) ?></span>
                            <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs text-emerald-300"><?= $template['is_default'] ? 'Padrao' : e($template['theme_mode']) ?></span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Tema <?= e($template['background_type']) ?> preparado para visual premium e multiempresa.</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
        <section id="dashboard" class="rounded-[2rem] bg-gradient-to-r from-slate-950 via-slate-900 to-brand-950 px-6 py-8 text-white shadow-soft">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs uppercase tracking-[0.35em] text-brand-100">Geracao automatica de certificados</p>
                    <h2 class="mt-3 text-3xl font-semibold sm:text-4xl">Painel corporativo com importacao, lote, PDF e validacao por QR Code</h2>
                    <p class="mt-4 text-sm leading-6 text-slate-300">Fluxo completo para treinamentos premium, universidades e academias corporativas com visao executiva e estrutura preparada para multiempresa.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" data-theme-toggle class="rounded-full border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold">Alternar tema</button>
                    <a href="#geracao" class="rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-900">Gerar certificados</a>
                </div>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <section class="mt-6 rounded-3xl border px-6 py-5 <?= $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-100' : 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-100' ?>">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold"><?= e($flash['message']) ?></p>
                        <p class="mt-1 text-xs opacity-80"><?= e($flashDetails ?: 'O sistema processa o lote, registra historico e disponibiliza os PDFs individuais.') ?></p>
                    </div>
                    <?php if ($zipFile): ?>
                        <a href="<?= e(url('/download/' . basename($zipFile))) ?>" class="inline-flex rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">Baixar ZIP do lote</a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total de certificados</p>
                <h3 class="mt-3 text-3xl font-semibold"><?= (int) ($stats['total'] ?? 0) ?></h3>
            </article>
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">Gerados com sucesso</p>
                <h3 class="mt-3 text-3xl font-semibold"><?= (int) ($stats['generated_count'] ?? 0) ?></h3>
            </article>
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">Pendentes ou com falha</p>
                <h3 class="mt-3 text-3xl font-semibold"><?= max(0, (int) ($stats['pending_count'] ?? 0)) ?></h3>
            </article>
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">Cursos diferentes</p>
                <h3 class="mt-3 text-3xl font-semibold"><?= (int) ($stats['unique_courses'] ?? 0) ?></h3>
            </article>
        </section>

        <section id="geracao" class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-brand-700 dark:text-brand-100">Entrada de dados</p>
                        <h3 class="mt-2 text-2xl font-semibold">Importe alunos ou cadastre manualmente</h3>
                    </div>
                    <div class="rounded-full bg-slate-100 px-3 py-2 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        CSV, XLSX e formulario manual
                    </div>
                </div>

                <form method="POST" action="<?= e(url('/certificados/gerar')) ?>" enctype="multipart/form-data" class="mt-6 space-y-6">
                    <input type="hidden" name="input_mode" value="<?= e($currentInputMode) ?>" data-input-mode>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                            <span class="text-sm font-semibold">Modo de entrada</span>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <button type="button" data-mode-choice="manual" class="mode-choice rounded-2xl border px-4 py-4 text-left transition">
                                    <span class="block text-sm font-semibold">Cadastro manual</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Preencha um participante e gere imediatamente.</span>
                                </button>
                                <button type="button" data-mode-choice="upload" class="mode-choice rounded-2xl border px-4 py-4 text-left transition">
                                    <span class="block text-sm font-semibold">Importar participantes</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Envie CSV ou XLSX e processe a lista em lote.</span>
                                </button>
                            </div>
                        </div>
                        <label class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                            <span class="text-sm font-semibold">Template visual</span>
                            <select name="template_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none ring-brand-500 focus:ring dark:border-slate-700 dark:bg-slate-950">
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= (int) $template['id'] ?>"><?= e($template['name']) ?> - <?= e($template['background_type']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <div data-manual-fields class="grid gap-4 md:grid-cols-2">
                        <label class="field-card">
                            <span>Nome do aluno</span>
                            <input type="text" name="full_name" value="Ana Carolina Martins" data-preview-input="student_name" class="field-input" required>
                        </label>
                        <label class="field-card">
                            <span>Curso</span>
                            <input type="text" name="course_name" value="Formacao em Lideranca Executiva" data-preview-input="course_name" class="field-input" required>
                        </label>
                        <label class="field-card">
                            <span>Carga horaria</span>
                            <input type="number" name="workload_hours" value="40" data-preview-input="workload_hours" class="field-input" required>
                        </label>
                        <label class="field-card">
                            <span>Data de conclusao</span>
                            <input type="date" name="completion_date" value="<?= date('Y-m-d') ?>" data-preview-input="completion_date" class="field-input" required>
                        </label>
                        <label class="field-card">
                            <span>Codigo do certificado</span>
                            <input type="text" name="certificate_code" value="<?= e($nextCertificateCode) ?>" data-preview-input="certificate_code" class="field-input">
                        </label>
                        <label class="field-card">
                            <span>Instrutor</span>
                            <input type="text" name="instructor_name" value="Dr. Ricardo Valente" data-preview-input="instructor_name" class="field-input" required>
                        </label>
                        <label class="field-card md:col-span-2">
                            <span>Instituicao</span>
                            <input type="text" name="institution_name" value="<?= e($company['name']) ?>" data-preview-input="institution_name" class="field-input" required>
                        </label>
                    </div>

                    <div data-upload-fields class="hidden rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-950/40">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="max-w-2xl">
                                <p class="text-sm font-semibold">Importacao de participantes</p>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Selecione um arquivo CSV ou XLSX com os participantes do curso. O sistema valida cabecalho, estrutura, carga horaria e dados obrigatorios antes de gerar os certificados.</p>
                                <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400">Cabecalho esperado</p>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">nome do aluno, curso, carga horaria, data de conclusao, codigo do certificado, instrutor, instituicao</p>
                            </div>
                            <a href="<?= e(asset('assets/templates/participantes-modelo.csv')) ?>" class="inline-flex rounded-full border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-900">
                                Baixar modelo CSV
                            </a>
                        </div>
                        <label class="mt-5 block">
                            <span class="text-sm font-semibold">Arquivo de participantes</span>
                            <input type="file" name="students_file" accept=".csv,.xlsx" data-upload-file class="mt-3 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-900">
                        </label>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Formatos</p>
                                <p class="mt-2 text-sm font-semibold">CSV e XLSX</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Limite</p>
                                <p class="mt-2 text-sm font-semibold">Ate 5 MB por arquivo</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Arquivo selecionado</p>
                                <p class="mt-2 text-sm font-semibold" data-upload-file-name>Nenhum arquivo selecionado</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-950/30">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="max-w-3xl">
                                <p class="text-sm font-semibold">Conteudo programatico do curso</p>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                    O verso do certificado sera preenchido automaticamente com este conteudo. Todos os campos abaixo sao obrigatorios antes da emissao.
                                </p>
                            </div>
                            <div class="rounded-full bg-slate-100 px-3 py-2 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                Obrigatorio para frente e verso
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4">
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
                                <textarea name="program_modules" rows="5" class="field-input min-h-36" required><?= e($programValues['program_modules']) ?></textarea>
                            </label>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="field-card">
                                    <span><?= e($programSectionLabels['methodology']) ?></span>
                                    <textarea name="program_methodology" rows="4" class="field-input min-h-32" required><?= e($programValues['program_methodology']) ?></textarea>
                                </label>
                                <label class="field-card">
                                    <span><?= e($programSectionLabels['evaluation']) ?></span>
                                    <textarea name="program_evaluation" rows="4" class="field-input min-h-32" required><?= e($programValues['program_evaluation']) ?></textarea>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold">Processamento em lote</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Barra visual demonstrativa para evolucao do fluxo de fila.</p>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Pronto para ZIP e historico</span>
                        </div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                            <div class="h-full w-2/3 rounded-full bg-gradient-to-r from-brand-500 to-cyan-400"></div>
                        </div>
                    </div>

                    <button type="submit" class="inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-white dark:text-slate-900">
                        Gerar certificados e exportar ZIP
                    </button>
                </form>
            </div>

            <div id="preview" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-brand-700 dark:text-brand-100">Preview</p>
                        <h3 class="mt-2 text-2xl font-semibold">Certificado A4 horizontal</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-2 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-300">Tempo real</span>
                </div>

                <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-100 p-3 dark:border-slate-700 dark:bg-slate-950">
                    <div class="certificate-preview relative mx-auto aspect-[1.414/1] w-full overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-8 text-slate-900 shadow-soft dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.12),_transparent_30%),radial-gradient(circle_at_bottom_left,_rgba(14,165,233,0.10),_transparent_25%)]"></div>
                        <div class="absolute inset-4 rounded-[1.5rem] border border-slate-200/80 dark:border-slate-700/80"></div>
                        <div class="relative flex h-full flex-col justify-between">
                            <div class="text-center">
                                <div class="mx-auto flex h-20 items-center justify-center">
                                    <img src="<?= e($institutionLogo) ?>" alt="Logo da instituicao" class="max-h-20 w-auto max-w-[220px] object-contain">
                                </div>
                                <h4 class="mt-3 text-3xl font-semibold" data-preview="title">Certificado de Conclusao</h4>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">Reconhecimento oficial de participacao e aproveitamento</p>
                                <p class="mt-2 text-xs uppercase tracking-[0.28em] text-slate-400">CNPJ <?= e($institutionCnpj) ?></p>
                            </div>

                            <div class="text-center">
                                <p class="text-sm uppercase tracking-[0.45em] text-slate-400">Concedido a</p>
                                <p class="mt-4 text-4xl font-semibold text-brand-950 dark:text-white" data-preview="student_name">Ana Carolina Martins</p>
                                <p class="mx-auto mt-5 max-w-3xl text-base leading-8 text-slate-600 dark:text-slate-300">
                                    Pela conclusao do curso <span class="font-semibold text-slate-900 dark:text-white" data-preview="course_name">Formacao em Lideranca Executiva</span>,
                                    com carga horaria total de <span class="font-semibold" data-preview="workload_hours">40</span> horas, finalizado em
                                    <span class="font-semibold" data-preview="completion_date"><?= e(format_date_br(date('Y-m-d'))) ?></span>.
                                </p>
                            </div>

                            <div class="grid grid-cols-[1fr_auto] items-end gap-6">
                                <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                                    <div>
                                        <div class="h-px w-44 bg-slate-300 dark:bg-slate-600"></div>
                                        <p class="mt-3 break-words text-sm font-semibold leading-6" data-preview="instructor_name">Dr. Ricardo Valente</p>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Instrutor responsavel</p>
                                    </div>
                                    <div>
                                        <div class="h-px w-44 bg-slate-300 dark:bg-slate-600"></div>
                                        <p class="mt-3 break-words text-sm font-semibold leading-6" data-preview="institution_name"><?= e($company['name']) ?></p>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Instituicao emissora</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">CNPJ <?= e($institutionCnpj) ?></p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Codigo do certificado</p>
                                        <p class="mt-2 text-sm font-semibold" data-preview="certificate_code"><?= e($nextCertificateCode) ?></p>
                                    </div>
                                </div>
                                <div class="rounded-3xl border border-slate-200 bg-white p-4 text-center shadow-sm dark:border-slate-700 dark:bg-slate-950">
                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100 text-[10px] uppercase tracking-[0.3em] text-slate-500 dark:bg-slate-800 dark:text-slate-300">QR</div>
                                    <p class="mt-3 text-[10px] uppercase tracking-[0.3em] text-slate-400">Validacao online</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="historico" class="mt-6 grid gap-6 xl:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold">Certificados recentes</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Ultimos emitidos</span>
                </div>
                <div class="mt-5 overflow-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="pb-3 pr-4">Aluno</th>
                                <th class="pb-3 pr-4">Curso</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3">Arquivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php foreach ($latestCertificates as $certificate): ?>
                                <tr>
                                    <td class="py-4 pr-4 font-medium"><?= e($certificate['full_name']) ?></td>
                                    <td class="py-4 pr-4"><?= e($certificate['course_name']) ?></td>
                                    <td class="py-4 pr-4">
                                        <span class="rounded-full px-3 py-1 text-xs <?= $certificate['status'] === 'generated' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' ?>">
                                            <?= e($certificate['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <?php if (!empty($certificate['pdf_path'])): ?>
                                            <a href="<?= e(url('/download/' . basename($certificate['pdf_path']))) ?>" class="text-brand-700 hover:underline dark:text-brand-100">Baixar PDF</a>
                                        <?php else: ?>
                                            <span class="text-slate-400">Indisponivel</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-xl font-semibold">Lotes recentes</h3>
                    <div class="mt-5 space-y-4">
                        <?php foreach ($latestBatches as $batch): ?>
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="font-medium"><?= e($batch['name']) ?></p>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs dark:bg-slate-800"><?= e($batch['status']) ?></span>
                                </div>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    <?= (int) $batch['processed_items'] ?>/<?= (int) $batch['total_items'] ?> processados via <?= e($batch['import_source']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="validacao" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-xl font-semibold">Validacao publica</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">Cada certificado recebe hash SHA-256 unico e URL individual. A pagina publica confirma autenticidade e reduz fraude documental.</p>
                    <div class="mt-5 rounded-2xl bg-slate-100 p-4 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        Exemplo: <?= e(url('/validar/SEU_HASH_AQUI')) ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold">Historico de geracao</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Auditoria operacional</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($history as $item): ?>
                    <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold"><?= e($item['action']) ?></p>
                            <span class="rounded-full px-3 py-1 text-xs <?= $item['status'] === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($item['status'] === 'error' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300') ?>">
                                <?= e($item['status']) ?>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300"><?= e($item['message']) ?></p>
                        <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400"><?= e($item['created_at']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
