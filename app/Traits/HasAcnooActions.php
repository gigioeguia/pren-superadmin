<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Implementación única de acnooFilter/deleteAll/status/exportExcel/exportCsv
 * (DT-C3, spec-trait-acnoo-fase0). Los 5 métodos públicos conservan el
 * comportamiento observable exacto de los métodos duplicados que reemplazan;
 * cada controlador configura solo los hooks que sus rutas usan.
 */
trait HasAcnooActions
{
    public function acnooFilter(Request $request)
    {
        $rows = $this->acnooQuery($request)->paginate($request->per_page ?? $this->acnooPerPage());

        if ($request->ajax()) {
            return response()->json([
                'data' => view($this->acnooView(), $this->acnooViewData($rows))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    // NOTA DE SEGURIDAD — Seg-5 (hallazgo 5 de .analisis/seguridad.md): lookup
    // SIN ownership scoping, preservado 1:1 de las copias originales por
    // contrato del spec-trait-acnoo-fase0 (§5 preservación estricta; el fix
    // está en OUT → Fase 2, plan-accion 2.1, con Policies). El fix se aplicará
    // aquí una sola vez; el test H3 (@group characterization) está diseñado
    // para romper cuando llegue. No añadir scoping sin diseñar la separación
    // Admin (sin scope) / Landlord (scoped) — un cambio global rompería admin.
    public function status(Request $request, string $id)
    {
        $this->acnooModel()::findOrFail($id)->update(['status' => $request->status]);

        $payload = ['message' => $this->acnooStatusMessage()];

        if ($route = $this->acnooStatusRedirectRoute()) {
            $payload['redirect'] = route($route);
        }

        return response()->json($payload);
    }

    // NOTA DE SEGURIDAD — Seg-5: whereIn SIN ownership scoping, ídem status()
    // arriba (preexistente en las 33+19 copias; fix en Fase 2, plan-accion 2.1;
    // caracterizado por H3).
    public function deleteAll(Request $request)
    {
        if ($this->acnooDeletesRowByRow()) {
            $this->acnooModel()::whereIn('id', $request->ids)->get()
                ->each(function (Model $row) {
                    $this->deletingAcnooRow($row);

                    $row->delete();
                });
        } else {
            $this->acnooModel()::whereIn('id', $request->ids)->delete();
        }

        return response()->json([
            'message' => $this->acnooDeleteMessage(),
            'redirect' => route($this->acnooRedirectRoute()),
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download($this->acnooExport($request), $this->acnooExcelFilename().'.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download($this->acnooExport($request), $this->acnooCsvFilename().'.csv');
    }

    // Hooks obligatorios para todo adoptante.

    /**
     * @return class-string<Model>
     */
    abstract protected function acnooModel(): string;

    abstract protected function acnooQuery(Request $request): Builder;

    abstract protected function acnooView(): string;

    abstract protected function acnooViewData(LengthAwarePaginator $rows): array;

    // Hooks con default real (el valor común de la mayoría de adoptantes).

    protected function acnooPerPage(): int
    {
        return 10;
    }

    protected function acnooStatusRedirectRoute(): ?string
    {
        return null;
    }

    protected function acnooDeletesRowByRow(): bool
    {
        return false;
    }

    protected function deletingAcnooRow(Model $row): void
    {
        //
    }

    // Hooks requeridos solo si la ruta correspondiente existe en el controlador.

    protected function acnooRedirectRoute(): string
    {
        throw new \LogicException(static::class.' debe definir acnooRedirectRoute() si usa deleteAll().');
    }

    protected function acnooStatusMessage(): string
    {
        throw new \LogicException(static::class.' debe definir acnooStatusMessage() si usa status().');
    }

    protected function acnooDeleteMessage(): string
    {
        throw new \LogicException(static::class.' debe definir acnooDeleteMessage() si usa deleteAll().');
    }

    protected function acnooExport(Request $request): object
    {
        throw new \LogicException(static::class.' debe definir acnooExport() si usa exportExcel()/exportCsv().');
    }

    protected function acnooExcelFilename(): string
    {
        throw new \LogicException(static::class.' debe definir acnooExcelFilename() si usa exportExcel().');
    }

    protected function acnooCsvFilename(): string
    {
        throw new \LogicException(static::class.' debe definir acnooCsvFilename() si usa exportCsv().');
    }
}
