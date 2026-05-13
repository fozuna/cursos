<?php
$useAdminLayout = true;
$activeNav = 'certificates';
$activeSubNav = $activeSubNav ?? 'generate';
$pageHeaderEyebrow = 'Certificados';
$pageHeaderTitle = 'Emissao, consulta e rastreabilidade';
$pageHeaderSubtitle = 'Gere certificados em lote por curso, acompanhe a producao recente e acesse rapidamente a validacao publica.';
$pageHeaderActions = '<a href="' . e(url('/validar')) . '" class="action-button">Abrir validacao</a>';
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/flash.php'); ?>
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="admin-card stats-card">
        <div class="card-header card-header--tight">
            <div>
                <p class="card-kicker">Operacao</p>
                <h2 class="card-title">Panorama dos certificados</h2>
                <p class="card-subtitle">Visao rapida do volume emitido, proximas numeracoes e disponibilidade dos cursos ativos.</p>
            </div>
        </div>
        <div class="stats-grid">
            <article class="admin-card stats-card">
                <p class="stats-card__label">Emitidos</p>
                <h2 class="stats-card__value"><?= (int) ($stats['generated_count'] ?? 0) ?></h2>
                <p class="stats-card__meta">Arquivos PDF concluidos e prontos para download.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Pendentes</p>
                <h2 class="stats-card__value"><?= (int) ($stats['pending_count'] ?? 0) ?></h2>
                <p class="stats-card__meta">Registros aguardando finalizacao do processamento.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Cursos com emissao</p>
                <h2 class="stats-card__value"><?= (int) ($stats['unique_courses'] ?? 0) ?></h2>
                <p class="stats-card__meta">Programas com historico de certificados gerados.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Proximo codigo</p>
                <h2 class="stats-card__value"><?= e($nextCertificateCode) ?></h2>
                <p class="stats-card__meta">Numeracao sequencial reservada para a proxima emissao.</p>
            </article>
        </div>
    </section>

    <section id="gerar-certificados" class="admin-card form-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Geracao</p>
                <h2 class="card-title">Gerar certificados por curso</h2>
                <p class="card-subtitle">Selecione um curso concluido e deixe o sistema montar lote ZIP, codigos unicos, QR Code e arquivos PDF automaticamente.</p>
            </div>
            <div class="page-actions">
                <a href="<?= e(url('/cursos')) ?>" class="action-button">Gerenciar cursos</a>
                <a href="<?= e(url('/matriculas')) ?>" class="action-button">Ver matriculas</a>
            </div>
        </div>

        <form method="POST" action="<?= e(url('/certificados/gerar')) ?>" class="mt-6 space-y-4">
            <input type="hidden" name="input_mode" value="course">
            <input type="hidden" name="redirect_to" value="/certificados">
            <div class="form-grid form-grid--2">
                <label class="field-card">
                    <span>Curso ativo</span>
                    <select name="course_id" class="field-input" required>
                        <option value="">Selecione um curso para emissao</option>
                        <?php foreach ($activeCourses as $course): ?>
                            <option value="<?= (int) $course['id'] ?>"><?= e($course['name']) ?> | <?= e(format_date_br((string) $course['end_date'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-help">Somente cursos ativos com matriculas concluidas geram certificados automaticamente.</small>
                </label>
                <div class="summary-card">
                    <p class="card-kicker">Fluxo</p>
                    <h3 class="card-title">Emissao em lote</h3>
                    <p class="card-subtitle">A rotina usa o conteudo programatico do curso, monta a validacao publica e gera os arquivos com padrao visual unificado.</p>
                </div>
            </div>
            <button type="submit" class="action-button action-button--primary">Gerar certificados do curso</button>
        </form>
    </section>

    <section id="lista-certificados" class="admin-card table-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Listagem</p>
                <h2 class="card-title">Certificados recentes</h2>
                <p class="card-subtitle">Historico operacional com acesso rapido ao hash de validacao e ao download do PDF emitido.</p>
            </div>
        </div>

        <?php if ($latestCertificates === []): ?>
            <div class="empty-state">
                <h3 class="empty-state__title">Nenhum certificado emitido</h3>
                <p class="empty-state__text">Finalize matriculas e execute a primeira emissao para popular esta listagem.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Curso</th>
                            <th>Codigo</th>
                            <th>Status</th>
                            <th>Validacao</th>
                            <th>Arquivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestCertificates as $certificate): ?>
                            <tr>
                                <td>
                                    <div class="data-table__title"><?= e($certificate['full_name']) ?></div>
                                    <div class="data-table__meta"><?= e(format_date_br((string) $certificate['completion_date'])) ?></div>
                                </td>
                                <td>
                                    <div class="data-table__title"><?= e($certificate['course_name']) ?></div>
                                    <div class="data-table__meta"><?= e($company['name'] ?? config('app.name')) ?></div>
                                </td>
                                <td>
                                    <div class="data-table__title"><?= e($certificate['certificate_code']) ?></div>
                                </td>
                                <td>
                                    <span class="badge"><?= e((string) $certificate['status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= e(url('/validar/' . $certificate['validation_hash'])) ?>" class="action-button">Ver autenticidade</a>
                                </td>
                                <td>
                                    <?php if (!empty($certificate['pdf_path'])): ?>
                                        <a href="<?= e(url('/download/' . basename((string) $certificate['pdf_path']))) ?>" class="action-button">Download PDF</a>
                                    <?php else: ?>
                                        <span class="data-table__meta">Arquivo em processamento</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
