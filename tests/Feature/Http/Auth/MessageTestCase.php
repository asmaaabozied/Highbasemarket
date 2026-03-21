<?php

namespace Feature\Http\Auth;

use App\Models\Account;
use App\Models\Chat;
use App\Models\Employee;
use App\Models\Message;
use App\Models\User;

class MessageTestCase
{
    protected $user;

    protected $chat;

    protected $branchId;

    protected $acceptedMessage;

    public function setUp(): void
    {
        $account = Account::factory()
            ->hasBranches(2)
            ->hasEmployees()
            ->create();

        $employee = $account->employees()->first();

        User::factory(2)->create([
            'userable_id'   => $employee->id,
            'userable_type' => Employee::class,
        ]);

        $receiver   = User::query()->first();
        $this->user = User::query()->latest()->first();

        $senderBranch   = $account->branches()->first();
        $receiverBranch = $account->branches()->latest()->first();

        $this->chat = Chat::factory()->create([
            'sender_branch_id'   => $senderBranch->id,
            'receiver_branch_id' => $receiverBranch->id,
        ]);

        Message::factory()->create([
            'receiver_id'        => $receiver->id,
            'sender_id'          => $this->user->id,
            'branch_id'          => $senderBranch->id,
            'receiver_branch_id' => $receiverBranch->id,
        ]);

        $this->branchId = $senderBranch->id;

        $this->acceptedMessage = Message::factory()->create([
            'receiver_id'        => $receiver->id,
            'sender_id'          => $this->user->id,
            'branch_id'          => $senderBranch->id,
            'receiver_branch_id' => $receiverBranch->id,
            'chat_id'            => $this->chat->id,
        ]);
    }
}
