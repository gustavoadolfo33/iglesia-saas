<?php

namespace App\Console\Commands;

use App\Models\Household;
use App\Models\Member;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillPersonHouseholdLinks extends Command
{
    protected $signature = 'households:backfill-person-links {--dry-run : Reporta inconsistencias sin guardar cambios}';

    protected $description = 'Pobla persons.household_id desde members.household_id usando members.person_id';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $summary = [
            'processed' => 0,
            'linked' => 0,
            'already_linked' => 0,
            'missing_person_link' => 0,
            'missing_person' => 0,
            'missing_household' => 0,
            'church_mismatches' => 0,
            'person_conflicts' => 0,
        ];

        Member::query()
            ->whereNotNull('household_id')
            ->orderBy('id')
            ->chunkById(200, function (Collection $members) use (&$summary, $dryRun) {
                foreach ($members as $member) {
                    $summary['processed']++;

                    if (!$member->person_id) {
                        $summary['missing_person_link']++;
                        $this->warn("Inconsistencia: member {$member->id} tiene household_id pero no person_id.");
                        continue;
                    }

                    $person = Person::query()->find($member->person_id);

                    if (!$person) {
                        $summary['missing_person']++;
                        $this->warn("Inconsistencia: member {$member->id} apunta a person {$member->person_id} inexistente.");
                        continue;
                    }

                    $household = Household::query()->find($member->household_id);

                    if (!$household) {
                        $summary['missing_household']++;
                        $this->warn("Inconsistencia: member {$member->id} apunta a household {$member->household_id} inexistente.");
                        continue;
                    }

                    if (
                        (int) $member->church_id !== (int) $person->church_id
                        || (int) $member->church_id !== (int) $household->church_id
                    ) {
                        $summary['church_mismatches']++;
                        $this->warn("Church mismatch: member {$member->id}, person {$person->id}, household {$household->id} no coinciden en church_id.");
                        continue;
                    }

                    if ($person->household_id !== null && (int) $person->household_id !== (int) $member->household_id) {
                        $summary['person_conflicts']++;
                        $this->warn("Conflicto: person {$person->id} ya tiene household {$person->household_id} distinto al household {$member->household_id} de member {$member->id}.");
                        continue;
                    }

                    if ((int) $person->household_id === (int) $member->household_id) {
                        $summary['already_linked']++;
                        $this->line("Ya enlazado: person {$person->id} -> household {$member->household_id}.");
                        continue;
                    }

                    if (!$dryRun) {
                        $person->forceFill([
                            'household_id' => $member->household_id,
                        ])->save();
                    }

                    $summary['linked']++;
                    $this->info(($dryRun ? '[dry-run] ' : '') . "Enlace listo: person {$person->id} -> household {$member->household_id}.");
                }
            });

        $this->newLine();
        $this->table(
            ['Procesados', 'Enlazados', 'Ya enlazados', 'Sin person_id', 'Person faltante', 'Household faltante', 'Church mismatch', 'Conflictos'],
            [[
                $summary['processed'],
                $summary['linked'],
                $summary['already_linked'],
                $summary['missing_person_link'],
                $summary['missing_person'],
                $summary['missing_household'],
                $summary['church_mismatches'],
                $summary['person_conflicts'],
            ]]
        );

        return self::SUCCESS;
    }
}
