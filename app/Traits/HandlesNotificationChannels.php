<?php

namespace App\Traits;

use App\Channels\FcmChannel;
use App\Channels\WhatsAppChannel;
use App\Enum\NotificationChannel;

trait HandlesNotificationChannels
{
    public function getPreferredChannels($notifiable): array
    {
        return collect($notifiable->notificationChannels())
            ->map(fn ($channel): string => match ($channel) {
                NotificationChannel::WEB      => FcmChannel::class,
                NotificationChannel::WHATSAPP => WhatsAppChannel::class,
            })
            ->toArray();
    }
}
