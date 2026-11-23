---
id: spec-authorize-rent-payment-offline-fase5
titulo: "Authorize en la rama rent_payment del offline payment (dinero) (Fase 5 / 5.24)"
estado: Implementado
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.24) — hallazgo lateral L2 de .agents/spec-impl/spec-tx-legadas-services-fase5.md (§12) — bug-clase Seg-5/Seg-33
cobertura: Cierra el hole de autorización en la rama rent_payment de Api/SecurityDepositController::offline_payment añadiendo Gate offlinePayment sobre RentPayment (paridad con la rama security_deposit), preservando el contrato HTTP congelado (500 + sin mutación). Regresión Gate-level + HTTP.
---

# Spec — Authorize en la rama `rent_payment` del offline payment (dinero) (Fase 5 / 5.24)

> Resumen de una línea: la rama `rent_payment` de `offline_payment` pasa a autorizar sobre el `RentPayment` resuelto mediante `RentPaymentPolicy::offlinePayment`, cerrando el IDOR que permitía a un tenant marcar `pending` una renta ajena; contrato HTTP 500 + sin mutación preservado.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.24). Hallazgo lateral L2 de `.agents/spec-impl/spec-tx-legadas-services-fase5.md` (§12). Bug-clase Seg-5/Seg-33 (autorización ausente sobre recurso resuelto).

---

## 1. Objetivo

Eliminar el hole de autorización en la rama `rent_payment` de `Api/SecurityDepositController::offline_payment`: añadir `RentPaymentPolicy::offlinePayment` (paridad con `SecurityDepositPolicy::offlinePayment`) y su invocación en el controlador, de modo que un tenant no pueda marcar `pending` una rent_payment ajena. El contrato HTTP observable (500 `Payment failed` + sin escritura para el caso ajeno) se preserva por paridad con F13b.

---

## 2. Alcance

### IN

- **Policy:** `app/Policies/RentPaymentPolicy.php` — nuevo método `offlinePayment(User, RentPayment)` (landlord o tenant del rent + bypass admin/superadmin vía `before()`).
- **Controller:** `app/Http/Controllers/Api/SecurityDepositController.php::offline_payment` — branch `rent_payment` con `$this->authorize('offlinePayment', RentPayment::findOrFail($id))` dentro del `try` existente.
- **Tests de regresión:** aserción Gate-level en `tests/Feature/Idor/IdorPolicySmokeTest.php` (`test_524_tenant_cannot_offline_pay_foreign_rent_payment`) y caso HTTP en `tests/Feature/Payment/OfflinePaymentContractTest.php` (`test_524_foreign_rent_payment_offline_returns_500`).
- **Cierre documental** (plan-accion, seguridad, spec).

### OUT

- **`SecurityDepositService::offlinePayment`** — sin cambios; la autorización ocurre en el controlador, antes de invocar el service, por paridad con la rama `security_deposit`.
- **Rutas, migraciones, modelos** — sin cambios.
- **Semántica del happy path F13** — intacta (rent_payment propio → 200 + `pending`).
- **Cambio del contrato HTTP a 403** — fuera de alcance; se preserva el 500 congelado por F13b por paridad.

### Cobertura objetivo

- 1 aserción Gate-level + 1 caso HTTP de regresión, ambos verdes.
- F13/F13b sin modificación y en verde.
- 0 regresiones en suite completa, PHPStan y Pint.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | `SecurityDepositPolicy::offlinePayment` como plantilla | Existe — `app/Policies/SecurityDepositPolicy.php:49` | Replicar en `RentPaymentPolicy` |
| P2 | Branch `rent_payment` en el controlador sin authorize | Existe — `app/Http/Controllers/Api/SecurityDepositController.php:69-71` | Añadir `elseif` con authorize |
| P3 | `RentPaymentPolicy` registrada (auto-discovery) | Existe | Ninguna |
| P4 | Contract test F13/F13b congelados | Existe — `tests/Feature/Payment/OfflinePaymentContractTest.php` | Mantener intactos; añadir F13c |
| P5 | IdorPolicySmokeTest con f12 deposit | Existe | Añadir aserción rent_payment |

---

## 4. Estructura propuesta

```
app/Policies/RentPaymentPolicy.php                       # MODIFICADO: + offlinePayment
app/Http/Controllers/Api/SecurityDepositController.php    # MODIFICADO: branch rent_payment authorize
tests/Feature/Idor/IdorPolicySmokeTest.php               # MODIFICADO: + test_524_...
tests/Feature/Payment/OfflinePaymentContractTest.php     # MODIFICADO: + test_524_...
.agents/spec-impl/spec-authorize-rent-payment-offline-fase5.md
```

---

## 5. Decisiones técnicas

- **Ability dedicada, no reutilización:** `offlinePayment` es distinto de `view`/`update` (semántica de pago, no de consulta/edición). Paridad con `SecurityDepositPolicy`.
- **Semántica del Gate:** landlord o tenant del rent (espejo de deposit), más bypass admin/superadmin vía `before()`. El actor real del endpoint es el tenant; el landlord se incluye por consistencia con la política existente.
- **Enforcement en controlador, dentro del `try`:** un pago ajeno lanza `AuthorizationException` que el `catch (\Exception)` convierte en 500 `Payment failed`, sin escritura. Paridad con F13b (no se cambia a 403).
- **Sin cambio en el service:** `SecurityDepositService::offlinePayment` no se toca; la resolución `RentPayment::findOrFail($id)` del service queda precedida por el authorize del controlador.
- **Regresión Gate-level:** `Gate::forUser(tenantA)->allows('offlinePayment', paymentB)` debe ser `false`; owner `true`.
- **Regresión HTTP:** rent_payment ajeno → 500 `Payment failed` + `payment_status` intacto.

---

## 6. Catálogo de casos

### 6.1 Policy (Gate-level)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| G1 | Tenant no puede offline-pay rent ajena | rent_payment de landlordB/tenantB | `Gate::forUser(tenantA)->allows('offlinePayment', paymentB)` | `false` |
| G2 | Tenant owner puede offline-pay la propia | rent_payment de tenantA | `Gate::forUser(tenantA)->allows('offlinePayment', paymentA)` | `true` |

### 6.2 HTTP (contract)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F13c | rent_payment ajeno → 500 sin mutación | tenant intruder, rent_payment propio de otro tenant con `payment_status=paid` | `POST /api/v1/tenant/offline-payment/{gw}/{id}` con `payment_type=rent_payment` | 500 `Payment failed`; `payment_status` permanece `paid` |

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `RentPaymentPolicy::offlinePayment` implementado (landlord o tenant del rent + bypass admin/superadmin).
- [x] Branch `rent_payment` en `Api/SecurityDepositController::offline_payment` con authorize dentro del `try`.
- [x] `test_524_tenant_cannot_offline_pay_foreign_rent_payment` en verde (IdorPolicySmokeTest).
- [x] `test_524_foreign_rent_payment_offline_returns_500` en verde (OfflinePaymentContractTest).
- [x] F13/F13b sin cambios y en verde.
- [x] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio.
- [x] `.analisis/plan/plan-accion.md` fila 5.24 actualizada a Cerrado con evidencia.
- [x] `.analisis/seguridad.md` actualizado (Seg-33 cerrado).
- [x] Rama de trabajo no es `main`.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Contrato 500 en lugar de 403 para el caso ajeno | Bajo (paridad con F13b congelada) | Documentado; el Gate-level (IdorPolicySmokeTest) fija la negación real |
| Hidden tests que esperen 403 HTTP | Medio | El plan referencia Gate-level f12, no HTTP; se mantiene paridad con deposit |
| Semántica landlord incluida podría ser demasiado permisiva | Bajo | Espejo exacto de `SecurityDepositPolicy::offlinePayment` |

**Pendiente de decisión:** ninguno (Q1–Q5 grilling 2026-11-23).

---

## 9. Estimación

- Policy + controller: 0.25 día.
- Tests de regresión: 0.25 día.
- Verificación + cierre documental: 0.25 día.
- Total: ~0.75 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.24
- `.analisis/seguridad.md` → Seg-33 (y Seg-5, Seg-29/31 como bug-clase)
- `.agents/spec-impl/spec-tx-legadas-services-fase5.md` → L2 origen, F13/F13b contrato congelado
- `.agents/spec-impl/spec-policies-fase2.md` → F12 Gate-level
- `app/Policies/SecurityDepositPolicy.php`
- `app/Policies/RentPaymentPolicy.php`
- `app/Http/Controllers/Api/SecurityDepositController.php`
- `tests/Feature/Idor/IdorPolicySmokeTest.php`
- `tests/Feature/Payment/OfflinePaymentContractTest.php`
