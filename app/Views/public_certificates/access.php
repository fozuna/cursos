<div class="public-shell">
    <div class="public-card">
        <div class="page-header__row">
            <div>
                <p class="page-header__eyebrow">Download protegido</p>
                <h1 class="page-header__title">Acesso ao certificado do aluno</h1>
                <p class="page-header__subtitle">Informe apenas o numero do celular vinculado ao seu cadastro para liberar a pre-visualizacao e o download do certificado.</p>
            </div>
        </div>

        <?php if ($errorMessage !== null): ?>
            <div class="mt-6 surface-panel surface-panel--danger">
                <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Acesso indisponivel</p>
                <p class="mt-3 text-sm text-rose-900 dark:text-rose-100"><?= e($errorMessage) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($tokenData !== null): ?>
            <?php if ($certificate === null): ?>
                <section class="mt-8 surface-panel">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Confirmacao por celular</p>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Digite o celular cadastrado para este aluno no formato brasileiro `(XX) 9XXXX-XXXX`. O sistema so libera os dados do certificado apos a confirmacao.</p>
                    <form method="POST" action="<?= e(url('/certificados-publicos/acesso/' . $token)) ?>" class="mt-6 space-y-4" aria-label="Formulario publico de acesso ao certificado">
                        <input type="hidden" name="_csrf_token" value="<?= e($csrfToken) ?>">
                        <label class="field-card">
                            <span>Celular do aluno</span>
                            <input
                                type="text"
                                name="phone"
                                value="<?= e($formPhone) ?>"
                                class="field-input"
                                placeholder="(00) 90000-0000"
                                inputmode="numeric"
                                autocomplete="tel"
                                data-phone-mask
                                maxlength="15"
                                required
                            >
                            <small class="field-help">Somente o numero do celular e solicitado. Nenhum outro dado pessoal e usado nesta validacao.</small>
                        </label>
                        <button type="submit" class="action-button action-button--primary">Liberar certificado</button>
                    </form>
                </section>
            <?php else: ?>
                <div class="mt-8 public-grid">
                    <div class="surface-panel surface-panel--success">
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Acesso autorizado</p>
                        <p class="mt-3 text-sm text-emerald-900 dark:text-emerald-100">O certificado foi liberado para visualizacao e download nesta sessao segura.</p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Codigo</p>
                        <p class="mt-3 text-xl font-semibold"><?= e((string) $certificate['certificate_code']) ?></p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Curso</p>
                        <p class="mt-3 text-xl font-semibold"><?= e((string) $certificate['course_name']) ?></p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Aluno</p>
                        <p class="mt-3 text-xl font-semibold"><?= e((string) $certificate['full_name']) ?></p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Carga horaria</p>
                        <p class="mt-3 text-xl font-semibold"><?= e((string) $certificate['workload_hours']) ?> horas</p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Conclusao</p>
                        <p class="mt-3 text-xl font-semibold"><?= e(format_date_br((string) $certificate['completion_date'])) ?></p>
                    </div>
                </div>

                <div class="mt-8 surface-panel">
                    <div class="inline-toolbar">
                        <a href="<?= e(url('/certificados-publicos/acesso/' . $token . '/download')) ?>" class="action-button action-button--primary">Baixar certificado PDF</a>
                        <a href="<?= e(url('/certificados-publicos/acesso/' . $token)) ?>" class="action-button">Nova verificacao</a>
                    </div>
                    <div class="mt-6">
                        <iframe
                            src="<?= e(url('/certificados-publicos/acesso/' . $token . '/preview')) ?>"
                            class="certificate-preview-frame"
                            title="Pre-visualizacao do certificado"
                            loading="lazy"
                        ></iframe>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mt-8 surface-panel surface-panel--danger">
                <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Link indisponivel</p>
                <p class="mt-3 text-sm text-rose-900 dark:text-rose-100">Este link de acesso nao esta disponivel no momento. Solicite um novo link individual ao responsavel pela emissao.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
