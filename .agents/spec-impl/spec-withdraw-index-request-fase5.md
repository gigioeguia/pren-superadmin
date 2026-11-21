---
id: spec-withdraw-index-request-fase5
titulo: "Cierre Seg-20: `AcnooWithdrawController::index` ya corregido en 4.4 — test de regresión HTTP y sellado documental (Fase 5 / 5.3)"
estado: Borrador
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.3) — .analisis/seguridad.md (Seg-20) — .agents/spec-impl/spec-inyectar-request-fase4.md (absorción)
cobertura: Cierre del bug Seg-20 (`index` usaba `$request->per_page` sin inyectar `Request` → error en runtime): documenta la absorción por Fase 4 / 4.4 (commit ea07c87), añade la anti-regresión que 4.4 dejó como "Inspección" (test HTTP F1–F3: per_page, rango de fechas, scoping por user_id) y sella los documentos que aún listan el hallazgo como vivo (seguridad.md #20, plan-accion 5.3, índice .agents/README.md)
---

# Spec — Cierre Seg-20: `AcnooWithdrawController::index` ya corregido en 4.4 — test de regresión HTTP y sellado documental (Fase 5 / 5.3)

> Resumen de una línea: el fix de Seg-20 ya vive en `main` desde 4.4 — este spec lo confirma con evidencia automatizada (test HTTP del endpoint, la reproducción exacta del bug) y cierra documentalmente el hallazgo en los 3 documentos que aún lo listan como vivo.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.3) — `.analisis/seguridad.md` (Seg-20). Absorbido por Fase 4 / 4.4 (`spec-inyectar-request-fase4`, commit `ea07c87`).

---

## 1. Objetivo

Cerrar el ítem 5.3 (Seg-20) sin re-implementar lo ya hecho: el fix (`Api\AcnooWithdrawController::index(Request $request)`) está en `main` desde 4.4, pero quedó verificado solo por "Inspección" (DoD F4 de 4.4) y los documentos de `.analisis/` aún listan el hallazgo como vivo. Este spec aporta la anti-regresión automatizada que falta y sella los documentos.

---

## 2. Alcance

### IN

- **Evidencia de estado actual** (verificada 2026-11-21, sin cambios de código):
  - Firma `public function index(Request $request)` en `app/Http/Controllers/Api/AcnooWithdrawController.php` (commit `ea07c87`).
  - Ruta `GET /api/v1/landlord/withdraw` (routes/api.php l.55; grupo `auth:sanctum` l.25 + `prefix('landlord')` l.32).
  - 0 usos de `request(` en `app/Http/Controllers/` (herencia 4.4).
- **Test de regresión HTTP** `tests/Feature/Withdraw/WithdrawIndexSmokeTest.php` (nuevo): F1–F3 (§6).
- **Cierre documental**:
  - `.analisis/seguridad.md` — #20 con anotación inline "cerrado (2026-11-21…)" (patrón #19, l.131).
  - `.analisis/plan/plan-accion.md` — fila 5.3 con referencia a este spec (patrón filas 5.1/5.2) + línea nueva en "Métricas de éxito" + nota del typo `'meassage'` en fila 5.10.
  - `.agents/README.md` — índice: sección fase5 (3 specs; hoy los 2 existentes no figuran).
- **Conteos de suite**: `tests/README.md` y `AGENTS.md` — 254 → 257 casos.

### OUT

- **`Landlord\AcnooWithdrawController::index()`** — no inyecta `Request` pero tampoco lo usa (paginate fijo): sin bug, no se toca. No confundir con Seg-20, que refiere al controlador Api (Q3 grilling 2026-11-21).
- **Typo `'meassage'`** en la key de respuesta del Api index (invoice/summary usan `'message'`) — diferido, candidato a 5.10 (Q5). El test no aserta la key de mensaje: solo status + shape de `data`.
- **Cambios de comportamiento observable** — ninguno: el endpoint no se modifica; este spec solo adiciona test y documentación.
- Renombrado prefijo `Acnoo` (5.10), typos de rutas (5.8), migración de withdraw a Services (5.13) — fuera de alcance.

### Cobertura objetivo

- F1–F3 en verde (firma rota ⇒ 500 en los 3 casos).
- 3 documentos coherentes: ninguno lista Seg-20 como hallazgo vivo.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Fix en `main` (absorción 4.4) | Existe — `index(Request $request)`, commit `ea07c87`, DoD F4 de 4.4 | Ninguna: el test lo verifica, no lo produce |
| P2 | Ruta `GET /api/v1/landlord/withdraw` bajo `auth:sanctum` | Existe — routes/api.php l.55 (grupo l.25/32) | Ninguna |
| P3 | Creación liviana de `Withdraw` | Existe — `Withdraw::create(['user_id','amount','charge','status'])` sin `UserMethod` (IdorPolicySmokeTest F15); `$fillable` no incluye `created_at` | F2 fija `created_at` por asignación directa + `save()` |
| P4 | Factory states por rol + `unique_id` explícito | Existe (testing-conventions) | `User::factory()->landlord()` en cada test |
| P5 | DB de test dedicada `dbrentpro_test` (ADR-0001) | Existe (phpunit.xml) | Ninguna |

---

## 4. Estructura propuesta

```
tests/Feature/Withdraw/WithdrawIndexSmokeTest.php   # NUEVO: F1–F3

.agents/README.md                                    # MODIFICADO: índice — sección fase5 (3 specs), conteo 45 → 48
tests/README.md                                      # MODIFICADO: conteo de suite 254 → 257
AGENTS.md                                            # MODIFICADO: conteo de suite 254 → 257

.analisis/ (gitignored — edición local, sin commit)
  seguridad.md                                      # MODIFICADO: #20 → cerrado
  plan/plan-accion.md                               # MODIFICADO: fila 5.3, Métricas de éxito, nota 5.10
```

Sin cambios en `app/`, `routes/`, `database/`.

---

## 5. Decisiones técnicas

- **Spec de cierre, no re-implementación:** el fix vive en `main` desde 4.4; aquí se aporta test + sellado documental. Se eleva a spec (y no a mera anotación) por trazabilidad del ítem de plan y precedente Fase 5: 5.1 creó spec sin cambios de código (Q8 grilling 2026-11-21 adjudica la tensión con spec-conventions "no spec para cambios triviales": gana el precedente de Fase 5).
- **Framework:** PHPUnit (`phpunit/phpunit ^10.1`, Laravel 11); trait `DatabaseTransactions` (sin cambios de schema); AAA comentado; un método = una aserción principal (testing-conventions).
- **Auth API:** `Sanctum::actingAs($landlord, ['*'])`; landlord vía `User::factory()->landlord()` con `unique_id` explícito.
- **Datos:** `Withdraw::create([...])` directo (no existe `WithdrawFactory`); fechas de F2 por asignación directa (`$withdraw->created_at = Carbon::parse(...); $withdraw->save()`) porque `created_at` no es fillable.
- **Grupos:** `@group smoke` en la clase; `@group security` además en F3 (scoping por `user_id`).
- **No asertar `'meassage'`** (typo diferido, OUT): el test aserta status + `data` (shape/conteo), nunca la key de mensaje.
- **Estados del spec:** Borrador → Revisado → Aprobado (la conversación de grilling hace de revisión/decisión) → Implementado con DoD tachado **dentro del mismo PR** (Q8a). Rama única `spec/withdraw-index-request-fase5-impl`; commits separados `spec(agents)` (spec + índice) y `test(withdraw)` (test + conteos).

---

## 6. Catálogo de casos

### 6.1 Api withdraw index (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | index respeta `per_page` | landlord autenticado con 7 `Withdraw` propios (`amount` 10, `charge` 0, `status` 'pending') | `GET /api/v1/landlord/withdraw?per_page=5` | 200; `data` con exactamente 5 items |
| F2 | index filtra por rango de fechas | landlord con 3 `Withdraw` propios con `created_at` 2026-11-01 / 10 / 20 | `GET /api/v1/landlord/withdraw?from_date=2026-11-05&to_date=2026-11-15` | 200; `data` solo con el del 10 (1 item) |
| F3 | index scopea por `user_id` | landlords A y B, cada uno con `Withdraw` propio | `GET /api/v1/landlord/withdraw` autenticado como A | 200; `data` contiene los de A, ninguno de B |

Nota: sin `Request $request` inyectado, F1–F3 devolverían 500 (variable `$request` indefinida) — el catálogo completo es la anti-regresión de Seg-20 (reproducción exacta del bug original: `$request->per_page` sin inyección).

### Tests de caracterización

Ninguno: el typo `'meassage'` no se caracteriza — diferido (OUT, candidato 5.10).

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] F1–F3 implementados y en verde: `php artisan test tests/Feature/Withdraw/WithdrawIndexSmokeTest.php`.
- [ ] Firma `index(Request $request)` intacta en `Api\AcnooWithdrawController` (inspección).
- [ ] Suite completa en verde: 257 casos (254 + F1–F3).
- [ ] `composer analyse` sin nuevas entradas vs baseline; `composer lint` limpio.
- [ ] `seguridad.md` #20 anotado como cerrado (patrón inline de #19).
- [ ] `plan-accion.md`: fila 5.3 referenciando este spec + línea en "Métricas de éxito" + nota del typo en fila 5.10.
- [ ] `.agents/README.md` índice con sección fase5 (3 specs) y conteo 48.
- [ ] `tests/README.md` y `AGENTS.md` con conteo 257.
- [ ] Rama `spec/withdraw-index-request-fase5-impl` + PR review (squash merge, 1 aprobación + CI); sin push a `main`.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `created_at` asignado manualmente podría sobreescribirse (hook `created` del modelo re-save) | Bajo | El hook `booted()` solo fija `invoice_no` al crear; asignar la fecha después del `create()` |
| `per_page` leído vía magic `__get` (`$request->per_page`) y no `input()` | Bajo | Comportamiento vigente; F1 lo fija como contrato observable |
| Conteos 254/257 se desincronizan si se mergean otros tests antes de este PR | Bajo | Recalcular con la salida real de `php artisan test` al cerrar el PR |
| Convención "no spec para cambios triviales" (spec-conventions) aplicada retrospectivamente | Bajo | Adjudicada en Q8 del grilling: gana el precedente Fase 5 (trazabilidad por ítem de plan); documentada en §5 |

**Pendiente de decisión:** ninguno (Q1–Q9 del grilling 2026-11-21).

---

## 9. Estimación

- Test F1–F3 + corrida focal: 0.25 día.
- Cierres documentales (3 documentos + conteos): 0.25 día.
- Verificación (analyse + lint + suite + PR): incluida en cada parte.
- Total: ~0.5 día.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.3
- `.analisis/seguridad.md` → Seg-20 (l.133)
- `.agents/spec-impl/spec-inyectar-request-fase4.md` → absorción 4.4 (§2 IN, §6 F4, notas de implementación)
- `.agents/spec-impl/spec-comand-txt-credenciales-fase5.md` · `spec-demo-user-passwords-fase5.md` → precedente de spec de cierre Fase 5
- `.agents/spec/spec-conventions.md` · `.agents/spec/spec-template.md`
- `.agents/skills/testing-conventions.md` · `.agents/skills/workflow-ramas.md`
- `tests/Feature/Idor/IdorPolicySmokeTest.php` → arrange de `Withdraw` (F15/F16)
- `AGENTS.md` → sección "Gotchas" (no `request()` en controladores)

---

### Notas de implementación

1. **F2 — fechas no fillables:**
   ```php
   $withdraw = Withdraw::create(['user_id' => $landlord->id, 'amount' => 10, 'charge' => 0, 'status' => 'pending']);
   $withdraw->created_at = Carbon::parse('2026-11-10');
   $withdraw->save();
   ```
2. **F1:** `assertJsonCount(5, 'data')` — `paginate(5)` con 7 filas propias.
3. **Auth:** `Sanctum::actingAs($landlord, ['*'])` (rutas bajo `auth:sanctum`, sin middleware de rol — el scoping lo hace el controlador con `auth()->id()`).
4. **Verificación rápida:**
   ```bash
   php artisan test tests/Feature/Withdraw/WithdrawIndexSmokeTest.php
   composer analyse && composer lint && php artisan test
   ```
