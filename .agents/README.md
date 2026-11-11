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
├── spec-impl/                 # specs concretos (un archivo por spec)
│   └── spec-tests-smoke-fase0.md
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
