<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationDropdown extends Component
{
    public $open = false;

    protected $listeners = ['notificationCreated' => '$refresh'];

    public function getNotificationsProperty()
    {
        return auth()->user()
            ->notifications()
            ->latest()
            ->take(20)
            ->get();
    }

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->each->markAsRead();
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open && $this->unreadCount > 0) {
            $this->markAllRead();
            $this->resetComputedProperties();
        }
    }

    private function resetComputedProperties(): void
    {
        unset($this->unreadCount);
    }

    public function render()
    {
        return view('livewire.notification-dropdown', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
