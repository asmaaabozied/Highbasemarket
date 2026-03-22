<?php

return [
    'importers' => [
        'products' => [
            'job' => \App\Jobs\ImportProductJob::class,
        ],
        'stocks' => [
            'job' => \App\Jobs\ImportStockJob::class,
        ],
    ],
];
