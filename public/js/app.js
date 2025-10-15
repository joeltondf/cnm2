(function () {
    'use strict';

    const municipioInput = document.querySelector('#municipio');
    const municipioList = document.querySelector('#municipioOptions');
    const municipioCompareSelect = document.querySelector('#municipiosComparar');

    const debounce = (fn, delay = 300) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    };

    const fetchMunicipios = debounce((term, targetList) => {
        if (!term || term.length < 2) {
            return;
        }

        fetch(`index.php?page=api-municipios&q=${encodeURIComponent(term)}`)
            .then((response) => response.json())
            .then((payload) => {
                if (targetList instanceof HTMLDataListElement) {
                    targetList.innerHTML = '';
                    payload.data.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = `${item.text}`;
                        option.dataset.ibge = item.id;
                        option.dataset.uf = item.uf;
                        targetList.appendChild(option);
                    });
                } else if (targetList instanceof HTMLSelectElement) {
                    targetList.innerHTML = '';
                    payload.data.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.text;
                        targetList.appendChild(option);
                    });
                }
            })
            .catch((error) => console.error('Erro ao buscar municípios', error));
    }, 250);

    if (municipioInput && municipioList) {
        municipioInput.addEventListener('input', (event) => {
            fetchMunicipios(event.target.value, municipioList);
        });
    }

    if (municipioCompareSelect) {
        const compareSearch = document.querySelector('#municipiosSearch');
        if (compareSearch) {
            compareSearch.addEventListener('input', (event) => {
                fetchMunicipios(event.target.value, municipioCompareSelect);
            });
        }
    }

    const extractIbgeFromInput = (inputValue) => {
        if (!municipioList) {
            return null;
        }
        const options = municipioList.querySelectorAll('option');
        for (const option of options) {
            if (option.value.toLowerCase() === inputValue.toLowerCase()) {
                return option.dataset.ibge;
            }
        }
        return null;
    };

    const forms = document.querySelectorAll('[data-rreo-form]');

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton?.setAttribute('disabled', 'disabled');
            submitButton?.classList.add('disabled');

            const formData = new FormData(form);
            if (form.dataset.requiresIbge === 'true') {
                const typedMunicipio = form.querySelector('input[name="municipio_nome"]');
                const hiddenIbge = form.querySelector('input[name="ibge"]');
                if (typedMunicipio && hiddenIbge) {
                    const ibge = extractIbgeFromInput(typedMunicipio.value) || typedMunicipio.dataset.ibge;
                    if (!ibge) {
                        alert('Selecione um município válido usando o campo de auto completar.');
                        submitButton?.removeAttribute('disabled');
                        submitButton?.classList.remove('disabled');
                        return;
                    }
                    hiddenIbge.value = ibge;
                }
            }

            fetch(form.action, {
                method: form.method,
                body: formData,
            })
                .then((response) => response.json())
                .then((payload) => {
                    if (payload.error) {
                        throw new Error(payload.error);
                    }

                    if (form.dataset.target) {
                        const container = document.querySelector(form.dataset.target);
                        if (container) {
                            renderRreoData(container, payload.data, form.dataset.context || 'single');
                        }
                    }
                })
                .catch((error) => {
                    alert(error.message || 'Não foi possível concluir a consulta.');
                })
                .finally(() => {
                    submitButton?.removeAttribute('disabled');
                    submitButton?.classList.remove('disabled');
                });
        });
    });

    const renderRreoData = (container, data, context = 'single') => {
        if (!data) {
            container.innerHTML = '<div class="alert alert-warning">Nenhum dado retornado pela API.</div>';
            return;
        }

        if (context === 'comparison') {
            renderComparison(container, data);
            return;
        }

        const annexes = data.annexes || data.anexos || [];
        if (!annexes.length) {
            container.innerHTML = `<pre class="bg-light p-3 rounded">${escapeHtml(JSON.stringify(data, null, 2))}</pre>`;
            return;
        }

        const navTabs = document.createElement('ul');
        navTabs.className = 'nav nav-tabs data-tabs mb-3';
        navTabs.role = 'tablist';

        const tabContent = document.createElement('div');
        tabContent.className = 'tab-content';

        annexes.forEach((annex, index) => {
            const annexId = `annex-${index}`;
            const navItem = document.createElement('li');
            navItem.className = 'nav-item';
            const navLink = document.createElement('button');
            navLink.className = 'nav-link' + (index === 0 ? ' active' : '');
            navLink.dataset.bsToggle = 'tab';
            navLink.dataset.bsTarget = `#${annexId}`;
            navLink.type = 'button';
            navLink.role = 'tab';
            navLink.textContent = annex.title || annex.nome || `Anexo ${index + 1}`;
            navItem.appendChild(navLink);
            navTabs.appendChild(navItem);

            const tabPane = document.createElement('div');
            tabPane.className = 'tab-pane fade' + (index === 0 ? ' show active' : '');
            tabPane.id = annexId;
            tabPane.role = 'tabpanel';
            tabPane.innerHTML = buildTables(annex.tables || annex.tabelas || []);
            tabContent.appendChild(tabPane);
        });

        container.innerHTML = '';
        container.append(navTabs, tabContent);
        activateDataTables(container);
    };

    const renderComparison = (container, data) => {
        const municipalities = Object.keys(data);
        if (!municipalities.length) {
            container.innerHTML = '<div class="alert alert-info">Nenhum município retornado.</div>';
            return;
        }

        container.innerHTML = '';
        const accordion = document.createElement('div');
        accordion.className = 'accordion';
        accordion.id = 'comparisonAccordion';

        municipalities.forEach((ibge, index) => {
            const info = data[ibge]?.municipio || {};
            const title = info.nome ? `${info.nome} (${ibge})` : `Município ${ibge}`;
            const item = document.createElement('div');
            item.className = 'accordion-item';
            item.innerHTML = `
                <h2 class="accordion-header" id="heading-${ibge}">
                    <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${ibge}" aria-expanded="${index === 0}" aria-controls="collapse-${ibge}">
                        ${title}
                    </button>
                </h2>
                <div id="collapse-${ibge}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" aria-labelledby="heading-${ibge}" data-bs-parent="#comparisonAccordion">
                    <div class="accordion-body">
                        <div class="comparison-body"></div>
                    </div>
                </div>`;
            accordion.appendChild(item);

            const body = item.querySelector('.comparison-body');
            renderRreoData(body, data[ibge], 'single');
        });

        container.appendChild(accordion);
    };

    const buildTables = (tables) => {
        if (!tables.length) {
            return '<div class="alert alert-secondary">Sem informações disponíveis para este anexo.</div>';
        }

        return tables
            .map((table, index) => {
                const tableId = `table-${Math.random().toString(36).slice(2, 9)}`;
                const headers = table.headers || table.colunas || [];
                const rows = table.rows || table.dados || [];
                const unit = table.unit || table.unidade || '';

                return `
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-0">${escapeHtml(table.title || table.titulo || `Tabela ${index + 1}`)}</h5>
                                ${unit ? `<small class="text-muted">Unidade: ${escapeHtml(unit)}</small>` : ''}
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-2" data-print-table="${tableId}">Imprimir</button>
                                <button class="btn btn-sm btn-outline-secondary" data-export-csv="${tableId}">Exportar CSV</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-pills mb-3" id="pills-${tableId}" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="dados-${tableId}-tab" data-bs-toggle="pill" data-bs-target="#dados-${tableId}" type="button" role="tab" aria-controls="dados-${tableId}" aria-selected="true">Tabela</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="grafico-${tableId}-tab" data-bs-toggle="pill" data-bs-target="#grafico-${tableId}" type="button" role="tab" aria-controls="grafico-${tableId}" aria-selected="false">Gráfico</button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="dados-${tableId}" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="${tableId}" data-table-json='${escapeHtml(JSON.stringify({ headers, rows }))}'>
                                            <thead>
                                                <tr>
                                                    ${headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${rows.map((row) => `<tr>${row.map((value) => `<td>${escapeHtml(String(value))}</td>`).join('')}</tr>`).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="grafico-${tableId}" role="tabpanel">
                                    <div class="chart-container">
                                        <canvas id="chart-${tableId}" data-chart-json='${escapeHtml(JSON.stringify({ headers, rows, title: table.title || table.titulo }))}'></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            })
            .join('');
    };

    const escapeHtml = (unsafe) => {
        return unsafe
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const activateDataTables = (container) => {
        container.querySelectorAll('table[data-table-json]').forEach((table) => {
            JSON.parse(table.dataset.tableJson || '{}');
            if (!$.fn.DataTable.isDataTable(table)) {
                $(table).DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json',
                    },
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    pageLength: 10,
                });
            }

            const canvas = container.querySelector(`#chart-${table.id}`);
            if (canvas) {
                const chartData = JSON.parse(canvas.dataset.chartJson || '{}');
                buildChart(canvas, chartData);
            }
        });

        container.querySelectorAll('[data-print-table]').forEach((button) => {
            button.addEventListener('click', () => {
                const tableId = button.getAttribute('data-print-table');
                const table = document.getElementById(tableId);
                if (!table) {
                    return;
                }
                const win = window.open('', '_blank', 'width=900,height=600');
                win.document.write('<html><head><title>Impressão</title>');
                win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
                win.document.write('</head><body>');
                win.document.write(table.outerHTML);
                win.document.write('</body></html>');
                win.document.close();
                win.print();
            });
        });

        container.querySelectorAll('[data-export-csv]').forEach((button) => {
            button.addEventListener('click', () => {
                const tableId = button.getAttribute('data-export-csv');
                const table = document.getElementById(tableId);
                if (!table) {
                    return;
                }
                const rows = Array.from(table.querySelectorAll('tr'));
                const csvContent = rows
                    .map((row) => Array.from(row.querySelectorAll('th,td')).map((cell) => `"${cell.textContent.replace(/"/g, '""')}"`).join(','))
                    .join('\n');

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', `${tableId}.csv`);
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        });
    };

    const buildChart = (canvas, chartData) => {
        const headers = chartData.headers || [];
        const rows = chartData.rows || [];
        if (!headers.length || !rows.length) {
            return;
        }

        const labels = rows.map((row) => row[0]);
        const datasets = [];

        for (let colIndex = 1; colIndex < headers.length; colIndex++) {
            const data = rows.map((row) => {
                const value = row[colIndex];
                return typeof value === 'string' ? Number(value.replace(/\./g, '').replace(',', '.')) || 0 : Number(value);
            });
            datasets.push({
                label: headers[colIndex],
                data,
                borderWidth: 2,
                fill: false,
            });
        }

        new window.Chart(canvas.getContext('2d'), {
            type: headers.length > 2 ? 'bar' : 'line',
            data: {
                labels,
                datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${Number(context.parsed.y).toLocaleString('pt-BR')}`,
                        },
                    },
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => Number(value).toLocaleString('pt-BR'),
                        },
                    },
                },
            },
        });
    };
})();
