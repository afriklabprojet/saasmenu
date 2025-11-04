<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PlanPricingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LangController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\addons\MediaController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\GlobalExtrasController;
use App\Http\Controllers\Admin\StoreCategoryController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OtherPagesController;
use App\Http\Controllers\Admin\SystemAddonsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\FeaturesController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ShippingareaController;
use App\Http\Controllers\Admin\TableBookController;
use App\Http\Controllers\Admin\TimeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\addons\WhatsappmessageController;
use App\Http\Controllers\addons\CinetPayController;
use App\Http\Controllers\Admin\CinetPayController as AdminCinetPayController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\admin\RecaptchaController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\web\HomeController;
use App\Http\Controllers\web\FavoriteController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\web\UserController as WebUserController;
use App\Http\Controllers\landing\HomeController as LandingHomeController;
use App\Http\Controllers\Admin\TableBookingController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Auth\SocialLoginController;

// 🔄 NOUVEAUX CONTRÔLEURS REFACTORISÉS
use App\Http\Controllers\web\CartController;
use App\Http\Controllers\web\OrderController as WebOrderController;
use App\Http\Controllers\web\PromoCodeController;
use App\Http\Controllers\web\PageController;
use App\Http\Controllers\web\ContactController as WebContactController;
use App\Http\Controllers\web\ProductController as WebProductController;
use App\Http\Controllers\web\RefactoredHomeController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



//  ------------------------------- ----------- -----------------------------------------   //
//  -------------------------------  FOR ADMIN  -----------------------------------------   //
//  ------------------------------- ----------- -----------------------------------------   //

// SOCIAL LOGIN - Routes publiques
Route::prefix('auth')->name('social.')->group(function () {
    Route::get('/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('login');
    Route::get('/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('callback');
});

Route::group(['prefix' => 'admin'], function () {
    Route::get('/', [AdminController::class, 'login']);
    Route::post('checklogin-{logintype}', [AdminController::class, 'check_admin_login']);
    Route::get('register', [VendorController::class, 'register']);
    Route::post('register_vendor', [VendorController::class, 'register_vendor']);
    Route::get('forgot_password', [VendorController::class, 'forgot_password']);
    Route::post('send_password', [VendorController::class, 'send_password']);
    Route::post('/getarea', [VendorController::class, 'getarea']);

    Route::get('apps', [SystemAddonsController::class, 'index'])->name('systemaddons');
    Route::get('createsystem-addons', [SystemAddonsController::class, 'createsystemaddons']);
    Route::post('systemaddons/store', [SystemAddonsController::class, 'store']);
    Route::get('systemaddons/status-{id}/{status}', [SystemAddonsController::class, 'change_status']);

    Route::get(
        '/verification',
        function () {
            return view('admin.auth.verify');
        }
    );
    Route::post('systemverification', [AdminController::class, 'systemverification'])->name('admin.systemverification');

    Route::group(
        ['middleware' => 'AuthMiddleware'],
        function () {
            // -------- COMMON --------
            Route::get('admin_back', [VendorController::class, 'admin_back']);
            Route::get('logout', [AdminController::class, 'logout']);
            Route::get('dashboard', [AdminController::class, 'index']);

            // SETTINGS
            Route::post('settings/updaterecaptcha', [RecaptchaController::class, 'updaterecaptcha']);

            Route::get('settings', [SettingsController::class, 'settings_index']);
            Route::post('settings/update', [SettingsController::class, 'settings_update']);
            Route::post('settings/updateseo', [SettingsController::class, 'settings_updateseo']);
            Route::post('settings/updatetheme', [SettingsController::class, 'settings_updatetheme']);
            Route::post('settings/updateanalytics', [SettingsController::class, 'settings_updateanalytics']);
            Route::post('settings/updatecustomedomain', [SettingsController::class, 'settings_updatecustomedomain']);
            Route::post('settings/update-profile-{id}', [VendorController::class, 'update']);
            Route::post('settings/change-password', [VendorController::class, 'change_password']);


            // TRANSACTION
            Route::get('transaction', [TransactionController::class, 'index']);
            Route::get('transaction/plandetails-{id}', [PlanPricingController::class, 'plan_details']);
            Route::get('transaction/generatepdf-{id}', [PlanPricingController::class, 'generatepdf']);

            // WALLET SYSTEM
            Route::group(['prefix' => 'wallet'], function () {
                Route::get('/', [App\Http\Controllers\admin\WalletController::class, 'index'])->name('admin.wallet');
                Route::get('transactions', [App\Http\Controllers\admin\WalletController::class, 'transactions'])->name('admin.wallet.transactions');
                Route::post('withdraw', [App\Http\Controllers\admin\WalletController::class, 'requestWithdrawal'])->name('admin.wallet.withdraw');
                Route::post('add-method', [App\Http\Controllers\admin\WalletController::class, 'addWithdrawalMethod'])->name('admin.wallet.add_method');
                Route::get('method/{id}/verify', [App\Http\Controllers\admin\WalletController::class, 'verifyWithdrawalMethod'])->name('admin.wallet.verify_method');
                Route::delete('method/{id}', [App\Http\Controllers\admin\WalletController::class, 'deleteWithdrawalMethod'])->name('admin.wallet.delete_method');
                Route::get('withdrawal/{id}', [App\Http\Controllers\admin\WalletController::class, 'getWithdrawalDetails'])->name('admin.wallet.withdrawal_details');
            });

            // LOCALISATION FRANÇAISE - multi_language addon
            Route::group(['prefix' => 'localization'], function () {
                Route::get('/', [LocalizationController::class, 'index'])->name('admin.localization');
                Route::post('change-locale', [LocalizationController::class, 'changeLocale'])->name('admin.localization.change');
                Route::get('test-translations', [LocalizationController::class, 'testTranslations'])->name('admin.localization.test');
                Route::get('stats', [LocalizationController::class, 'getStats'])->name('admin.localization.stats');
            });

            // MULTI-LANGUAGE ADDON - Administration
            Route::group(['prefix' => 'multi-language'], function () {
                Route::get('/', [\App\Http\Controllers\Admin\MultiLanguageController::class, 'index'])->name('admin.multi-language.index');
                Route::post('change-language', [\App\Http\Controllers\Admin\MultiLanguageController::class, 'changeLanguage'])->name('admin.multi-language.change');
                Route::get('test', [\App\Http\Controllers\Admin\MultiLanguageController::class, 'testTranslations'])->name('admin.multi-language.test');
            });

            // DOMAINE PERSONNALISÉ - Protection par middleware subscription.limit
            Route::group(['prefix' => 'custom-domain', 'middleware' => 'subscription.limit:custom_domain'], function () {
                Route::get('/', [\App\Http\Controllers\admin\CustomDomainController::class, 'index'])->name('admin.custom-domain');
                Route::post('/', [\App\Http\Controllers\admin\CustomDomainController::class, 'store'])->name('admin.custom-domain.store');
                Route::post('/verify', [\App\Http\Controllers\admin\CustomDomainController::class, 'verify'])->name('admin.custom-domain.verify');
                Route::delete('/', [\App\Http\Controllers\admin\CustomDomainController::class, 'destroy'])->name('admin.custom-domain.destroy');
                Route::get('/dns-instructions', [\App\Http\Controllers\admin\CustomDomainController::class, 'dnsInstructions'])->name('admin.custom-domain.dns');
            });

            // TABLE BOOKING - Réservations de tables
            Route::resource('table-booking', TableBookingController::class, [
                'as' => 'admin',
                'names' => [
                    'index' => 'admin.table-booking.index',
                    'create' => 'admin.table-booking.create',
                    'store' => 'admin.table-booking.store',
                    'show' => 'admin.table-booking.show',
                    'edit' => 'admin.table-booking.edit',
                    'update' => 'admin.table-booking.update',
                    'destroy' => 'admin.table-booking.destroy',
                ]
            ]);
            Route::patch('table-booking/{tableBooking}/status', [TableBookingController::class, 'updateStatus'])
                ->name('admin.table-booking.update-status');

            // SEO - Optimisation moteurs de recherche
            Route::prefix('seo')->name('admin.seo.')->group(function () {
                Route::get('/', [SeoController::class, 'index'])->name('index');
                Route::get('/create/{pageType}/{pageId?}', [SeoController::class, 'createOrEdit'])->name('create');
                Route::post('/store', [SeoController::class, 'store'])->name('store');
                Route::delete('/{id}', [SeoController::class, 'destroy'])->name('destroy');
                Route::get('/generate-sitemap', [SeoController::class, 'generateSitemap'])->name('sitemap');
                Route::get('/generate-robots', [SeoController::class, 'generateRobots'])->name('robots');
            });

            // PLANS
            Route::get('plan', [PlanPricingController::class, 'view_plan']);
            Route::get('/themeimages', [PlanPricingController::class, 'themeimages']);
            // PAYMENT
            Route::group(
                ['prefix' => 'payment'],
                function () {
                    Route::get('/', [PaymentController::class, 'index']);
                    Route::post('update', [PaymentController::class, 'update']);
                    Route::post('/reorder_payment', [PaymentController::class, 'reorder_payment']);
                }
            );

            // CINETPAY SETTINGS
            Route::group(
                ['prefix' => 'cinetpay'],
                function () {
                    Route::get('/', [AdminCinetPayController::class, 'index'])->name('admin.cinetpay.index');
                    Route::put('update', [AdminCinetPayController::class, 'update'])->name('admin.cinetpay.update');
                    Route::post('test', [AdminCinetPayController::class, 'test'])->name('admin.cinetpay.test');
                }
            );

            // inquiries
            Route::get('/inquiries', [OtherPagesController::class, 'inquiries']);
            Route::get('/inquiries/delete-{id}', [OtherPagesController::class, 'inquiries_delete']);

            // customers
            Route::group(['prefix' => 'customers'], function () {
                Route::get('/', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('admin.customers.index');
                Route::get('{id}', [App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('admin.customers.show');
                Route::get('{id}/edit', [App\Http\Controllers\Admin\CustomerController::class, 'edit'])->name('admin.customers.edit');
                Route::put('{id}', [App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('admin.customers.update');
                Route::delete('{id}', [App\Http\Controllers\Admin\CustomerController::class, 'destroy'])->name('admin.customers.destroy');
            });

            // Other Pages
            Route::get('/subscribers', [OtherPagesController::class, 'subscribers']);
            Route::get('/subscribers/delete-{id}', [OtherPagesController::class, 'subscribers_delete']);

            Route::get('privacy-policy', [OtherPagesController::class, 'privacypolicy']);
            Route::get('refund-policy', [OtherPagesController::class, 'refundpolicy']);
            Route::post('refund-policy/update', [OtherPagesController::class, 'refundpolicy_update']);
            Route::post('privacy-policy/update', [OtherPagesController::class, 'privacypolicy_update']);
            Route::get('terms-conditions', [OtherPagesController::class, 'termscondition']);
            Route::post('terms-conditions/update', [OtherPagesController::class, 'termscondition_update']);
            Route::get('aboutus', [OtherPagesController::class, 'aboutus']);
            Route::post('aboutus/update', [OtherPagesController::class, 'aboutus_update']);


            // tax
            Route::group(
                ['prefix' => 'tax'],
                function () {
                    Route::get('/', [TaxController::class, 'index']);
                    Route::get('add', [TaxController::class, 'add']);
                    Route::post('save', [TaxController::class, 'save']);
                    Route::get('edit-{id}', [TaxController::class, 'edit']);
                    Route::post('update-{id}', [TaxController::class, 'update']);
                    Route::get('change_status-{id}/{status}', [TaxController::class, 'change_status']);
                    Route::get('delete-{id}', [TaxController::class, 'delete']);
                    Route::post('reorder_tax', [TaxController::class, 'reorder_tax']);
                }
            );

            Route::post('social_links/update', [SettingsController::class, 'social_links_update']);
            Route::get('settings/delete-sociallinks-{id}', [SettingsController::class, 'delete_sociallinks']);
            Route::post('/orders/customerinfo/', [OrderController::class, 'customerinfo']);
            Route::post('/orders/vendor_note/', [OrderController::class, 'vendor_note']);

            Route::group(['prefix' => 'language-settings'], function () {
                Route::get('/', [LangController::class, 'language']);
            });
            Route::middleware('adminmiddleware')->group(
                function () {
                    Route::get('transaction-{id}-{status}', [TransactionController::class, 'status']);

                    // ANALYTICS
                    Route::group(['prefix' => 'analytics', 'middleware' => 'subscription.limit:analytics'], function () {
                        Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('admin.analytics.dashboard');
                        Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('admin.analytics.revenue');
                        Route::get('/top-selling', [AnalyticsController::class, 'topSelling'])->name('admin.analytics.top-selling');
                        Route::get('/peak-hours', [AnalyticsController::class, 'peakHours'])->name('admin.analytics.peak-hours');
                        Route::get('/customers', [AnalyticsController::class, 'customers'])->name('admin.analytics.customers');
                        Route::get('/categories', [AnalyticsController::class, 'categories'])->name('admin.analytics.categories');
                        Route::get('/compare', [AnalyticsController::class, 'compare'])->name('admin.analytics.compare');
                        Route::get('/export', [AnalyticsController::class, 'export'])->name('admin.analytics.export');
                    });

                    // PLAN
                    Route::group(
                        ['prefix' => 'plan'],
                        function () {
                            Route::get('add', [PlanPricingController::class, 'add_plan']);
                            Route::post('save_plan', [PlanPricingController::class, 'save_plan']);
                            Route::get('edit-{id}', [PlanPricingController::class, 'edit_plan']);
                            Route::post('update_plan-{id}', [PlanPricingController::class, 'update_plan']);
                            Route::get('status_change-{id}/{status}', [PlanPricingController::class, 'status_change']);
                            Route::get('delete-{id}', [PlanPricingController::class, 'delete']);
                            Route::post('reorder_plan', [PlanPricingController::class, 'reorder_plan']);
                        }
                    );
                    // VENDORS
                    Route::group(
                        ['prefix' => 'users'],
                        function () {
                            Route::get('/', [VendorController::class, 'index']);
                            Route::get('add', [VendorController::class, 'add']);
                            Route::get('edit-{slug}', [VendorController::class, 'edit']);
                            Route::post('update-{slug}', [VendorController::class, 'update']);
                            Route::get('status-{slug}/{status}', [VendorController::class, 'status']);
                            Route::get('login-{slug}', [VendorController::class, 'vendor_login']);
                            Route::post('/store/page/is_allow', [VendorController::class, 'is_allow']);
                            Route::get('delete-{slug}', [VendorController::class, 'deletevendor']);
                        }
                    );

                    //   FAQs
                    Route::group(
                        ['prefix' => 'faqs'],
                        function () {
                            Route::get('/', [OtherPagesController::class, 'faq_index']);
                            Route::get('/add', [OtherPagesController::class, 'faq_add']);
                            Route::post('/save', [OtherPagesController::class, 'faq_save']);
                            Route::get('/edit-{id}', [OtherPagesController::class, 'faq_edit']);
                            Route::post('/update-{id}', [OtherPagesController::class, 'faq_update']);
                            Route::get('/delete-{id}', [OtherPagesController::class, 'faq_delete']);
                            Route::post('/reorder_faq', [OtherPagesController::class, 'reorder_faq']);
                        }
                    );

                    //features
                    Route::group(
                        ['prefix' => 'features'],
                        function () {
                            Route::get('/', [FeaturesController::class, 'index']);
                            Route::get('/add', [FeaturesController::class, 'add']);
                            Route::post('/save', [FeaturesController::class, 'save']);
                            Route::get('/edit-{id}', [FeaturesController::class, 'edit']);
                            Route::post('/update-{id}', [FeaturesController::class, 'update']);
                            Route::get('/delete-{id}', [FeaturesController::class, 'delete']);
                            Route::post('/reorder_features', [FeaturesController::class, 'reorder_features']);
                        }
                    );

                    //testimonial
                    Route::group(
                        ['prefix' => 'testimonials'],
                        function () {
                            Route::get('/', [TestimonialController::class, 'index']);
                            Route::get('/add', [testimonialController::class, 'add']);
                            Route::post('/save', [testimonialController::class, 'save']);
                            Route::get('/edit-{id}', [testimonialController::class, 'edit']);
                            Route::post('/update-{id}', [testimonialController::class, 'update']);
                            Route::get('/delete-{id}', [testimonialController::class, 'delete']);
                            Route::post('/reorder_testimonials', [testimonialController::class, 'reorder_testimonials']);
                        }
                    );

                    // citys
                    Route::group(
                        ['prefix' => 'cities'],
                        function () {
                            Route::get('/', [OtherPagesController::class, 'cities']);
                            Route::get('/add', [OtherPagesController::class, 'add_city']);
                            Route::post('/save', [OtherPagesController::class, 'save_city']);
                            Route::get('/edit-{id}', [OtherPagesController::class, 'edit_city']);
                            Route::post('/update-{id}', [OtherPagesController::class, 'update_city']);
                            Route::get('/delete-{id}', [OtherPagesController::class, 'delete_city']);
                            Route::get('/change_status-{id}/{status}', [OtherPagesController::class, 'statuschange_city']);
                            Route::post('/reorder_city', [OtherPagesController::class, 'reorder_city']);
                        }
                    );

                    // areas
                    Route::group(
                        ['prefix' => 'areas'],
                        function () {
                            Route::get('/', [OtherPagesController::class, 'areas']);
                            Route::get('/add', [OtherPagesController::class, 'add_area']);
                            Route::post('/save', [OtherPagesController::class, 'save_area']);
                            Route::get('/edit-{id}', [OtherPagesController::class, 'edit_area']);
                            Route::post('/update-{id}', [OtherPagesController::class, 'update_area']);
                            Route::get('/delete-{id}', [OtherPagesController::class, 'delete_area']);
                            Route::get('/change_status-{id}/{status}', [OtherPagesController::class, 'statuschange_area']);
                            Route::post('/reorder_area', [OtherPagesController::class, 'reorder_area']);
                        }
                    );
                    // promotional banner
                    Route::group(
                        ['prefix' => 'promotionalbanners'],
                        function () {
                            Route::get('/', [BannerController::class, 'promotional_banner']);
                            Route::get('add', [BannerController::class, 'promotional_banneradd']);
                            Route::get('edit-{id}', [BannerController::class, 'promotional_banneredit']);
                            Route::post('save', [BannerController::class, 'promotional_bannersave_banner']);
                            Route::post('update-{id}', [BannerController::class, 'promotional_bannerupdate']);
                            Route::get('delete-{id}', [BannerController::class, 'promotional_bannerdelete']);
                            Route::post('reorder_promotionalbanner', [BannerController::class, 'reorder_promotionalbanner']);
                        }
                    );
                    // STORE CATEGORIES
                    Route::group(
                        ['prefix' => 'store_categories'],
                        function () {
                            Route::get('/', [StoreCategoryController::class, 'index']);
                            Route::get('add', [StoreCategoryController::class, 'add_category']);
                            Route::post('save', [StoreCategoryController::class, 'save_category']);
                            Route::get('edit-{id}', [StoreCategoryController::class, 'edit_category']);
                            Route::post('update-{id}', [StoreCategoryController::class, 'update_category']);
                            Route::get('change_status-{id}/{status}', [StoreCategoryController::class, 'change_status']);
                            Route::get('delete-{id}', [StoreCategoryController::class, 'delete_category']);
                            Route::post('/reorder_category', [StoreCategoryController::class, 'reorder_category']);
                        }
                    );

                    Route::group(['prefix' => 'language-settings'], function () {
                        Route::post('/update', [LangController::class, 'storeLanguageData']);
                        Route::get('/language/edit-{id}', [LangController::class, 'edit']);
                        Route::post('/update-{id}', [LangController::class, 'update']);
                        Route::get('/layout/delete-{id}/{status}', [LangController::class, 'delete']);
                        Route::get('/status-{id}/{status}', [LangController::class, 'status']);
                    });

                    Route::post('/landingsettings', [SettingsController::class, 'landingsettings']);
                }
            );
            Route::middleware('VendorMiddleware')->group(
                function () {
                    // OTHERS
                    Route::get('settings/delete-banner', [SettingsController::class, 'delete_viewall_page_image']);
                    Route::get('settings/delete-feature-{id}', [SettingsController::class, 'delete_feature']);
                    Route::get('share', [OtherPagesController::class, 'share']);
                    Route::get('getorder', [NotificationController::class, 'getorder']);
                    Route::post('app_section/update', [SettingsController::class, 'app_section']);
                    // TIME
                    Route::group(
                        ['prefix' => 'time'],
                        function () {
                            Route::get('/', [TimeController::class, 'index']);
                            Route::post('store', [TimeController::class, 'store']);
                        }
                    );
                    // ORDERS
                    Route::get('/report', [OrderController::class, 'index']);

                    // Whatesapp settings
                    Route::post('settings/whatsapp_update', [WhatsappmessageController::class, 'whatsapp_update']);

                    Route::group(
                        ['prefix' => 'orders'],
                        function () {
                            Route::get('/', [OrderController::class, 'index']);
                            Route::get('/update-{id}-{status}-{type}', [OrderController::class, 'update']);
                            Route::get('/invoice/{order_number}', [OrderController::class, 'invoice']);
                            Route::get('/print/{order_number}', [OrderController::class, 'print']);
                            Route::post('/payment_status-{status}', [OrderController::class, 'payment_status']);
                            Route::get('/generatepdf/{order_number}', [OrderController::class, 'generatepdf']);
                        }
                    );
                    // CATEGORIES
                    Route::group(
                        ['prefix' => 'categories'],
                        function () {
                            Route::get('/', [CategoryController::class, 'index']);
                            Route::get('add', [CategoryController::class, 'add_category'])->middleware('subscription.limit:categories');
                            Route::post('save', [CategoryController::class, 'save_category'])->middleware('subscription.limit:categories');
                            Route::get('edit-{slug}', [CategoryController::class, 'edit_category']);
                            Route::post('update-{slug}', [CategoryController::class, 'update_category']);
                            Route::get('change_status-{slug}/{status}', [CategoryController::class, 'change_status']);
                            Route::get('delete-{slug}', [CategoryController::class, 'delete_category']);
                            Route::post('reorder_category', [CategoryController::class, 'reorder_category']);
                        }
                    );
                    // SHIPPING-AREA
                    Route::group(
                        ['prefix' => 'shipping-area'],
                        function () {
                            Route::get('/', [ShippingareaController::class, 'index']);
                            Route::get('add', [ShippingareaController::class, 'add']);
                            Route::get('show-{id}', [ShippingareaController::class, 'show']);
                            Route::post('store', [ShippingareaController::class, 'store']);
                            Route::post('update-{id}', [ShippingareaController::class, 'store']);
                            Route::get('status-{id}-{status}', [ShippingareaController::class, 'status']);
                            Route::get('delete-{id}', [ShippingareaController::class, 'delete']);
                            Route::post('/reorder_shippingarea', [ShippingareaController::class, 'reorder_shippingarea']);
                        }
                    );
                    // PRODUCTS - Protection par middleware subscription.limit
                    Route::group(
                        ['prefix' => 'products'],
                        function () {
                            Route::get('/', [ProductController::class, 'index']);
                            Route::get('add', [ProductController::class, 'add'])->middleware('subscription.limit:products');
                            Route::post('save', [ProductController::class, 'save'])->middleware('subscription.limit:products');
                            Route::get('edit-{slug}', [ProductController::class, 'edit']);
                            Route::post('update-{slug}', [ProductController::class, 'update_product']);
                            Route::post('updateimage', [ProductController::class, 'update_image']);
                            Route::post('storeimages', [ProductController::class, 'store_image']);
                            Route::post('destroyimage', [ProductController::class, 'destroyimage']);
                            Route::get('status-{slug}/{status}', [ProductController::class, 'status']);
                            Route::get('delete/variation-{id}-{product_id}', [ProductController::class, 'delete_variation']);
                            Route::get('delete/extras-{id}', [ProductController::class, 'delete_extras']);
                            Route::get('delete-{slug}', [ProductController::class, 'delete_product']);
                            Route::post('/product-variants-possibilities/{product_id}', [ProductController::class, 'getProductVariantsPossibilities']);
                            Route::get('/get-product-variants-possibilities', [ProductController::class, 'getProductVariantsPossibilities']);
                            Route::get('/variants/edit/{product_id}', [ProductController::class, 'productVariantsEdit']);
                            Route::post('reorder_product', [ProductController::class, 'reorder_product']);
                            Route::post('/reorder_image-{item_id}', [ProductController::class, 'reorder_image']);
                        }
                    );

                    // extras
                    Route::get('/getextras', [GlobalExtrasController::class, 'getextras']);
                    Route::get('/editgetextras-{id}', [GlobalExtrasController::class, 'editgetextras']);
                    Route::group(
                        ['prefix' => 'extras'],
                        function () {
                            Route::get('/', [GlobalExtrasController::class, 'index']);
                            Route::get('/add', [GlobalExtrasController::class, 'add']);
                            Route::post('/save', [GlobalExtrasController::class, 'save']);
                            Route::get('/edit-{id}', [GlobalExtrasController::class, 'edit']);
                            Route::post('/update-{id}', [GlobalExtrasController::class, 'update']);
                            Route::get('/change_status-{id}/{status}', [GlobalExtrasController::class, 'change_status']);
                            Route::get('delete-{id}', [GlobalExtrasController::class, 'delete']);
                            Route::post('/reorder_extras', [GlobalExtrasController::class, 'reorder_extras']);
                        }
                    );
                    // Media
                    Route::group(
                        ['prefix' => 'media'],
                        function () {
                            Route::get('/', [MediaController::class, 'index'])->name('admin.media.index');
                            Route::post('/add_image', [MediaController::class, 'add_image'])->name('admin.media.upload');
                            Route::get('delete-{id}', [MediaController::class, 'delete_media'])->name('admin.media.delete');
                            Route::get('download-{id}', [MediaController::class, 'download'])->name('admin.media.download');
                        }
                    );
                    // PLAN
                    Route::group(
                        ['prefix' => 'plan'],
                        function () {
                            Route::get('selectplan-{id}', [PlanPricingController::class, 'select_plan']);
                            Route::post('buyplan', [PlanPricingController::class, 'buyplan']);
                            Route::any('buyplan/paymentsuccess/success', [PlanPricingController::class, 'success']);
                        }
                    );
                    // BANNERS
                    Route::group(
                        ['prefix' => 'banner'],
                        function () {
                            Route::get('/', [BannerController::class, 'index'])->name('banner');
                            Route::get('/add', [BannerController::class, 'add']);
                            Route::post('/store', [BannerController::class, 'store']);
                            Route::get('/edit-{id}', [BannerController::class, 'show']);
                            Route::post('/update-{id}', [BannerController::class, 'update']);
                            Route::get('/delete-{id}', [BannerController::class, 'delete']);
                            Route::post('/reorder_banner', [BannerController::class, 'reorder_banner']);
                        }
                    );

                    Route::group(['prefix' => 'language-settings'], function () {
                        Route::get('/languagestatus-{code}/{status}', [LangController::class, 'languagestatus']);
                        Route::get('/setdefault-{code}/{status}', [LangController::class, 'setdefault']);
                    });
                    Route::get('/booking', [TableBookController::class, 'index']);

                    // FORMATION ÉQUIPE ADMIN
                    Route::group(['prefix' => 'training'], function () {
                        Route::get('/', [App\Http\Controllers\Admin\TrainingController::class, 'index'])->name('admin.training.index');
                        Route::post('/start', [App\Http\Controllers\Admin\TrainingController::class, 'start'])->name('admin.training.start');
                        Route::get('/session/{sessionId}', [App\Http\Controllers\Admin\TrainingController::class, 'session'])->name('admin.training.session');
                        Route::get('/session/{sessionId}/content', [App\Http\Controllers\Admin\TrainingController::class, 'getSectionContent'])->name('admin.training.content');
                        Route::post('/session/{sessionId}/evaluate', [App\Http\Controllers\Admin\TrainingController::class, 'submitEvaluation'])->name('admin.training.evaluate');
                        Route::get('/session/{sessionId}/certificate', [App\Http\Controllers\Admin\TrainingController::class, 'generateCertificate'])->name('admin.training.certificate');
                        Route::get('/schedule', [App\Http\Controllers\Admin\TrainingController::class, 'schedule'])->name('admin.training.schedule');
                        Route::post('/schedule', [App\Http\Controllers\Admin\TrainingController::class, 'scheduleTraining'])->name('admin.training.schedule.store');
                        Route::get('/reports', [App\Http\Controllers\Admin\TrainingController::class, 'reports'])->name('admin.training.reports');
                        Route::get('/export', [App\Http\Controllers\Admin\TrainingController::class, 'export'])->name('admin.training.export');
                    });
                }
            );
        }
    );
});


Route::get('login/google', [VendorController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [VendorController::class, 'handleGoogleCallback']);
Route::get('login/facebook', [VendorController::class, 'redirectToFacebook']);
Route::get('login/facebook/callback', [VendorController::class, 'handleFacebookCallback']);


//  ------------------------------- ----------- -----------------------------------------   //
//  -------------------------------  FOR WEB/FRONT  -------------------------------------   //
//  ------------------------------- ----------- -----------------------------------------   //

Route::group(['namespace' => '', 'middleware' => 'landingMiddleware'], function () {
    Route::get('/', [LandingHomeController::class, 'index']);
    Route::post('/emailsubscribe', [LandingHomeController::class, 'emailsubscribe']);
    Route::post('/inquiry', [LandingHomeController::class, 'inquiry']);

    Route::get('/about_us', [LandingHomeController::class, 'about_us']);
    Route::get('/privacy_policy', [LandingHomeController::class, 'privacy_policy']);
    Route::get('/terms_condition', [LandingHomeController::class, 'terms_condition']);
    Route::get('/refund_policy', [LandingHomeController::class, 'refund_policy']);
    Route::get('/faqs', [LandingHomeController::class, 'faqs']);

    Route::get('/contact', [LandingHomeController::class, 'contact']);
    Route::get('/stores', [LandingHomeController::class, 'allstores']);
    Route::get('/blog_list', [LandingHomeController::class, 'blogs']);
    Route::get('/blog_details-{id}', [LandingHomeController::class, 'blogs_details']);
    Route::post('/getarea', [VendorController::class, 'getarea']);
});


$domain = env('WEBSITE_HOST');
$parsedUrl = parse_url(url()->current());
$host = $parsedUrl['host'];
if (array_key_exists('host', $parsedUrl)) {
    // if it is a path based URL (localhost, 127.0.0.1, or WEBSITE_HOST)
    if (
        $host == env('WEBSITE_HOST') ||
        strpos($host, 'localhost') !== false ||
        strpos($host, '127.0.0.1') !== false
    ) {
        $domain = $domain;
        $prefix = '{vendor}';
    }
    // if it is a subdomain / custom domain
    else {
        $prefix = '';
    }
}

// 🔄 ROUTES REFACTORISÉES - REMPLACENT L'ANCIEN HomeController

// Routes produits
Route::post('/product-details', [WebProductController::class, 'details'])->name('front.details');

// Routes commandes
Route::post('/orders/checkplan', [RefactoredHomeController::class, 'checkPlan'])->name('front.checkplan');
Route::post('/orders/paymentmethod', [WebOrderController::class, 'create'])->name('front.whatsapporder');

// Routes panier (nouvelles)
Route::post('add-to-cart', [CartController::class, 'addToCart'])->name('front.addtocart');
Route::post('/cart/qtyupdate', [CartController::class, 'updateQuantity'])->name('front.qtyupdate');
Route::post('/cart/deletecartitem', [CartController::class, 'removeItem'])->name('front.deletecartitem');

Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');

// Routes panier supplémentaires
Route::post('/changeqty', [CartController::class, 'updateQuantity']);
Route::get('get-products-variant-quantity', [WebProductController::class, 'getVariations']);

Route::group(['prefix' => $prefix, 'middleware' => 'FrontMiddleware'], function () {

    // 🏠 PAGE D'ACCUEIL ET NAVIGATION
    Route::get('/', [RefactoredHomeController::class, 'index'])->name('front.home');
    Route::get('/categories', [RefactoredHomeController::class, 'categories'])->name('front.categories');

    // 🛒 PANIER ET PRODUITS
    Route::get('/product/{id}', [WebProductController::class, 'details'])->name('front.product');
    Route::get('/cart', [CartController::class, 'cart'])->name('front.cart');
    Route::get('/search', [WebProductController::class, 'search']);

    // 📦 COMMANDES
    Route::get('/checkout', [WebOrderController::class, 'checkout'])->name('front.checkout');
    Route::get('/cancel-order/{ordernumber}', [WebOrderController::class, 'cancel'])->name('front.cancelorder');
    Route::any('/payment', [WebOrderController::class, 'create']);

    // 📄 PAGES STATIQUES
    Route::get('/terms', [PageController::class, 'termsConditions'])->name('front.terms');
    Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('front.privacy');
    Route::get('/privacypolicy', [PageController::class, 'privacyPolicy']);
    Route::get('/refundprivacypolicy', [PageController::class, 'refundPrivacyPolicy']);
    Route::get('/terms_condition', [PageController::class, 'termsConditions']);
    Route::get('/aboutus', [PageController::class, 'aboutUs']);

    // 📞 CONTACT ET RÉSERVATIONS
    Route::get('/book', [WebContactController::class, 'tableBook'])->name('front.book');
    Route::post('/subscribe', [WebContactController::class, 'subscribe']);

    // 📦 COMMANDES ET SUIVI
    Route::get('/track-order/{ordernumber}', [WebOrderController::class, 'track'])->name('front.trackorder');
    Route::get('/success', [WebOrderController::class, 'track'])->name('front.success');
    Route::get('/success/{order_number}', [WebOrderController::class, 'success']);

    // ⏰ HORAIRES ET AUTRES
    Route::post('/timeslot', [RefactoredHomeController::class, 'getTimeslot']);


    Route::get('/login', [WebUserController::class, 'user_login']);
    Route::post('/checklogin-{logintype}', [WebUserController::class, 'check_login']);
    Route::get('/register', [WebUserController::class, 'user_register']);
    Route::get('/forgotpassword', [WebUserController::class, 'userforgotpassword']);

    Route::post('/send_password', [WebUserController::class, 'send_password']);

    Route::post('/register_customer', [WebUserController::class, 'register_customer']);
    Route::get('/logout', [WebUserController::class, 'logout']);

    Route::get('/profile', [WebUserController::class, 'profile']);
    Route::post('/updateprofile', [WebUserController::class, 'updateprofile']);

    Route::get('/change-password', [WebUserController::class, 'changepassword']);
    Route::post('/change_password', [WebUserController::class, 'change_password']);

    Route::get('/orders', [WebUserController::class, 'orders']);
    Route::get('/loyality', [WebUserController::class, 'loyality']);

    //CONTACTS - REFACTORISÉ
    Route::get('/contact', [WebContactController::class, 'contact']);
    Route::post('/submit', [WebContactController::class, 'saveContact']);


    //RÉSERVATIONS DE TABLES - REFACTORISÉ
    Route::get('/tablebook', [WebContactController::class, 'tableBook']);
    Route::post('/book', [WebContactController::class, 'saveBooking']);

    // PAGES REDONDANTES (déjà définies plus haut)
    Route::get('/terms_condition', [PageController::class, 'termsConditions']);
    Route::get('/privacypolicy', [PageController::class, 'privacyPolicy']);

    // favorite
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('user-favouritelist');
    Route::post('/managefavorite', [FavoriteController::class, 'managefavorite']);

    Route::get('/delete-password', [WebUserController::class, 'deletepassword']);
    Route::get('/deleteaccount', [WebUserController::class, 'deleteaccount']);

    // PRODUITS - REFACTORISÉ
    Route::get('/topdeals', [WebProductController::class, 'topDeals']);

    // 🆕 NOUVELLES ROUTES API REFACTORISÉES
    // Routes AJAX pour fonctionnalités avancées
    Route::prefix('api')->group(function () {
        // Panier API
        Route::post('/cart/add', [CartController::class, 'addToCart'])->name('api.cart.add');
        Route::patch('/cart/update', [CartController::class, 'updateQuantity'])->name('api.cart.update');
        Route::delete('/cart/remove', [CartController::class, 'removeItem'])->name('api.cart.remove');

        // Codes promo API
        Route::post('/promo/apply', [PromoCodeController::class, 'apply'])->name('api.promo.apply');
        Route::delete('/promo/remove', [PromoCodeController::class, 'remove'])->name('api.promo.remove');
        Route::get('/promo/available', [PromoCodeController::class, 'getAvailable'])->name('api.promo.available');

        // Produits API
        Route::get('/products/category/{category_id}', [WebProductController::class, 'getByCategory'])->name('api.products.category');
        Route::get('/products/{item_id}/variations', [WebProductController::class, 'getVariations'])->name('api.products.variations');
        Route::post('/products/check-availability', [WebProductController::class, 'checkAvailability'])->name('api.products.availability');
        Route::get('/products/featured', [WebProductController::class, 'getFeatured'])->name('api.products.featured');

        // Commandes API
        Route::post('/orders/track', [WebOrderController::class, 'track'])->name('api.orders.track');

        // Contact API
        Route::get('/booking/timeslots', [WebContactController::class, 'getAvailableTimeSlots'])->name('api.booking.timeslots');

        // Pages API
        Route::post('/pages/content', [PageController::class, 'getPageContent'])->name('api.pages.content');
        Route::get('/pages/available', [PageController::class, 'getAvailablePages'])->name('api.pages.available');
    });

    // CinetPay routes
    Route::get('/cinetpay/return', [CinetPayController::class, 'return'])->name('cinetpay.return');

    /*
    |--------------------------------------------------------------------------
    | Customer Account Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/customer/dashboard', [\App\Http\Controllers\CustomerAccountController::class, 'index'])->name('customer.dashboard');

        // Profile
        Route::get('/customer/profile', [\App\Http\Controllers\CustomerAccountController::class, 'profile'])->name('customer.profile');
        Route::post('/customer/profile/update', [\App\Http\Controllers\CustomerAccountController::class, 'updateProfile'])->name('customer.profile.update');
        Route::post('/customer/password/change', [\App\Http\Controllers\CustomerAccountController::class, 'changePassword'])->name('customer.password.change');

        // Orders
        Route::get('/customer/orders', [\App\Http\Controllers\CustomerAccountController::class, 'orders'])->name('customer.orders');
        Route::get('/customer/orders/{id}', [\App\Http\Controllers\CustomerAccountController::class, 'orderDetails'])->name('customer.order.details');
        Route::post('/customer/orders/{id}/reorder', [\App\Http\Controllers\CustomerAccountController::class, 'reorder'])->name('customer.order.reorder');
        Route::post('/customer/orders/{id}/cancel', [\App\Http\Controllers\CustomerAccountController::class, 'cancelOrder'])->name('customer.order.cancel');

        // Addresses
        Route::get('/customer/addresses', [\App\Http\Controllers\CustomerAccountController::class, 'addresses'])->name('customer.addresses');
        Route::post('/customer/addresses/store', [\App\Http\Controllers\CustomerAccountController::class, 'storeAddress'])->name('customer.address.store');
        Route::post('/customer/addresses/{id}/update', [\App\Http\Controllers\CustomerAccountController::class, 'updateAddress'])->name('customer.address.update');
        Route::delete('/customer/addresses/{id}/delete', [\App\Http\Controllers\CustomerAccountController::class, 'deleteAddress'])->name('customer.address.delete');

        // Wishlist
        Route::get('/customer/wishlist', [\App\Http\Controllers\CustomerAccountController::class, 'wishlist'])->name('customer.wishlist');
        Route::post('/customer/wishlist/add', [\App\Http\Controllers\CustomerAccountController::class, 'addToWishlist'])->name('customer.wishlist.add');
        Route::delete('/customer/wishlist/{id}/remove', [\App\Http\Controllers\CustomerAccountController::class, 'removeFromWishlist'])->name('customer.wishlist.remove');
        Route::delete('/customer/wishlist/clear', [\App\Http\Controllers\CustomerAccountController::class, 'clearWishlist'])->name('customer.wishlist.clear');
    });

    // TABLE BOOKING - Routes publiques pour clients
    Route::get('/{vendor_slug}/reserver-une-table', [TableBookingController::class, 'customerCreate'])
        ->name('customer.table-booking.create');
    Route::post('/{vendor_slug}/reserver-une-table', [TableBookingController::class, 'customerStore'])
        ->name('customer.table-booking.store');
});

// CinetPay webhook (outside domain-based routes)
Route::post('/cinetpay/notify', [CinetPayController::class, 'notify'])->name('cinetpay.notify');
Route::post('/cinetpay/init', [CinetPayController::class, 'initPayment'])->name('cinetpay.init');

// PWA routes
require __DIR__ . '/pwa.php';

// Language switching routes
Route::get('/lang/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');
Route::get('/api/lang/current', [App\Http\Controllers\LanguageController::class, 'current'])->name('lang.current');
