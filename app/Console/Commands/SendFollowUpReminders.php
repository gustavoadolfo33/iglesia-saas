<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Notifications\FollowUpReminderNotification;
use Illuminate\Console\Command;

class SendFollowUpReminders extends Command
{
    protected $signature = 'crm:send-follow-up-reminders';

    protected $description = 'Send database reminders for due and overdue follow ups';

    public function handle(): int
    {
        $followUps = FollowUp::query()
            ->with(['leader.user', 'person'])
            ->where(function ($query) {
                $query->whereDate('due_at', today())
                    ->orWhere(function ($overdue) {
                        $overdue->where('status', 'pending')->where('due_at', '<', now());
                    });
            })
            ->get();

        $sent = 0;

        foreach ($followUps as $followUp) {
            $user = $followUp->leader?->user;

            if (!$user) {
                continue;
            }

            $user->notify(new FollowUpReminderNotification($followUp));
            $sent++;
        }

        $this->info("Reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
