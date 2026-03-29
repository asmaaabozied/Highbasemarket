<?php

namespace App\SubscriptionPostPaid;

interface PostPaidStrategyInterface
{
    public function execute($quote, $attribute): void;
}
