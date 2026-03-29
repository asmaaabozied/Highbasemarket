<?php

namespace App\Services;

use App\Mail\Invitation;
use App\Models\EmailConfig;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

use function Laravel\Prompts\error;

class MailConfigService
{
    /**
     * @var \App\Models\EmailConfig|null
     */
    public $config;

    public function setEmployeeEmailConfig(int $employeeId): void
    {

        $config = EmailConfig::query()->where('user_id', $employeeId)->first();

        if (isset($config?->id)) {
            Config::set(
                [
                    'mail.mailers.smtp.username' => $config->email,
                    'mail.mailers.smtp.password' => $config->password,
                    'mail.from.address'          => $config->email,
                    'mail.from.name'             => 'HIGHBASECO',
                ]);
        }
    }

    public function getUserEmailConfig(int $userId): ?\Illuminate\Database\Eloquent\Model
    {
        return EmailConfig::query()->where('user_id', $userId)->first();
    }

    /**
     * @throws Exception
     */
    public function sendEmailWithContent(string $email, $content, $cc = []): void
    {
        $this->setEmployeeEmailConfig(auth()->user()->id);

        $this->config = EmailConfig::query()->latest()->first();

        try {
            Mail::to($email)->cc($cc)->send(new Invitation($content));

            \App\Models\Invitation::where('email', $email)->update(['sent_at' => now()]);
        } catch (Exception $e) {
            error($e);
        }

    }
}
