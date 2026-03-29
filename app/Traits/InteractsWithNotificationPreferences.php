<?php

namespace App\Traits;

use App\Enum\NotificationChannel;

trait InteractsWithNotificationPreferences
{
    public function prefersChannel(NotificationChannel $channel): bool
    {
        return in_array($channel->value, $this->notification_preferences ?? [], true);
    }

    public function notificationChannels(): array
    {
        return collect($this->notification_preferences ?? [])
            ->map(fn ($value) => NotificationChannel::from($value))
            ->all();
    }
}
