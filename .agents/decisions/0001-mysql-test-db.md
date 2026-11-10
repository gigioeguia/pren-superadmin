# ADR-0001 — MySQL dedicada para tests (sobre SQLite in-memory)

- **Estado**: Aceptado
- **Fecha**: 2026-11-10
- **Decisor**: equipo backend
- **Refs**: `.agents/spec-impl/spec-tests-smoke-fase0.md` (Decisión técnica DB)

## Contexto

El spec de tests smoke (Fase 0 / 0.1) necesita una DB de testing aislada de la DB de desarrollo (`dbrentpro` en `.env`). Hay dos opciones razonables:

1. **SQLite in-memory** (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) — rápido, aislado, sin configuración.
2. **MySQL dedicada** (`dbrentpro_test`) — mismo motor que producción.

El repo usa columnas JSON nativas de MySQL en varios modelos (`User->phone`, `Property->address_info`, `Property->rent_info`, `Property->room_info`, etc.) y 105 archivos de traducción JSON. Las queries usan `whereJsonContains` y sintaxis `->` que MySQL soporta pero SQLite puede no soportar de forma equivalente.

## Decisión

**Usar MySQL dedicada `dbrentpro_test`** como DB de testing.

## Consecuencias

### Positivas
- Sin sorpresas de sintaxis JSON entre test y producción.
- `whereJsonContains` y `->` funcionan igual que en prod.
- Detección de bugs específicos de MySQL en tests.
- Misma versión de motor permite reproducir issues reales.

### Negativas
- Requiere crear la DB manualmente: `CREATE DATABASE dbrentpro_test`.
- Más lento que SQLite in-memory (I/O de disco + conexión).
- Necesita MySQL corriendo en el entorno de test (CI incluido).

## Alternativas consideradas

- **SQLite in-memory**: descartada por riesgo de incompatibilidad con columnas JSON y `whereJsonContains`. Aceptable solo si se valida primero que las queries JSON del repo funcionan en SQLite, y se asume el riesgo de divergencia.
- **MySQL del `.env` (`dbrentpro`)**: descartada — pisaría datos de desarrollo, inaceptable.

## Notas de implementación

- `phpunit.xml` debe setear `DB_DATABASE=dbrentpro_test` (no `:memory:`).
- `APP_KEY` fija de test en `phpunit.xml`.
- Si la suite excede ~30s por la lentitud de `RefreshDatabase` + 52 migraciones, migrar a `DatabaseTransactions` en tests que no toquen schema (ver skill `testing-conventions.md`).
- CI futuro debe provisionar `dbrentpro_test` antes de `php artisan test`.
