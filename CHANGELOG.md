# Changelog

Todas as mudancas relevantes do projeto devem ser registradas neste arquivo.

O formato adotado e inspirado em Keep a Changelog, com linguagem objetiva e orientada a entrega.

## [Unreleased]

### Added

- Politica formal de versionamento com commit e push obrigatorios apos cada conjunto de alteracoes concluido.
- Fluxo padronizado para mudancas de schema com criacao de migration, teste local, verificacao de rollback e atualizacao de documentacao.
- Documento operacional em `docs/versionamento-e-migrations.md`.
- Controle de reemissao de certificados com registro persistente por lote, bloqueio automatico de duplicatas, auditoria do solicitante e relatorio detalhado de itens gerados, bloqueados e falhos.
- Pagina publica de download de certificados por celular com token temporario individual de 7 dias, preview em PDF, download protegido, CSRF, rate limiting por IP e auditoria de acessos.

### Changed

- README atualizado com regras de versionamento, changelog e rotina obrigatoria para migrations.

## [2026-05-13]

### Added

- Modulos de cursos, alunos e matriculas integrados ao fluxo de certificados.
- Exportacoes CSV e PDF para os modulos administrativos.
- Design system administrativo com padrao vertical de layout e componentes reutilizaveis.

### Fixed

- Correcao de regressao visual no layout administrativo causada por utilitarios do Tailwind CDN sem fallback local suficiente em telas criticas.
- Ajuste de compatibilidade do menu lateral responsivo para navegadores com suporte legado a `matchMedia`.
- Ajustes de schema e preparacao automatica de diretorios no bootstrap da aplicacao.
- Correcao de geracao de certificados em duas paginas com QR Code e URL de validacao estabilizados.
