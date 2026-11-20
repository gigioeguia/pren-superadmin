---
id: spec-plan-duration-days-fase4
titulo: "Plan::durationInDays() + verificar duplicación OTP (Fase 4 / 4.7)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.7) — DT-M2, DT-M3 (absorbido parcialmente por 4.6)
cobertura: Extraer el cálculo de duración de plan a Plan::durationInDays() y reemplazar los 7 bloques duplicados; verificar que la duplicación OTP ya quedó eliminada por App\Support\Otp (Fase 4 / 4.6).
---

# Spec — Plan::durationInDays() (Fase 4 / 4.7)

> Resumen: unificar el cálculo `duration_type → días` en un único método `Plan::durationInDays()`, eliminando la duplicación en 7 call sites, y verificar que la duplicación OTP (DT-M3) ya no existe tras Fase 4 / 4.6.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.7) — `DT-M2`, `DT-M3`. Absorbe el remanente de `DT-M3` verificado por `spec-helpers-globales-clases-fase4`.

---

## 1. Objetivo

Hacer que el cálculo de duración de un plan (`month → ×30`, `year → ×365`, `day → ×1`) viva en un único método `Plan::durationInDays()`, y reemplazar los 7 bloques duplicados existentes. Como parte del mismo spec, verificar que la duplicación OTP ya quedó eliminada por `App\Support\Otp` (Fase 4 / 4.6) y que no queda `getOtpTimeInSeconds` en `app/`.

## 2. Alcance

### IN
- `app/Models/Plan.php`: método `durationInDays(): int`.
- Reemplazo de los 7 bloques duplicados en:
  - `app/Http/Controllers/Auth/SignupController.php` (`store`)
  - `app/Http/Controllers/Api/Auth/RegistrationController.php` (`maanRegistration`, 2 bloques: free plan + subscription)
  - `app/Http/Controllers/Api/AcnooSecurityDepositController.php`
  - `app/Http/Controllers/PaymentController.php` (`payment`, 2 bloques: manual + subscription)
  - `app/Http/Controllers/Admin/AcnooLandlordController.php`
- Tests unitarios de `Plan::durationInDays()`.
- Verificación por grep de que no queda duplicación de duración ni de OTP.

### OUT
- No se cambia la lógica de duración (`month=×30`, `year=×365`, `day=×1`).
- No se toca `PlanSubscribe` ni migraciones.
- No se modifica `App\Support\Otp` (ya centralizado en 4.6); aquí solo se verifica.

### Cobertura objetivo
- 0 bloques `if ($...->duration_type === 'month')` fuera de `Plan::durationInDays()`.
- 0 ocurrencias de `getOtpTimeInSeconds` en `app/`.
- Tests unitarios en verde + suite completa en verde.
- `composer analyse` y `composer lint` en verde.

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo | En `main` | Crear `spec/plan-duration-days-fase4-impl` antes de implementar |
| P2 | Suite verde | Verde (219 tests) | Ejecutar `php artisan test` antes de empezar |
| P3 | PHPStan/Pint/CI | Configurados (Fase 0) | Usar como gate |
| P4 | Modelo `Plan` | Existe sin `durationInDays()` | Añadir método |
| P5 | `App\Support\Otp` | Existe (Fase 4 / 4.6) | Ninguna; solo verificación |

## 4. Estructura propuesta

```
app/Models/Plan.php                                      # MODIFICADO: + durationInDays()
app/Http/Controllers/Auth/SignupController.php           # MODIFICADO
app/Http/Controllers/Api/Auth/RegistrationController.php  # MODIFICADO
app/Http/Controllers/Api/AcnooSecurityDepositController.php # MODIFICADO
app/Http/Controllers/PaymentController.php                # MODIFICADO
app/Http/Controllers/Admin/AcnooLandlordController.php    # MODIFICADO
tests/Unit/PlanTest.php                                  # NUEVO
```

## 5. Decisiones técnicas

- El método vive en el modelo `Plan` (no en un Service), porque es comportamiento intrínseco de la entidad.
- Firma: `public function durationInDays(): int`.
- Implementación con `match` (PHP 8.3):
  ```php
  return match ($this->duration_type) {
      'month' => $this->duration * 30,
      'year'  => $this->duration * 365,
      'day'   => (int) $this->duration,
      default => 0,
  };
  ```
- Los call sites reemplazan el bloque completo por `$duration = $plan->durationInDays();` (o `$free_plan->durationInDays()`).
- OTP: se verifica con grep que `getOtpTimeInSeconds` no existe en `app/`; la lógica vive en `App\Support\Otp::visibilityTimeInSeconds()`.
- **Estilo:** código en inglés, comentarios en español solo si aportan valor; Pint preset laravel.

## 6. Catálogo de casos

### 6.1 `Plan::durationInDays()` (`U`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| U1 | Meses | `duration=2, duration_type=month` | `durationInDays()` | `60` |
| U2 | Años | `duration=1, duration_type=year` | `durationInDays()` | `365` |
| U3 | Días | `duration=15, duration_type=day` | `durationInDays()` | `15` |
| U4 | Tipo desconocido | `duration_type='week'` | `durationInDays()` | `0` |
| U5 | Duración cero | `duration=0` | `durationInDays()` | `0` |

### 6.2 Refactor de controladores (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Sin bloques duplicados | Código migrado | `grep -rn "duration_type === 'month'" app/` (excl. Plan.php) | 0 resultados |
| F2 | Signup web sigue creando suscripción | Plan gratis | Flujo de signup con OTP off | `PlanSubscribe.duration` idéntico al anterior |
| F3 | Registro API (free plan) | Plan gratis | Registro API | `PlanSubscribe.duration` idéntico al anterior |
| F4 | SecurityDeposit | Plan de suscripción | Flujo de depósito | `duration` idéntico al anterior |
| F5 | Payment manual | Plan de suscripción | Flujo manual de pago | `duration` idéntico al anterior |
| F6 | Payment automático | Plan de suscripción | Flujo de gateway | `duration` idéntico al anterior |
| F7 | Admin landlord assign free plan | Plan gratis | Flujo admin | `duration` idéntico al anterior |

### 6.3 OTP y calidad (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F8 | Sin duplicación OTP | Código migrado | `grep -rn "getOtpTimeInSeconds" app/` | 0 resultados |
| F9 | PHPStan sin nuevos errores | Código migrado | `composer analyse` | Sin regresiones vs baseline |
| F10 | Pint limpio | Código migrado | `composer lint` | Sin violaciones |
| F11 | Suite completa en verde | Código migrado | `php artisan test` | Todos los tests en verde |

## 7. Criterios de aceptación (Definition of Done)

- [x] `Plan::durationInDays()` implementado en `app/Models/Plan.php`.
- [x] 7 call sites reemplazados por `$plan->durationInDays()` / `$free_plan->durationInDays()`.
- [x] 0 bloques duplicados de duración fuera de `Plan` (F1).
- [x] 0 ocurrencias de `getOtpTimeInSeconds` en `app/` (F8).
- [x] `PlanTest` en verde (U1–U5).
- [x] Tests de auth/pagos en verde (F2–F7).
- [x] `composer analyse` sin nuevos errores (F9).
- [x] `composer lint` limpio (F10).
- [x] `php artisan test` completo en verde (F11).

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Reemplazo incompleto deja un bloque viejo | Medio | Verificación por grep en DoD (F1) |
| `duration` null en BD | Bajo | Cast `(int)` en el método |
| Regresión en flujos de pago/auth | Medio | Suite de smoke tests (F2–F7) |

**Pendiente de decisión:**
- Si `durationInDays()` debe lanzar excepción ante `duration_type` desconocido en lugar de devolver `0` (este spec propone `0` para preservar comportamiento actual).

## 9. Estimación

- Modelo + 7 reemplazos: 0.5–1 día.
- Tests unitarios: 0.5 día.
- Verificación PHPStan/Pint/suite: 0.25 día.
- Total: 1.25–1.75 días.

## 10. Referencias

- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
- `.analisis/plan/plan-accion.md` → Fase 4 / 4.7
- `.analisis/deuda-tecnica.md` → DT-M2, DT-M3
- `.agents/spec-impl/spec-helpers-globales-clases-fase4.md` → absorbe OTP (4.6)
- `app/Models/Plan.php`
