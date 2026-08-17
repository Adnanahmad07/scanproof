<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IssueResolvedNotification extends Notification implements ShouldQueue
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
        $assignee = $this->issue->assignee?->name ?? 'Unknown Worker';

        return (new MailMessage)
            ->subject("Issue Resolved: {$location}")
            ->line("An issue at {$location} has been resolved by {$assignee}.")
            ->line("Tracking Code: {$this->issue->tracking_code}")
            ->line(Str::limit($this->issue->description, 100))
            ->action('View Issue', url("/supervisor/issues"))
            ->line('Please review and verify the resolution.');
    }

    public function toArray(User $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'tracking_code' => $this->issue->tracking_code,
            'location' => $this->issue->location?->name,
            'description' => Str::limit($this->issue->description, 100),
            'resolved_by' => $this->issue->assignee?->name,
        ];
    }
}
