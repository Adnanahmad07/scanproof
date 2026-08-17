<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
    ) {}

    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $assignee = $this->task->assignee?->name ?? 'Unknown Worker';
        $location = $this->task->location ?? 'Unknown Location';

        return (new MailMessage)
            ->subject("Task Completed: {$this->task->title}")
            ->line("A task has been completed by {$assignee}.")
            ->line("Title: {$this->task->title}")
            ->line("Location: {$location}")
            ->action('View Task', url("/supervisor/tasks"));
    }

    public function toArray(User $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'location' => $this->task->location,
            'completed_by' => $this->task->assignee?->name,
            'type' => 'task_completed',
        ];
    }
}
