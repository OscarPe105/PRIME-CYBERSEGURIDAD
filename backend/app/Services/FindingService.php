<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class FindingService
{
    public function __construct(private readonly PrimeEngine $engine) {}

    public function all(): array
    {
        $result = [];
        foreach (DB::table('findings')->orderBy('id')->limit(1000)->get() as $finding) {
            $latest = DB::table('triage_revisions')->where('finding_id', $finding->id)->latest('id')->first();
            $snapshot = json_decode($latest->snapshot, true);
            unset($snapshot['epss']['raw'], $snapshot['kev']['raw']);

            $result[] = [
                ...get_object_vars($finding),
                'input' => json_decode($finding->input, true),
                'calculation' => json_decode($latest->calculation, true),
                'cti' => $snapshot,
                'revision_id' => $latest->id,
            ];
        }
        usort($result, $this->comparePriority(...));

        return $result;
    }

    public function revise(int $id, array $input, array $cti): void
    {
        $calculation = $this->engine->calculate($input, $cti);
        DB::table('triage_revisions')->insert([
            'finding_id' => $id,
            'policy_version' => PrimeEngine::VERSION,
            'snapshot' => json_encode($cti),
            'calculation' => json_encode($calculation),
            'score' => $calculation['score'],
            'priority' => $calculation['priority'],
            'provisional' => $calculation['provisional'],
            'created_at' => now(),
        ]);
    }

    private function comparePriority(array $left, array $right): int
    {
        return ($right['calculation']['kev_urgent'] <=> $left['calculation']['kev_urgent'])
            ?: ($right['calculation']['score'] <=> $left['calculation']['score'])
            ?: ($left['id'] <=> $right['id']);
    }
}
