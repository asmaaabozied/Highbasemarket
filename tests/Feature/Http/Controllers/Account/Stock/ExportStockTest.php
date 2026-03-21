<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

describe('Exporting stock', function () {
    it('should export stock', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        createMultiStocks($branch->id, 10);
        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id, 10);

        $this->actingAs($user);
        $response = $this->get(route('export.stocks'));

        $response->assertHeader('Content-Disposition', 'attachment; filename=stocks.xls');
        $response->assertDownload();

        $content = $response->streamedContent();

        Storage::fake('download');

        Storage::put('stocks.xls', $content);

        $data = (new \Rap2hpoutre\FastExcel\FastExcel)->import(
            Storage::path('stocks.xls'),
        );

        expect($data->count())->toBe(10);

        Storage::delete('stocks.xls');
    });
});
