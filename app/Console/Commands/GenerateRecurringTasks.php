<?php

namespace App\Console\Commands;

use App\Models\RecurringTask;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring';
    protected $description = 'Generate today\'s recurring tasks';

    public function handle(): int
    {
        $recurringTasks = RecurringTask::where('is_active', true)->get();

        $generated = 0;

        foreach ($recurringTasks as $recurringTask) {
            if ($recurringTask->shouldGenerateToday()) {
                $task = $recurringTask->generateTask();

                if ($recurringTask->assigned_to) {
                    $assignee = User::find($recurringTask->assigned_to);
                    if ($assignee) {
                        $assignee->notify(new TaskAssignedNotification($task));
                    }
                }

                $generated++;
                $this->info("Generated: {$task->title}");
            }
        }

        $this->info("Done. Generated {$generated} recurring task(s).");

        return Command::SUCCESS;
    }
}
