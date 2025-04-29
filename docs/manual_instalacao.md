# Manual de Instalação e Demonstração: Casos da Impacta com Azure WAF

**Autor**: Manus (Assistente AI)
**Data**: 29 de Abril de 2025
**Versão**: 1.0

## 1. Introdução

Este manual detalha os procedimentos para implantar a aplicação web fictícia "Casos da Impacta" no Serviço de Aplicativo (App Service) do Azure e configurar o Azure Web Application Firewall (WAF) associado a um Gateway de Aplicativo (Application Gateway) para proteger a aplicação contra vulnerabilidades comuns, como SQL Injection, Cross-Site Scripting (XSS) e Cross-Site Request Forgery (CSRF), que foram intencionalmente introduzidas na aplicação para fins didáticos.

O objetivo é demonstrar a eficácia do Azure WAF na detecção e bloqueio desses ataques, fornecendo uma camada essencial de segurança para aplicações web hospedadas no Azure.

## 2. Pré-requisitos

*   Uma assinatura ativa do Microsoft Azure. Contas gratuitas podem ser utilizadas, mas podem ter limitações em relação aos SKUs do Application Gateway e WAF.
*   Azure CLI instalado e configurado (opcional, mas recomendado para automação) ou acesso ao Portal do Azure.
*   Código-fonte do projeto "Casos da Impacta" (o arquivo .zip que acompanha este manual).
*   Um cliente MySQL (como MySQL Workbench, DBeaver ou o próprio `mysql` CLI) para importar o schema inicial (opcional, pode ser feito via Azure Cloud Shell).
*   Um navegador web para testes.

## 3. Implantação da Aplicação no Azure App Service

A aplicação PHP "Casos da Impacta" será hospedada no Azure App Service.

### 3.1. Criação do Serviço de Aplicativo

1.  **Acesse o Portal do Azure**.
2.  Clique em **Criar um recurso**.
3.  Pesquise por **Serviço de Aplicativo** e clique em **Criar**.
4.  **Configurações Básicas**:
    *   **Assinatura**: Selecione sua assinatura.
    *   **Grupo de Recursos**: Crie um novo (ex: `rg-casos-impacta`) ou selecione um existente.
    *   **Nome**: Escolha um nome globalmente exclusivo para o seu aplicativo (ex: `casos-impacta-vulneravel`). A URL será `[nome].azurewebsites.net`.
    *   **Publicar**: Selecione `Código`.
    *   **Pilha de runtime**: Selecione `PHP 8.1` (ou a versão compatível mais recente disponível).
    *   **Sistema Operacional**: Selecione `Linux`.
    *   **Região**: Escolha uma região próxima a você (ex: `Brazil South`).
    *   **Plano do Serviço de Aplicativo**: Crie um novo plano. Escolha um SKU (ex: `Free F1` para testes iniciais, ou um SKU Básico/Standard para melhor desempenho e recursos).
5.  Clique em **Revisar + criar** e depois em **Criar**.

### 3.2. Implantação do Código

Existem várias formas de implantar o código. A implantação via ZIP é uma das mais simples:

1.  **Prepare o ZIP**: Certifique-se de que o arquivo `.zip` contém a estrutura correta do projeto, com os diretórios `public`, `admin`, `db`, `assets`, `docs` e o `README.md` na raiz.
2.  **Navegue até o App Service** criado no Portal do Azure.
3.  No menu lateral, vá para **Centro de Implantação**.
4.  Em **Origem**, selecione `Arquivo Zip Externo` (ou use a opção de upload direto se disponível na interface mais recente).
5.  **Via Azure CLI (Recomendado)**:
    *   Abra o terminal ou prompt de comando.
    *   Execute o comando (substitua os placeholders):
        ```bash
        az webapp deployment source config-zip --resource-group rg-casos-impacta --name casos-impacta-vulneravel --src /caminho/para/seu/arquivo.zip
        ```
6.  **Via Kudu (Ferramentas Avançadas)**:
    *   No menu do App Service, vá para **Ferramentas Avançadas** e clique em **Ir**.
    *   No Kudu, vá para **Tools** > **Zip Push Deploy**.
    *   Arraste e solte o arquivo `.zip` na área indicada.

## 4. Configuração do Banco de Dados MySQL

Usaremos o serviço **Banco de Dados do Azure para MySQL**.

1.  **Crie um Servidor MySQL**:
    *   No Portal do Azure, clique em **Criar um recurso**.
    *   Pesquise por **Banco de Dados do Azure para MySQL** e clique em **Criar**.
    *   Escolha a opção **Servidor Flexível** (recomendado).
    *   **Configurações Básicas**:
        *   **Assinatura** e **Grupo de Recursos**: Use os mesmos do App Service.
        *   **Nome do servidor**: Escolha um nome exclusivo (ex: `mysql-casos-impacta`).
        *   **Região**: Use a mesma do App Service.
        *   **Versão do MySQL**: Selecione uma versão compatível (ex: 8.0).
        *   **Tipo de computação e armazenamento**: Escolha `Burstable` com um SKU pequeno (ex: `B1ms`) para testes.
        *   **Nome de usuário do administrador** e **Senha**: Defina credenciais seguras e anote-as.
    *   **Rede**: Em **Método de conectividade**, selecione `Acesso público`. Adicione uma **Regra de firewall** para permitir o acesso dos serviços do Azure (`Permitir acesso público de qualquer serviço do Azure dentro do Azure a este servidor`). Para importar o schema do seu IP local, adicione também o seu IP atual (`Adicionar endereço IP do cliente atual`).
    *   Clique em **Revisar + criar** e **Criar**.
2.  **Crie o Banco de Dados**: Após a criação do servidor, navegue até ele.
    *   No menu lateral, vá para **Bancos de dados**.
    *   Clique em **+ Adicionar**.
    *   Nome do banco de dados: `casos_impacta_db`.
    *   Charset: `utf8mb4`.
    *   Collation: `utf8mb4_unicode_ci`.
    *   Clique em **OK**.
3.  **Importe o Schema Inicial (`init.sql`)**:
    *   Obtenha os detalhes de conexão do servidor MySQL (Nome do servidor, Nome de usuário admin).
    *   Use um cliente MySQL ou o Azure Cloud Shell para conectar ao servidor:
        ```bash
        mysql -h mysql-casos-impacta.mysql.database.azure.com -u seu_admin_user@mysql-casos-impacta -p
        ```
    *   Após conectar, selecione o banco de dados e execute o script:
        ```sql
        USE casos_impacta_db;
        SOURCE /caminho/local/para/init.sql; -- Ou cole o conteúdo do init.sql diretamente
        ```

## 5. Configuração da Aplicação

O App Service precisa das credenciais do banco de dados.

1.  Navegue até o seu **App Service** (`casos-impacta-vulneravel`).
2.  No menu lateral, vá para **Configuração**.
3.  Na aba **Configurações do aplicativo**, clique em **+ Nova configuração de aplicativo** e adicione as seguintes variáveis de ambiente (o `db_config.php` tentará ler estas variáveis):
    *   `DB_HOST`: `mysql-casos-impacta.mysql.database.azure.com` (o nome do seu servidor MySQL)
    *   `DB_NAME`: `casos_impacta_db`
    *   `DB_USERNAME`: `seu_admin_user@mysql-casos-impacta` (o nome de usuário completo)
    *   `DB_PASSWORD`: `sua_senha_segura`
4.  Clique em **Salvar** na parte superior.

Neste ponto, a aplicação deve estar acessível em `http://casos-impacta-vulneravel.azurewebsites.net` e conectando-se ao banco de dados.

## 6. Criação e Configuração do Azure WAF com Application Gateway

Vamos criar um Application Gateway com o WAF habilitado para ficar na frente do App Service.

1.  **Crie um Application Gateway**:
    *   No Portal do Azure, clique em **Criar um recurso**.
    *   Pesquise por **Application Gateway** e clique em **Criar**.
    *   **Configurações Básicas**:
        *   **Assinatura** e **Grupo de Recursos**: Use os mesmos.
        *   **Nome do Application Gateway**: Ex: `agw-casos-impacta`.
        *   **Região**: Use a mesma.
        *   **Camada**: Selecione `WAF V2`.
        *   **Habilitar dimensionamento automático**: Sim (recomendado).
        *   **Contagem mínima de instâncias**: 1 ou 2.
        *   **Zona de disponibilidade**: Escolha `Nenhuma` ou selecione zonas.
        *   **HTTP2**: Habilitado.
        *   **Rede Virtual**: Crie uma nova ou selecione uma existente. O Application Gateway requer uma sub-rede dedicada.
2.  **Front-ends**:
    *   Clique em **Adicionar um front-end**.
    *   **Tipo de endereço IP de front-end**: `Público`.
    *   **Endereço IP Público**: Crie um novo (ex: `pip-agw-casos-impacta`).
3.  **Back-ends**:
    *   Clique em **Adicionar um pool de back-ends**.
    *   **Nome**: Ex: `pool-app-service`.
    *   **Adicionar pool de back-ends sem destinos**: Não.
    *   **Tipo de destino**: `Serviço de Aplicativo`.
    *   **Destino**: Selecione o App Service `casos-impacta-vulneravel`.
4.  **Configuração (Regras de Roteamento)**:
    *   Clique em **Adicionar uma regra de roteamento**.
    *   **Nome da regra**: Ex: `rule-http`.
    *   **Listener**:
        *   **Nome do listener**: Ex: `listener-http`.
        *   **IP de front-end**: `Público`.
        *   **Protocolo**: `HTTP`.
        *   **Porta**: `80`.
        *   (Para HTTPS, você precisaria de um certificado e configuraria a porta 443).
    *   **Destinos de back-end**:
        *   **Tipo de destino**: `Pool de back-ends`.
        *   **Destino de back-end**: Selecione `pool-app-service`.
        *   **Configuração de back-end**: Clique em **Adicionar novo**.
            *   **Nome da configuração de back-end**: Ex: `setting-http-appservice`.
            *   **Protocolo de back-end**: `HTTP`.
            *   **Porta de back-end**: `80`.
            *   **Usar nome do host do pool de back-ends**: Sim.
            *   **Usar investigação personalizada**: Não (por enquanto).
            *   Clique em **Adicionar**.
    *   Clique em **Adicionar** para salvar a regra de roteamento.
5.  **Web Application Firewall**:
    *   **Modo do Firewall**: Comece com `Detecção` para observar os logs sem bloquear.
    *   **Política de WAF**: Clique em **Criar nova**.
        *   **Nome**: Ex: `wafpolicy-casos-impacta`.
        *   **Política para**: `Regional`.
        *   **Assinatura**, **Grupo de Recursos**, **Localização**: Use os mesmos.
        *   Deixe as regras gerenciadas padrão (OWASP CRS) habilitadas.
        *   Clique em **OK**.
6.  Clique em **Revisar + criar** e **Criar**. A implantação do Application Gateway pode levar vários minutos.

## 7. Testando as Vulnerabilidades (Sem WAF Ativo)

Após a implantação do Application Gateway, acesse a aplicação através do IP público ou DNS do Application Gateway (encontre-o na visão geral do AGW).

1.  **SQL Injection (Login)**:
    *   Navegue até a página de login (`/login.php`).
    *   No campo **Usuário**, insira: `' OR '1'='1' -- `
    *   No campo **Senha**, insira qualquer coisa (ex: `x`).
    *   Clique em **Entrar**.
    *   **Resultado Esperado (Vulnerável)**: Você deve ser logado com sucesso (provavelmente como o primeiro usuário do banco, 'admin'), mesmo sem saber a senha correta, pois a condição `OR '1'='1'` sempre será verdadeira.

2.  **Cross-Site Scripting (XSS - Stored)**:
    *   Navegue até um artigo (ex: `/artigo.php?id=1`).
    *   No formulário de comentários:
        *   **Nome**: `Testador XSS`
        *   **Comentário**: `<script>alert('XSS Vulnerável!');</script>`
    *   Clique em **Enviar Comentário**.
    *   **Resultado Esperado (Vulnerável)**: Ao recarregar a página do artigo (ou para qualquer outro usuário que a visite), uma caixa de alerta JavaScript com a mensagem 'XSS Vulnerável!' deve aparecer, indicando que o script foi executado.

3.  **Cross-Site Request Forgery (CSRF)**:
    *   Faça login na aplicação (use o SQL Injection acima ou as credenciais 'admin'/'senha123').
    *   Navegue até o Painel Admin simulado (`/admin/painel.php`). Observe o formulário "Simular Exclusão de Usuário".
    *   Crie um arquivo HTML local (ex: `csrf_attack.html`) com o seguinte conteúdo, substituindo `SEU_DOMINIO_AGW` pelo DNS do seu Application Gateway:
        ```html
        <html>
          <body onload="document.forms[0].submit()">
            <h3>Página Maliciosa Simples</h3>
            <p>Enviando requisição forjada...</p>
            <form action="http://SEU_DOMINIO_AGW/admin/processar_acao.php" method="POST">
              <input type="hidden" name="action" value="delete_user_simulated" />
              <input type="hidden" name="user_id_to_delete" value="999" />
              <input type="submit" value="Clique aqui se não for redirecionado" />
            </form>
          </body>
        </html>
        ```
    *   Abra o arquivo `csrf_attack.html` no mesmo navegador onde você está logado no "Casos da Impacta".
    *   **Resultado Esperado (Vulnerável)**: O formulário na página maliciosa será submetido automaticamente. Você será redirecionado de volta para `/admin/painel.php` (ou verá a mensagem de sucesso lá) indicando que a "ação de exclusão" foi processada, mesmo sem você ter clicado no botão dentro do painel administrativo.

## 8. Habilitando e Configurando o Azure WAF (Modo Prevenção)

Agora, vamos ativar o WAF para bloquear esses ataques.

1.  Navegue até a **Política de WAF** (`wafpolicy-casos-impacta`) no Portal do Azure.
2.  Em **Configurações**, altere o **Modo** de `Detecção` para `Prevenção`.
3.  **Regras Gerenciadas**:
    *   Verifique se o conjunto de regras padrão (ex: `OWASP_3.2`, `OWASP_3.1` ou `OWASP_3.0`) está atribuído.
    *   Explore os grupos de regras (ex: `SQLI`, `XSS`). As regras padrão já cobrem as vulnerabilidades que estamos testando.
4.  Clique em **Salvar**.

## 9. Testando as Vulnerabilidades (Com WAF Ativo)

Aguarde alguns instantes para a política ser aplicada e repita os testes da Seção 7.

1.  **SQL Injection (Login)**:
    *   Tente fazer login com `' OR '1'='1' -- `.
    *   **Resultado Esperado (Protegido)**: O WAF deve detectar a tentativa de SQL Injection e bloquear a requisição. Você provavelmente receberá uma página de erro `403 Forbidden` do Application Gateway/WAF, e o login falhará.

2.  **Cross-Site Scripting (XSS - Stored)**:
    *   Tente submeter o comentário com `<script>alert('XSS Bloqueado!');</script>`.
    *   **Resultado Esperado (Protegido)**: O WAF deve detectar o script malicioso na carga útil da requisição POST e bloqueá-la. Você receberá um erro `403 Forbidden`, e o comentário não será salvo nem exibido.

3.  **Cross-Site Request Forgery (CSRF)**:
    *   **Observação**: O WAF padrão (OWASP CRS) *não* protege inerentemente contra CSRF, pois a requisição forjada pode parecer legítima se não houver um token anti-CSRF sendo validado pela aplicação. A proteção CSRF geralmente requer a implementação de tokens na própria aplicação (como comentado no código `processar_acao.php`). O WAF pode ajudar se regras personalizadas forem criadas ou se a falta de certos cabeçalhos (como `Origin` ou `Referer` em alguns cenários) for usada como indicador, mas a defesa primária é na aplicação.
    *   **Resultado Esperado (Provavelmente Vulnerável)**: O ataque CSRF provavelmente ainda funcionará, pois o WAF não tem como saber que a requisição originada da `csrf_attack.html` não foi intencional, a menos que regras muito específicas sejam configuradas.

## 10. Visualizando Logs do WAF

Para ver como o WAF detectou e bloqueou os ataques:

1.  Navegue até o **Application Gateway** (`agw-casos-impacta`).
2.  No menu lateral, em **Monitoramento**, clique em **Diagnóstico de configurações**.
3.  Configure o envio de logs para um **Log Analytics workspace** (crie um se não tiver).
    *   Selecione as categorias de log `ApplicationGatewayAccessLog` e `ApplicationGatewayFirewallLog`.
4.  Após configurar e aguardar a propagação dos logs (pode levar alguns minutos), vá para o **Log Analytics workspace**.
5.  Clique em **Logs**.
6.  Execute consultas Kusto para ver os logs do WAF. Exemplo:
    ```kusto
    AzureDiagnostics
    | where Category == "ApplicationGatewayFirewallLog"
    | where action_s == "Blocked" // Ou "Detected" se estava em modo Detecção
    | project TimeGenerated, clientIp_s, ruleSetType_s, ruleSetVersion_s, ruleId_s, ruleGroup_s, Message, details_message_s, details_data_s
    | order by TimeGenerated desc
    ```
7.  Você deverá ver entradas correspondentes às tentativas de SQL Injection e XSS que foram bloqueadas, com detalhes sobre a regra que foi acionada (ex: regra `941100` para XSS, `942100` para SQLi).

## 11. Conclusão

Este manual demonstrou como implantar uma aplicação PHP vulnerável no Azure App Service e como o Azure WAF, configurado em um Application Gateway, pode efetivamente detectar e bloquear ataques comuns como SQL Injection e XSS, aumentando significativamente a postura de segurança da aplicação. A vulnerabilidade CSRF destaca a importância de defesas na própria aplicação, como tokens anti-CSRF, que complementam a proteção oferecida pelo WAF.

