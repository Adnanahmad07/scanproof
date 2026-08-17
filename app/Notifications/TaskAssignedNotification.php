<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
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
        $location = $this->task->location ?? 'Unknown Location';

        return (new MailMessage)
            ->subject("Task Assigned: {$this->task->title}")
            ->line("You have been assigned a new task.")
            ->line("Title: {$this->task->title}")
            ->line("Location: {$location}")
            ->line("Category: {$this->task->category->label()}")
            ->line("Priority: {$this->task->priority->label()}")
            ->when($this->task->due_date, fn ($m) => $m->line("Due: {$this->task->due_date->format('M d, Y')}"))
            ->action('View Task', url("/staff/tasks"));
    }

    public function toArray(User $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'location' => $this->task->location,
            'category' => $this->task->category->value,
            'priority' => $this->task->priority->value,
            'due_date' => $this->task->due_date?->format('Y-m-d'),
            'type' => 'task_assigned',
        ];
    }
}
