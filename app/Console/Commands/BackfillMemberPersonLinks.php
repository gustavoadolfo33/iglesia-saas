<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillMemberPersonLinks extends Command
{
    protected $signature = 'members:backfill-person-links {--dry-run : Reporta inconsistencias sin guardar cambios}';

    protected $description = 'Pobla members.person_id desde persons.member_id y reporta inconsistencias';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $duplicateMemberIds = Person::query()
            ->whereNotNull('member_id')
            ->select('member_id')
            ->groupBy('member_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('member_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $duplicateMemberIds = array_fill_keys($duplicateMemberIds, true);

        $summary = [
            'processed' => 0,
            'linked' => 0,
            'already_linked' => 0,
            'duplicates' => 0,
            'conflicts' => 0,
            'church_mismatches' => 0,
            'missing_members' => 0,
        ];

        Person::query()
            ->whereNotNull('member_id')
            ->orderBy('id')
            ->chunkById(200, function (Collection $persons) use (&$summary, $duplicateMemberIds, $dryRun) {
                foreach ($persons as $person) {
                    $summary['processed']++;

                    $memberId = (int) $person->member_id;

                    if (isset($duplicateMemberIds[$memberId])) {
                        $summary['duplicates']++;
                        $this->warn("Duplicado: varias persons apuntan a member {$memberId}. Person {$person->id} omitida.");
                        continue;
                    }

                    $member = Member::query()->find($memberId);

                    if (!$member) {
                        $summary['missing_members']++;
                        $this->warn("Inconsistencia: person {$person->id} referencia member {$memberId} inexistente.");
                        continue;
                    }

                    if ((int) $member->church_id !== (int) $person->church_id) {
                        $summary['church_mismatches']++;
                        $this->warn("Church mismatch: person {$person->id} y member {$member->id} pertenecen a iglesias distintas.");
                        continue;
                    }

                    if ($member->person_id !== null && (int) $member->person_id !== (int) $person->id) {
                        $summary['conflicts']++;
                        $this->warn("Conflicto: member {$member->id} ya enlazado a person {$member->person_id}; person {$person->id} omitida.");
                        continue;
                    }

                    if ((int) $member->person_id === (int) $person->id) {
                        $summary['already_linked']++;
                        $this->line("Ya enlazado: member {$member->id} -> person {$person->id}.");
                        continue;
                    }

                    if (!$dryRun) {
                        $member->forceFill([
                            'person_id' => $person->id,
                        ])->save();
                    }

                    $summary['linked']++;
                    $this->info(($dryRun ? '[dry-run] ' : '') . "Enlace listo: member {$member->id} -> person {$person->id}.");
                }
            });

        $this->newLine();
        $this->table(
            ['Procesados', 'Enlazados', 'Ya enlazados', 'Duplicados', 'Conflictos', 'Church mismatch', 'Members faltantes'],
            [[
                $summary['processed'],
                $summary['linked'],
                $summary['already_linked'],
                $summary['duplicates'],
                $summary['conflicts'],
                $summary['church_mismatches'],
                $summary['missing_members'],
            ]]
        );

        return self::SUCCESS;
    }
}
