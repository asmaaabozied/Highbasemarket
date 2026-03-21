<?php

use App\Actions\Account\SearchCouponBrandsAction;
use App\Actions\Account\SearchCouponCategoriesAction;
use App\Actions\Account\SearchCouponCustomersAction;
use App\Actions\Account\SearchCouponProductsAction;
use App\Http\Controllers\Account\AcceptChatController;
use App\Http\Controllers\Account\AgentVisits\AgentVisitController;
use App\Http\Controllers\Account\AnonymousCustomerSearchController;
use App\Http\Controllers\Account\AutoAssignOrderController;
use App\Http\Controllers\Account\BankDetailsController;
use App\Http\Controllers\Account\BranchAddressController;
use App\Http\Controllers\Account\BranchController;
use App\Http\Controllers\Account\Card\CardController;
use App\Http\Controllers\Account\ChangeRFQStatusController;
use App\Http\Controllers\Account\CitySearchController;
use App\Http\Controllers\Account\ClaimController;
use App\Http\Controllers\Account\CommentController;
use App\Http\Controllers\Account\CommissionController;
use App\Http\Controllers\Account\CommissionPaymentController;
use App\Http\Controllers\Account\CompleteBuyerProfileController;
use App\Http\Controllers\Account\CouponController;
use App\Http\Controllers\Account\CreditSettingsController;
use App\Http\Controllers\Account\CurrentBranchController;
use App\Http\Controllers\Account\CustomerController;
use App\Http\Controllers\Account\CustomerSearchController;
use App\Http\Controllers\Account\Employee\EmployeeBranchController;
use App\Http\Controllers\Account\Employee\EmployeeRoleController;
use App\Http\Controllers\Account\Employee\EmployeeSearchController;
use App\Http\Controllers\Account\EmployeeMaxDiscountController;
use App\Http\Controllers\Account\GetCitiesByStateController;
use App\Http\Controllers\Account\HighbaseVisitDataController;
use App\Http\Controllers\Account\InstantOrderController;
use App\Http\Controllers\Account\InvoiceSettingsController;
use App\Http\Controllers\Account\MessageSentController;
use App\Http\Controllers\Account\MyCouponsController;
use App\Http\Controllers\Account\NewsFeedController;
use App\Http\Controllers\Account\Orders\AddMultiOrdersToMultiEmployeesVisitsController;
use App\Http\Controllers\Account\Orders\AddOrderToVisitController;
use App\Http\Controllers\Account\Orders\ApproveOrderController;
use App\Http\Controllers\Account\Orders\BulkAddToCartController;
use App\Http\Controllers\Account\Orders\BulkDeleteCartController;
use App\Http\Controllers\Account\Orders\CancelOrderController;
use App\Http\Controllers\Account\Orders\CartController;
use App\Http\Controllers\Account\Orders\ConfirmItemsDeliveryController;
use App\Http\Controllers\Account\Orders\OrderController;
use App\Http\Controllers\Account\Orders\OrderDeliveredController;
use App\Http\Controllers\Account\Orders\PurchaseController;
use App\Http\Controllers\Account\Orders\RejectOrderController;
use App\Http\Controllers\Account\Orders\ShipOrderController;
use App\Http\Controllers\Account\Payment\ConfirmPaymentController;
use App\Http\Controllers\Account\Payment\PaymentController;
use App\Http\Controllers\Account\Payment\RejectPaymentController;
use App\Http\Controllers\Account\Plans\SubscriptionController;
use App\Http\Controllers\Account\Progress\ActionController;
use App\Http\Controllers\Account\Progress\ProgressController;
use App\Http\Controllers\Account\ProposalController;
use App\Http\Controllers\Account\PublishRFQController;
use App\Http\Controllers\Account\QuoteAssignedController;
use App\Http\Controllers\Account\QuoteOrderController;
use App\Http\Controllers\Account\QuoteOrderPaymentController;
use App\Http\Controllers\Account\QuoteProductController;
use App\Http\Controllers\Account\RFQController;
use App\Http\Controllers\Account\RFQPaymentController;
use App\Http\Controllers\Account\ShareEmployeeVisitController;
use App\Http\Controllers\Account\StateSearchController;
use App\Http\Controllers\Account\Stocks\AddToStockController;
use App\Http\Controllers\Account\Stocks\HighbaseVisibilityController;
use App\Http\Controllers\Account\Stocks\PriceLockerController;
use App\Http\Controllers\Account\Terms\TermsController;
use App\Http\Controllers\Account\UpdateBranchAddressController;
use App\Http\Controllers\Account\VendorController;
use App\Http\Controllers\Account\VisitAttachmentController;
use App\Http\Controllers\Account\Wallet\InitiateDepositController;
use App\Http\Controllers\Account\Wallet\WalletTransferController;
use App\Http\Controllers\Account\WalletController;
use App\Http\Controllers\AddBulkStockController;
use App\Http\Controllers\Admin\Accounts\BranchPlanController;
use App\Http\Controllers\BrandNewsController;
use App\Http\Controllers\BulkUpdateStocksController;
use App\Http\Controllers\ChangeVisitDateController;
use App\Http\Controllers\ConfirmEmployeeVisitController;
use App\Http\Controllers\CustomerSpecialPriceController;
use App\Http\Controllers\DefaultTermController;
use App\Http\Controllers\EmployeeVisitCommentController;
use App\Http\Controllers\EmployeeVisitController;
use App\Http\Controllers\EmployeeVisitTimelineController;
use App\Http\Controllers\LikePostController;
use App\Http\Controllers\PostponeVisitController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProductNewsController;
use App\Http\Controllers\Quotes\QuoteController;
use App\Http\Controllers\Quotes\QuoteStatusController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SavedListController;
use App\Http\Controllers\SavedListItemController;
use App\Http\Controllers\ScheduleVisitController;
use App\Http\Controllers\SpecialPriceTemplateController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'account', 'as' => 'account.', 'middleware' => ['account', 'invite', 'not-disabled']],
    function () {
        Route::delete('branch-plan/{branch}', [BranchPlanController::class, 'destroy'])->name('branch-plan.destroy');
        Route::delete('quote-products/{quote_product}', QuoteProductController::class)->name('quote-products.destroy');
        Route::delete('bulk-delete-cart', BulkDeleteCartController::class)->name('bulk-delete-cart');
        Route::delete(
            'branch-address/{branch_address}',
            [BranchAddressController::class, 'destroy']
        )->name('branch-address.destroy');
        Route::delete('special-price/{customerSpecialPrice}', [CustomerSpecialPriceController::class, 'destroy'])
            ->name('customer.special-price.destroy');

        Route::get('/chat/accept/{branch:slug}', AcceptChatController::class)->name('chat.accept');
        Route::get('quote-payment/{quote}', QuoteOrderPaymentController::class)->name('quote-payment.show');
        Route::get('quote-assigned', [QuoteAssignedController::class, 'index'])->name('quote-assigned.index');
        Route::get('stocks/add', AddToStockController::class)->name('stocks.add');
        Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
        Route::get('products-news', [ProductNewsController::class, 'index'])->name('products.news');
        Route::get('brands-news', [BrandNewsController::class, 'index'])->name('brands.news');
        Route::get('send-message', [MessageSentController::class, 'sendMessageForm'])->name('send.message');
        Route::get('chat-branches', [BranchController::class, 'getBranches'])->name('chat.branches.index');
        Route::get('employees', function () {
            return inertia('Employees/Index', [
                'employees' => auth()->user()->getAccount()->employees()->with('user')->get(),
            ]);
        })->name('employees');

        Route::get(
            '/purchases/{purchase}/confirmation',
            App\Http\Controllers\Account\Orders\PurchaseConfirmationController::class
        )
            ->name('purchases.confirmation');

        Route::any('plans/subscribe/{plan}', SubscriptionController::class)
            ->name('plans.subscribe')
            ->middleware('auth:sanctum');

        Route::post('deposit', InitiateDepositController::class)
            ->name('deposit');
        Route::post('employees.roles', EmployeeRoleController::class)->name('employees.roles');
        Route::post('sending-message', [MessageSentController::class, 'sendMessage'])->name('sending.message');
        Route::post('news-feeds/{post}/like', LikePostController::class)->name('news-feeds.like');
        Route::post('stocks/bulk-add', AddBulkStockController::class)->name('stocks.add-bulk');
        Route::post('actions', [ActionController::class, 'store'])->name('actions.store');
        Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
        Route::post('bulk-cart', BulkAddToCartController::class)->name('bulk.cart');
        Route::post('employee/branches', EmployeeBranchController::class)->name('employee.branches');
        Route::post('special-price/{template_id}/{customer_id}', [CustomerSpecialPriceController::class, 'store'])
            ->name('customer.special-price.store');
        Route::post('orders/add-lines-to-visit', AddOrderToVisitController::class)
            ->name('orders.add-lines-to-visit');
        Route::post(
            'orders/add-multi-orders-to-multi-employees-visits',
            AddMultiOrdersToMultiEmployeesVisitsController::class
        )
            ->name('orders.add-multi-orders-to-multi-employees-visits');

        Route::put('stocks/bulk-update', BulkUpdateStocksController::class)->name('stocks.bulk-update');
        Route::put('stocks/price-locker', PriceLockerController::class)->name('stocks.price-locker');
        Route::put(
            'stocks/highbase-visibility',
            HighbaseVisibilityController::class
        )->name('stocks.highbase-visibility');
        Route::put('/orders/{order}/approve', ApproveOrderController::class)->name('orders.approve');
        Route::put('/orders/{order}/reject', RejectOrderController::class)->name('orders.reject');
        Route::put('/orders/{order}/deliver', OrderDeliveredController::class)->name('orders.deliver');
        Route::put('/orders/{order}/ship', ShipOrderController::class)->name('orders.ship');
        Route::put('/orders/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
        Route::put('auto-assign-orders', AutoAssignOrderController::class)->name('auto-assign-orders');
        Route::put(
            'default-terms/{default_term}',
            [DefaultTermController::class, 'update']
        )->name('default-terms.update');
        Route::put('rfq-published/{rfq_post}', PublishRFQController::class)->name('rfq-published.update');
        Route::put('rfq-status/{rfq_post}', ChangeRFQStatusController::class)->name('rfq-status.update');
        Route::put('actions/{step}', [ActionController::class, 'update'])->name('actions.update');
        Route::put('quote-status/{quote}', [QuoteStatusController::class, 'update'])->name('quote.status');
        Route::put('current-branch/{branch:slug}', CurrentBranchController::class)->name('current-branch');
        Route::put('/payments/{payment}/confirm', ConfirmPaymentController::class)->name('payments.confirm');
        Route::put('/payments/{payment}/reject', RejectPaymentController::class)->name('payments.reject');
        Route::put('credit-settings/{customer}', CreditSettingsController::class)->name('credit-settings.update');
        Route::put('branch-address', UpdateBranchAddressController::class)->withoutMiddleware('verified')
            ->name('branch-address.update');
        Route::put('visits/{visit}/on-the-way', App\Http\Controllers\Account\Visits\OnTheWayController::class)
            ->name('visits.on-the-way');
        Route::put('visits/{visit}/delivered', ConfirmItemsDeliveryController::class)
            ->name('visits.items-delivered');

        Route::resource('roles', RoleController::class);
        Route::resource('branches', BranchController::class);
        Route::get('coupons/search-products', SearchCouponProductsAction::class)->name('coupons.search-products');
        Route::get('coupons/search-customers', SearchCouponCustomersAction::class)->name('coupons.search-customers');
        Route::get('coupons/search-brands', SearchCouponBrandsAction::class)->name('coupons.search-brands');
        Route::get('coupons/search-categories', SearchCouponCategoriesAction::class)->name('coupons.search-categories');
        Route::resource('coupons', CouponController::class);
        Route::get('my-coupons', MyCouponsController::class)->name('my-coupons.index');
        Route::resource('news-feeds', NewsFeedController::class)->except('show', 'create', 'index');
        Route::resource('wallet-transfers', WalletTransferController::class);
        Route::resource('quotes', QuoteController::class);
        Route::resource('cards', CardController::class)->only(['index', 'destroy']);
        Route::resource('products', App\Http\Controllers\Product\ProductController::class);

        Route::get('/employees/search', EmployeeSearchController::class)
            ->name('employees.search');
        Route::get('/customers/search', CustomerSearchController::class)
            ->name('customers.search');
        Route::get('/states/search', StateSearchController::class)
            ->name('states.search');
        Route::get('/cities/search', CitySearchController::class)
            ->name('cities.search');

        Route::resource('employees', App\Http\Controllers\Account\Employee\EmployeeController::class);
        Route::resource('customers', CustomerController::class)
            ->only(['index', 'show'])
            ->parameter('customer', 'branch:slug');
        Route::resource('vendors', VendorController::class);
        Route::resource('carts', CartController::class)->only([
            'store',
            'update',
            'destroy',
        ])->withoutMiddlewareFor('store', 'verified');
        Route::resource('purchases', PurchaseController::class)->only(['index', 'store', 'show']);
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::resource('stocks', StockController::class);
        Route::resource('saved-lists', SavedListController::class)->only('store', 'destroy');
        Route::resource('saved-lists-items', SavedListItemController::class)->only('store', 'destroy');
        Route::resource('progress', ProgressController::class)->except(['show']);
        Route::resource('terms', TermsController::class)->except(['show']);
        Route::resource('quote-order', QuoteOrderController::class);
        Route::resource('quote-rfq', RFQController::class);
        Route::resource('rfq-payment', RFQPaymentController::class);
        Route::resource('proposals', ProposalController::class);
        Route::resource('payments', PaymentController::class)->only([
            'store',
            'destroy',
        ]);
        Route::resource('claims', ClaimController::class);
        Route::resource('pricing', PricingController::class)->only(['index', 'show']);
        Route::resource('special-price-templates', SpecialPriceTemplateController::class)
            ->except(['show']);
        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::get('/commissions/by-order/{order}', [CommissionController::class, 'show'])->name('commissions.show');
        Route::post('/pay-commission', CommissionPaymentController::class)->name('commission.pay');
        Route::resource('visits', AgentVisitController::class);
        Route::resource('schedule-visits', ScheduleVisitController::class);
        Route::resource('employee-visits', EmployeeVisitController::class);
        Route::resource('bank-details', BankDetailsController::class)->only(
            ['store', 'update', 'destroy']
        );

        Route::put('postpone-visit', PostponeVisitController::class)->name('postponed-visit');
        Route::get('visit-timeline', EmployeeVisitTimelineController::class)->name('employee-visit-timeline');
        Route::put(
            'change-visit-date',
            ChangeVisitDateController::class
        )->name('change-visit-date');

        Route::post('employee-visits/{employee_visit}/comments', EmployeeVisitCommentController::class)
            ->name('employee-visits.comments.store');

        Route::resource('instant-orders', InstantOrderController::class)->only(['create', 'store']);

        Route::post('/employees/{employee}/discounts', EmployeeMaxDiscountController::class)
            ->name('employee-discounts.update');

        Route::post('employee-visits/{employee_visit}/confirm', ConfirmEmployeeVisitController::class)
            ->name('employee-visits.confirm');

        Route::post('employee-visits/{employee_visit}/highbase-data', HighbaseVisitDataController::class)
            ->name('employee-visits.highbase-data');

        Route::post(
            'employee-visits/{employee_visit}/attachments',
            [VisitAttachmentController::class, 'store']
        )->name('employee-visits.attachments.store');

        Route::delete(
            'employee-visits/{employee_visit}/attachments/{media}',
            [VisitAttachmentController::class, 'destroy']
        )->name('employee-visits.attachments.destroy');

        Route::get('/stats/{state}/cities', GetCitiesByStateController::class)
            ->name('stats.cities');

        Route::post('complete-profile', CompleteBuyerProfileController::class)->name('complete-profile');

        // Invoice Settings
        Route::put('invoice-settings', [InvoiceSettingsController::class, 'update'])
            ->name('invoice-settings.update');

        Route::post(
            '/employee-visits/{employee_visit}/share',
            [ShareEmployeeVisitController::class, 'store']
        )->name('employee-visits.share');
        Route::delete(
            '/employee-visits/{employee_visit}/share/{employee}',
            [ShareEmployeeVisitController::class, 'destroy']
        )->name('employee-visits.revoke');

        Route::get('/anonymous-customers/suggest', AnonymousCustomerSearchController::class)
            ->name('anonymous-customers.suggest');
    }
);
