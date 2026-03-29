<?php

namespace App\SubscriptionPostPaid;

use App\Enum\SubscriptionInviter;

class PostPaidStrategyContext
{
    private readonly PostPaidStrategyInterface $postPaidStrategy;

    public function __construct(string $type)
    {
        $this->postPaidStrategy = match ($type) {
            SubscriptionInviter::BETWEEN_HIGH_BASE_AND_INFLUENCER => new AllPayment,
            default                                               => throw new \InvalidArgumentException('You must pass an attributes'),
        };
    }

    /**
     * @throws \Exception
     */
    public function execute($quote, $attribute): void
    {
        $this->postPaidStrategy->execute($quote, $attribute);
    }
}
