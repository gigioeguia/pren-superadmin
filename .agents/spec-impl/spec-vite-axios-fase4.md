---
id: spec-vite-axios-fase4
titulo: "Actualizar Vite 3 → 6, axios, laravel-vite-plugin (Fase 4 / 4.5)"
estado: Implementado
fecha: 2026-11-20
origen: .analisis/plan/plan-accion.md (Fase 4 / 4.5) — DT-A5
cobertura: Migración del toolchain frontend a versiones con soporte: Vite 6, laravel-vite-plugin 1.x, axios 1.7, lodash 4.17.21, postcss 8.4. Build (`npm run build`) exitoso y CI `frontend-build` en verde. Sin cablear `@vite` en vistas (OUT).
---

# Spec — Actualizar Vite 3 → 6, axios, laravel-vite-plugin (Fase 4 / 4.5)

> Resumen: actualizar las dependencias frontend obsoletas (Vite 3, laravel-vite-plugin 0.7, axios 1.1, lodash 4.17.19) a versiones con soporte, eliminando las advisory dev-only (vite GHSA-15r9, esbuild GHSA-67mh) y modernizando el build pipeline. Sin cambios de comportamiento en vistas (los assets compilados no se consumen vía `@vite` todavía).

**Origen:** `.analisis/plan/plan-accion.md` (Fase 4 / 4.5) — `DT-A5`. Evidencia 2026-11-10: `npm audit` reporta GHSA-15r9 (vite, high) y GHSA-67mh-4wv8-2f99 (esbuild, moderate), ambas dev-only.

---

## 1. Objetivo

Hacer que `npm run build` funcione con Vite 6 + laravel-vite-plugin 1.x + Node 20, sin advisory high/critical bloqueantes, y que el job `frontend-build` de CI pase. Eliminar `allowScripts`/esbuild 0.15.18 obsoleto.

---

## 2. Alcance

### IN
- `package.json`: versiones objetivo.
- `vite.config.js`: ajustes de compatibilidad con Vite 6 / plugin 1.x (si requiere).
- `resources/js/bootstrap.js`: verificar imports (lodash, axios) compilan; sin cambios funcionales.
- `.github/workflows/ci.yml`: solo si el build requiere cambio de Node (esperado: sin cambios, Node 20 compatible).
- Verificación: `npm ci` + `npm run build` + `npm audit`.

### OUT
- Cablear `@vite(['resources/css/app.css','resources/js/app.js'])` en layouts/vistas: los assets compilados no se consumen actualmente (layouts usan `asset('backend/assets/...')`). Trabajo de frontend aparte.
- Migración a Vite 7 (plan fija Vite 6).
- Eliminar lodash/axios si no se usan (se mantienen; `window._` y `window.axios`).
- Crear `postcss.config.js` (Vite 6 usa defaults).
- Cambios en `resources/css/app.css` (0 bytes, preexistente).
- Actualizar README.md más allá de versiones (Fase 4.5/5).

### Cobertura objetivo
- `npm ci` exitoso en Node 20.
- `npm run build` exitoso (genera `public/build/manifest.json`).
- `npm audit`: 0 high/critical (moderates dev-only aceptables).
- CI `frontend-build` job en verde.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Node 20 en CI | Existe (setup-node v4) | Ninguna |
| P2 | `package.json` con versiones viejas | Vite 3, plugin 0.7, axios 1.1, lodash 4.17.19, postcss 8.1 | Actualizar |
| P3 | `vite.config.js` básico | Existe, compatible con plugin 0.7 | Ajustar a plugin 1.x si hace falta |
| P4 | `resources/js/bootstrap.js` importa lodash/axios | Existe | Verificar compilación |
| P5 | `resources/css/app.css` vacío | 0 bytes | Sin cambios (build emite manifest aunque css vacío) |

---

## 4. Estructura propuesta

```
package.json                  # MODIFICADO: versiones
vite.config.js                # MODIFICADO si requiere (probablemente sin cambios)
resources/js/bootstrap.js     # SIN CAMBIOS (verificar)
resources/js/app.js           # SIN CAMBIOS
.github/workflows/ci.yml      # SIN CAMBIOS (Node 20 ok)
```

Sin cambios en `composer`, `app/`, vistas ni CSS.

---

## 5. Decisiones técnicas

- **Versiones objetivo:**
  - `vite`: `^6.0.0`
  - `laravel-vite-plugin`: `^1.0.0`
  - `axios`: `^1.7.0`
  - `lodash`: `^4.17.21`
  - `postcss`: `^8.4.0`
- **Mantener `allowScripts`** en `package.json`, actualizado a `esbuild@0.25.12` (npm 11 exige aprobar el postinstall; sin ello CI emite warning y puede omitir el binario). AGENTS.md prohíbe removerlo.
- **Node 20** en CI (Vite 6 soporta Node 18+; 20 es estable y ya configurado).
- **vite.config.js**: plugin 1.x mantiene la misma API (`laravel({ input, refresh })`); no se espera cambio. Si el build falla, ajustar `refresh` o `build.outDir`.
- **bootstrap.js**: mantener imports de lodash y axios; Vite 6 maneja ESM/CJS interop igual que Vite 3 para estos paquetes.
- **Sin `@vite` en vistas**: los layouts siguen usando `asset('backend/assets/...')`. El build solo valida el pipeline y genera manifest; no afecta runtime.
- **npm audit**: informativo; no bloquear por moderates dev-only (política del plan). Bloquear solo si aparecen high/critical nuevos.
- **Rama:** `spec/vite-axios-fase4-impl`.

---

## 6. Catálogo de casos

### 6.1 Build (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | `npm ci` en Node 20 | `package.json` actualizado | `npm ci` | Exit 0, sin errores de peer deps |
| F2 | `npm run build` exitoso | Deps instaladas | `npm run build` | Exit 0, genera `public/build/manifest.json` |
| F3 | Manifest contiene app.js y app.css | Build exitoso | Inspección `public/build/manifest.json` | Entradas `resources/js/app.js` y `resources/css/app.css` presentes |
| F4 | `npm run dev` arranca (smoke) | Deps instaladas | `npm run dev` (timeout 10s) | Servidor Vite inicia sin error fatal |
| F5 | `npm audit` sin high/critical | Deps actualizadas | `npm audit --audit-level=high` | 0 high/critical (moderates aceptables) |
| F6 | CI `frontend-build` en verde | Push a rama | GitHub Actions | Job pasa |
| F7 | Vite config válido | `vite.config.js` | `npm run build` | Sin warnings de config deprecada |
| F8 | bootstrap.js compila | Import de lodash/axios | Build | Sin errores de resolución de módulo |
| F9 | `allowScripts` actualizado a `esbuild@0.25.12` | `package.json` | Inspección | Clave presente con la versión instalada de esbuild (evita warning en CI) |
| F10 | Versiones objetivo en `package.json` | Archivo modificado | Inspección | `vite ^6`, `laravel-vite-plugin ^1`, `axios ^1.7`, `lodash ^4.17.21`, `postcss ^8.4` |

### Tests de caracterización

Ninguno: cambio de toolchain frontend, sin comportamiento observable en backend. La suite PHPUnit no se ve afectada (no toca frontend).

---

## 7. Criterios de aceptación (Definition of Done)

- [x] F1: `npm ci` exitoso en Node 20.
- [x] F2: `npm run build` exitoso.
- [x] F3: `public/build/manifest.json` generado con entradas de app.js y app.css.
- [x] F4: `npm run dev` arranca sin error fatal.
- [x] F5: `npm audit --audit-level=high` sin high/critical.
- [x] F6: CI `frontend-build` en verde.
- [x] F7–F8: sin warnings de config ni errores de resolución.
- [x] F9: `allowScripts` actualizado a `esbuild@0.25.12`.
- [x] F10: versiones objetivo presentes.
- [x] `php artisan test` sigue en verde (sin regresión; frontend no afecta backend).
- [x] Rama dedicada y PR.

---

## 8. Riesgos y decisiones pendientes

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Vite 6 breaking en `laravel-vite-plugin` API | Medio | Plugin 1.x mantiene API; probar build antes de commitear |
| lodash CJS/ESM interop en Vite 6 | Bajo | lodash 4.17.21 exporta CJS; Vite 6 lo resuelve igual que Vite 3 |
| esbuild 0.21+ produce output diferente | Bajo | Build smoke + revisión del manifest |
| `npm audit` sigue reportando moderates dev-only | Bajo | Aceptado por política del plan; solo bloquear high/critical |
| CI Node 20 vs Vite 6 | Bajo | Compatible (Node 18+); sin cambio |

**Pendiente de decisión:** ninguno (decisiones fijadas en grilling).

---

## 9. Estimación

- Actualizar `package.json` + `npm ci`: 0.25 día.
- Ajustar `vite.config.js` si hace falta + build smoke: 0.25 día.
- Verificación CI + audit: 0.25 día.
- Total: ~0.75 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 4 / 4.5 (DT-A5)
- `.analisis/deuda-tecnica.md` → DT-A5
- `AGENTS.md` (sección "Tooling", "Gotchas")
- `.github/workflows/ci.yml` (job `frontend-build`)
- `package.json`, `vite.config.js`, `resources/js/bootstrap.js`

---

### Notas de implementación

1. **`package.json`**:
   ```json
   {
     "devDependencies": {
       "axios": "^1.7.0",
       "laravel-vite-plugin": "^1.0.0",
       "lodash": "^4.17.21",
       "postcss": "^8.4.0",
       "vite": "^6.0.0"
     }
   }
   ```
   (eliminar `allowScripts`).
2. **`vite.config.js`**: probar sin cambios; si falla, revisar `refresh` y `build.manifest`.
3. **Verificación**:
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   npm run build
   npm audit --audit-level=high
   npm run dev  # smoke, Ctrl+C tras 10s
   ```
4. **CI**: no cambiar Node 20 salvo que el build lo requiera; documentar si se cambia.
5. **Rama:** crear `spec/vite-axios-fase4-impl` antes de editar.