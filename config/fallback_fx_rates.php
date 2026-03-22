<?php

use App\Enum\CurrencyEnum;

return [
    CurrencyEnum::AED->value => [CurrencyEnum::USD->value => 0.27],
    CurrencyEnum::SAR->value => [CurrencyEnum::USD->value => 0.27],
    CurrencyEnum::QAR->value => [CurrencyEnum::USD->value => 0.27],
    CurrencyEnum::KWD->value => [CurrencyEnum::USD->value => 3.26],
    CurrencyEnum::BHD->value => [CurrencyEnum::USD->value => 2.65],
    CurrencyEnum::OMR->value => [CurrencyEnum::USD->value => 2.60],
    CurrencyEnum::USD->value => [CurrencyEnum::AED->value => 3.67, CurrencyEnum::SAR->value => 3.75],
];
