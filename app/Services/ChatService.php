<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Chat;

class ChatService
{
    public Chat $chat;

    public function __construct(public Branch $sender, public Branch $receiver)
    {
        $this->chat = Chat::where('sender_branch_id', $sender->id)
            ->where('receiver_branch_id', $receiver->id)
            ->first();
    }

    public function accept(): void
    {
        if ($this->chat) {
            $this->chat->accept();
        }
    }

    public static function make(Branch $sender, Branch $receiver): self
    {
        return new self($sender, $receiver);
    }
}
