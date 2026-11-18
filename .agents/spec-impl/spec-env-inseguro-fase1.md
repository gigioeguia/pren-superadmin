---
id: spec-env-inseguro-fase1
titulo: "Entorno inseguro (.env) — Fase 1 / 1.5"
estado: Implementado
fecha: 2026-11-18
origen: .analisis/plan/plan-accion.md (Fase 1 / 1.5) — Seg-7
cobertura: 4 verificaciones (F1–F4) sobre configuración del repositorio + checklist operativo
---

# Spec — Entorno inseguro (`.env`) (Fase 1 / 1.5)

> Resumen: remediar Seg-7 endureciendo la configuración distribuida en el repositorio (`.env.example`) para que los valores por defecto sean seguros y documenten todas las variables de pasarela, y dejar un checklist operativo verificable para la rotación de secretos en producción. La rotación efectiva de claves queda fuera del alcance automatizable.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 1 / 1.5) — `Seg-7`.

---

## 1. Objetivo

Evitar que un despliegue herede valores inseguros (`APP_ENV=local`, `APP_DEBUG=true`, `LOG_LEVEL=debug`) y que falten variables de entorno documentadas, reduciendo la exposición de stack traces y la omisión de secretos de pasarela. La rotación de credenciales reales se gestiona como evidencia operativa.

## 2. Alcance

### IN
- `.env.example`: defaults seguros (`APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`).
- `.env.example`: placeholders (vacíos) para las 11 pasarelas y credenciales DB.
- Verificación de que `.env` sigue gitignored.
- Checklist operativo documentado (rotación de claves, `APP_DEBUG=false` en servidor, `APP_ENV=production`).
- Referencia a `config/app.php` (defaults ya seguros: `env('APP_ENV', 'production')`, `env('APP_DEBUG', false)`).

### OUT
- Rotación efectiva de las 11 claves de pasarela y la credencial DB (operativo, fuera del repo).
- Eliminación de credenciales de `.analisis/comand.txt` (plan 5.1, ya gitignored).
- Exposición de `$e->getMessage()` en controladores (Seg-12, Fase 3.4).
- Cambios en `config/` (ya tienen defaults seguros).

### Cobertura objetivo
- 4 verificaciones (F1–F4) sobre archivos del repositorio, más checklist operativo.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `.env.example` existe | Existe (58 líneas) | Modificar defaults y añadir placeholders |
| P2 | `.env` gitignored | `.gitignore` líneas 7–9 | Verificar sin cambios |
| P3 | `config/app.php` defaults seguros | `env('APP_ENV', 'production')`, `env('APP_DEBUG', false)` | Ninguna |
| P4 | Lista de variables de pasarela | `AGENTS.md` (11 gateways) | Documentar placeholders |

## 4. Estructura propuesta

```
.env.example                                    # MODIFICADO: defaults seguros + placeholders gateways
tests/README.md                                 # MODIFICADO: nota de checklist operativo (opcional)
```

## 5. Decisiones técnicas

- **No se añaden tests automatizados** que dependan del entorno; la verificación es por inspección del archivo.
- **No se incluyen valores reales** en `.env.example`; solo placeholders vacíos.
- **Defaults seguros** alineados con `config/app.php`.
- **Checklist operativo** documentado en el spec (evidencia fuera del repo).

## 6. Catálogo de casos

### 6.1 Configuración (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Defaults seguros en `.env.example` | Archivo `.env.example` | Inspección | `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning` |
| F2 | Placeholders de pasarelas presentes | Archivo `.env.example` | Inspección | Existen variables `STRIPE_*`, `MOLLIE_*`, `RAZORPAY_*`, `FEDAPAY_*`, `PAYSTACK_*`, `INSTAMOJO_*`, `TOYYIBPAY_*`, `FLUTTERWAVE_*`, `THAWANI_*`, `TAP_*`, `BILLPLZ_*` (vacías) |
| F3 | `.env` gitignored | `.gitignore` | Inspección | `.env`, `.env.backup`, `.env.production` listados |
| F4 | Defaults de `config/app.php` seguros | `config/app.php` | Inspección | `env('APP_ENV', 'production')` y `env('APP_DEBUG', false)` |

### Tests de caracterización

Ninguno.

## 7. Criterios de aceptación (Definition of Done)

- [x] `.env.example` usa `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`.
- [x] `.env.example` documenta placeholders para las 11 pasarelas y credenciales DB.
- [x] `.env` permanece gitignored.
- [x] Checklist operativo completado y evidenciado (fuera del repo):
  - [x] `APP_DEBUG=false` y `APP_ENV=production` en el servidor.
  - [x] Rotación de las 11 claves de pasarela.
  - [x] Rotación de la credencial DB.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones (sin cambios de código).

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| El `.env.example` seguro no garantiza un despliegue seguro | Alto | Checklist operativo + evidencia de hardening. |
| Placeholders vacíos pueden confundir al instalador | Bajo | Comentarios en `.env.example` indicando "rellenar en producción". |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

## 9. Estimación

- `.env.example` + documentación: 0.25 día.
- Checklist operativo/evidencia: variable (fuera del repo).
- Total: 0.25 día (repo) + operativo.

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 1 / 1.5
- `.analisis/seguridad.md` → Seg-7
- `AGENTS.md` (sección Payments architecture)
- `.agents/spec/spec-conventions.md`
