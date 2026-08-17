<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IssueAssignedNotification extends Notification implements ShouldQueue
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
            ->subject("Issue Assigned to You: {$location}")
            ->line("An issue has been assigned to you at {$location}.")
            ->line("Tracking Code: {$this->issue->tracking_code}")
            ->line(Str::limit($this->issue->description, 100))
            ->action('View Issue', url("/staff/issues/{$this->issue->id}"))
            ->line('Please start working on this issue as soon as possible.');
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
