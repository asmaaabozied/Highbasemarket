<?php

use App\Jobs\ClosingRFQPost;
use App\Jobs\MarkMissedVisitsJob;
use App\Jobs\ProcessImportFileJob;
use App\Jobs\UpdateScheduledVisitsToPendingJob;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\ImportedFile;
use App\Models\Invitation;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schedule;
use Rap2hpoutre\FastExcel\FastExcel;

Artisan::command('run:importing', function () {
    ImportedFile::all()->each(function ($import) {
        ProcessImportFileJob::dispatch($import);
    });
})->purpose('Display an inspiring quote every hour');

Artisan::command('link:invitation', function () {
    $branches = Branch::with('account')
        ->select('branches.*', 'invitations.id as invitation_id')
        ->join('invitations', 'branches.email', '=', 'invitations.email')
        ->get();

    $branches->each(function (Branch $branch): void {
        if (! $branch->account->referred_id) {
            $branch->account->update([
                'referred_type' => Invitation::class,
                'referred_id'   => $branch->invitation_id,
            ]);
        }
    });
});

Artisan::command('reset-password {id}', function ($id) {
    $user = User::find($id);

    $token = Password::broker()->createToken($user);

    $url = url(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ], false));
    dump($url);
});

Schedule::job(new ClosingRFQPost)->everySixHours($minutes = 0);
Schedule::command('commissions:check-overdue')->daily();

Schedule::command('visits:generate-daily')->dailyAt('5:00');
Schedule::command('employees:send-daily-visit-reminders')->at('06:00');

Schedule::job(new MarkMissedVisitsJob)->dailyAt('22:00');
Schedule::job(new UpdateScheduledVisitsToPendingJob)->dailyAt('5:00');

// Daily
Schedule::command('reports:send-visit-reports daily')
    ->dailyAt('18:00');

// Weekly (Monday)
Schedule::command('reports:send-visit-reports weekly')
    ->weeklyOn(1, '00:15');

// Monthly (1st day)
Schedule::command('reports:send-visit-reports monthly')
    ->monthlyOn(1, '00:30');

Schedule::command('invoices:send-weekly-summary')
    ->weeklyOn(6, '06:30');

Artisan::command('translate:categories', function () {
    $chunks = (new FastExcel)->import(base_path('categories.xlsx'))->chunk(200);

    $lists = [];
    $chunks->each(function ($chunk) use (&$lists) {
        $data = [];

        foreach ($chunk as $product) {
            $data[] = [
                'translatable_id'   => $product['id'],
                'translatable_type' => Category::class,
                'field'             => 'name',
                'lang'              => 'ar',
                'translation'       => $product['category name (ar)'],
            ];
        }

        Translation::insert($data);
    });
});

Artisan::command('translate:brands', function () {
    $chunks = (new FastExcel)->import(base_path('brands.xlsx'))->chunk(200);

    $lists = [];
    $chunks->each(function ($chunk) use (&$lists) {
        $data = [];

        foreach ($chunk as $product) {
            $data[] = [
                'translatable_id'   => $product['brand_id'],
                'translatable_type' => \App\Models\Brand::class,
                'field'             => 'name',
                'lang'              => 'ar',
                'translation'       => $product['brand name (ar)'],
            ];
        }

        Translation::insert($data);
    });
});

Artisan::command('translate:products', function () {
    $chunks = (new FastExcel)->import(base_path('products.xlsx'))->chunk(200);

    $lists = [];
    $chunks->each(function ($chunk) use (&$lists) {
        $data = [];

        foreach ($chunk as $product) {
            $data[] = [
                'translatable_id'   => $product['var_id'],
                'translatable_type' => Variant::class,
                'field'             => 'name',
                'lang'              => 'ar',
                'translation'       => $product['var name (ar)'],
            ];
        }

        Translation::insert($data);
    });
});

Artisan::command('generate:coupons', function () {
    $chunks = (new \Rap2hpoutre\FastExcel\FastExcel)->import(base_path('coupons.xlsx'));

    foreach ($chunks as $chunk) {
        if (! $chunk['Branch Id']) {
            continue;
        }

        $name = [
            'ar' => $chunk['Coupon Code'],
            'en' => $chunk['Coupon Code'],
        ];

        $times = null;

        if ($chunk['Usage '] == 'First orders only') {
            $times = 1;
        }

        Coupon::create([
            'branch_id'             => $chunk['Branch Id'],
            'name'                  => $name,
            'code'                  => $chunk['Coupon Code'],
            'value'                 => $chunk['Discount Amount'],
            'type'                  => 'percent',
            'starting_time'         => '2026-02-01 00:00:00',
            'ending_time'           => '2026-03-20 00:00:00',
            'quantity_per_customer' => $times,
        ]);

        dump('chunk: '.$chunk['Branch Id']);
    }
});

Artisan::command('import:lng', function () {
    $chunks = (new \Rap2hpoutre\FastExcel\FastExcel)->import(base_path('lngs.xlsx'));

    foreach ($chunks as $chunk) {

        $branch = \App\Models\AnonymousCustomer::query()->where('cr_number', $chunk['CR'])->first();

        $branch?->customerBranches()->update([
            'address->pin_location' => [
                'lat' => $chunk['Latitude'],
                'lng' => $chunk['Longitude'],
            ],
        ]);
    }
});

Artisan::command('reassign:employee', function () {
    $chunks = (new \Rap2hpoutre\FastExcel\FastExcel)->import(base_path('DRIVERS.xlsx'));

    $employees = [
        ['employee_name' => 'Ali', 'employee_id' => 943],
        ['employee_name' => 'Shabeer', 'employee_id' => 942],
        ['employee_name' => 'Noman', 'employee_id' => 941],
        ['employee_name' => 'Yasser', 'employee_id' => 940],
        ['employee_name' => 'Hayat', 'employee_id' => 939],
    ];

    foreach ($chunks as $chunk) {
        \App\Models\EmployeeVisit::query()->where('id', $chunk['visit id'])->update([
            'employee_id' => collect($employees)->firstWhere('employee_name', $chunk['DRIVER'])['employee_id'] ?? null,
        ]);
    }
});
