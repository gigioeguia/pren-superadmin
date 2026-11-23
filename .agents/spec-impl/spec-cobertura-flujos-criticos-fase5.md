---
id: spec-cobertura-flujos-criticos-fase5
titulo: "Cobertura de tests ≥ 60 % en flujos críticos (auth, pagos, IDOR)"
estado: Aprobado
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md (Métricas de éxito — "Cobertura de tests ≥ 60 % en flujos críticos (auth, pagos, IDOR)") + Fase 0 / 0.1 (tests smoke) + Seg-5 (IDOR) + Seg-6 (pagos)
cobertura: Medir y elevar la cobertura de app/ en los flujos críticos (auth, pagos, IDOR) a ≥ 60 %, con driver PCOV y umbral en CI.
---

# Spec — Cobertura de tests ≥ 60 % en flujos críticos (auth, pagos, IDOR)

> Resumen de una línea: instalar un driver de cobertura, medir el baseline de `app/` en los flujos críticos y elevarlo a ≥ 60 % con tests nuevos, fijando un umbral en CI.

**Origen:** `.analisis/plan/plan-accion.md` (Métricas de éxito). Hallazgos relacionados: Seg-5 (IDOR), Seg-6 (pagos), DT-C1 (tests smoke).

---

## 1. Objetivo

Cerrar la métrica pendiente "Cobertura de tests ≥ 60 % en flujos críticos (auth, pagos, IDOR)" instalando un driver de cobertura, midiendo el baseline y añadiendo tests hasta alcanzar el umbral, con enforcement en CI.

---

## 2. Alcance

### IN

- Instalar y documentar un driver de cobertura (PCOV preferido; Xdebug como fallback) en local y CI.
- Definir el conjunto de flujos críticos y sus archivos fuente objetivo:
  - Auth: `app/Http/Controllers/Auth/*`, `app/Http/Controllers/Api/RegistrationController.php` (o equivalente), middleware de auth.
  - Pagos: `app/Http/Controllers/PaymentController.php`, `app/Services/PaymentService.php`, `app/Services/SecurityDepositService.php`, `app/Library/*`.
  - IDOR: `app/Policies/*`, controladores Admin/Landlord/Api de las 5 entidades con Policy.
- Medir cobertura en CI con PCOV (`php artisan test --coverage --min=60`) sobre los flujos críticos.
- Añadir tests (smoke/caracterización) donde el baseline sea < 60 %.
- Fijar umbral ≥ 60 % en CI (job o step) sobre los flujos críticos.
- Actualizar la métrica en `plan-accion.md` (⬜ → ✅) con evidencia.

### OUT

- Cobertura del 100 % o de módulos no críticos (admin CRUD cosmético, exports).
- Refactor de código para "testabilidad" más allá de lo necesario.
- Cambios de lógica de negocio.

### Cobertura objetivo

- ≥ 60 % de líneas en los archivos fuente de los flujos críticos (auth, pagos, IDOR), medido por grupo.
- Baseline documentado antes de añadir tests.
- Umbral en CI que falle si se baja del 60 % en esos flujos.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Driver de cobertura (PCOV/Xdebug) | No instalado (`php -m` sin pcov/xdebug) | Instalar `php8.4-pcov` en local y en el runner de CI |
| P2 | phpunit.xml con `<source>` | Existe (directorio `app`) | Añadir configuración de reporte si se desea (`<coverage>`) |
| P3 | Definición de flujos críticos | Parcial (tests smoke 84.6% endpoints) | Listar archivos fuente objetivo y mapear a tests |
| P4 | CI con job de tests | Existe (`.github/workflows/ci.yml`) | Añadir step de cobertura con umbral |

---

## 4. Estructura propuesta

```
.github/workflows/ci.yml                    # MODIFICADO: step de cobertura con umbral
phpunit.xml                                 # MODIFICADO: reporte de cobertura (opcional)
tests/Feature/Auth/*                        # MODIFICADOS/AMPLIADOS
tests/Feature/Payment/*                     # MODIFICADOS/AMPLIADOS
tests/Feature/Idor/*                        # MODIFICADOS/AMPLIADOS
.analisis/plan/plan-accion.md              # MODIFICADO: métrica ⬜ → ✅ con evidencia
```

---

## 5. Decisiones técnicas

- **Driver:** PCOV (más rápido que Xdebug) en local y CI; documentar instalación.
- **Métrica:** líneas cubiertas (line coverage) sobre los archivos fuente de los flujos críticos, no sobre todo `app/`.
- **Umbral:** ≥ 60 %; falla en CI si baja.
- **Tests:** seguir convenciones del repo (PHPUnit 12, atributos `#[Group]`, `RefreshDatabase`/`DatabaseTransactions` según el caso).
- **Caracterización:** donde un flujo crítico carezca de test, añadir tests de caracterización (skill `tests-caracterizacion`) antes de cualquier fix.

---

## 6. Catálogo de casos (verificación)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | PCOV instalado | Entorno local/CI con PHP 8.4 | `php -m \| grep pcov` | Módulo presente |
| U2 | Cobertura medida en CI | Suite completa con PCOV en CI | `php artisan test --coverage --min=60` en el job `coverage` | Job `coverage` en verde (exit 0) |
| U3 | Umbral ≥ 60 % por grupo | Baseline + tests añadidos | Ejecutar cobertura filtrada a auth, pagos e IDOR por separado | Cobertura de líneas ≥ 60 % en cada grupo (auth, pagos, IDOR), no solo en el total de `app/` |
| U4 | CI enforcement | PR que baja cobertura < 60 % en algún grupo | Job de cobertura en CI | Job falla (exit != 0) |
| U5 | Métrica actualizada | Cobertura verificada en CI | Revisar `plan-accion.md` | Línea de métrica marcada ✅ con evidencia (job `coverage` en verde) |

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] PCOV (o Xdebug) instalado y documentado en local y CI.
- [ ] Cobertura validada en CI mediante PCOV (job `coverage` con `--min=60`) en verde.
- [ ] Cobertura ≥ 60 % en flujos críticos (auth, pagos, IDOR).
- [ ] Job/step de CI con umbral ≥ 60 % que falle si se baja.
- [ ] Métrica en `plan-accion.md` actualizada (⬜ → ✅) con evidencia.
- [ ] `composer analyse` y `composer lint` en verde.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Instalación de PCOV en CI (runner ubuntu) | Bajo | Usar `shivammathur/setup-php` con `coverage: pcov` |
| Tiempo de ejecución con cobertura | Medio | Filtrar a `app/` y ejecutar solo tests relevantes en el job de cobertura |
| Tests frágiles por cobertura | Bajo | Preferir tests de comportamiento sobre tests de líneas |

**Pendiente de decisión:** elección final de driver (PCOV vs Xdebug) y si el umbral se aplica a todo `app/` o solo a flujos críticos.

---

## 9. Estimación

- Instalación driver + baseline: 0.25 día.
- Análisis de gaps + tests: 1–2 días.
- CI enforcement + documentación: 0.25 día.
- Total: ~1.5–2.5 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Métricas de éxito (cobertura ≥ 60 %)
- `.agents/spec/spec-conventions.md`
- `.agents/skills/testing-conventions.md`
- `.agents/skills/tests-caracterizacion.md`
- `phpunit.xml`
- `.github/workflows/ci.yml`
- `tests/README.md`
