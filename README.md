# ⚡ RelayFlow Engine

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![RabbitMQ](https://img.shields.io/badge/RabbitMQ-FF6600?style=for-the-badge&logo=rabbitmq&logoColor=white)](https://www.rabbitmq.com/)
[![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![Pest PHP](https://img.shields.io/badge/Pest_PHP-00D2B8?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com/)

> **Engine de Execução de Workflows e Automações de Nível Enterprise.**
>
> O **RelayFlow Engine** é um sistema distribuído de alta performance projetado para orquestração de fluxos assíncronos, ramificações condicionais não-lineares (DAG), resiliência contra falhas de rede e comunicação segura via Webhooks.

---

## 📌 Visão Geral da Arquitetura

O RelayFlow resolve o desafio de integrar e automatizar processos complexos entre microsserviços e APIs externas com **garantia de entrega**, **auditoria completa** e **isolamento contra falhas catastróficas**.

    [ Webhook Client ] ──( HMAC SHA-256 + Rate Limit )──> [ RelayFlow Engine ]
                                                                 │
                                                        (Dispatch Job Async)
                                                                 ▼
    [ Dead Letter Queue ] ◄──( Retries & Backoff )──── [ RabbitMQ Queue ]
             │                                                   │
       (Replay Button)                                     (Worker Exec)
             └───────────────────────────────────────────────────► [ Workflow Executor (DAG) ]

---

## 🛠️ Tecnologias e Decisões de Arquitetura

| Tecnologia / Conceito | Aplicação no Projeto | Benefício Chave |
| :--- | :--- | :--- |
| **Laravel 11 + Inertia.js (React)** | Core do Backend e Dashboard SSR | Separação clara entre regra de negócio e UI fluida sem chamadas manuais de REST/Fetch no frontend. |
| **RabbitMQ** | Fila distribuída de mensageria | Processamento assíncrono de workflows, liberando respostas de Webhook com tempo `< 50ms`. |
| **Redis** | Rate Limiting e Cache de Estados | Proteção contra ataques de negação de serviço (DDoS) e throttling configurável por tenant. |
| **DAG (Directed Acyclic Graph)** | Estrutura de navegação por ponteiros (`next_step_id` / `on_failure_step_id`) | Suporte a fluxos complexos com árvores de decisão, desvios e tratamento nativo de exceções em nós. |
| **HMAC SHA-256** | Middleware de Validação de Assinatura | Garantia de integridade e não-repúdio na recepção de Webhooks externos. |
| **Pest PHP** | Suíte de Testes Automatizados de Integração | Cobertura rigorosa dos cenários de borda, resiliência de filas e bifurcações da engine. |

---

## 🔥 Principais Diferenciais Técnicos

### 🔒 1. Segurança Enterprise em Webhooks
* **Autenticação HMAC:** Toda requisição de webhook valida a assinatura do payload usando chave secreta do tenant (`X-RelayFlow-Signature`).
* **Proteção contra Replay Attack:** Rejeita requisições expiradas baseadas no cabeçalho temporal `X-RelayFlow-Timestamp`.
* **Rate Limiting Inteligente:** Limitação de requisições por segundo utilizando Redis (`HTTP 429 Too Many Requests`).

### 🧩 2. Motor de Mapeamento e Interpolação Dinâmica
* Suporte nativo a interpolação por regex em tempo de execução:
  `{{ customer.email }}` ou encadeamento entre etapas `{{ steps.step_1.output.token }}`.
* Tratamento seguro para arrays aninhados e JSONs complexos via *Dot Notation*.

### ⚡ 3. Resiliência, Dead Letter Queue (DLQ) & Replay
* **Exponential Backoff:** Retentativas de execução em falhas com intervalos crescentes (ex: 5s, 15s, 45s) para mitigar instabilidade em APIs terceiras.
* **Quarentena (DLQ):** Workflows que esgotam as tentativas são isolados sem perda de dados para análise de causa raiz.
* **Botão de Replay UI:** Re-injeção idempotente do payload original diretamente a partir do painel no dashboard para requisições com falha.

### 🌿 4. Processamento Não-Linear (DAG)
* Em vez de uma esteira simples e linear, o RelayFlow executa **árvores de decisão**:
  * **Se a condição passar (`True`):** O motor segue pelo nó `next_step_id`.
  * **Se a condição falhar (`False`):** O motor captura o desvio e navega pelo nó `on_failure_step_id` sem interromper o workflow.

---

## 📂 Estrutura do Projeto

    app/
    ├── Http/
    │   └── Middlewares/
    │       ├── ValidateHmacSignature.php        # Validação de integridade HMAC
    │       └── RateLimitWorkflowExecution.php   # Throttling via Redis
    ├── Jobs/
    │   └── ExecuteWorkflowJob.php              # Worker desacoplado com Backoff Exponencial
    ├── Models/
    │   ├── Workflow.php                        # Definição do Grafo
    │   └── WorkflowStep.php                    # Nós da DAG com ponteiros dinâmicos
    └── Services/
        ├── WorkflowExecutorService.php         # Motor principal de navegação da DAG
        ├── VariableInterpolatorService.php     # Resolvedor de Expressões Regex/Dot Notation
        └── WorkflowActions/
            ├── ActionFactory.php               # Design Pattern Factory para Ações
            ├── ConditionAction.php             # Nó de Decisão / Avaliador Lógico
            ├── HttpRequestAction.php           # Nó de Integração HTTP Externa
            └── LogAction.php                   # Nó de Registro e Auditoria

---

## 🧪 Testes Automatizados

A aplicação conta com suíte de testes de integração via **Pest PHP**:

    # Executar todos os testes da aplicação
    php artisan test

    # Executar testes da engine de grafos (DAG)
    php artisan test --filter=DagWorkflowBranchingTest

---

## 🚀 Como Rodar o Projeto Localmente

### Pré-requisitos
* PHP 8.2+
* Composer
* Node.js (18+) e NPM
* Docker e Docker Compose (para RabbitMQ e Redis)

### Passo a Passo

1. **Clonar o Repositório:**
       git clone https://github.com/seu-usuario/relayflow-engine.git
       cd relayflow-engine

2. **Instalar Dependências:**
       composer install
       npm install

3. **Subir Infraestrutura com Docker (RabbitMQ & Redis):**
       docker-compose up -d

4. **Configurar Ambiente:**
       cp .env.example .env
       php artisan key:generate

5. **Executar Migrações:**
       php artisan migrate

6. **Iniciar Serviços:**
       # Terminal 1: Servidor Web
       php artisan serve

       # Terminal 2: Worker de Fila (RabbitMQ)
       php artisan queue:work

       # Terminal 3: Build de Ativos Frontend
       npm run dev

---

## ✉️ Contato

Desenvolvido com foco em alta performance e arquitetura de software limpa.

* **GitHub:** https://github.com/YuriTreichel/
* **LinkedIn:** https://www.linkedin.com/in/yuri-treichel-fernandes/