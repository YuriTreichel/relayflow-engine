# ⚡ RelayFlow Engine

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![RabbitMQ](https://img.shields.io/badge/RabbitMQ-FF6600?style=for-the-badge&logo=rabbitmq&logoColor=white)](https://www.rabbitmq.com/)
[![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![Pest PHP](https://img.shields.io/badge/Pest_PHP-00D2B8?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com/)

> **Enterprise-Grade Workflow & Automation Execution Engine.**
>
> **RelayFlow Engine** is a high-performance distributed orchestration system designed for asynchronous workflows, non-linear conditional branching (DAG), network failure resilience, and secure webhook communication.

---

## 📌 Architecture Overview

RelayFlow solves the challenge of integrating and automating complex processes across microservices and external APIs with **guaranteed delivery**, **comprehensive auditing**, and **failure isolation**.

```text
    [ Webhook Client ] ──( HMAC SHA-256 + Rate Limit )──> [ RelayFlow Engine ]
                                                                 │
                                                        (Dispatch Job Async)
                                                                 ▼
    [ Dead Letter Queue ] ◄──( Retries & Backoff )──── [ RabbitMQ Queue ]
             │                                                   │
       (Replay Button)                                     (Worker Exec)
             └───────────────────────────────────────────────────► [ Workflow Executor (DAG) ]
```

---

## 🛠️ Tech Stack & Architectural Decisions

| Technology / Concept | Project Application | Key Benefit |
| :--- | :--- | :--- |
| **Laravel 11 + Inertia.js (React)** | Core Backend & SSR Dashboard | Clean separation of business logic and reactive UI without manual REST/Fetch boilerplate on the frontend. |
| **RabbitMQ** | Distributed Message Queue | Asynchronous workflow execution, returning Webhook responses in `< 50ms`. |
| **Redis** | Rate Limiting & State Caching | DDoS mitigation and configurable per-tenant request throttling. |
| **DAG (Directed Acyclic Graph)** | Pointer-based navigation structure (`next_step_id` / `on_failure_step_id`) | Support for complex decision trees, fallback branches, and native exception handling per node. |
| **HMAC SHA-256** | Signature Validation Middleware | Payload integrity and non-repudiation for incoming external Webhooks. |
| **Pest PHP** | Automated Integration Test Suite | Rigorous edge-case coverage, queue resilience, and engine branching verification. |

---

## 🔥 Key Technical Highlights

### 🔒 1. Enterprise Webhook Security
* **HMAC Authentication:** Every incoming webhook request verifies payload authenticity using the tenant's secret key (`X-RelayFlow-Signature`).
* **Replay Attack Protection:** Rejects expired requests using timestamp validation (`X-RelayFlow-Timestamp`).
* **Smart Rate Limiting:** Enforces requests-per-minute limits backed by Redis (`HTTP 429 Too Many Requests`).

### 🧩 2. Dynamic Variable Mapping & Interpolation
* Native runtime interpolation using regex:
  `{{ customer.email }}` or step-to-step chaining `{{ steps.step_1.output.token }}`.
* Safe evaluation of nested arrays and complex JSON objects via *Dot Notation*.

### ⚡ 3. Resilience, Dead Letter Queue (DLQ) & Replay
* **Exponential Backoff:** Automated retry strategies with progressive intervals (e.g., 5s, 15s, 45s) to handle transient third-party API failures.
* **Quarantine (DLQ):** Workflows exceeding retry thresholds are isolated without data loss for root-cause inspection.
* **UI Replay:** Idempotent re-execution of the original payload directly from the dashboard for failed runs.

### 🌿 4. Non-Linear Execution (DAG)
* Beyond linear pipelines, RelayFlow executes **decision trees**:
  * **On Condition Met (`True`):** The engine continues through `next_step_id`.
  * **On Condition Failure (`False`):** The engine catches the branching signal and routes through `on_failure_step_id` without breaking the workflow.

---

## 📂 Project Structure

```text
app/
├── Http/
│   └── Middlewares/
│       ├── ValidateHmacSignature.php        # HMAC integrity validation
│       └── RateLimitWorkflowExecution.php   # Redis throttling
├── Jobs/
│   └── ExecuteWorkflowJob.php              # Decoupled worker with Exponential Backoff
├── Models/
│   ├── Workflow.php                        # Graph definition
│   └── WorkflowStep.php                    # DAG nodes with dynamic pointer routing
└── Services/
    ├── WorkflowExecutorService.php         # Core DAG navigation engine
    ├── VariableInterpolatorService.php     # Regex/Dot Notation expression resolver
    └── WorkflowActions/
        ├── ActionFactory.php               # Factory design pattern for action handlers
        ├── ConditionAction.php             # Decision node / Logical evaluator
        ├── HttpRequestAction.php           # Outbound HTTP integration node
        └── LogAction.php                   # Audit & recording node
```

---

## 🧪 Automated Testing

The project includes a comprehensive integration test suite built with **Pest PHP**:

```bash
# Run all application tests
php artisan test

# Run DAG workflow branching tests
php artisan test --filter=DagWorkflowBranchingTest
```

---

## 🚀 Getting Started Locally

### Prerequisites
* PHP 8.2+
* Composer
* Node.js (18+) & NPM
* Docker & Docker Compose (for RabbitMQ & Redis)

### Step-by-Step Setup

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/YuriTreichel/relayflow-engine.git
   cd relayflow-engine
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Start Infrastructure with Docker (RabbitMQ & Redis):**
   ```bash
   docker-compose up -d
   ```

4. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Run Database Migrations:**
   ```bash
   php artisan migrate
   ```

6. **Start Services:**
   ```bash
   # Terminal 1: Web Server
   php artisan serve

   # Terminal 2: Queue Worker (RabbitMQ)
   php artisan queue:work

   # Terminal 3: Frontend Asset Server
   npm run dev
   ```

---

## ✉️ Author & Contact

Built with a focus on high performance, clean software architecture, and resilient distributed patterns.

* **GitHub:** https://github.com/YuriTreichel/
* **LinkedIn:** https://www.linkedin.com/in/yuri-treichel-fernandes/
