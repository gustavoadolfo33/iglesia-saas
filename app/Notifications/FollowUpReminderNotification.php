<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpReminderNotification extends Notification
{
    use Queueable;

    public function __construct(protected FollowUp $followUp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'follow_up_id' => $this->followUp->id,
            'church_id' => $this->followUp->church_id,
            'person_name' => $this->followUp->person?->full_name,
            'summary' => $this->followUp->summary,
            'due_at' => optional($this->followUp->due_at)?->toDateTimeString(),
        ];
    }
}
