# Guia de Componentes Visuais

Este projeto utiliza um padrao visual proprio em `public/assets/css/app.css`, mantendo compatibilidade com o carregamento atual por CDN, mas sem depender exclusivamente dele para o acabamento principal das telas administrativas.

## Estrutura global

- `admin-shell`: contenedor principal das telas administrativas.
- `section-stack`: empilhamento vertical padrao entre blocos da pagina.
- `page-header`: cabecalho principal de pagina com titulo, descricao e acoes.
- `admin-card`: base estrutural de qualquer bloco visual.

## Cards padrao

- `form-card`: bloco de cadastro e edicao.
- `filter-card`: bloco de busca, filtros e exportacoes.
- `table-card`: bloco de listagem e tabelas.
- `stats-card`: bloco de indicadores e dashboard.
- `summary-card`: bloco de apoio para relatorios, orientacoes e resumos.

## Formularios

- `form-grid`: grid base de campos.
- `form-grid--2`: duas colunas a partir de `768px`.
- `form-grid--3`: tres colunas a partir de `768px`.
- `field-card`: contenedor visual de campo.
- `field-input`: estilo padrao para `input`, `select` e `textarea`.
- `field-help`: texto auxiliar.
- `field-error`: texto de erro.

## Acoes

- `action-button`: botao base reutilizavel.
- `action-button--primary`: botao principal.

Estados previstos:

- `hover`
- `active`
- `focus-visible`
- `disabled`

## Tabelas

- `table-wrap`: contenedor com overflow horizontal controlado.
- `data-table`: tabela principal.
- `data-table__title`: destaque primario dentro da celula.
- `data-table__meta`: metadado ou informacao secundaria.
- `badge`: rotulo de status.

## Publico e feedback

- `public-shell` e `public-card`: paginas publicas, como validacao de certificado.
- `status-shell` e `status-card`: paginas de erro, como `404` e `500`.
- `empty-state`: estado vazio.
- `surface-panel`: painel informativo.
- `surface-panel--success`: sucesso.
- `surface-panel--danger`: erro/alerta.

## Modal reutilizavel

Arquivo:

- `app/Views/partials/modal.php`

Markup base:

```php
<?php
$modalId = 'modal-exemplo';
$modalTitle = 'Detalhes do item';
$modalDescription = 'Informacoes complementares para operacao.';
$modalBody = '<p>Conteudo interno do modal.</p>';
$modalFooter = '<button type="button" class="action-button" data-modal-close>Fechar</button>';
require base_path('app/Views/partials/modal.php');
?>
```

Acionamento:

```html
<button type="button" class="action-button" data-modal-open="modal-exemplo">
    Abrir modal
</button>
```

## Acessibilidade

Padroes considerados:

- foco visivel em componentes interativos;
- link para pular diretamente ao conteudo principal;
- estrutura semantica com regioes de cabecalho e conteudo;
- modal com `role="dialog"` e `aria-modal="true"`;
- contraste visual reforcado em estados de destaque;
- suporte a `prefers-reduced-motion`.

## Responsividade

Breakpoints considerados no CSS proprio:

- base mobile: `0px+`
- tablet: `768px+`
- desktop largo: `1280px+`

## Recomendacoes para novas telas

- seguir a ordem: `page-header` -> `form-card` -> `filter-card` -> `table-card` -> `summary-card`;
- evitar formularios gigantes ao lado de tabelas;
- usar `form-grid--2` e `form-grid--3` apenas quando a densidade do formulario justificar;
- manter acoes principais em `action-button--primary`;
- preferir uma unica tabela principal por pagina.
