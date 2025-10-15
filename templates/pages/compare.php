<section class="mb-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Comparação de Municípios</h2>
                    <small class="text-muted">Selecione dois ou mais municípios para comparar seus RREOs.</small>
                </div>
                <div class="card-body">
                    <form method="post" action="index.php?page=api-compare" data-rreo-form data-context="comparison" data-target="#resultadoComparacao">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label for="municipiosSearch" class="form-label">Buscar municípios</label>
                            <input type="text" class="form-control" id="municipiosSearch" placeholder="Digite o nome do município" autocomplete="off">
                            <div class="form-text">Digite para carregar a lista e selecione abaixo.</div>
                        </div>
                        <div class="mb-3">
                            <label for="municipiosComparar" class="form-label">Municípios selecionados</label>
                            <select id="municipiosComparar" name="municipios[]" class="form-select" size="8" multiple required></select>
                            <div class="form-text">Mantenha Ctrl ou ⌘ pressionado para selecionar múltiplos.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="year" class="form-label">Ano</label>
                                <input type="number" class="form-control" id="year" name="year" min="2010" max="<?= date('Y'); ?>" value="<?= date('Y'); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label for="demonstrativo" class="form-label">Demonstrativo</label>
                                <select class="form-select" id="demonstrativo" name="demonstrativo" required>
                                    <option value="RREO">RREO</option>
                                    <option value="RREO Simplificado">RREO Simplificado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="period" class="form-label">Bimestre</label>
                                <select class="form-select" id="period" name="period" required>
                                    <option value="1">1º Bimestre</option>
                                    <option value="2">2º Bimestre</option>
                                    <option value="3">3º Bimestre</option>
                                    <option value="4">4º Bimestre</option>
                                    <option value="5">5º Bimestre</option>
                                    <option value="6">6º Bimestre</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sphere" class="form-label">Esfera</label>
                                <select class="form-select" id="sphere" name="sphere" required>
                                    <option value="M">Municípios</option>
                                    <option value="E">Estados e DF</option>
                                    <option value="C">Consórcios Públicos</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="annex" class="form-label">Anexo</label>
                                <select class="form-select" id="annex" name="annex" required>
                                    <option value="RREO-Anexo 01">RREO - Anexo 01</option>
                                    <option value="RREO-Anexo 02">RREO - Anexo 02</option>
                                    <option value="RREO-Anexo 03">RREO - Anexo 03</option>
                                    <option value="RREO-Anexo 04">RREO - Anexo 04</option>
                                    <option value="RREO-Anexo 05">RREO - Anexo 05</option>
                                    <option value="RREO-Anexo 06">RREO - Anexo 06</option>
                                    <option value="RREO-Anexo 07">RREO - Anexo 07</option>
                                </select>
                                <div class="form-text">Combine o mesmo anexo para comparar métricas equivalentes.</div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-bar-chart"></i> Comparar</button>
                            <button type="reset" class="btn btn-outline-secondary">Limpar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h3 class="h6 mb-0">Como interpretar</h3>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Utilize o dropdown do resultado para alternar entre anexos e identifique diferenças significativas por meio dos gráficos agrupados. Passe o cursor sobre os pontos para visualizar valores exatos.</p>
                    <p class="small text-muted mb-0">As informações são obtidas em tempo real pela API FINBRA e não ficam armazenadas no sistema.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section>
    <div id="resultadoComparacao"></div>
</section>
