<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IssueReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Issue $issue,
    ) {}

    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $location = $this->issue->location?->name ?? 'Unknown Location';

        return (new MailMessage)
            ->subject("New Issue Reported: {$location}")
            ->line("A new issue has been reported at {$location}.")
            ->line("Tracking Code: {$this->issue->tracking_code}")
            ->line(Str::limit($this->issue->description, 100))
            ->action('View Issue', url("/supervisor/issues"))
            ->line('Please review and assign this issue to a team member.');
    }

    public function toArray(User $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'tracking_code' => $this->issue->tracking_code,
            'location' => $this->issue->location?->name,
            'description' => Str::limit($this->issue->description, 100),
        ];
    }
}
