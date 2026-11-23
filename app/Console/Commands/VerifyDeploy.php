<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Patrón 5.9 (Fase 5 / 5.9, Seg-26): evidencia operacional de los flips
 * del `.env` de producción que el repo no puede commitear. Verifica config
 * EFECTIVO (respeta config:cache — verdad runtime); la salida pegada bajo
 * .analisis/ es la evidencia que cierra los residuales de 5.9/5.5/5.6.
 * Enforcement solo con APP_ENV=production (report-only en el resto);
 * sin flags y sin secretos en la salida (APP_KEY solo set/missing).
 */
class VerifyDeploy extends Command
{
    protected $signature = 'ops:verify-deploy';

    protected $description = 'Verify effective deploy posture (LOG_LEVEL, session, TLS, PHP runtime) — evidence for Fase 5 residuals';

    public function handle(): int
    {
        $enforce = config('app.env') === 'production';

        $this->info(
            'APP_ENV: '.(string) config('app.env')
            .($enforce ? ' — enforcing' : ' — report-only (expectations apply to production)')
        );

        $rows = array_merge($this->hardChecks(), $this->warnChecks());

        $this->table(['check', 'expected', 'effective', 'result'], $rows);

        // Footer siempre (Q12): la salida completa es la evidencia, PASS o FAIL.
        $this->line('Paste this output as evidence under .analisis/ (gitignored).');

        $hasFailures = (bool) array_filter(
            $rows,
            fn (array $row): bool => $row['result'] === 'FAIL',
        );

        return ($enforce && $hasFailures) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Checks HARD (Q10): fallan (exit 1) solo con enforcement activo.
     *
     * @return list<array{check: string, expected: string, effective: string, result: string}>
     */
    private function hardChecks(): array
    {
        $logLevels = $this->defaultChannelLevels();

        return [
            $this->row('app.debug', 'false', config('app.debug') ? 'true' : 'false', ! (bool) config('app.debug')),
            $this->row(
                'log level',
                'warning',
                $logLevels === [] ? '(no level)' : implode(',', array_unique($logLevels)),
                $this->logLevelPasses($logLevels),
            ),
            $this->row('session.driver', 'database', (string) config('session.driver'), config('session.driver') === 'database'),
            $this->row('app.url', 'https://…', (string) config('app.url'), str_starts_with((string) config('app.url'), 'https://')),
            $this->row('app.force_https', 'true', config('app.force_https') ? 'true' : 'false', config('app.force_https') === true),
            $this->row('php version', '>= 8.4.1', PHP_VERSION, self::phpFloorPasses(PHP_VERSION)),
            $this->row('session.serialization', 'json', (string) config('session.serialization'), self::sessionSerializationPasses((string) config('session.serialization'))),
        ];
    }

    /**
     * Checks WARN (Q10): reportan, nunca afectan el exit code.
     *
     * @return list<array{check: string, expected: string, effective: string, result: string}>
     */
    private function warnChecks(): array
    {
        $configCached = is_file(base_path('bootstrap/cache/config.php'));
        $trustedProxies = trim((string) (config('app.trusted_proxies') ?? ''));
        $appKeySet = trim((string) (config('app.key') ?? '')) !== '';

        return [
            $this->warnRow('config cache', 'cached — re-run config:cache after env flips', $configCached ? 'cached' : 'not cached', $configCached),
            $this->warnRow('app.trusted_proxies', 'set only behind a TLS-terminating LB', $trustedProxies === '' ? '(empty)' : 'set', $trustedProxies !== ''),
            $this->warnRow('app.key', 'set', $appKeySet ? 'set' : 'missing', $appKeySet),
        ];
    }

    /**
     * Floor de runtime del spec 5.20 (Q9): helper puro porque PHP_VERSION es
     * verdad del runtime, no config — la rama FAIL no es simulable por
     * config() y se cubre en tests unitarios.
     */
    public static function phpFloorPasses(string $version): bool
    {
        return version_compare($version, '8.4.1', '>=');
    }

    /**
     * Serialización de sesión del spec 5.21 (Q6): helper puro para que la
     * rama FAIL viva en tests unitarios (U22/U23); el consumo real lee
     * config('session.serialization') — config:cache friendly.
     */
    public static function sessionSerializationPasses(?string $value): bool
    {
        return $value === 'json';
    }

    /**
     * Levels de las hojas del canal default: Laravel 11 no tiene level
     * global — el default puede ser stack y hay que perforar sus hojas
     * (así sigue a LOG_CHANNEL=daily sin cambios; canal null → sin levels).
     *
     * @return list<string>
     */
    private function defaultChannelLevels(): array
    {
        $channels = (array) config('logging.channels');
        $default = (string) config('logging.default');

        $defaultChannel = is_array($channels[$default] ?? null) ? $channels[$default] : [];
        $names = ($defaultChannel['driver'] ?? '') === 'stack'
            ? (array) ($defaultChannel['channels'] ?? [])
            : [$default];

        $levels = [];

        foreach ($names as $name) {
            $channel = is_array($channels[$name] ?? null) ? $channels[$name] : [];
            $level = $channel['level'] ?? null;

            if (is_string($level) && $level !== '') {
                $levels[] = $level;
            }
        }

        return $levels;
    }

    /**
     * Canal null (sin levels) → PASS trivial (decisión sellada, ronda 3).
     *
     * @param  list<string>  $levels
     */
    private function logLevelPasses(array $levels): bool
    {
        if ($levels === []) {
            return true;
        }

        foreach ($levels as $level) {
            if ($level !== 'warning') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{check: string, expected: string, effective: string, result: string}
     */
    private function row(string $check, string $expected, string $effective, bool $passes): array
    {
        return [
            'check' => $check,
            'expected' => $expected,
            'effective' => $effective,
            'result' => $passes ? 'PASS' : 'FAIL',
        ];
    }

    /**
     * @return array{check: string, expected: string, effective: string, result: string}
     */
    private function warnRow(string $check, string $expected, string $effective, bool $ok): array
    {
        return [
            'check' => $check,
            'expected' => $expected,
            'effective' => $effective,
            'result' => $ok ? 'PASS' : 'WARN',
        ];
    }
}
