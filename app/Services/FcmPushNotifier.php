<?php

namespace App\Services;

use App\Interfaces\PushNotifier;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;

readonly class FcmPushNotifier implements PushNotifier
{
    /**
     * Create a new class instance.
     */
    public function __construct(private Messaging $messaging)
    {
        //
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function send(CloudMessage $message): array
    {
        return $this->messaging->send($message);
    }
}
