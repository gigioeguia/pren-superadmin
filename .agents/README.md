# `.agents/` — Convenciones y skills para agentes

Carpeta de convenciones, especificaciones, skills y decisiones que rigen cómo trabajan los agentes en este repo. **Versión local Markdown** (sin integración con el tool skill de OpenCode); cualquier agente que lea esta carpeta puede seguirlas.

## Estructura

```
.agents/
├── README.md                  # este índice
├── glossary.md                # glosario del dominio
├── spec/                      # convenciones + plantilla para specs
│   ├── spec-conventions.md
│   └── spec-template.md
├── spec-impl/                 # specs concretos (70; un archivo por spec)
│   │
│   ├── fase0 (3):  spec-tests-smoke-fase0 · spec-phpstan-pint-ci-fase0 · spec-trait-acnoo-fase0
│   ├── fase1 (6):  spec-admin-middleware-fase1 · spec-env-inseguro-fase1 · spec-registro-api-role-arbitrario-fase1 ·
│   │               spec-reset-password-otp-fase1 · spec-rutas-artisan-publicas-fase1 · spec-webhooks-sin-firma-fase1
│   ├── fase2 (6):  spec-deleteall-row-by-row-media-fase2 · spec-gateway-namespace-whitelist-fase2 ·
│   │               spec-payment-controller-sin-user-id-fase2 · spec-payment-success-fase2 · spec-policies-fase2 ·
│   │               spec-property-update-landlord-id-fase2
│   ├── fase3 (8):  spec-bug-rutas-landlord-cruzadas-fase3 · spec-demo-reset-demomode-fase3 ·
│   │               spec-no-exponer-error-message-fase3 · spec-otp-hash-equals-fase3 · spec-password-policy-fase3 ·
│   │               spec-permission-middleware-admin-fase3 · spec-throttle-otp-login-fase3 ·
│   │               spec-uploads-normalizar-extension-nombre-fase3
│   ├── fase4 (21): spec-upgrade-laravel-php-fase4 · spec-vite-axios-fase4 ·
│   │               spec-unificar-capas-services-fase4 (+11 hijos spec-service-*-fase4: expense, expensecategory,
│   │               income, incomecategory, notification, refundrequest, rentpayment, securitydeposit, support,
│   │               tenant, withdrawmethod) · spec-env-to-config-fase4 · spec-inyectar-request-fase4 ·
│   │               spec-helpers-globales-clases-fase4 · spec-plan-duration-days-fase4 ·
│   │               spec-db-transactions-no-external-io-fase4 · spec-cache-invalidacion-centralizada-fase4 ·
│   │               spec-seeders-firstorcreate-fase4
│   ├── fase5 (27): spec-comand-txt-credenciales-fase5 · spec-demo-user-passwords-fase5 ·
│   │               spec-withdraw-index-request-fase5 · spec-ownership-withdraw-store-fase5 · spec-trust-hosts-fase5 ·
│   │               spec-session-database-secure-fase5 · spec-app-url-https-force-tls-fase5 ·
│   │               spec-get-option-tipos-consistentes-fase5 · spec-typos-rutas-fase5 ·
│   │               spec-log-level-warning-fase5 · spec-acnoo-rename-fase5 ·
│   │               spec-user-fillable-restrictivo-fase5 · spec-devdeps-limpieza-fase5 ·
│   │               spec-public-writes-throttle-fase5 · spec-laravel13-advisories-fase5 ·
│   │               spec-restauracion-crud-42-fase5 · spec-tx-legadas-services-fase5 ·
│   │               spec-moderacion-comentarios-fase5 · spec-php84-bump-fase5 ·
│   │               spec-session-serialization-json-fase5 ·
│   │               spec-unificar-capas-services-fase5 (+6 hijos 5.13: spec-service-language-fase5 ·
│   │               spec-service-rent-fase5 · spec-service-application-fase5 · spec-service-landlord-fase5 ·
│   │               spec-service-payment-fase5 · spec-controladores-residuales-fase5)
│   └── meta (1):   spec-reconciliacion-analisis-fase0-3
│
├── skills/                    # procedimientos operacionales
│   ├── crear-spec.md
│   ├── revisar-spec.md
│   ├── implementar-spec.md
│   ├── tests-caracterizacion.md
│   ├── workflow-ramas.md
│   ├── secure-coding.md
│   └── testing-conventions.md
└── decisions/                 # Architectural Decision Records (ADRs)
    └── 0001-mysql-test-db.md
```

> Índice regenerado 2026-11-23 (fase5 completa 27: añade devdeps, public-writes, laravel13-advisories, restauracion-crud-42, tx-legadas, moderacion, php84-bump, session-serialization-json y ownership-withdraw-store; total 73 specs): solo contenido trackeado; los archivos locales sin extensión bajo `skills/` (herramientas de agente) no se listan.

## Orden de lectura sugerido (para un agente nuevo)

1. `AGENTS.md` (raíz del repo) — overview del stack, rutas, middleware, pagos.
2. Este `README.md` — qué hay en `.agents/`.
3. `glossary.md` — vocabulario del dominio (landlord, tenant, security_deposit, etc.).
4. `skills/workflow-ramas.md` — **antes de cualquier edición** (reglas de git).
5. `spec/spec-conventions.md` — si vas a escribir o revisar specs.
6. `skills/secure-coding.md` — si vas a tocar código (checklist anti-reincidencia).
7. `skills/testing-conventions.md` — si vas a escribir tests.
8. Los demás skills según la tarea.

## Flujo de trabajo de specs

```
crear-spec → revisar-spec → (estado: Aprobado) → implementar-spec
                                                          │
                                                          ├── tests-caracterizacion (si caracteriza bugs)
                                                          ├── secure-coding (al escribir código)
                                                          └── testing-conventions (al escribir tests)
```

Cierre: al verificar y tachar el DoD, el spec pasa a `Implementado` (estados y transiciones en `spec-conventions.md`).

Todo el flujo está sujeto a `workflow-ramas.md` (nunca editar `main`).

## Ver también

- `AGENTS.md` (raíz) — overview del repo: stack, rutas, middleware, pagos, gotchas.
- `.analisis/` (gitignored) — análisis de seguridad y deuda técnica que originan los specs:
  - `.analisis/seguridad.md` — 26 vulnerabilidades
  - `.analisis/deuda-tecnica.md` — 28 ítems de deuda
  - `.analisis/plan/plan-accion.md` — roadmap de remediación por fases
- `.agents/decisions/` — ADRs que capturan decisiones ya tomadas (consultar antes de reabrir una decisión).
