# Casos de Uso (Use Cases)

---

## UC01 – Realizar Login

- **1. Finalidade / Objetivo:** Permitir que o usuário acesse o sistema mediante autenticação
- **2. Atores:** Autônomo
- **3. Evento Inicial:** O ator acessa a tela de login.
- **4. Fluxo Principal:**
  a) O sistema apresenta a tela de autenticação;  
  b) O usuário informa e-mail e senha;  
  c) O sistema valida as credenciais; [T1] [A1]  
  d) O sistema concede acesso ao sistema;  
  e) O caso de uso é encerrado.  
- **5. Fluxo Alternativo:**
  - **A1 – Credenciais inválidas:**
    a) O sistema informa que o usuário ou senha são inválidos;  
    b) O sistema solicita nova autenticação;  
    c) Retorna ao passo "b" do fluxo principal.  
- **6. Testes:**
  - **T1 – Validar credenciais:**
    a) O sistema deverá verificar se o usuário e senha existem e estão ativos.

---

## UC02 – Cadastrar Transação

- **1. Finalidade / Objetivo:** Registrar uma movimentação financeira.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona a opção “Cadastrar Transação”.
- **5. Fluxo Principal:**
  a) O sistema apresenta o formulário de cadastro;  
  b) O usuário informa descrição, valor, categoria, tipo e data;  
  c) O sistema valida os dados; [T1] [A1]  
  d) O sistema grava a transação;  
  e) O sistema informa sucesso;  
  f) O caso de uso é encerrado.  
- **6. Fluxo Alternativo:**
  - **A1 – Dados inválidos:**
    a) O sistema informa os campos inválidos;  
    b) O usuário corrige as informações;  
    c) Retorna ao passo "c" do fluxo principal.  
- **7. Testes:**
  - **T1 – Validar dados da transação:**
    a) O sistema deverá verificar campos obrigatórios e valor válido.

---

## UC03 – Manter Contas a Pagar

- **1. Finalidade / Objetivo:** Cadastrar, alterar, consultar e excluir contas a pagar.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona a opção "Contas a Pagar".
- **5. Fluxo Principal:**
  a) O sistema apresenta as contas cadastradas;  
  b) O usuário seleciona uma operação;  
  c) O sistema apresenta os dados;  
  d) O usuário realiza as alterações;  
  e) O sistema salva as informações;  
  f) O caso de uso é encerrado.  

---

## UC04 – Manter Contas a Receber

- **1. Finalidade / Objetivo:** Cadastrar, alterar, consultar e excluir contas a receber.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona a opção "Contas a Receber".
- **5. Fluxo Principal:**
  a) O sistema apresenta as contas cadastradas;  
  b) O usuário seleciona uma operação;  
  c) O sistema apresenta os dados;  
  d) O usuário realiza as alterações;  
  e) O sistema salva as informações;  
  f) O caso de uso é encerrado.  

---

## UC05 – Marcar Conta a Pagar como Paga

- **1. Finalidade / Objetivo:** Registrar o pagamento de uma conta pendente.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado e possuir contas pendentes.
- **4. Evento Inicial:** O ator seleciona uma conta a pagar.
- **5. Fluxo Principal:**
  a) O sistema apresenta os detalhes da conta;  
  b) O usuário seleciona "Marcar como Paga";  
  c) O sistema solicita confirmação;  
  d) O usuário confirma; [A1]  
  e) O sistema altera o status para "Paga";  
  f) O caso de uso é encerrado.  
- **6. Fluxo Alternativo:**
  - **A1 – Usuário cancela confirmação:**
    a) O usuário cancela a operação;  
    b) O sistema mantém a conta pendente;  
    c) O caso de uso é encerrado.  

---

## UC06 – Marcar Conta a Receber como Recebida

- **1. Finalidade / Objetivo:** Registrar o recebimento de uma conta.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado e possuir contas pendentes.
- **4. Evento Inicial:** O ator seleciona uma conta a receber.
- **5. Fluxo Principal:**
  a) O sistema exibe os detalhes da conta;  
  b) O usuário seleciona "Marcar como Recebida";  
  c) O sistema solicita confirmação;  
  d) O usuário confirma;  
  e) O sistema altera o status para "Recebida";  
  f) O caso de uso é encerrado.  

---

## UC07 – Manter Usuários

- **1. Finalidade / Objetivo:** Gerenciar os usuários do sistema.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona a opção "Usuários".
- **5. Fluxo Principal:**
  a) O sistema apresenta a listagem de usuários;  
  b) O usuário escolhe cadastrar, editar ou excluir;  
  c) O sistema apresenta os dados necessários;  
  d) O usuário informa as alterações;  
  e) O sistema salva as informações; [A1]  
  f) O caso de uso é encerrado.  
- **6. Fluxo Alternativo:**
  - **A1 – Dados inválidos:**
    a) O sistema informa o erro encontrado;  
    b) O usuário corrige os dados;  
    c) Retorna ao passo "e" do fluxo principal.  

---

## UC08 – Manter Categorias

- **1. Finalidade / Objetivo:** Gerenciar categorias financeiras.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona a opção "Categorias".
- **5. Fluxo Principal:**
  a) O sistema apresenta as categorias existentes;  
  b) O usuário escolhe cadastrar, editar ou excluir;  
  c) O sistema apresenta os campos necessários;  
  d) O usuário informa os dados;  
  e) O sistema salva as alterações;  
  f) O caso de uso é encerrado.  

---

## UC09 – Emitir Relatório de Fluxo de Caixa

- **1. Finalidade / Objetivo:** Exibir o fluxo de caixa em determinado período.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona "Relatório de Fluxo de Caixa".
- **5. Fluxo Principal:**
  a) O sistema solicita o período desejado;  
  b) O usuário informa as datas;  
  c) O sistema processa os dados; [T1]  
  d) O sistema apresenta o relatório;  
  e) O usuário pode imprimir ou exportar; [A1]  
  f) O caso de uso é encerrado.  
- **6. Fluxo Alternativo:**
  - **A1 – Exportar relatório:**
    a) O usuário seleciona exportar;  
    b) O sistema gera o arquivo solicitado;  
    c) O caso de uso é encerrado.  
- **7. Testes:**
  - **T1 – Validar período informado:**
    a) O sistema deverá verificar se a data inicial é menor ou igual à data final.

---

## UC10 – Emitir Relatório de Gastos por Categorias

- **1. Finalidade / Objetivo:** Exibir os gastos agrupados por categoria.
- **2. Atores:** Autônomo
- **3. Pré-condição:** Estar logado
- **4. Evento Inicial:** O ator seleciona "Relatório de Gastos por Categoria".
- **5. Fluxo Principal:**
  a) O sistema solicita o período desejado;  
  b) O usuário informa as datas;  
  c) O sistema agrupa os gastos por categoria;  
  d) O sistema apresenta o relatório;  
  e) O usuário pode imprimir ou exportar;  
  f) O caso de uso é encerrado.
