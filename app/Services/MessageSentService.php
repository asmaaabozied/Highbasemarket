<?php

namespace App\Services;

use App\Dto\MessageDto;
use App\Events\MessageSent;
use App\Exceptions\Message\SendingToMySelfException;
use App\Models\Branch;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageMedia;
use App\Models\MessageQuote;
use Carbon\Carbon;

class MessageSentService
{
    public function __construct(public EmployeeAccountServices $employeeAccountServices) {}

    /**
     * @throws SendingToMySelfException
     */
    public function sendMessage($body, int $sender_id, int $receiver_id, $senderBranchId, $messageable_id, $messageable_type): MessageDto
    {
        $message = $this->sendMessageProcess(
            body: $body,
            sender_id: $sender_id,
            sender_branch_id: $senderBranchId,
            receiver_branch_id: $receiver_id,
            messsageable_type: $messageable_type,
            messageable_id: $messageable_id,
        );
        MessageSent::dispatch($message);

        return $message;
    }

    public function getChatMessages(int $receiverBranchId, ?int $senderBranchId = null): \Illuminate\Database\Eloquent\Collection|array
    {
        $senderBranch = $senderBranchId;

        if (! $senderBranch) {
            $senderBranch = $this->employeeAccountServices->getEmployeeCurrentBranch()->id;
        }

        return Message::query()
            ->with(['chat', 'messageMedia', 'messageQuote.messagable'])
            ->where(function ($query) use ($receiverBranchId, $senderBranch): void {
                $query->where('sender_branch_id', $senderBranch)
                    ->where('receiver_branch_id', $receiverBranchId);

            })->orWhere(function ($query) use ($receiverBranchId, $senderBranch): void {
                $query->where('sender_branch_id', $receiverBranchId)
                    ->where('receiver_branch_id', $senderBranch);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function getUserMessages(): \Illuminate\Database\Eloquent\Collection|array
    {
        $branch = currentBranch();

        return Message::query()
            ->with(['sender', 'chat'])
            ->where('receiver_branch_id', $branch->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function userMessageMarkAsRead($branch): void
    {
        $branch->senderMessages()->update(['read_at' => Carbon::Now()]);
    }

    public function acceptChat(int $messageId, $planId = null): string
    {
        $message = Message::query()->findOrFail($messageId)
            ->load('receiverBranch', 'senderBranch');

        if (! $message->receiverBranch->customers()->where('customer_id', $message->sender_branch_id)->exists()) {
            $message->receiverBranch->customers()->attach($message->sender_branch_id, ['config' => json_encode(['paid' => true, 'plan' => $planId])]);
        }
        $accountBranchIds = auth()->user()->getAccount()->branches()->pluck('id');

        if ($message->receiverBranch()->whereIn('account_id', $accountBranchIds)->exists()) {
            \Session::put('current_branch', $message->receiverBranch);

            return $message->senderBranch->slug;
        }
        \Session::put('current_branch', $message->senderBranch);

        return $message->receiverBranch->slug;
    }

    /**
     * @throws SendingToMySelfException
     */
    public function notMessagingMyself($senderId, $receiverId): void
    {
        if ($senderId === $receiverId) {
            throw new SendingToMySelfException;
        }
    }

    public function getEmployeeSenderBranches($q = null): \Illuminate\Database\Eloquent\Collection|array
    {

        $senderBranch = $this->employeeAccountServices->getEmployeeCurrentBranch();
        $messages     = new MessageQuery($senderBranch->id);
        $query        = $messages->execute();

        if ($q) {
            $query->where('branches.name', 'LIKE', "%$q%");
        }

        return $query->get();
    }

    public function getEmployeeSenderNotification(): \Illuminate\Database\Eloquent\Collection|array
    {
        $senderBranch = $this->employeeAccountServices->getEmployeeCurrentBranch();
        $messages     = new MessageQuery($senderBranch->id);
        $query        = $messages->notifyExecute();

        return $query->get();
    }

    public function declineMessage($branchId): void
    {
        Branch::query()->find($branchId)->senderMessages()->delete();
    }

    /**
     * @throws SendingToMySelfException
     * @throws \Exception
     */
    public function sendMessageProcess($body, $sender_id, $sender_branch_id, $receiver_branch_id, $messsageable_type, $messageable_id): MessageDto
    {
        $media           = null;
        $messageQuote    = null;
        $receiverAccount = Branch::query()->findOrFail($receiver_branch_id)->account;

        if (! $receiverAccount) {
            throw new \Exception(__('There is no account belong to this branch'));
        }
        $this->notMessagingMyself($sender_branch_id, $receiver_branch_id);
        $chat = Chat::query()
            ->where(function ($query) use ($receiver_branch_id, $sender_branch_id): void {
                $query->where('receiver_branch_id', $receiver_branch_id)
                    ->where('sender_branch_id', $sender_branch_id);
            })
            ->orWhere(function ($query) use ($receiver_branch_id, $sender_branch_id): void {
                $query->where('sender_branch_id', $receiver_branch_id)
                    ->where('receiver_branch_id', $sender_branch_id);
            })
            ->first();

        if (! $chat) {
            $chat = Chat::query()->create([
                'sender_branch_id'   => $sender_branch_id,
                'receiver_branch_id' => $receiver_branch_id,
                'accept'             => false,
            ]);
        }

        if (request()->hasFile('attachment')) {
            $files = request()->file('attachment');
            $files = is_array($files) ? $files : [$files];

            $media = MessageMedia::query()->create([
                'body' => $body,
            ]);

            foreach ($files as $file) {
                $media->addMedia($file)->toMediaCollection('attachments');
            }
        }

        if ($messsageable_type) {
            $messageQuote = MessageQuote::query()->create([
                'body'            => $body,
                'messagable_type' => $messsageable_type,
                'messagable_id'   => $messageable_id,
            ]);
        }

        Message::query()->create(
            [
                'body'               => $body,
                'sender_branch_id'   => $sender_branch_id,
                'receiver_branch_id' => $receiver_branch_id,
                'chat_id'            => $chat?->id,
                'message_media_id'   => $media?->id,
                'message_quote_id'   => $messageQuote?->id,
                'messageable_type'   => $messsageable_type,
                'messageable_id'     => $messageable_id,
            ]
        );

        $ids = (new EmployeeAccountServices)
            ->getBranchUsersByAccountOrPermissions(
                account: $receiverAccount,
                permissions: ['view quote'],
                receiverBranchId: $receiver_branch_id
            )->pluck('user.id');

        return new MessageDto(
            body: $body,
            sender_id: $sender_id,
            sender_branch_id: $sender_branch_id,
            receiver_branch_id: $receiver_branch_id,
            user_receiver_ids: $ids,
            media_message : $media?->body,
            message_quote : $messageQuote?->load('messagable'),
            attachment: $media?->attachment
        );
    }
}
