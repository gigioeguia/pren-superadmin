---
id: spec-ownership-withdraw-store-fase5
titulo: "Ownership en withdraw store y UserMethod API: scopear recursos al usuario autenticado (Fase 5 / 5.23, extensión)"
estado: Implementado
fecha: 2026-11-23
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.23) — hallazgo lateral L1 de .agents/spec-impl/spec-tx-legadas-services-fase5.md (§12) — extensión grilling 2026-11-23 (Api WithdrawMethodController update/destroy) — bug-clase Seg-29/Seg-31
cobertura: Cierra holes de ownership sobre UserMethod: stores de retiro Api/Landlord (5.23) y update/destroy de Api WithdrawMethodController. Fix por lookup scopeado (404, sin efecto colateral), regresiones negativas + happy path mínimo API, sin cambio de firma en WithdrawService.
---

# Spec — Ownership en withdraw store y UserMethod API: scopear recursos al usuario autenticado (Fase 5 / 5.23, extensión)

> Resumen de una línea: un landlord autenticado no puede operar `UserMethod` ajenos: los stores de retiro y los endpoints API update/destroy de withdraw-method resuelven el recurso scopeado por `user_id` y responden 404 sin efectos colaterales ante IDs ajenos.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.23). Hallazgo lateral L1 de `.agents/spec-impl/spec-tx-legadas-services-fase5.md` (§12). Extensión acordada en grilling 2026-11-23 para incluir `Api/WithdrawMethodController::update/destroy`. Mismo bug-clase que Seg-29/Seg-31 (resolución por input sin scope del usuario autenticado).

---

## 1. Objetivo

Eliminar holes de ownership sobre `UserMethod`: (1) los stores de retiro `Api/WithdrawController::store` y `Landlord/WithdrawController::store` resolvían `UserMethod::findOrFail($request->user_method_id)` sin scope, permitiendo retirar con método bancario ajeno (5.23, flujo de dinero); (2) `Api/WithdrawMethodController::update` y `::destroy` resuelven `UserMethod::findOrFail($id)` sin scope, permitiendo mutar/borrar/reasignar el método de otro usuario.

---

## 2. Alcance

### IN

- **Fix ownership 5.23 (retiros)** en los dos entrypoints nombrados por el plan:
  - `app/Http/Controllers/Api/WithdrawController.php::store`
  - `app/Http/Controllers/Landlord/WithdrawController.php::store`
- **Fix ownership extensión (CRUD UserMethod API)** en:
  - `app/Http/Controllers/Api/WithdrawMethodController.php::update`
  - `app/Http/Controllers/Api/WithdrawMethodController.php::destroy`
- **Resolución scopeada** de `UserMethod` por `user_id` del usuario autenticado; el caso ajeno/inexistente para ese usuario responde **404**.
- **Tests de regresión**: negativos cross-user para los 4 endpoints (2 stores + update + destroy) con aserciones de no-efecto; happy path mínimo para update/destroy API (no existen tests previos).
- **Compatibilidad** con contrato feliz congelado por 5.18: F1/F2 de `tests/Feature/Withdraw/WithdrawTxContractTest.php` permanecen intactos y verdes.
- **Documentación/spec closure** al implementar: fila 5.23 en `.analisis/plan/plan-accion.md` (incluyendo extensión), nota en `.analisis/seguridad.md` (bug-clase Seg-29/Seg-31), y actualización de índices/conteos si aplica.

### OUT

- **`WithdrawService::store`** — mantiene firma y contrato (`User $user, UserMethod $userMethod, float|string $amount, float $charge, Channel $channel): Withdraw`); no se añade guard redundante dentro del service.
- **Otros endpoints de `Api/WithdrawMethodController`** — `index` (catálogo WithdrawMethod), `show` y `user_withdraw_method` ya scopean/operan sobre datos del usuario; `store` fuerza `user_id = auth()->id()`.
- **Admin/Landlord WithdrawMethod CRUD web** — ya scopean por usuario en Landlord; Admin opera sobre catálogo global `WithdrawMethod` (modelo distinto). Fuera de este bug-clase.
- **Admin withdraw flows** (`approve/reject/index/show`) — fuera del vector reportado; ya cubiertos por 5.18/policy.
- **Cambios de UX/mensajes** en caminos legítimos, cambios de validación no relacionados, o refactor a FormRequest/policy dedicada.
- **Authorize/policy para store/update/destroy** — el control relevante aquí es ownership del `UserMethod` referenciado; no se introduce policy nueva salvo necesidad clara.

### Cobertura objetivo

- 6 casos nuevos mínimos (4 negativos cross-user + 2 happy path API update/destroy) en verde.
- F1–F6 existentes de `WithdrawTxContractTest` sin modificación y en verde.
- 0 regresiones en suite completa, PHPStan y Pint.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Hallazgos localizados | Existe — stores: `Api/WithdrawController.php:47`, `Landlord/WithdrawController.php:45`; CRUD API: `Api/WithdrawMethodController.php:77` (update), `:90` (destroy), todos con `UserMethod::findOrFail` sin scope | Cambiar a lookup scopeado por usuario autenticado |
| P2 | Contrato feliz congelado | Existe — `WithdrawTxContractTest` F1/F2 (spec 5.18) cubren store Api/Landlord | Mantener intactos; ejecutar como guard de no-regresión |
| P3 | Modelo `UserMethod` con `user_id` | Existe — `app/Models/UserMethod.php` tiene `user_id` fillable/cast y relación `withdraw_method()` | Ninguna; usar `where('user_id', ...)` |
| P4 | Patrón de scoping en repo | Existe — `Landlord/WithdrawMethodController` y otros flujos scopean por `auth()->id()`; Seg-29/Seg-31 establecen precedente | Reutilizar patrón de lookup scopeado |
| P5 | DB de test dedicada | Existe — `dbrentpro_test`, `DatabaseTransactions` (ADR-0001 / testing-conventions) | Ninguna |

---

## 4. Estructura propuesta

```
app/Http/Controllers/Api/WithdrawController.php        # MODIFICADO: scopear UserMethod por user autenticado (store)
app/Http/Controllers/Landlord/WithdrawController.php     # MODIFICADO: scopear UserMethod por user autenticado (store)
app/Http/Controllers/Api/WithdrawMethodController.php    # MODIFICADO: scopear UserMethod por user autenticado (update/destroy)
tests/Feature/Withdraw/WithdrawTxContractTest.php       # MODIFICADO: añadir F7–F8 (regresión ownership store)
tests/Feature/Withdraw/ApiWithdrawMethodOwnershipTest.php # NUEVO: F9–F12 (ownership update/destroy + happy path mínimo)

.agents/README.md                                       # MODIFICADO: índice specs fase5/conteo (al implementar)

.analisis/ (gitignored — edición local al implementar, sin commit)
  plan/plan-accion.md                                   # MODIFICADO: fila 5.23 → Cerrado + evidencia (alcance extendido)
  seguridad.md                                          # MODIFICADO: registrar/cerrar hallazgo (bug-clase Seg-29/Seg-31)
```

Sin cambios en `WithdrawService`, rutas, migraciones, modelos ni policies.

---

## 5. Decisiones técnicas

- **Lookup scopeado, no authorize post-fetch:** el `UserMethod` se resuelve con `UserMethod::where('user_id', $user->id)->findOrFail($request->user_method_id)` (o equivalente con `auth()->id()` ya resuelto como `$user`). No se hace `findOrFail` global + check manual, para evitar oráculo de existencia.
- **Respuesta 404:** caso ajeno/inexistente para el usuario autenticado → 404 estándar de `ModelNotFoundException`/`findOrFail`. No 403.
- **Enforcement en controlador:** el boundary HTTP que recibe input hace la resolución correcta. `WithdrawService::store` conserva firma y semántica; no se duplica guard dentro del service.
- **Sin cambio de contrato feliz:** F1/F2 siguen iguales; el fix solo restringe inputs cross-user que nunca debieron ser aceptados. Los endpoints update/destroy API no tienen tests previos; se agrega happy path mínimo para fijar contrato legítimo.
- **Ubicación de tests:** F7/F8 (stores) en `tests/Feature/Withdraw/WithdrawTxContractTest.php` para reutilizar helpers (`landlordWithMethod`, `landlordWithPlanAndMethod`). F9–F12 (Api withdraw-method update/destroy) en `tests/Feature/Withdraw/ApiWithdrawMethodOwnershipTest.php` (nuevo), porque no hay helpers previos ni tests existentes para esos endpoints. IDs correlativos dentro del spec; nombres descriptivos (`test_f7_...`–`test_f12_...`).
- **Caracterización-antes-de-fix (patrón repo):** la implementación puede añadir primero el test rojo que expone el hole y luego el fix en el mismo flujo de trabajo; el DoD exige el contrato final 404 + no-efecto en verde.
- **Framework/DB:** PHPUnit 12 + atributos `#[Group('...')]` si se crean métodos nuevos (el archivo ya existe; seguir el estilo vigente del archivo), `DatabaseTransactions`/`RefreshDatabase` según el test existente (`RefreshDatabase` ya usado), AAA comentado.

---

## 6. Catálogo de casos

### 6.1 Withdraw store ownership (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F7 | Api store rechaza `user_method_id` ajeno | landlord A autenticado por Sanctum con balance suficiente; landlord B con `UserMethod` propio; A conoce/envía el `user_method_id` de B | `POST /api/v1/landlord/withdraw` con `{user_method_id: <B>, amount: "10"}` como A | 404; no se crea `Withdraw` para A ni para B; balance de A intacto; no se crea notificación `SendNotification` |
| F8 | Landlord web store rechaza `user_method_id` ajeno | landlord A autenticado web con plan vigente y balance suficiente; landlord B con `UserMethod` propio; A envía el `user_method_id` de B | `POST /landlord/withdraw` con `{user_method_id: <B>, amount: "10"}` como A (sin CSRF en test, según patrón existente) | 404; no se crea `Withdraw` para A ni para B; balance de A intacto |

> **Nota de seguridad (L1 / 5.23):** el comportamiento actual acepta el método ajeno y crea retiro debitando balance del atacante sobre el método de otro usuario. El fix cierra el vector; estos casos fijan el contrato post-fix.

### 6.2 Api WithdrawMethod ownership (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F9 | Api update rechaza `UserMethod` ajeno | landlord A autenticado por Sanctum; landlord B con `UserMethod` propio (`method_id` válido) | `PUT/PATCH /api/v1/landlord/withdraw-methods/{id_de_B}` como A con payload válido (`method_id`) | 404; registro de B intacto (incluye `user_id` original y campos no mutados) |
| F10 | Api destroy rechaza `UserMethod` ajeno | landlord A autenticado por Sanctum; landlord B con `UserMethod` propio | `DELETE /api/v1/landlord/withdraw-methods/{id_de_B}` como A | 404; el `UserMethod` de B sigue existiendo |
| F11 | Api update happy path (dueño) | landlord A autenticado por Sanctum con `UserMethod` propio | `PUT/PATCH /api/v1/landlord/withdraw-methods/{id_de_A}` como A con payload válido | 200; mensaje de éxito; `UserMethod` actualizado; `user_id` permanece en A |
| F12 | Api destroy happy path (dueño) | landlord A autenticado por Sanctum con `UserMethod` propio | `DELETE /api/v1/landlord/withdraw-methods/{id_de_A}` como A | 200; mensaje de éxito; `UserMethod` eliminado |

> **Nota:** no hay tests previos para estos endpoints API; F11/F12 fijan el contrato legítimo mínimo junto al fix.

### Tests de caracterización

Ninguno en el spec final: F7–F12 se definen como contrato objetivo post-fix (404 + no-efecto / happy path legítimo). Si durante implementación se usa caracterización transitoria del hole, no se conserva como caracterización permanente salvo decisión explícita.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] F7–F8 implementados y en verde en `tests/Feature/Withdraw/WithdrawTxContractTest.php`. Evidencia: `php artisan test tests/Feature/Withdraw/WithdrawTxContractTest.php tests/Feature/Withdraw/ApiWithdrawMethodOwnershipTest.php` → 12 passed (70 assertions).
- [x] F9–F12 implementados y en verde en `tests/Feature/Withdraw/ApiWithdrawMethodOwnershipTest.php`. Evidencia: misma corrida focal, 4/4 verdes.
- [x] F1–F6 de `WithdrawTxContractTest` permanecen sin cambios funcionales y en verde. Evidencia: F1–F6 verdes en corrida focal y en suite completa.
- [x] `Api/WithdrawController::store`, `Landlord/WithdrawController::store`, `Api/WithdrawMethodController::update` y `Api/WithdrawMethodController::destroy` resuelven `UserMethod` scopeado por usuario autenticado; input ajeno produce 404 sin mutación/borrado. Evidencia: lookups `UserMethod::where('user_id', ...)->findOrFail(...)` en los 4 métodos.
- [x] `WithdrawService::store` conserva firma y comportamiento. Evidencia: sin cambios en `app/Services/WithdrawService.php`.
- [x] Suite completa en verde (`php artisan test`). Evidencia: 586 passed (6382 assertions).
- [x] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio. Evidencia: PHPStan `[OK] No errors`; Pint `PASS` tras fix de estilo en el nuevo test.
- [x] `.analisis/plan/plan-accion.md` fila 5.23 actualizada a Cerrado con evidencia, incluyendo la extensión update/destroy (edición local gitignored).
- [x] `.analisis/seguridad.md` actualizado con cierre del hallazgo (bug-clase Seg-29/Seg-31) (edición local gitignored).
- [x] `.agents/README.md` y conteos documentales actualizados si la suite/conteo de specs cambia.
- [x] Rama de trabajo no es `main`; PR con CI verde antes de merge. Evidencia: rama `spec/ownership-withdraw-store-fase5-impl`; PR/CI pendiente al momento de cerrar el spec.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| 404 cambia comportamiento para clientes que explotaban/envíaban IDs ajenos por bug | Bajo (comportamiento legítimo no afectado) | Contrato feliz F1/F2 intacto; el caso cross-user era un hole de seguridad, no un flujo soportado |
| Tests existentes que creen UserMethod sin cuidar ownership podrían asumir find global | Bajo | Buscar usos en tests de withdraw; F1/F2 ya usan método propio |
| Superficie `UserMethod` restante sin auditar exhaustivamente | Bajo | Revisado en grilling: `Api/WithdrawMethodController` store/show/user_withdraw_method/index no presentan el mismo hole; Landlord withdraw-method ya scopea. Mantener vigilancia en nuevos endpoints que reciban `user_method_id`/`id` de `UserMethod`. |
| Implementación agrega guard en service y cambia firma accidentalmente | Bajo | Decisión técnica fija: sin cambio de firma; revisar diff |

**Pendiente de decisión:** ninguno (Q1–Q5 grilling 2026-11-23).

---

## 9. Estimación

- Implementación (4 métodos en 3 controladores, lookup scopeado): 0.5 día.
- Tests F7–F12 + corrida focal: 0.5 día.
- Verificación (suite completa + analyse + lint) y cierre documental: 0.25 día.
- Total: ~1.25 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.23
- `.analisis/seguridad.md` → Seg-29, Seg-31 (bug-clase de referencia), nuevo cierre del hallazgo L1/5.23
- `.analisis/deuda-tecnica.md` → N/A directo (hallazgo de seguridad/dinero)
- `.agents/spec-impl/spec-tx-legadas-services-fase5.md` → L1 origen, F1/F2 contrato congelado, D7
- `.agents/spec/spec-conventions.md`
- `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md`
- `.agents/skills/secure-coding.md`
- `.agents/skills/workflow-ramas.md`
- `tests/Feature/Withdraw/WithdrawTxContractTest.php`

---

### Notas de implementación

1. **Lookup scopeado recomendado (ambos controladores):**

   ```php
   $user = User::findOrFail(auth()->id());
   $userMethod = UserMethod::where('user_id', $user->id)->findOrFail($request->user_method_id);
   $method = WithdrawMethod::findOrFail($userMethod->method_id);
   ```

   Mantiene el tipado explícito ya presente y limita la resolución al dueño.

2. **F7 — esqueleto Api:**

   ```php
   [$owner, $ownerMethod] = $this->landlordWithMethod(balance: 100);
   [$attacker] = $this->landlordWithMethod(balance: 100);

   Sanctum::actingAs($attacker);

   $response = $this->postJson('/api/v1/landlord/withdraw', [
       'user_method_id' => $ownerMethod->id,
       'amount' => '10',
   ]);

   $response->assertNotFound();
   $this->assertSame(0, Withdraw::count());
   $this->assertSame(100.0, (float) $attacker->fresh()->balance);
   $this->assertSame(0, DB::table('notifications')->where('type', SendNotification::class)->count());
   ```

3. **F8 — esqueleto Landlord web:**

   ```php
   [$owner, $ownerMethod] = $this->landlordWithPlanAndMethod(balance: 100);
   [$attacker] = $this->landlordWithPlanAndMethod(balance: 100);

   $response = $this->actingAs($attacker)
       ->withoutMiddleware(PreventRequestForgery::class)
       ->postJson('/landlord/withdraw', [
           'user_method_id' => $ownerMethod->id,
           'amount' => '10',
       ]);

   $response->assertNotFound();
   $this->assertSame(0, Withdraw::count());
   $this->assertSame(100.0, (float) $attacker->fresh()->balance);
   ```

4. **No tocar F1–F6:** cualquier cambio en esos tests invalida el contrato congelado por 5.18 salvo desviación explícita y documentada.

5. **Api WithdrawMethodController update/destroy — lookup scopeado recomendado:**

   ```php
   $method = UserMethod::where('user_id', auth()->id())->findOrFail($id);
   ```

   Aplicar en `update` antes de mutar y en `destroy` antes de borrar. Mantener el contrato de respuesta actual en happy path.

6. **F10 (destroy ajeno) — esqueleto:**

   ```php
   $owner = $this->createLandlord();
   $attacker = $this->createLandlord();
   $withdrawMethod = WithdrawMethod::create([...]);
   $ownerMethod = UserMethod::create(['user_id' => $owner->id, 'method_id' => $withdrawMethod->id, ...]);

   Sanctum::actingAs($attacker);
   $response = $this->deleteJson("/api/v1/landlord/withdraw-methods/{$ownerMethod->id}");

   $response->assertNotFound();
   $this->assertDatabaseHas('user_methods', ['id' => $ownerMethod->id, 'user_id' => $owner->id]);
   ```
