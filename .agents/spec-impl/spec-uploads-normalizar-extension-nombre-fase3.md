---
id: spec-uploads-normalizar-extension-nombre-fase3
titulo: "Uploads: normalizar extensión y nombre (Fase 3 / 3.3)"
estado: Implementado
fecha: 2026-11-19
origen: .analisis/plan/plan-accion.md (Fase 3 / 3.3) — .analisis/seguridad.md (Seg-11) — .analisis/deuda-tecnica.md (DT-M1)
cobertura: 1 trait (4 métodos) + 3 uploads directos + auditoría mimes/max en ~32 controladores + 2 casos de caracterización
---

# Spec — Uploads: normalizar extensión y nombre (Fase 3 / 3.3)

> Resumen: generar siempre el nombre de archivo en `HasUploader`, normalizar la extensión (lowercase, sin caracteres no alfanuméricos) y garantizar validación `mimes` + `max` en todo endpoint que acepta archivos. Elimina `uploadWithFileName()` y corrige uploads directos fuera del trait.

**Origen:** `.analisis/plan/plan-accion.md` (Fase 3 / 3.3) — `Seg-11` — `DT-M1`.

---

## 1. Objetivo

Eliminar la dependencia del nombre/extensión original del cliente en todos los uploads y asegurar que cada endpoint que acepta archivos valide `mimes` y `max`, cerrando el vector de subida de archivos peligrosos (`.phtml`, `.php5`, nombres con traversal) y de colisión/sobreescritura.

---

## 2. Alcance

### IN

- `app/Helpers/HasUploader.php`: los 4 métodos (`upload`, `multipleUpload`, `multipleUploadArray`, y eliminación de `uploadWithFileName`) generan nombre propio, normalizan extensión, unifican directorio y garantizan unicidad.
- `app/Http/Controllers/Admin/AcnooGatewayController.php`: añadir `use HasUploader` (bug latente).
- `app/Http/Controllers/Admin/SystemSettingController.php`: migrar `->move()` a `Storage`.
- Limpieza de old files unificada a `deleteOldFile()` en 5 controladores de perfil/tenant (Admin/Landlord/Api).
- Auditoría y adición de `mimes` + `max` en todos los campos de upload que hoy carecen de ellos (ver tabla §6.3).
- Exclusión de `svg` de las allowlists de imágenes en los controladores cuya regla se reescribe por este spec.
- 2 casos de caracterización de normalización (F1, F2).

### OUT

- Sanitización de contenido SVG (se excluye el formato, no se sanitiza).
- Validación por magic bytes más allá de `mimes:` (Laravel ya valida MIME).
- Migración de archivos ya almacenados en disco.
- Refactor de `HasUploader` a clase inyectable (Fase 4 / 4.4).
- Subidas vía Summernote/JS genérico (fuera de controladores).
- Cambio del disco por defecto (`FILESYSTEM_DISK`).

### Cobertura objetivo

- 4/4 métodos de `HasUploader` generan nombre propio y normalizan extensión.
- 0 referencias a `uploadWithFileName` en `app/`.
- 100 % de los campos listados en §6.3 con `mimes:` + `max:`.
- F1 y F2 en verde.
- `composer lint` y `composer analyse` sin nuevas violaciones.

---

## 3. Pre-requisitos

| # | Pre-requisito | Estado actual | Acción necesaria |
|---|---------------|---------------|------------------|
| P1 | Trait `HasUploader` existe y es usado por 32 controladores | Existe | Sin acción |
| P2 | Tests smoke con `Storage::fake()` disponible | Existe (`PaymentFlowSmokeTest`) | Sin acción |
| P3 | `AcnooGatewayController` invoca `$this->upload` sin trait | Bug latente | Añadir `use HasUploader` |
| P4 | `phpstan-baseline.neon` incluye el error de `AcnooGatewayController` | Congelado | Regenerar baseline tras el fix (justificado en PR) |
| P5 | Callers de `service-account-credentials.json` | Verificado: solo `SystemSettingController` | Sin acción |
| P6 | Inventario de uploads y validaciones | Completado (exploración) | Sin acción |

---

## 4. Estructura propuesta

```
app/Helpers/HasUploader.php                                    # MODIFICADO: normalizeExtension, nombre, directorio, unicidad
app/Http/Controllers/Admin/AcnooGatewayController.php           # MODIFICADO: use HasUploader
app/Http/Controllers/Admin/SystemSettingController.php          # MODIFICADO: Storage::putFileAs
app/Http/Controllers/Admin/AcnooProfileController.php           # MODIFICADO: deleteOldFile
app/Http/Controllers/Landlord/AcnooProfileController.php        # MODIFICADO: deleteOldFile
app/Http/Controllers/Api/UserController.php                     # MODIFICADO: deleteOldFile
app/Http/Controllers/Admin/AcnooTenantController.php            # MODIFICADO: deleteOldFile
app/Http/Controllers/Landlord/AcnooTenantController.php         # MODIFICADO: deleteOldFile
app/Http/Controllers/Api/AcnooTenantController.php              # MODIFICADO: deleteOldFile
app/Http/Controllers/Admin/*.php                                # MODIFICADO: validaciones mimes/max (ver §6.3)
app/Http/Controllers/Landlord/*.php                             # MODIFICADO: validaciones mimes/max (ver §6.3)
app/Http/Controllers/Api/*.php                                  # MODIFICADO: validaciones mimes/max (ver §6.3)
app/Http/Controllers/PaymentController.php                      # MODIFICADO: reordenar regla attachment
tests/Feature/Admin/AcnooActionsSmokeTest.php                   # MODIFICADO: +F1, F2
```

---

## 5. Decisiones técnicas

- **Framework:** Laravel 10 `Storage`, `UploadedFile`.
- **Extensión normalizada:** `strtolower(preg_replace('/[^a-z0-9]/', '', $file->getClientOriginalExtension()))`. Si resulta vacía, usar `bin` (fallback defensivo).
- **Nombre generado:** `{YmdHis}_{uniqid}.{ext}` en los 4 métodos.
- **Directorio unificado:** `uploads/{y/m}` (sin `rand`).
- **Validación en controladores:** reglas `mimes:` + `max:` (KB) por campo; el trait no valida.
- **Allowlists por tipo:**
  - Imágenes: `jpeg,png,jpg,gif` (se elimina `svg` en controladores tocados).
  - Documentos: `pdf,doc,docx,xls,xlsx,csv,txt`.
  - Soporte/archivos genéricos: `jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt,zip`.
  - Credenciales: `json,txt` (sin cambio).
- **Limpieza:** `deleteOldFile()` borra solo el archivo, no el directorio.
- **SystemSetting:** `Storage::disk(...)->putFileAs('uploads', $file, 'service-account-credentials.json')`, manteniendo nombre fijo por compatibilidad con lectura externa del archivo.
- **Tests:** `Storage::fake(config('filesystems.default'))` + `UploadedFile::fake()->image(...)` / `create(...)`.

---

## 6. Catálogo de casos

### 6.1 Normalización en `HasUploader` (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F1 | Nombre generado no conserva original | Disco fake, `UploadedFile::fake()->image('evil.php.jpg')` | `POST` a endpoint de store Property (Admin) con `cover_image` | respuesta 200/302; path devuelto no contiene `evil.php.jpg` y termina en `.jpg` lowercase |
| F2 | Extensión peligrosa rechazada | Disco fake | `POST` store Property con archivo `.php` | 422, error en `cover_image`/`images` |

### 6.2 Uploads directos (`F`)

| ID | Escenario | Dado | Cuando | Entonces |
|----|-----------|------|--------|----------|
| F3 | Gateway image upload no falla | Trait añadido | `POST` admin gateway store con `image` | 200/302, sin error 500 por método indefinido |
| F4 | SystemSetting credentials vía Storage | Disco fake | `POST` admin system setting con `service_account_credentials` (json) | archivo existe en disco en `uploads/service-account-credentials.json` |
| F5 | Profile image replacement borra solo archivo | Old image en disco | `POST` profile update con nueva imagen | old file eliminado; directorio padre permanece |

### 6.3 Auditoría de validaciones `mimes` + `max` (resumen por grupo)

| Grupo | Controladores / campos | Regla actual | Regla propuesta |
|-------|------------------------|--------------|-----------------|
| Imágenes con `svg` | Blog, Highlight, Feature, Client, Language, Plan, Profile (create), Tenant (create), Landlord (create) | `mimes:...,svg` (algunos sin `max`) | quitar `svg`, añadir `max` donde falte |
| Web settings | `AcnooWebSettingController` (16 campos de imagen) | sin validación | `nullable\|image\|mimes:jpeg,png,jpg,gif\|max:2048` por campo |
| Settings | `SettingController` (`logo`, `favicon`, `login_page_img`) | `logo: nullable\|image`; otros sin validación | `nullable\|image\|mimes:jpeg,png,jpg,gif\|max:2048` |
| Soporte (3 capas) | `AcnooSupportController::file` | `nullable\|file` | `nullable\|file\|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt,zip\|max:10240` |
| Refunds | `AcnooRefundRequestController::attachment` | `required\|file` | `required\|file\|mimes:... (igual soporte)\|max:5120` |
| Maintenance | `AcnooTenantMaintenanceController::images` | `nullable\|array` | `nullable\|array` + cada elemento `file\|mimes:jpeg,png,jpg,gif\|max:2048` |
| Aplicaciones/documentos | `AcnooTenantApplicationController::document_file`, `landlord_agreement`, `tenant_agreement`; `AcnooRentController`, `AcnooRentInvitationController`, `AcnooApplicationController`, `AcnooLandlordApplicationController` | `nullable` o sin validación | `nullable\|file\|mimes:pdf,doc,docx,jpg,jpeg,png\|max:5120` |
| Depósitos | `AcnooSecurityDepositController::payment_receipt` | `required\|file` | `required\|file\|mimes:jpeg,png,jpg,pdf\|max:5120` |
| Plan | `PlanController::image` | `mimes:jpeg,png,jpg,svg` (sin max) | `nullable\|image\|mimes:jpeg,png,jpg\|max:2048` |

### Tests de caracterización

No se caracterizan bugs conocidos en este spec; F1–F5 verifican el comportamiento remedado.

---

## 7. Criterios de aceptación (Definition of Done)

- [x] `HasUploader`: los 4 métodos generan nombre `{YmdHis}_{uniqid}.{ext}` con extensión normalizada y directorio `uploads/{y/m}`.
- [x] `uploadWithFileName()` eliminado y sin referencias en `app/`.
- [x] `normalizeExtension()` aplicado en los 4 métodos.
- [x] `AcnooGatewayController` usa `HasUploader`.
- [x] `SystemSettingController` usa `Storage::putFileAs` (sin `->move()`).
- [x] Los 5 controladores de perfil/tenant usan `deleteOldFile()` en lugar de `Storage::deleteDirectory`.
- [x] Todos los campos de §6.3 tienen `mimes:` + `max:`.
- [x] `svg` eliminado de allowlists en los controladores cuya regla se reescribe.
- [x] F1 y F2 (y F3–F5 si se implementan) en verde.
- [x] `phpstan-baseline.neon` actualizado (error de Gateway removido) con justificación en el PR.
- [x] `composer lint` y `composer analyse` sin nuevas violaciones.
- [x] `php artisan test tests/Feature/Admin/AcnooActionsSmokeTest.php` en verde.

---

## 8. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Churn masivo de validaciones rompe tests que suben archivos sin mimes válidos | Medio | Revisar tests existentes que usen `UploadedFile::fake()` y ajustar |
| Eliminar `svg` rompe logos existentes subidos como svg | Bajo | Solo en controladores tocados; archivos existentes no se revalidan |
| Unicidad con `uniqid` insuficiente bajo concurrencia extrema | Bajo | Suficiente para volumen actual; `uniqid(more_entropy: true)` si se requiere |
| `SystemSetting` nombre fijo sobrescribe ante subidas simultáneas | Bajo | Aceptable para configuración global |
| `deleteOldFile` deja directorios vacíos | Bajo | Inocuo; limpieza de directorios fuera de alcance |

---

## 9. Estimación

- `HasUploader` refactor: 0.5 día.
- Controladores directos (Gateway, SystemSetting, perfiles/tenants): 0.5 día.
- Auditoría `mimes`/`max` en ~32 controladores: 1 día.
- Tests (F1–F5): 0.5 día.
- Total: ~2.5 días.

---

## 10. Referencias

- `.analisis/plan/plan-accion.md` → Fase 3 / 3.3
- `.analisis/seguridad.md` → Seg-11
- `.analisis/deuda-tecnica.md` → DT-M1
- `.agents/spec/spec-conventions.md`
- `.agents/skills/testing-conventions.md`
- `app/Helpers/HasUploader.php`
- `tests/Feature/Payment/PaymentFlowSmokeTest.php` (patrón `Storage::fake`)
---