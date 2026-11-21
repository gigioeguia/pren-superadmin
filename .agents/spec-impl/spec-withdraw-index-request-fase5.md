---
id: spec-withdraw-index-request-fase5
titulo: "Cierre Seg-20: `AcnooWithdrawController::index` ya corregido en 4.4 — test de regresión HTTP y sellado documental (Fase 5 / 5.3)"
estado: Implementado
fecha: 2026-11-21
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.3) — .analisis/seguridad.md (Seg-20) — .agents/spec-impl/spec-inyectar-request-fase4.md (absorción)
cobertura: Cierre del bug Seg-20 (`index` usaba `$request->per_page` sin inyectar `Request` → error en runtime): documenta la absorción por Fase 4 / 4.4 (commit ea07c87), añade la anti-regresión que 4.4 dejó como "Inspección" (test HTTP F1–F3: per_page, rango de fechas, scoping por user_id), sella los documentos que aún listan el hallazgo como vivo (seguridad.md #20, plan-accion 5.3, índice .agents/README.md) y actualiza los conteos de suite 254→262 en README.md, tests/README.md y AGENTS.md
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
- **Conteos de suite**: `README.md` (raíz, l.55 "254 tests"), `tests/README.md` (nota l.10 + snapshot l.104) y `AGENTS.md` (l.15) — 254 → **262** casos (base real 259 + F1–F3; los conteos seguían en 254 por reconciliación pendiente de 5.2 — riesgo §8 materializado). `tests/README.md` además: línea `Withdraw/` en el árbol de estructura y mini-sección "## Withdraw (Fase 5 / 5.3)" con F1–F3 (patrón de las secciones Throttle/Permisos admin).

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

.agents/README.md                                    # MODIFICADO: índice — sección fase5 (3 specs), conteo 45 → 48 (commiteado con este spec)
README.md                                            # MODIFICADO: "suite completa (254 tests)" → 262 (l.55)
tests/README.md                                      # MODIFICADO: conteos l.10/l.104 → 262; línea Withdraw/ en el árbol
                                                     #              + mini-sección "## Withdraw (Fase 5 / 5.3)" con F1–F3
AGENTS.md                                            # MODIFICADO: "suite completa (254 casos)" → 262 (l.15)

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
- **Estados del spec:** Borrador → Revisado → Aprobado (la conversación de grilling hace de revisión/decisión) → Implementado con DoD tachado. **Desviación vs Q8a** ("DoD tachado dentro del mismo PR"): el spec se mergeó temprano vía PR #48 (rama `-impl`, aún en Borrador) desde fuera de la sesión, por lo que la implementación aterriza en la rama de seguimiento `spec/withdraw-index-request-fase5-revision`; commits separados `spec(agents)` (estados + cierre) y `test(withdraw)` (test + conteos en README raíz, tests/README y AGENTS.md).

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

- [x] F1–F3 implementados y en verde: corrida focal 3 passed (8 assertions); suite completa 262 passed (814 assertions).
- [x] Firma `index(Request $request)` intacta en `Api\AcnooWithdrawController` (inspección; en `main` desde `ea07c87`).
- [x] Suite completa en verde: **262 casos** (259 base real + F1–F3; los conteos documentales decían 254 porque los 5 tests del spec 5.2 / PR #47 nunca se reconciliaron — riesgo §8 materializado; conteos fijados con la salida real de `php artisan test`).
- [x] `composer analyse` sin nuevas entradas vs baseline ([OK] 293/293); `composer lint` limpio (passed).
- [x] `seguridad.md` #20 anotado como cerrado (patrón inline de #19; edición local, gitignored).
- [x] `plan-accion.md`: fila 5.3 "Cerrado" + línea en "Métricas de éxito" + nota del typo en fila 5.10 (edición local, gitignored).
- [x] `.agents/README.md` índice con sección fase5 (3 specs) y conteo 48 (PR #48, mergeado).
- [x] `README.md` (raíz), `tests/README.md` y `AGENTS.md` con conteo 262; `tests/README.md` con línea `Withdraw/` en el árbol y mini-sección F1–F3.
- [x] Rama `spec/withdraw-index-request-fase5-revision` (la `-impl` original se mergeó temprano vía PR #48 con el spec en Borrador — desviación documentada en §5) + PR de implementación; sin push a `main` (squash + 1 aprobación + CI al merge).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| `created_at` asignado manualmente podría sobreescribirse (hook `created` del modelo re-save) | Bajo | El hook `booted()` solo fija `invoice_no` al crear; asignar la fecha después del `create()` |
| `per_page` leído vía magic `__get` (`$request->per_page`) y no `input()` | Bajo | Comportamiento vigente; F1 lo fija como contrato observable |
| Conteos se desincronizan si se mergean otros tests antes de este PR | Bajo | **Materializado (2026-11-21):** base real 259 (5 tests del spec 5.2, PR #47, nunca reconciliados) → conteos fijados en 262 con la salida real de `php artisan test` |
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
2. **F1–F3 — rutas de aserción:** el controlador devuelve el paginador completo bajo la key `data`, así que los items viven en `data.data`: F1 usa `assertJsonCount(5, 'data.data')` + `assertJsonPath('data.per_page', 5)` (`paginate(5)` con 7 filas propias); F2/F3 cuentan sobre `'data.data'` y F3 además `assertJsonPath('data.data.0.id', $withdrawA->id)`.
3. **Auth:** `Sanctum::actingAs($landlord, ['*'])` (rutas bajo `auth:sanctum`, sin middleware de rol — el scoping lo hace el controlador con `auth()->id()`).
4. **Verificación rápida:**
   ```bash
   php artisan test tests/Feature/Withdraw/WithdrawIndexSmokeTest.php
   composer analyse && composer lint && php artisan test
   ```
5. **Redacciones documentales (acordadas en grilling 2026-11-21, ronda 2):**
   - `plan-accion.md` fila 5.3 → "**Cerrado (2026-11-21):** fix ya en `main` (absorción 4.4, commit `ea07c87`); spec `spec-withdraw-index-request-fase5` añade test de regresión F1–F3 y sella documentalmente (seguridad.md #20)".
   - `plan-accion.md` Métricas de éxito → línea nueva tras la de `comand.txt`: "✅ Fix Seg-20 (`AcnooWithdrawController::index` con `Request` inyectado) verificado con test de regresión HTTP (Fase 5 / 5.3 — spec-withdraw-index-request-fase5, 2026-11-21; absorción 4.4)". Encabezado "(reconciliadas 2026-11-19)" intacto (precedente 5.1).
   - `plan-accion.md` fila 5.10 → extender la celda "Parcial": "typo de key API `'meassage'` (`Api\AcnooWithdrawController::index`) registrado desde el spec 5.3".
   - `plan-accion.md` encabezado l.7 ("Última reconciliación… HEAD `ae662fa`") y sección "Ver también" **sin cambios** — cierre de ítem, no de fase (Q6).
