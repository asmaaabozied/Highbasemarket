<?php

namespace App\Services\Whatsapp;

use App\Enum\WhatsAppButtonTypeEnum;

readonly class WhatsAppButton
{
    public function __construct(
        private WhatsAppButtonTypeEnum $type,
        private ?string $url = null
    ) {}

    public function toArray(): array
    {
        return [
            'type'     => 'button',
            'sub_type' => $this->type->subType(),
            'index'    => 0,
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
