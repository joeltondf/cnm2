# Explorador RREO FINBRA

Aplicação web em PHP 8 que consulta o Relatório Resumido de Execução Orçamentária (RREO) da API FINBRA, oferecendo visualização interativa com Bootstrap, DataTables e Chart.js. O banco de dados MySQL armazena apenas a lista de estados e municípios brasileiros.

## Requisitos

- PHP 8.1 ou superior com extensões `pdo_mysql`, `curl` e `json` habilitadas;
- Composer para gerenciamento de autoload;
- Servidor MySQL 5.7+ ou MariaDB 10+;
- Acesso à API FINBRA (URL base e, se necessário, token).

## Instalação

1. Clone o repositório e instale as dependências do Composer (somente autoload é configurado):

   ```bash
   composer install
   ```

2. Configure as variáveis de ambiente ou edite `config/database.php` e `config/config.php` conforme o ambiente:

   ```bash
   export DB_HOST=localhost
   export DB_PORT=3306
   export DB_NAME=u371107598_cnm2
   export DB_USER=u371107598_usercnm2
   export DB_PASS='@Amora051307'
   export FINBRA_API_URL="https://apidatalake.tesouro.gov.br/ords/siconfi/tt/rreo_anexo"
   export FINBRA_API_TOKEN="seu_token_aqui" # opcional
   export APP_BASE_URL="http://localhost:8000"
   ```

3. Crie o banco de dados e importe o esquema:

   ```bash
   mysql -u u371107598_usercnm2 -p -e "CREATE DATABASE u371107598_cnm2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u u371107598_usercnm2 -p u371107598_cnm2 < sql/schema.sql
   ```

4. Baixe os arquivos `estados.csv` e `municipios.csv` do repositório [kelvins/municipios-brasileiros](https://github.com/kelvins/municipios-brasileiros) e utilize o carregador web:

   - Inicie o servidor embutido do PHP na pasta raiz do projeto:

     ```bash
     php -S localhost:8000 -t public
     ```

   - Em outro terminal, carregue os dados acessando `http://localhost:8000/populate_municipios.php` e envie os dois CSVs. O script realiza a inserção utilizando prepared statements e exibe mensagens de progresso.

5. Acesse `http://localhost:8000/index.php?page=home` para utilizar a aplicação.

## Estrutura do projeto

```
config/           # Configurações de banco e API
public/           # Front controller, assets e rotas públicas
src/              # Classes PHP (Controllers, Services, Repository, Security)
templates/        # Views (layouts e páginas)
sql/schema.sql    # DDL das tabelas estados e municípios
populate_municipios.php  # Script de importação via navegador
```

## Funcionamento

- As consultas ao RREO são feitas on-demand através da classe `App\Service\FinbraService`, que utiliza cURL e retorna JSON.
- Os resultados são apresentados em abas por anexo e tabela com DataTables e gráficos (Chart.js).
- A comparação entre municípios realiza múltiplas chamadas à API e monta painéis lado a lado.
- CSRF é aplicado nos formulários por meio de `App\Security\CsrfTokenManager`.
- Não há persistência dos dados do RREO no banco; apenas a lista de municípios fica armazenada localmente.

## Dicas de produção

- Configure HTTPS e um servidor web (Apache ou Nginx) apontando para `public/`.
- Utilize cache de resultados em memória (APCu ou Redis) se for necessário otimizar chamadas repetidas.
- Proteja o script `populate_municipios.php` com autenticação básica quando em produção.

## Licença

Projeto disponibilizado para uso institucional. Consulte o Tesouro Nacional para termos de uso dos dados FINBRA.
