---
id: spec-template
titulo: "Plantilla de Spec"
estado: Borrador
fecha: 2026-11-10
origen: N/A (plantilla)
cobertura: Plantilla reutilizable
---

# Spec — <Título>

> Resumen de una línea: qué se especifica y por qué.

**Origen:** `.analisis/plan/plan-accion.md` (Fase X / X.X) — referenciar también `Seg-N` / `DT-N` si aplica.

---

## 1. Objetivo

Qué se busca lograr con este spec. Una frase. Ejemplo: "Caracterizar el comportamiento actual de los flujos críticos para que los fixes de Fase 1 rompan tests de forma detectable."

---

## 2. Alcance

### IN
- <lista de flujos/ endpoints / comportamientos cubiertos>

### OUT
- <lo que explícitamente no cubre este spec y por qué>

### Cobertura objetivo
- <porcentaje o conteo, ej. "≥ 60 % de los endpoints críticos listados">

---

## 3. Pre-requisitos

Cosas que deben existir antes de poder implementar el spec.

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | <nombre> | <existe / falta / parcial> | <acción> |

---

## 4. Estructura propuesta

```
<árbol de archivos o módulos involucrados>
```

Traits/helpers compartidos si aplica.

---

## 5. Decisiones técnicas

Fijadas, no negociables salvo volver el spec a `Borrador`:

- **Framework:** <ej. PHPUnit 10>
- **DB:** <ej. MySQL dbrentpro_test, RefreshDatabase>
- **Auth en tests:** <actingAs / Sanctum::actingAs>
- **Mocks obligatorios:** <lista de facades/clases a mockear>

---

## 6. Catálogo de casos

### 6.1 <Dominio 1> (`<Prefijo>`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| X1 | <nombre> | <precondición> | <acción HTTP> | <aserción> |
| X2 | <nombre> | <precondición> | <acción HTTP> | <aserción> |

> **Caracterización de Seg-N** — se espera que falle tras Fase X.

### 6.2 <Dominio 2> (`<Prefijo>`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| Y1 | ... | ... | ... | ... |

### Tests de caracterización

Lista consolidada de los casos que documentan comportamiento actual (inseguro/incorrecto) y que se espera rompan en fases posteriores:

| ID | Bug caracterizado | Fase que lo romperá |
|----|-------------------|---------------------|
| X1 | Seg-N | Fase X |

---

## 7. Criterios de aceptación (Definition of Done)

- [ ] <condición objetiva y medible>
- [ ] <condición>
- [ ] tiempo de ejecución total < N s

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| <riesgio> | <alto/medio/bajo> | <acción> |

**Pendiente de decisión:**
- <decisión abierta>

---

## 9. Estimación

- <fase / parte>: <rango de días>

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase X / X.X
- `.analisis/seguridad.md` → Seg-N
- `.analisis/deuda-tecnica.md` → DT-N
- `.agents/spec/spec-conventions.md`
