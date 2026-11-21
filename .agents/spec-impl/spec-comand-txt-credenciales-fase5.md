---
id: spec-comand-txt-credenciales-fase5
titulo: "Credenciales de comand.txt: rotación out-of-band, saneo local y cierre documental (Fase 5 / 5.1)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 5 / 5.1) — .analisis/seguridad.md (Seg-19) — .analisis/deuda-tecnica.md (DT-B9)
cobertura: Rotación de los 3 pares de credenciales no-demo del blob histórico de comand.txt (acción out-of-band) + saneo in-place de .analisis/comand.txt + actualización de los 4 documentos de .analisis/ + declaración de riesgo residual aceptado (sin history rewrite)
---

# Spec — Credenciales de `comand.txt`: rotación out-of-band, saneo local y cierre documental (Fase 5 / 5.1)

> Resumen de una línea: cerrar Seg-19/5.1 tratando los 3 pares de credenciales no-demo del blob histórico como comprometidos (rotación inmediata, fuera del repo), dejando el blob declarado **riesgo residual aceptado** (sin history rewrite) y reflejando el cierre en los 4 documentos de `.analisis/` — sin cambiar código de aplicación.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 5 / 5.1) — `.analisis/seguridad.md` (Seg-19) — `.analisis/deuda-tecnica.md` (DT-B9).

---

## 1. Objetivo

Cerrar el ítem 5.1 (Seg-19): las 3 credenciales con pinta de cuenta personal expuestas en el blob histórico de `comand.txt` (repo público) quedan **rotadas con fecha registrada**, el artefacto local `.analisis/comand.txt` queda **sin passwords en claro**, y los 4 documentos de `.analisis/` cuentan el cierre de forma coherente, con el blob de git history declarado **riesgo residual aceptado**.

---

## 2. Alcance

### IN

- **Rotación out-of-band** de los 3 pares no-demo del blob (commit `816a734`, raíz de la historia), tratados como vivos:
  - `erika.cruz@gmail.com`
  - `superadmin@gmail.com`
  - `admin@gmail.com`
  - Confirmación fechada registrada en este spec (§6, F1) — acción sobre los sistemas/instancias externos, no genera commits.
- **Saneo in-place** de `.analisis/comand.txt` (gitignored): cada password → `[ROTATED <fecha>]`; emails y estructura conservados como registro histórico; nota de cabecera que referencia este spec.
- **Actualización de los 4 documentos** de `.analisis/` en el mismo movimiento (detalle en §4):
  - `.analisis/plan/plan-accion.md` — fila 5.1, métrica "`comand.txt` saneado/..." y sección "Ver también".
  - `.analisis/seguridad.md` — Seg-19 (l.131) y "Referencia sensible" (l.149–151).
  - `.analisis/deuda-tecnica.md` — DT-B9 (l.119–120) y "Referencia sensible" (l.143–145).
  - `.analisis/resumen-ejecutivo.md` — l.126.
- **Declaración formal** en `plan-accion.md`: blob = riesgo residual aceptado con justificación; repo público = decisión consciente; los 4 usuarios demo (`*@acnoo.com`) son **públicos por diseño** hasta que aterrice 5.2.

### OUT

- **History rewrite** — excluido (decisión Q2 del grilling 2026-11-20): el blob vive en el commit raíz → rewrite reescribe los 163 commits completos y todas las ramas remote; GitHub seguiría sirviendo los commits colgantes por SHA hasta purge de Support; las credenciales que importan se protegen con rotación, no con ofuscación de historia. Precedente del repo: key FedaPay en history → "rotarla si algún entorno la usó" (4.10).
- **Contraseñas del `UserSeeder`** (usuarios demo `*@acnoo.com`) — es el ítem 5.2, spec propio. Este spec declara explícitamente que siguen públicas (README/AGENTS.md/seeders) hasta 5.2.
- **Cambios de código de aplicación, README.md, AGENTS.md** — la publicación deliberada de las credenciales demo no se toca aquí.
- **Cambio de visibilidad del repo** — `gigioeguia/pren-superadmin` sigue público como decisión consciente (Q6).
- **Rotación de la key FedaPay histórica** — ya cerrada en 4.10 con criterio "rotar si se usó".

### Cobertura objetivo

- 3/3 pares no-demo rotados con confirmación fechada.
- 4/4 documentos de `.analisis/` actualizados, ninguno describe la acción como pendiente.
- 0 passwords en claro en `.analisis/comand.txt`, en este spec y en cualquier archivo commiteado.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Rama de trabajo (no `main`) | `spec/comand-txt-credenciales-fase5-draft` creada | Sin acción |
| P2 | Autoridad/acceso para rotar los 3 pares | Confirmado por usuario (grilling Q9, 2026-11-20) | Ejecutar rotación out-of-band y registrar fecha |
| P3 | `comand.txt` fuera de HEAD | Fuera desde `3976034`; blob solo en history | Sin acción (verificado: `git log --all -- comand.txt` = 2 commits) |
| P4 | Repo público con blob accesible por SHA | `gigioeguia/pren-superadmin` público | Sin acción — exposición se mitiga por rotación (P2), no por rewrite |
| P5 | Convenciones de specs | Presentes (`.agents/spec/spec-conventions.md`) | Sin acción |

---

## 4. Estructura propuesta

```
.agents/spec-impl/spec-comand-txt-credenciales-fase5.md  # este spec (único cambio commiteado)
.analisis/comand.txt                                    # saneo in-place, passwords → [ROTATED <fecha>] (gitignored)
.analisis/plan/plan-accion.md                           # fila 5.1 (l.106) + métrica (l.160) + Ver también (l.174)
.analisis/seguridad.md                                  # Seg-19 (l.131) + Referencia sensible (l.149–151)
.analisis/deuda-tecnica.md                              # DT-B9 (l.119–120) + Referencia sensible (l.143–145)
.analisis/resumen-ejecutivo.md                          # mención de comand.txt (l.126)
```

Nota: los 4 documentos viven en `.analisis/` (gitignored) — su actualización es local, no viaja en el PR. El PR contiene únicamente este spec.

---

## 5. Decisiones técnicas

Fijadas (grilling 2026-11-20), no negociables salvo volver el spec a `Borrador`:

- **Los 3 pares no-demo se tratan como vivos:** están expuestos en un repo público y la rotación es barata; nadie tiene que auditar la "muerte" de un secreto.
- **Sin history rewrite:** blob = riesgo residual aceptado con la justificación de §2 OUT. La mitigación real es la rotación.
- **Emails sí, passwords nunca:** este spec y los documentos referencian cuentas por email; ningún valor de password se reproduce en archivo commiteado ni en los docs de `.analisis/`.
- **La rotación es out-of-band:** no toca el repo; su evidencia es la confirmación fechada registrada en este spec (F1), no un commit.
- **Repo público como decisión consciente**, documentada aquí y en `plan-accion.md`.
- **Los 4 usuarios demo quedan fuera:** públicos por diseño (README, AGENTS.md, `UserSeeder`); su cierre es 5.2.

---

## 6. Catálogo de verificaciones (prefijo `F` — documental/transversal)

Este spec no cubre flujos HTTP; el catálogo adapta `Dado/Cuando/Entonces` a verificaciones documentales, siguiendo el patrón de `spec-reconciliacion-analisis-fase0-3.md`. **Total: 8 verificaciones (F1–F8)**.

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Rotación confirmada | Usuario con acceso a los 3 sistemas/instancias (P2) | Rotar los 3 pares out-of-band | Fecha de confirmación registrada en este spec (§Verificación de rotación), sin valores |
| F2 | Copia local saneada | `.analisis/comand.txt` con passwords en claro | Ejecutar el saneo in-place | 0 passwords en claro; cada uno sustituido por `[ROTATED <fecha>]`; emails y estructura conservados |
| F3 | Fila 5.1 cerrada | `plan-accion.md` fila 5.1 "Parcial" | Actualizar la fila | Estado final: rotación ejecutada + blob riesgo residual aceptado (con justificación) + referencia a este spec |
| F4 | Métrica cerrada | Métrica "`comand.txt` saneado/..." en `⬜` | Actualizar `plan-accion.md` | `✅` con referencia a este spec |
| F5 | Seg-19 coherente | `seguridad.md` mezcla comand.txt (cerrable) y UserSeeder (5.2) | Actualizar Seg-19 (l.131) y Referencia sensible (l.149–151) | comand.txt marcado cerrado; la parte de `UserSeeder` queda explícitamente pendiente bajo 5.2 |
| F6 | DT-B9 coherente | `deuda-tecnica.md` DT-B9 y Referencia sensible describen acción pendiente | Actualizar DT-B9 (l.119–120) y Referencia (l.143–145) | Ambas secciones reflejan el cierre con referencia a este spec |
| F7 | Resumen ejecutivo coherente | `resumen-ejecutivo.md` l.126 describe saneo/rotación pendientes | Actualizar la línea | Refleja cierre con fecha, sin reproducir valores |
| F8 | Cero passwords expuestos | Spec y docs actualizados | `grep` de los 3 valores de password del blob sobre spec + `.analisis/` | 0 coincidencias en cualquier archivo (commiteado o local) |

### Verificación de rotación (registro out-of-band)

| Cuenta | Rotada (fecha) | Confirmado por |
|--------|---------------|----------------|
| `erika.cruz@gmail.com` | 2026-11-20 | usuario (aprobación del spec, sesión 2026-11-20) |
| `superadmin@gmail.com` | 2026-11-20 | usuario (aprobación del spec, sesión 2026-11-20) |
| `admin@gmail.com` | 2026-11-20 | usuario (aprobación del spec, sesión 2026-11-20) |

> F1 cerrado: el usuario confirmó las 3 rotaciones al aprobar el spec (2026-11-20). Las fechas registradas son de confirmación del usuario; el cierre documental del PR se registra el 2026-11-21.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] 3/3 pares no-demo rotados y confirmados con fecha en la tabla de §Verificación de rotación (confirmación del usuario 2026-11-20).
- [x] `.analisis/comand.txt` sin passwords en claro (2026-11-21): 3 pares personales → `[ROTATED 2026-11-20]`; usuarios demo → `[DEMO — público por diseño, cierre en 5.2]` (no marcados ROTATED porque no lo fueron — ver §2 OUT); emails y estructura conservados; nota de cabecera con referencia a este spec.
- [x] Fila 5.1 de `plan-accion.md` en estado final **Cerrado (2026-11-21)** con justificación de riesgo residual aceptado y referencia a este spec.
- [x] Métrica "`comand.txt` saneado/fuera de exposición y secretos rotados" en `✅` en `plan-accion.md` (2026-11-21) con referencia a este spec.
- [x] `seguridad.md` (fila 19–26, Seg-19 y Referencia sensible), `deuda-tecnica.md` (DT-B9 y Referencia sensible) y `resumen-ejecutivo.md` actualizados (2026-11-21) — ninguna sección describe la acción como pendiente; la parte de `UserSeeder` queda explícitamente bajo 5.2.
- [x] F8 en verde (2026-11-21): 0 pares email+password de `comand.txt` en HEAD, working tree y `.analisis/` — emails solo en este spec (sin passwords) y en `comand.txt` saneado; la única coincidencia de `1234567890` es un fixture de teléfono en `tests/Feature/Idor/PropertyOwnershipSmokeTest.php`, no un par credencial.
- [x] PR con este spec como único cambio de repo (`git diff main...HEAD --stat` = 1 archivo, el spec); CI verde: PR #45, run https://github.com/gigioeguia/pren-superadmin/actions/runs/44873695707 (tests, static-analysis, style, frontend-build = success).

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Blob accesible por SHA en GitHub hasta purge de Support | Bajo | Aceptado explícitamente: las creds que importan quedan muertas por rotación; las demo son públicas por diseño |
| Terceros ya clonaron/exfiltraron el blob | Bajo | Ídem: tras la rotación el blob contiene creds muertas + demo públicas |
| Algún par no-demo resulta inaccesible al rotar (sin autoridad) | Medio | Reabriría la decisión "sin rewrite": el rewrite + purge de Support pasaría a última ratio; registrar en este spec si ocurre |
| Docs de `.analisis/` no versionados (gitignored) | Bajo | La trazabilidad vive en este spec (commiteado); los docs se citan con línea exacta |

**Pendiente de decisión:** ninguna — las decisiones Q1–Q13 del grilling (2026-11-20) están cerradas; F1 confirmado cerrado (verificación de rotación, §6).

---

## 9. Estimación

- Rotación out-of-band (3 cuentas, incluye coordinación si aplica): < 0.5 día
- Saneo de `.analisis/comand.txt` + actualización de los 4 docs + PR del spec: < 0.5 día

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 5 / 5.1, métricas y "Ver también"
- `.analisis/seguridad.md` → Seg-19 (credenciales por defecto en control de versiones)
- `.analisis/deuda-tecnica.md` → DT-B9 (`comand.txt` como documentación informal)
- `.agents/spec-impl/spec-reconciliacion-analisis-fase0-3.md` → patrón de spec documental (prefijo `F`)
- `.agents/spec/spec-conventions.md`
- Historia git: blob introducido en `816a734` (commit raíz), fuera de HEAD desde `3976034`
