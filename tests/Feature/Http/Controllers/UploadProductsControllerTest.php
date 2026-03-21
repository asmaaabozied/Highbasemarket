<?php

namespace Http\Controllers;

use App\Http\Controllers\Product\UploadProductsController;
use App\Models\Account;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadProductsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_product_with_brand()
    {
        Storage::fake('local');
        $uniqFile = uniqid();
        $fileName = 'product.jpg';
        $filePath = 'chunked_attachments/'.$uniqFile;

        $file = UploadedFile::fake()->create($fileName, 500);

        $file->storeAs($filePath, $fileName);

        $brand = Brand::factory()->create();

        $account = Account::factory()->hasEmployees(1)->create();

        $user = User::factory()->create([
            'userable_id'   => $account->employees()->first()->id,
            'userable_type' => Employee::class,
        ]);

        $request = new Request([
            'file'  => $uniqFile,
            'brand' => $brand->id,
        ]);

        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $controller = new UploadProductsController;
        $controller->__invoke($request);

        Storage::assertExists(
            'accounts/'.$user->getAccount()->id."/products/$uniqFile-$fileName"
        );
    }
}
