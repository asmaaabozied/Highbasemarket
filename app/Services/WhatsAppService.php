<?php

namespace App\Services;

use App\Enum\WhatsAppTemplate;
use App\Enum\WhatsappTemplateLangEnum;
use App\Interfaces\WhatsAppMessenger;
use Exception;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Response;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Throwable;

readonly class WhatsAppService implements WhatsAppMessenger
{
    public function __construct(private WhatsAppCloudApi $client)
    {
        //
    }

    /**
     * @throws Exception
     */
    public function sendMessage(string $number, string $message): Response
    {
        try {
            return $this->client->sendTextMessage($number, $message, true);
        } catch (Throwable|ResponseException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws Exception
     */
    public function sendTemplateMessage(
        string $to,
        WhatsAppTemplate $template,
        ?Component $component = null,
        WhatsappTemplateLangEnum $lang = WhatsappTemplateLangEnum::EN
    ): Response {

        try {
            return $this->client->sendTemplate(
                to: $to,
                template_name: $template->value,
                language: $lang->value,
                components: $component
            );
        } catch (Throwable|ResponseException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
