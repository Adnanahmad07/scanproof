<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
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
        $assignee = $this->task->assignee?->name ?? 'Unassigned';
        $location = $this->task->location ?? 'Unknown Location';
        $daysOverdue = $this->task->due_date->diffInDays(now());

        return (new MailMessage)
            ->subject("Task Overdue: {$this->task->title}")
            ->line("A task is now {$daysOverdue} day(s) overdue.")
            ->line("Title: {$this->task->title}")
            ->line("Location: {$location}")
            ->line("Assigned to: {$assignee}")
            ->line("Due: {$this->task->due_date->format('M d, Y')}")
            ->action('View Task', url("/supervisor/tasks"));
    }

    public function toArray(User $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'location' => $this->task->location,
            'assigned_to' => $this->task->assignee?->name,
            'due_date' => $this->task->due_date?->format('Y-m-d'),
            'days_overdue' => $this->task->due_date->diffInDays(now()),
            'type' => 'task_overdue',
        ];
    }
}
