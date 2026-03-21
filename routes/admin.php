<?php

use App\Http\Controllers\Account\CommentController;
use App\Http\Controllers\Account\NewsFeedController;
use App\Http\Controllers\Account\WalletController;
use App\Http\Controllers\ActingAsAccountUserController;
use App\Http\Controllers\Admin\Accounts\AccountController;
use App\Http\Controllers\Admin\Accounts\AccountStatusController;
use App\Http\Controllers\Admin\Accounts\BranchPlanController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AssignPlanController;
use App\Http\Controllers\Admin\AttachBrandsToOwnerController;
use App\Http\Controllers\Admin\Branches\BranchController;
use App\Http\Controllers\Admin\ClaimController;
use App\Http\Controllers\Admin\Commission\AccountCommissionController;
use App\Http\Controllers\Admin\Commission\BranchCommissionController;
use App\Http\Controllers\Admin\Commission\CommissionOverviewController;
use App\Http\Controllers\Admin\Commission\OrderCommissionController;
use App\Http\Controllers\Admin\Commission\UpdateCommissionController;
use App\Http\Controllers\Admin\DataImporterController;
use App\Http\Controllers\Admin\EmailConfigController;
use App\Http\Controllers\Admin\Employees\EmployeeChangePasswordController;
use App\Http\Controllers\Admin\Employees\EmployeeController;
use App\Http\Controllers\Admin\Excel\UploadReadingController;
use App\Http\Controllers\Admin\ExceptionController;
use App\Http\Controllers\Admin\ImportContactController;
use App\Http\Controllers\Admin\InfluencerBranchController;
use App\Http\Controllers\Admin\InfluencerController;
use App\Http\Controllers\Admin\InfluencerOrderController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\Orders\ApproveOrderController;
use App\Http\Controllers\Admin\Orders\CancelOrderController;
use App\Http\Controllers\Admin\Orders\OrderController;
use App\Http\Controllers\Admin\Orders\OrderDeliveredController;
use App\Http\Controllers\Admin\Orders\RejectOrderController;
use App\Http\Controllers\Admin\Orders\ShipOrderController;
use App\Http\Controllers\Admin\Plans\PlanController;
use App\Http\Controllers\Admin\SendingEmailController;
use App\Http\Controllers\Admin\ShowNotificationController;
use App\Http\Controllers\Admin\Stocks\AddBulkStockController;
use App\Http\Controllers\Admin\Stocks\BulkUpdateStocksController;
use App\Http\Controllers\Admin\Stocks\HighbaseVisibilityController;
use App\Http\Controllers\Admin\Stocks\PriceLockerController;
use App\Http\Controllers\Admin\Stocks\StockController;
use App\Http\Controllers\Admin\Stocks\UpdateStockController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Category\CategoryGroupController;
use App\Http\Controllers\Category\UpdateCategoryAttributesController;
use App\Http\Controllers\Category\UpdateCategoryCustomFieldsController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ReturnToAdminController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admins.', 'middleware' => ['auth', 'verified']], function () {
    Route::put('return-to-admin', ReturnToAdminController::class)->name('return-to-admin');
    Route::put('acting-as-account-user/{user}', ActingAsAccountUserController::class)
        ->name('act-as');
});

Route::group(['prefix' => 'admin', 'as' => 'admins.', 'middleware' => 'admin'], function () {
    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('excel/read', [UploadReadingController::class, 'readExcel'])->name('excel.read');
    Route::get('sending-email', [SendingEmailController::class, 'index'])->name('sending.email.index');
    Route::get('data-importer', DataImporterController::class)->name('data.importer');
    Route::get('show-all-notifications', ShowNotificationController::class)->name('show.notifications');
    Route::get('influencer-accounts', InfluencerController::class)->name('influencer.accounts');
    Route::get('influencer-orders', [InfluencerOrderController::class, 'index'])->name('influencer.orders');
    Route::put('influencer-orders/{payment}',
        [InfluencerOrderController::class, 'update'])->name('influencer.orders.update');
    Route::get('influencer-branches', InfluencerBranchController::class)->name('influencer.branches');

    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('admin/roles', AdminRoleController::class)->name('admins.roles');
    Route::post('email-config', EmailConfigController::class)->name('email.config');
    Route::post('sending-email', [SendingEmailController::class, 'sendingEmail'])->name('sending.email');
    Route::post('excel/upload', [UploadReadingController::class, 'uploadExcel'])->name('excel.upload');
    Route::post('attach-brands-to-owner', AttachBrandsToOwnerController::class)->name('attach-brands-to-owner');
    Route::post('import-contact', ImportContactController::class)->name('import.contact');
    Route::post('stocks/bulk-add/{branch}', AddBulkStockController::class)
        ->name('stocks.add-bulk');

    Route::put('/orders/{order}/approve', ApproveOrderController::class)->name('orders.approve');
    Route::put('/orders/{order}/reject', RejectOrderController::class)->name('orders.reject');
    Route::put('/orders/{order}/deliver', OrderDeliveredController::class)->name('orders.deliver');
    Route::put('/orders/{order}/ship', ShipOrderController::class)->name('orders.ship');
    Route::put('/orders/{order}/cancel', CancelOrderController::class)->name('orders.cancel');

    Route::put('stocks/update', UpdateStockController::class)->name('stocks.update');
    Route::put('stocks/bulk-update', BulkUpdateStocksController::class)->name('stocks.bulk-update');
    Route::put('stocks/price-locker/{branch}', PriceLockerController::class)->name('stocks.price-locker');
    Route::put('stocks/highbase-visibility/{branch}', HighbaseVisibilityController::class)->name('stocks.highbase-visibility');
    Route::put('users/{user}', [EmployeeChangePasswordController::class, 'update'])
        ->name('users.changePassword.update');
    Route::put('accounts-status-update/{account}', [AccountStatusController::class, 'update'])
        ->name('accounts.status.update');
    Route::put('categories/{category}/attributes', UpdateCategoryAttributesController::class)
        ->name('categories.attributes.update');
    Route::put('categories/{category}/custom-fields', UpdateCategoryCustomFieldsController::class)
        ->name('categories.custom-fields.update');

    Route::resource('roles', RoleController::class);
    Route::resource('admins', AdminController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('invitations', InvitationController::class);
    Route::resource('invitations', InvitationController::class);
    Route::resource('products', ProductController::class);
    Route::resource('uploads', UploadController::class)->except('destroy');
    Route::resource('plans', PlanController::class);
    Route::resource('modules', ModuleController::class);
    Route::resource('branch-plan', BranchPlanController::class)
        ->parameter('branch-plan', 'branch')
        ->except('destroy');
    Route::resource('branches', BranchController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('branch.employees', EmployeeController::class)->scoped(['branch' => 'slug']);
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::resource('news-feeds', NewsFeedController::class)->except('show', 'create', 'index');
    Route::resource('branch.stocks', StockController::class)->scoped(['branch' => 'id']);
    Route::resource('assign-plans', AssignPlanController::class)->only(['edit', 'update']);
    Route::resource('categories/product-groups', CategoryGroupController::class)
        ->parameter('product_group', 'group');
    Route::resource('exceptions', ExceptionController::class)->only('update', 'destroy');
    Route::resource('claims', ClaimController::class);
    Route::resource('notification-templates', NotificationTemplateController::class);
    Route::resource('account/{account}/employees', \App\Http\Controllers\Admin\Accounts\EmployeeController::class);
    Route::resource('account/{account}/roles', \App\Http\Controllers\Admin\Accounts\RolesController::class)->names('accounts.roles');

    // routes/web.php

    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/overview', CommissionOverviewController::class)->name('overview');
        Route::get('/by-account/{account}', AccountCommissionController::class)->name('by-account');
        Route::get('/by-branch/{branch}', BranchCommissionController::class)->name('by-branch');
        Route::get('/by-order/{order}', OrderCommissionController::class)->name('by-order');
        Route::put('/{order}', UpdateCommissionController::class)->name('update');
    });

    Route::resource('generated-qrs', \App\Http\Controllers\GeneratedQRsController::class)->only(['index', 'update', 'create']);
});
