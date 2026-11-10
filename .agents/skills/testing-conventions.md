---
id: testing-conventions
name: "Testing Conventions"
description: "Convenciones de estructura, nomenclatura y selección de traits para tests PHPUnit del repo."
when_to_use: "Al escribir o modificar tests en tests/Feature/ o tests/Unit/."
---

# Testing Conventions

## Nomenclatura

- **Clase**: `<Dominio><Tipo>Test.php` en `tests/Feature/<Dominio>/` (ej. `Auth/WebLoginSmokeTest.php`).
- **Método**: `test_<escenario>_<resultado>` en snake_case (ej. `test_landlord_login_returns_200`, `test_tenant_login_is_blocked`).
  - Alternativa aceptada: `it_<does_x>` (BDD style) — elegir **una** y mantener consistente en el archivo.
- **@group**: usar para aislar suites:
  - `@group smoke` — smoke tests.
  - `@group characterization` — ver skill `tests-caracterizacion`.
  - `@group security` — tests de autorización/IDOR.

## Estructura AAA

Cada test se organiza en tres bloques comentados:

```php
public function test_landlord_login_returns_200(): void
{
    // Arrange
    $landlord = User::factory()->landlord()->create();

    // Act
    $response = $this->postJson('/login/store', [
        'email' => $landlord->email,
        'password' => 'password',
    ]);

    // Assert
    $response->assertOk()
        ->assertJson(['redirect' => route('landlord.dashboard')]);
}
```

## Traits de DB

| Trait | Cuándo | Notas |
|-------|--------|-------|
| `RefreshDatabase` | Test que **crea/modifica schema** (migraciones, columnas nuevas) o necesita estado limpio total. | migrate:fresh por run. Lento con 52 migraciones. |
| `DatabaseTransactions` | Test que solo inserta/actualiza filas (no schema) y corre en paralelo seguro. | Más rápido; rollback al final. |
| `DatabaseMigrations` | Solo si necesitas controlar el momento exacto de migrar. | Raro en smoke tests. |

**Regla**: si el test no crea columnas, usar `DatabaseTransactions`. Si la suite > 30s con `RefreshDatabase`, migrar a transacciones.

## Factories

- **Siempre setear `unique_id` explícito** en `UserFactory` (el `User::boot()` usa `User::max('id')+1`, frágil en DB recién migrada).
- **Estados por rol**: `landlord()`, `admin()`, `tenant()`, `superadmin()` — cada uno con `role` y `is_setupped=1` apropiados.
- **No depender de IDs fijos** (ej. `user_id => 3`) como hacen los seeders existentes (DT-M9). Usar `create()->id`.

## Mocks / fakes / stubs

| Qué | Cómo |
|-----|------|
| Mail | `Mail::fake()` + `Mail::assertSent(WelcomeMail::class)` |
| Artisan | `Artisan::shouldReceive('call')` — **jamás** correr migraciones/seed reales en tests |
| Queue | `$this->withoutNotifications()` o `Queue::fake()` |
| HTTP externo (gateways) | Mock la clase `App\Library\*` o usar `Http::fake()` |
| Eventos | `Event::fake()` si el test no depende del side effect del listener |

## Auth en tests

- **Web**: `$this->actingAs($user)` (sesión).
- **API**: `Sanctum::actingAs($user, ['*'])` + header `Authorization: Bearer <token>` si aplica.

## Cobertura

- **Mínimo**: smoke tests cubren ≥ 60 % de endpoints críticos (ver spec `spec-tests-smoke-fase0.md`).
- **Orden de implementación**: L → S → R → A → P → Pub → G → H → M (orden del catálogo del spec).
- **Correr por dominio**: `php artisan test tests/Feature/Auth/WebLoginSmokeTest.php`.
- **Correr por grupo**: `php artisan test --group=characterization`.

## DB de testing

- MySQL dedicada `dbrentpro_test` (decisión ADR-0001). **Nunca** la DB del `.env` (`dbrentpro`).
- `phpunit.xml` setea `DB_DATABASE=dbrentpro_test` + `APP_KEY` fija de test.

## Evitar

- Tests que toquen la DB real del `.env`.
- Confiar en `User::max('id')+1` para `unique_id`.
- Mezclar `RefreshDatabase` y `DatabaseTransactions` en la misma clase.
- Tests sin AAA (difíciles de leer).
- Asserts sobre más de un comportamiento por test (un test = una aserción principal).
- Hardcodear IDs/emails que dependen de seeders con datos fijos.

## Relacionados

- `implementar-spec.md` — proceso que aplica estas convenciones.
- `tests-caracterizacion.md` — marcado `@group characterization` específico.
- `secure-coding.md` — tests de seguridad (`@group security`).
- `.agents/decisions/0001-mysql-test-db.md` — decisión de MySQL sobre SQLite.
