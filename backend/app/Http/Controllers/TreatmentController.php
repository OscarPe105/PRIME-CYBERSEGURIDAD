<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class TreatmentController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function propose(Request $request, int $id): array
    {
        $validated = $request->validate(['treatment' => 'required|string|max:4000']);
        DB::transaction(function () use ($id, $validated) {
            $finding = DB::table('findings')->where('id', $id)->lockForUpdate()->first();
            abort_unless($finding, 404);
            abort_unless($finding->status === 'abierto', 409, 'El hallazgo ya tiene tratamiento propuesto.');
            DB::table('findings')->where('id', $id)->update(['status' => 'propuesto', 'treatment' => $validated['treatment'], 'proposed_by' => Auth::id(), 'updated_at' => now()]);
            $this->audit->record('treatment_proposed', ['finding' => $id]);
        });

        return ['status' => 'propuesto'];
    }

    public function review(Request $request, int $id): array
    {
        $validated = $request->validate(['note' => 'required|string|min:10|max:4000']);
        DB::transaction(function () use ($id, $validated) {
            $finding = DB::table('findings')->where('id', $id)->lockForUpdate()->first();
            abort_unless($finding, 404);
            abort_unless($finding->status === 'propuesto', 409, 'Primero proponga tratamiento.');
            abort_if($finding->proposed_by === Auth::id(), 403, 'La aprobación requiere otra persona.');
            abort_unless(DB::table('evidence_files')->where('finding_id', $id)->exists(), 422, 'Adjunte evidencia antes de cerrar.');
            DB::table('findings')->where('id', $id)->update(['status' => 'mitigado', 'reviewed_by' => Auth::id(), 'updated_at' => now()]);
            $this->audit->record('treatment_reviewed', ['finding' => $id, 'note' => $validated['note']]);
        });

        return ['status' => 'mitigado', 'message' => 'Cierre administrativo registrado. La eficacia del control debe sustentarse en la evidencia revisada.'];
    }
}
