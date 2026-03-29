<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageQuery
{
    private $user;

    private readonly ?Account $account;

    public function __construct(private readonly int $branchId)
    {
        $this->user    = auth()->user();
        $this->account = $this->user->getAccount();
    }

    public function execute(): Builder|HasMany
    {
        $query = Branch::query()
            ->where(function (\Illuminate\Database\Eloquent\Builder $query): void {
                $this->addMessageConditions($query);

                if ($this->user->hasPermission('view quote')) {
                    $this->addQuoteViewConditions($query);
                }
            })
            ->whereNot('id', $this->branchId);

        $this->withMessage($query);

        return $query;

    }

    private function addMessageConditions(Builder $query): void
    {
        if ($this->user->userable->branches()->exists()) {
            $ids = $this->user->userable->branches()->pluck('branches.id');
            $query->where(function ($where) use ($ids): void {
                $where->whereHas('senderMessages', function ($q) use ($ids): void {
                    $q->whereIn('receiver_branch_id', $ids);
                })
                    ->orWhereHas('receiverMessages', function ($q) use ($ids): void {
                        $q->whereIn('sender_branch_id', $ids);
                    });

            });

            return;
        }
        $query->where(function ($where): void {
            $where->whereHas('senderMessages', function ($q): void {
                $q->where('receiver_branch_id', $this->branchId);
            })
                ->orWhereHas('receiverMessages', function ($q): void {
                    $q->where('sender_branch_id', $this->branchId);
                });
        });
    }

    private function addQuoteViewConditions(Builder $query): void
    {
        $accountBranchIds = $this->account->branches()->pluck('id');
        $query->orWhere(function ($query) use ($accountBranchIds): void {
            $query->orWhere(function ($q) use ($accountBranchIds): void {
                $q->whereHas('senderMessages', function ($q) use ($accountBranchIds): void {
                    $q->whereIn('receiver_branch_id', $accountBranchIds);
                })
                    ->orWhereHas('receiverMessages', function ($q) use ($accountBranchIds): void {
                        $q->whereIn('sender_branch_id', $accountBranchIds);
                    });
            });
        });
    }

    public function notifyExecute(): Builder|HasMany
    {
        $query = Branch::query()
            ->select('id', 'slug', 'name', 'address', 'status', 'created_at')
            ->where(function (\Illuminate\Database\Eloquent\Builder $query): void {
                $this->addMessageConditions($query);

                if ($this->user->hasPermission('view quote')) {
                    $this->addQuoteViewConditions($query);
                }
            })

            ->whereNot('id', $this->branchId);
        $this->withMessage($query);

        return $query;
    }

    public function withMessage($query): void
    {

        if ($this->user->hasPermission('view quote')) {

            $accountBranchIds = $this->account->branches()->pluck('id');
            $query->with(['senderMessages' => function ($q) use ($accountBranchIds): void {
                $q->whereIn('receiver_branch_id', $accountBranchIds);
            },  'receiverMessages' => function ($q) use ($accountBranchIds): void {
                $q->whereIn('sender_branch_id', $accountBranchIds);
            }]);

            return;
        }

        $query->with(['senderMessages' => function ($q): void {
            $q->where('receiver_branch_id', $this->branchId);
        },  'receiverMessages' => function ($q): void {
            $q->where('sender_branch_id', $this->branchId);
        }]);
    }
}
