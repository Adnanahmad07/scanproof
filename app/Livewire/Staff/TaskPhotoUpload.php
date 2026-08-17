<?php

namespace App\Livewire\Staff;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskPhoto;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskPhotoUpload extends Component
{
    use WithFileUploads;

    public $taskId;
    public $photoType = 'before'; // before or after
    public $photo;
    public $showUploadModal = false;
    public $task;

    protected $rules = [
        'photo' => 'required|image|mimes:jpeg,png,webp|max:5120',
        'photoType' => 'required|in:before,after',
    ];

    protected $messages = [
        'photo.required' => 'Please select a photo.',
        'photo.image' => 'File must be an image.',
        'photo.mimes' => 'Only JPEG, PNG, and WebP formats are allowed.',
        'photo.max' => 'Photo must be less than 5MB.',
    ];

    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->loadTask();
    }

    private function loadTask(): void
    {
        $this->task = Task::where('id', $this->taskId)
            ->where('assigned_to', auth()->id())
            ->with('photos')
            ->first();
    }

    public function openUploadModal(string $type = 'before'): void
    {
        $this->photoType = $type;
        $this->photo = null;
        $this->showUploadModal = true;
        $this->resetValidation();
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->photo = null;
    }

    public function uploadPhoto(): void
    {
        $this->validate();

        if (!$this->task) {
            return;
        }

        $path = $this->photo->store('task-photos/' . $this->taskId, 'public');

        TaskPhoto::create([
            'task_id' => $this->taskId,
            'type' => $this->photoType,
            'path' => $path,
        ]);

        $this->loadTask();
        $this->closeUploadModal();

        session()->flash('success', ucfirst($this->photoType) . ' photo uploaded successfully.');

        $this->dispatch('upload-success');
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = TaskPhoto::where('id', $photoId)
            ->where('task_id', $this->taskId)
            ->first();

        if ($photo) {
            \Storage::disk('public')->delete($photo->path);
            $photo->delete();
            $this->loadTask();
            session()->flash('success', 'Photo deleted.');
        }
    }

    public function getBeforePhotosProperty()
    {
        return $this->task ? $this->task->photos()->where('type', 'before')->get() : collect();
    }

    public function getAfterPhotosProperty()
    {
        return $this->task ? $this->task->photos()->where('type', 'after')->get() : collect();
    }

    public function canStartTask(): bool
    {
        return $this->task && $this->task->status === TaskStatus::Pending && $this->beforePhotos->isEmpty();
    }

    public function canCompleteTask(): bool
    {
        return $this->task && $this->task->status === TaskStatus::InProgress && $this->afterPhotos->isEmpty();
    }

    public function render()
    {
        return view('livewire.staff.task-photo-upload', [
            'task' => $this->task,
            'beforePhotos' => $this->beforePhotos,
            'afterPhotos' => $this->afterPhotos,
        ]);
    }
}
