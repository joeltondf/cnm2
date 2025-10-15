<?php /** @var int $municipiosCount */ ?>
<div class="row g-4 align-items-stretch">
    <div class="col-lg-8">
        <div class="card hero-card shadow-sm border-0 h-100">
            <div class="card-body">
                <h1 class="display-6 fw-bold">Explore o RREO de forma intuitiva</h1>
                <p class="lead">Analise receitas, despesas, comparativos e indicadores do Relatório Resumido de Execução Orçamentária (RREO) publicado pelo Tesouro Nacional para cada município brasileiro.</p>
                <ul class="mb-0 list-unstyled">
                    <li class="mb-2"><i class="bi bi-graph-up"></i> Dados atualizados diretamente da API FINBRA</li>
                    <li class="mb-2"><i class="bi bi-building"></i> <?= number_format($municipiosCount, 0, ',', '.'); ?> municípios cadastrados</li>
                    <li><i class="bi bi-shield-lock"></i> Segurança com CSRF e consultas sob demanda</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <h5 class="card-title">Como funciona?</h5>
                <ol class="small mb-4">
                    <li>Selecione o município e o período desejado.</li>
                    <li>Escolha o anexo e visualize tabelas e gráficos dinâmicos.</li>
                    <li>Exporte resultados em CSV, PDF ou imprima.</li>
                </ol>
                <a href="#consulta" class="btn btn-primary">Comece agora</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error)) : ?>
    <div class="alert alert-danger mt-4" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<section id="consulta" class="mt-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 mb-0">Consulta ao RREO</h2>
                        <small class="text-muted">Selecione os filtros conforme o portal FINBRA.</small>
                    </div>
                    <span class="badge bg-success">Consulta segura</span>
                </div>
                <div class="card-body">
                    <form method="post" action="index.php?page=api-rreo" data-rreo-form data-target="#resultado" data-context="single" data-requires-ibge="true">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="ibge" value="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="municipio" class="form-label">Município</label>
                                <input type="text" class="form-control" id="municipio" name="municipio_nome" list="municipioOptions" placeholder="Digite o nome do município" autocomplete="off" required>
                                <datalist id="municipioOptions"></datalist>
                                <div class="form-text">Digite pelo menos duas letras para buscar.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="form-label">Ano</label>
                                <input type="number" class="form-control" id="year" name="year" min="2010" max="<?= date('Y'); ?>" value="<?= date('Y'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="periodicity" class="form-label">Periodicidade</label>
                                <select class="form-select" id="periodicity" name="periodicity" required>
                                    <option value="bimestral">Bimestral</option>
                                    <option value="quadrimestral">Quadrimestral</option>
                                    <option value="semestral">Semestral</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="period" class="form-label">Período</label>
                                <select class="form-select" id="period" name="period" required>
                                    <option value="1">1º Período</option>
                                    <option value="2">2º Período</option>
                                    <option value="3">3º Período</option>
                                    <option value="4">4º Período</option>
                                    <option value="5">5º Período</option>
                                    <option value="6">6º Período</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="scope" class="form-label">Abrangência</label>
                                <select class="form-select" id="scope" name="scope" required>
                                    <option value="consolidado">Consolidado</option>
                                    <option value="poder-executivo">Poder Executivo</option>
                                    <option value="poder-legislativo">Poder Legislativo</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Consultar</button>
                            <button type="reset" class="btn btn-outline-secondary">Limpar</button>
                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#metodologiaModal">
                                <i class="bi bi-info-circle"></i> Metodologia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h3 class="h6 mb-0">Dicas rápidas</h3>
                </div>
                <div class="card-body">
                    <ul class="small ps-3 mb-0">
                        <li>Utilize as abas para navegar entre anexos e tabelas.</li>
                        <li>Faça download em CSV para integrar a planilhas.</li>
                        <li>Os gráficos são gerados automaticamente a partir da tabela.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-5">
    <div class="breadcrumb-area p-3 shadow-sm">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#consulta">Consulta</a></li>
                <li class="breadcrumb-item active" aria-current="page">Resultados</li>
            </ol>
        </nav>
    </div>
    <div id="resultado" class="mt-4"></div>
</section>

<div class="modal fade" id="metodologiaModal" tabindex="-1" aria-labelledby="metodologiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="metodologiaLabel">Metodologia do RREO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Os dados do Relatório Resumido de Execução Orçamentária são publicados bimestralmente conforme a Lei de Responsabilidade Fiscal (Lei Complementar nº 101/2000). As informações são extraídas diretamente do Sistema de Informações Contábeis e Fiscais do Setor Público Brasileiro (SICONFI) e disponibilizadas pelo Tesouro Nacional.</p>
                <p>Consulte o <a href="https://www.tesourotransparente.gov.br/temas/contabilidade-e-fiscal/relatorio-resumido-da-execucao-orcamentaria-rreo" target="_blank" rel="noopener">Tesouro Transparente</a> para orientações oficiais.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
