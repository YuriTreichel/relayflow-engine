# ⚡ RelayFlow Engine

[![CI / Test Suite](https://github.com/YuriTreichel/relayflow-engine/actions/workflows/ci.yml/badge.svg)](https://github.com/YuriTreichel/relayflow-engine/actions/workflows/ci.yml)
[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![RabbitMQ](https://img.shields.io/badge/RabbitMQ-FF6600?style=for-the-badge&logo=rabbitmq&logoColor=white)](https://www.rabbitmq.com/)
[![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![Pest PHP](https://img.shields.io/badge/Pest_PHP-00D2B8?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com/)

> **Enterprise-Grade Distributed Workflow & Automation Engine.**
>
> **RelayFlow Engine** is a high-throughput distributed orchestration platform engineered for asynchronous pipelines, non-linear conditional branching (Directed Acyclic Graphs), network partition resilience, and cryptographic webhook verification.

---

## 📌 Architecture Overview

RelayFlow solves the challenge of integrating and orchestrating complex asynchronous workflows across microservices and external APIs with **guaranteed delivery**, **comprehensive auditing**, and **failure isolation**.

```text
    [ External Webhook Client ]
                 │
                 ▼ (HMAC SHA-256 + Rate Limiter)
        [ RelayFlow Ingress API ]
                 │
                 ▼ (Async Dispatch < 50ms)
        [ RabbitMQ Message Broker ]
                 │
                 ▼ (Worker Pull)
       [ Workflow Executor (DAG Engine) ]
                 │
       ┌─────────┴─────────┐
       ▼                   ▼
 [ Next Step ID ]   [ On-Failure Step ID ]
       │                   │
       └─────────┬─────────┘
                 │
                 ▼ (Tries Exhausted)
       [ Dead Letter Queue (DLQ) ] ──(One-Click Replay)──► [ Dashboard UI ]
```

---

## 🛠️ Tech Stack & Architectural Decisions

| Technology / Concept | Project Application | Key Benefit |
| :--- | :--- | :--- |
| **Laravel 11 + Inertia.js (React)** | Core Backend & SSR Dashboard | Clean separation of business logic and reactive UI without manual REST/Fetch boilerplate on the frontend. |
| **RabbitMQ** | Distributed Message Queue | Asynchronous workflow execution, returning Webhook responses in `< 50ms`. |
| **Redis** | Rate Limiting & State Caching | DDoS mitigation and configurable per-tenant request throttling (`HTTP 429`). |
| **DAG (Directed Acyclic Graph)** | Pointer-based navigation structure (`next_step_id` / `on_failure_step_id`) | Support for complex decision trees, fallback branches, and native exception handling per node. |
| **HMAC SHA-256** | Signature Validation Middleware | Payload integrity, anti-tampering, and non-repudiation for incoming external Webhooks. |
| **Pest PHP** | Automated Integration Test Suite | Rigorous edge-case coverage, queue resilience, and engine branching verification. |

---

## 🔥 Key Technical Highlights

### 🔒 1. Enterprise Webhook Security
* **HMAC Authentication:** Every incoming webhook verifies payload authenticity using the tenant's secret key (`X-RelayFlow-Signature`). Evaluated using constant-time comparisons (`hash_equals`) to prevent timing attacks.
* **Replay Attack Protection:** Rejects expired requests using timestamp validation (`X-RelayFlow-Timestamp`), blocking requests older than 5 minutes.
* **Smart Rate Limiting:** Enforces requests-per-minute limits backed by Redis (`HTTP 429 Too Many Requests`) with dynamic `Retry-After` headers.

### 🧩 2. Dynamic Variable Mapping & Interpolation
* Native runtime interpolation using regex:
  `{{ customer.email }}` or step-to-step chaining `{{ steps.step_1.output.token }}`.
* Safe evaluation of nested arrays and complex JSON objects via *Dot Notation*.

### ⚡ 3. Resilience, Dead Letter Queue (DLQ) & Replay
* **Exponential Backoff:** Automated retry strategies with progressive intervals (e.g., 5s, 15s) to handle transient third-party API failures.
* **Quarantine (DLQ):** Workflows exceeding retry thresholds are isolated without data loss for root-cause inspection.
* **UI Replay:** Idempotent re-execution of the original payload directly from the dashboard for failed runs.

### 🌿 4. Non-Linear Execution (DAG)
* Beyond linear pipelines, RelayFlow executes **decision trees**:
  * **On Condition Met (`True`):** The engine continues through `next_step_id`.
  * **On Condition Failure (`False`):** The engine catches the branching signal and routes through `on_failure_step_id` without breaking the workflow.

---

## 📡 Webhook Trigger Example (cURL)

External clients trigger workflows via cryptographic signature:

```bash
# 1. Prepare timestamp and payload
TIMESTAMP=$(date +%s)
PAYLOAD='{"event":"order.created","order":{"id":"ORD-98214","total":750.00,"customer_email":"alex@enterprise.com"}}'
SECRET="YOUR_APP_KEY"

# 2. Compute HMAC SHA-256 signature
SIGNATURE=$(echo -n "${TIMESTAMP}.${PAYLOAD}" | openssl dgst -sha256 -hmac "${SECRET}" | sed 's/^.* //')

# 3. Trigger workflow endpoint
curl -X POST http://localhost:8000/api/v1/workflows/1/execute \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-RelayFlow-Timestamp: ${TIMESTAMP}" \
  -H "X-RelayFlow-Signature: ${SIGNATURE}" \
  -d "${PAYLOAD}"
```

> **Postman / Insomnia:** You can also import [`relayflow-collection.json`](file:///c:/laragon/www/Projetos%20GitHub/relayflow-engine/relayflow-collection.json) directly into Postman. It includes pre-request scripts that automatically compute the HMAC signature on the fly.

---

## 📂 Project Structure

```text
relayflow-engine/
├── .github/workflows/ci.yml         # GitHub Actions automated CI test suite
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/      # Ingress Webhook Trigger Controller
│   │   ├── Controllers/Web/         # Dashboard & Replay Web Controllers
│   │   └── Middlewares/
│   │       ├── ValidateHmacSignature.php        # HMAC integrity & replay defense
│   │       └── RateLimitWorkflowExecution.php   # Redis throttling
│   ├── Jobs/
│   │   └── ExecuteWorkflowJob.php              # Decoupled worker with Exponential Backoff & DLQ
│   ├── Models/
│   │   ├── Workflow.php                        # Graph definition
│   │   └── WorkflowStep.php                    # DAG nodes with dynamic pointer routing
│   └── Services/
│       ├── WorkflowExecutorService.php         # Core DAG navigation engine
│       ├── VariableInterpolatorService.php     # Regex/Dot Notation expression resolver
│       └── WorkflowActions/
│           ├── ActionFactory.php               # Factory design pattern for action handlers
│           ├── ConditionAction.php             # Decision node / Logical evaluator
│           ├── HttpRequestAction.php           # Outbound HTTP integration node
│           └── LogAction.php                   # Audit & recording node
└── tests/
    └── Feature/                                # Pest integration tests for DAG, HMAC & queues
```

---

## 🧪 Automated Testing & CI

The project maintains a comprehensive integration test suite built with **Pest PHP**:

```bash
# Run all tests locally
php artisan test

# Run DAG workflow branching tests
php artisan test --filter=DagWorkflowBranchingTest
```

Automated CI runs via **GitHub Actions** on every push to verify:
- PHP 8.2 compatibility
- Redis rate limiter integration
- Zero regressions across all 37 test cases
- Frontend TypeScript / Vite asset compilation

---

## 🚀 Getting Started Locally

### Prerequisites
* PHP 8.2+
* Composer
* Node.js (18+) & NPM
* Docker & Docker Compose (for RabbitMQ & Redis)

### Quickstart

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

3. **Start Infrastructure Services (RabbitMQ & Redis):**
   ```bash
   docker-compose up -d
   ```

4. **Environment Setup & Database Seeding:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```
   > Seeding creates demo workflows (DAG branching e-commerce and CRM lead routing), an admin account (`admin@relayflow.dev` / `password`), and sample completed/failed execution logs ready for testing the UI Replay button.

5. **Start Application:**
   ```bash
   # Terminal 1: Web Server
   php artisan serve

   # Terminal 2: Queue Worker (RabbitMQ)
   php artisan queue:work

   # Terminal 3: Vite Dev Server
   npm run dev
   ```

---

## ✉️ Author & Contact

Engineered with a focus on high throughput, distributed resilience, and clean software architecture.

* **GitHub:** [https://github.com/YuriTreichel](https://github.com/YuriTreichel)
* **LinkedIn:** [https://www.linkedin.com/in/yuri-treichel-fernandes/](https://www.linkedin.com/in/yuri-treichel-fernandes/)
