<?php

use App\Http\Controllers\Account\AcceptInvitationController;
use App\Http\Controllers\Account\Chat\ShowMessageController;
use App\Http\Controllers\Account\FollowerController;
use App\Http\Controllers\Account\InterestController;
use App\Http\Controllers\Account\InvitationController;
use App\Http\Controllers\Account\MessageSentController;
use App\Http\Controllers\Account\PaymentController;
use App\Http\Controllers\Account\Stocks\ExportStocksController;
use App\Http\Controllers\Account\UnfollowController;
use App\Http\Controllers\Account\UserGuideController;
use App\Http\Controllers\Admin\ImportProductsController;
use App\Http\Controllers\Admin\ImportStockController;
use App\Http\Controllers\Admin\ShareProfileController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\API\BranchesController;
use App\Http\Controllers\API\BrandsController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\CityController as ApiCityController;
use App\Http\Controllers\API\ExceptionController;
use App\Http\Controllers\API\PaginateBrandsController;
use App\Http\Controllers\API\ProductsController;
use App\Http\Controllers\API\StateController;
use App\Http\Controllers\API\ToBeDeliveredOrdersController;
use App\Http\Controllers\AssignController;
use App\Http\Controllers\BranchQrConfigController;
use App\Http\Controllers\Brands\BrandController;
use App\Http\Controllers\Brands\BrandSearchController;
use App\Http\Controllers\Brands\UploadBrandsController;
use App\Http\Controllers\CardPayment\CreatePaymentController;
use App\Http\Controllers\CardPayment\PaymentConfirmationController;
use App\Http\Controllers\CardPayment\PaymentPageController;
use App\Http\Controllers\ChangeLanguageController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Download\DownloadUploadsController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HandleChunkUploadsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Notifications\NotificationRouterController;
use App\Http\Controllers\Notifications\ReadNotificationController;
use App\Http\Controllers\Notifications\ShowNotificationController;
use App\Http\Controllers\Product\ProductSearchController;
use App\Http\Controllers\Product\UploadProductsController;
use App\Http\Controllers\Storefront\InvitationLandingController;
use App\Http\Controllers\Storefront\MainSearchController;
use App\Http\Controllers\Storefront\MarketSwitchController;
use App\Http\Controllers\Storefront\SupportController;
use App\Http\Controllers\ToggleStatusController;
use App\Http\Controllers\UploadChunkController;
use App\Http\Controllers\UploadDataController;
use App\Http\Middleware\ActiveMiddleware;
use App\Http\Middleware\SelfConfigureQrStickerMiddleware;
use AshAllenDesign\ShortURL\Controllers\ShortURLController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

Route::get('invitations/{invitation:uuid}', \App\Http\Controllers\Storefront\InvitationController::class)
    ->middleware('signed')
    ->name('invitations.view');

Route::get('error', function () {
    return inertia('Errors/NotFound');
});

Route::get('viewer/{view}', function ($view) {
    return view('viewer', compact('view'));
})
    ->whereIn('view', ['presentation', 'profile'])
    ->name('viewer');

Route::get('change-language/{lang}', ChangeLanguageController::class)
    ->whereIn('lang', ['en', 'ar'])
    ->name('change.language');

include 'articles.php';

Route::get('download', function (Request $request) {
    $file = Str::after($request->string('file'), 'storage');

    return response()->download(storage_path(Str::beforeLast($file, '/')));
});

Route::get('market/{market}', MarketSwitchController::class)->name('market');
Route::get('products/search', ProductSearchController::class)->name('products.search');
Route::get('brands/search', BrandSearchController::class)->name('brands.search');
Route::get('search', MainSearchController::class)->name('search')->withoutMiddleware('HandleInertiaRequests');

Route::post('supports', [SupportController::class, 'store'])->name('supports.store');

Route::group(['middleware' => 'follow-up'], function () {
    Route::get('/', HomeController::class)
        ->middleware(['signature', 'invite'])
        ->name('home');
});

Route::get('invitation/landing', InvitationLandingController::class)->name('invitation.landing');
Route::get('card', function () {
    $data = Http::withHeaders([
        'authorization' => 'Bearer '.config()->string('auth.tap_secret_api_key'), 'content-type' => 'application/json',
    ])->get('https://api.tap.company/v2/card/cus_TS02A1020241522Zq922710263/card_JN1p44241222OBRf27659V648');

    return json_decode($data->body());
})->name('tap.form');
Route::any('tap-callback', [PaymentController::class, 'callback'])->name('tap.callback');

Route::any('chunk-upload', HandleChunkUploadsController::class)->name('chunk-upload');

Route::post('tap-payment', [PaymentController::class, 'payment'])->name('tap.payment');
Route::post('upload-data', UploadDataController::class)->name('upload-data');
Route::get('user-guides', UserGuideController::class)->name('user.guides');

Route::resource('form', FormController::class)
    ->only('create', 'store');
//, 'verified'
Route::group(['middleware' => ['auth:sanctum', ActiveMiddleware::class]], function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('invite')
        ->name('dashboard');

    Route::get('download/{upload}', DownloadUploadsController::class)->name('download');

    include 'admin.php';
    include 'account.php';

    Route::post('follow', [FollowerController::class, 'store'])->name('follow');
    Route::delete('unfollow/{branch}', UnfollowController::class)->name('unfollow');
    Route::post('import-products', ImportProductsController::class)->name('import.products');
    Route::post('import-stocks/{branch?}', ImportStockController::class)->name('import.stocks');
    Route::get('export-stocks/{branch?}', ExportStocksController::class)->name('export.stocks');

    Route::resource('assigns', AssignController::class)
        ->only(['store', 'destroy']);
    Route::resource('brands', BrandController::class)->middleware('not-disabled');

    Route::resource('uploads', UploadController::class)->only('destroy');

    Route::get('notifications/{notification}', NotificationRouterController::class)->name('notifications.router');
    Route::get('cities/{state}', CityController::class)->name('cities');

    Route::get('my-messages', [MessageSentController::class, 'getUserMessage'])->name('my.messages');
    Route::get('accept-chat/{messageId}', [MessageSentController::class, 'acceptChat'])->name('accept.chat');
    Route::get('sender/branches', [MessageSentController::class, 'getEmployeeSenderBranches'])
        ->name('sender.branches');
    Route::get('sender/notifications', [
        MessageSentController::class, 'getEmployeeSenderNotification',
    ])
        ->withoutMiddleware('verified')
        ->name('sender.notifications');

    Route::get('decline/messages/{id}', [MessageSentController::class, 'declineMessage'])->name('decline.messages');
    Route::get('show-all-notifications', ShowNotificationController::class)->name('show.notifications');
    Route::get('show-all-messages', ShowMessageController::class)->name('show.messages');
    Route::get('interest', [InterestController::class, 'index'])->name('interest.index');
    Route::get('followers', [FollowerController::class, 'index'])->name('following.index');

    Route::put('status/update/{model}/{status}/{id}', ToggleStatusController::class)->name('status.toggle');

    Route::group(['middleware' => 'not-disabled'], function () {
        Route::post('interest', [InterestController::class, 'store'])->name('interest.store');
        Route::post('upload-brands', UploadBrandsController::class)->name('upload-brands');
        Route::post('upload-products', UploadProductsController::class)->name('upload-products');
        Route::post('notifications/read', ReadNotificationController::class)->name('notifications.read');
        Route::post('send/message', [MessageSentController::class, 'sendMessage'])->name('send.message');
    });

    Route::group(['prefix' => 'rest-api', 'as' => 'api.'], function () {
        Route::get('brands', BrandsController::class)->name('brands');
        Route::get('paginate-brands', PaginateBrandsController::class)->name('paginate.brands');
        Route::get('categories', CategoriesController::class)->name('categories');
        Route::get('products', ProductsController::class)->name('products');
        Route::get('share-link/{branch:slug}', ShareProfileController::class)->name('share-link');
        Route::get('branches', BranchesController::class)->name('branches');
        Route::get('states', StateController::class)->name('states.index');
        Route::get('cities', ApiCityController::class)->name('cities.index');
        Route::get('exceptionable', [ExceptionController::class, 'index'])->name('exceptionable');
        Route::post('exceptions', [ExceptionController::class, 'store'])->name('exceptions.store');
        Route::get('undelivered-orders/{type}/{branch_id}', ToBeDeliveredOrdersController::class)
            ->name('undelivered.orders');
    });

    Route::get('account/{branch:slug}/{type}/{employee?}', InvitationController::class)
        ->name('invite')
        ->middleware('signed');
    Route::get('accept/{branch:slug}/{type}/{employee?}', AcceptInvitationController::class)
        ->middleware('signed')
        ->name('accept.invite');

});
include 'storefront.php';

Route::any('tap-callback', [PaymentController::class, 'callback'])->name('tap.callback');

Route::get('/payments/create/{method}/{card?}', CreatePaymentController::class)
    ->name('payments.create')
    ->middleware('auth:sanctum', ActiveMiddleware::class);

Route::get('payments/confirmation', PaymentConfirmationController::class)
    ->name('payments.confirmation')
    ->middleware('auth:sanctum', ActiveMiddleware::class);

Route::get('/payments/init', PaymentPageController::class)
    ->name('payments.init')
    ->middleware('auth:sanctum', ActiveMiddleware::class);

Route::post('tap-payment', [PaymentController::class, 'payment'])->name('tap.payment');
Route::post('upload-data', UploadDataController::class)->name('upload-data');
Route::get('user-guides', UserGuideController::class)->name('user.guides');

Route::post('/upload-chunk', UploadChunkController::class)->name('upload.chunk');

Route::post('/user/fcm-token', function (Request $request) {
    $request->validate(['token' => 'required|string']);
    auth()->user()?->update(['fcm_token' => $request->token]);

    return response()->json(['message' => 'Token successfully updated!']);
});

Route::resource('/branch/qr/config', BranchQrConfigController::class)
    ->names('branch.qr.config')
    ->only(['index', 'update']);

Route::prefix(config()->string('short-url.prefix'))
    ->middleware(SelfConfigureQrStickerMiddleware::class)
    ->get('/{shortURLKey}', ShortURLController::class)
    ->name('short-url.invoke');

Route::get('knowledge-base/{slug}', function (string $slug) {
    $lang = request()->string('lang', 'en');

    $path = resource_path("markdown/Knowledege/$slug/$lang.md");

    if (! File::exists($path)) {
        abort(404, 'File not found');
    }

    return Str::markdown(File::get($path), [
        'heading_permalink' => [
            'html_class'          => 'heading-permalink',
            'symbol'              => '#',
            'id_prefix'           => '',
            'fragment_prefix'     => "pop-up=$lang=$slug=",
            'apply_id_to_heading' => true,
        ],
    ], [
        new HeadingPermalinkExtension,
    ]);
})->name('knowledge.base');

Route::get('test', function () {
    return [
        'message' => 'test',
    ];
});
