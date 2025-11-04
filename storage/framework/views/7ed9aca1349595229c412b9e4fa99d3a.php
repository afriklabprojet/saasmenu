<?php
    if (Auth::user()->type == 4) {
        $role_id = Auth::user()->role_id;
        $vendor_id = Auth::user()->vendor_id;
    } else {
        $role_id = '';
        $vendor_id = Auth::user()->id;
    }
    $user = App\Models\User::where('id', $vendor_id)->first();
?>
<ul class="navbar-nav mx-3">
    <li class="nav-item fs-7 <?php echo e(helper::check_menu($role_id, 'role_dashboard') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center  <?php echo e(request()->is('admin/dashboard') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('admin/dashboard')); ?>">
            <span class="<?php echo e(request()->is('admin/dashboard') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <!-- <i class="fa-solid fa-house-user"></i> -->
                <i class="fa-solid fa-desktop"></i>
            </span>
            <span class="px-2"><?php echo e(trans('labels.dashboard')); ?></span>
        </a>
    </li>

    
    <?php if(Auth::user()->type == 2): ?>
    <li class="nav-item fs-7">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/custom-domain*') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('/admin/custom-domain')); ?>">
            <span class="<?php echo e(request()->is('admin/custom-domain*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-globe"></i>
            </span>
            <span class="px-2">🌐 Mon Domaine</span>
            <?php if(Auth::user()->custom_domain && Auth::user()->domain_verified): ?>
                <span class="badge bg-success ms-2">✓</span>
            <?php elseif(Auth::user()->custom_domain): ?>
                <span class="badge bg-warning ms-2">!</span>
            <?php endif; ?>
        </a>
    </li>
    <?php endif; ?>

    <?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
        <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_orders') == 1 || helper::check_menu($role_id, 'role_report') == 1 || helper::check_menu($role_id, 'role_pos') == 1 ? 'd-block' : 'd-none'); ?>">
            <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.orders_management')); ?></h6>
        </li>

        <?php if(App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1): ?>

            <?php if(App\Models\SystemAddons::where('unique_identifier', 'pos')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'pos')->first()->activated == 1): ?>

                <?php

                    if (Auth::user()->allow_without_subscription == 1) {
                        $pos = 1;
                    } else {
                        $pos = @helper::get_plan(Auth::user()->id)->pos;
                    }

                ?>

                <?php if($pos == 1): ?>
                    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_pos') == 1 ? 'd-block' : 'd-none'); ?>">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/pos*') ? 'active' : ''); ?>"
                            aria-current="page" href="<?php echo e(URL::to('admin/pos')); ?>">
                            <span class="<?php echo e(request()->is('admin/pos*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                                <!-- <i class="fa-solid fa-users"></i> -->
                                <i class="fa-solid fa-cash-register"></i>
                            </span>
                            <span class="nav-text px-2"><?php echo e(trans('labels.pos')); ?></span>
                            <?php if(env('Environment') == 'sendbox'): ?>
                                <span
                                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

            <?php endif; ?>
        <?php else: ?>
            <?php if(App\Models\SystemAddons::where('unique_identifier', 'pos')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'pos')->first()->activated == 1): ?>
                <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_pos') == 1 ? 'd-block' : 'd-none'); ?>">
                    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/pos*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('admin/pos')); ?>">
                        <span class="<?php echo e(request()->is('admin/pos*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                            <!-- <i class="fa-solid fa-users"></i> -->
                            <i class="fa-solid fa-cash-register"></i>
                        </span>
                        <span class="nav-text px-2"><?php echo e(trans('labels.pos')); ?></span>
                        <?php if(env('Environment') == 'sendbox'): ?>
                            <span
                                class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_orders') == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex d-flex align-items-center <?php echo e(request()->is('admin/orders*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/orders')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/orders*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-cart-shopping"></i> -->
                    <i class="fa-solid fa-list-check"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.orders')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_report') == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex d-flex align-items-center <?php echo e(request()->is('admin/report*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/report')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/report*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-chart-mixed"></i> -->
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.report')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_report') == 1 || Auth::user()->type == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex d-flex align-items-center <?php echo e(request()->is('admin/analytics*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/analytics/dashboard')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/analytics*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <span class="nav-text px-2">📊 <?php echo e(trans('labels.analytics') ?? 'Analytics'); ?></span>
            </a>
        </li>
    <?php endif; ?>

    <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_customers') == 1 ? 'd-block' : 'd-none'); ?>">
        <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.user_management')); ?></h6>
    </li>

    <?php if(Auth::user()->type == '1'): ?>
    <li class="nav-item mb-2 fs-7">
        <a class="nav-link d-flex align-items-center  <?php echo e(request()->is('admin/users*') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('admin/users')); ?>">
            <span class="<?php echo e(request()->is('admin/users*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-user-plus"></i>
            </span>
            <span class="px-2">
                <?php echo e(trans('labels.users')); ?>

            </span>
        </a>
    </li>
<?php endif; ?>
<?php if(App\Models\SystemAddons::where('unique_identifier', 'customer_login')->first() != null &&
        App\Models\SystemAddons::where('unique_identifier', 'customer_login')->first()->activated == 1): ?>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_customers') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center  <?php echo e(request()->is('admin/customers*') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('admin/customers')); ?>">
            <span class="<?php echo e(request()->is('admin/customers') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-users"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.customers')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span class="badge badge bg-danger float-right mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>
<?php endif; ?>

<?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
<li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_categories') == 1 || helper::check_menu($role_id, 'role_products') == 1 || helper::check_menu($role_id, 'role_global_extras') == 1 || helper::check_menu($role_id, 'role_import_product') == 1 ? 'd-block' : 'd-none'); ?>">
    <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.product_managment')); ?></h6>
</li>
<li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_categories') == 1 ? 'd-block' : 'd-none'); ?>">
    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/categories*') ? 'active' : ''); ?>"
        aria-current="page" href="<?php echo e(URL::to('admin/categories')); ?>">
        <span class="<?php echo e(request()->is('admin/categories*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
            <i class="fa-sharp fa-solid fa-list"></i>
        </span>
        <span class="px-2"><?php echo e(trans('labels.categories')); ?></span>
    </a>
</li>
<li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_tax') == 1 ? 'd-block' : 'd-none'); ?>">
    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/tax*') ? 'active' : ''); ?>"
        aria-current="page" href="<?php echo e(URL::to('admin/tax')); ?>">
        <span class="<?php echo e(request()->is('admin/tax*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
            <i
            class="fa-solid fa-money-check-dollar"></i>
        </span>
        <span class="px-2"><?php echo e(trans('labels.tax')); ?></span>
    </a>
</li>
<li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_global_extras') == 1 ? 'd-block' : 'd-none'); ?>">
    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/extras*') ? 'active' : ''); ?>"
        aria-current="page" href="<?php echo e(URL::to('admin/extras')); ?>">
        <span class="<?php echo e(request()->is('admin/extras*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
            <i class="fa-sharp fa-solid fa-planet-moon"></i>
        </span>
        <span class="px-2"><?php echo e(trans('labels.global_extras')); ?></span>
    </a>
</li>

<li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_products') == 1 ? 'd-block' : 'd-none'); ?>">
    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/products*') && !request()->is('admin/products/import') ? 'active' : ''); ?>"
        aria-current="page" href="<?php echo e(URL::to('admin/products')); ?>">
        <span class="<?php echo e(request()->is('admin/products*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
            <!-- <i class="fa-solid fa-list-timeline"></i> -->
            <i class="fa-solid fa-layer-group"></i>
        </span>
        <span class="px-2"><?php echo e(trans('labels.products')); ?></span>
    </a>
</li>
<?php if(App\Models\SystemAddons::where('unique_identifier', 'product_import')->first() != null &&
        App\Models\SystemAddons::where('unique_identifier', 'product_import')->first()->activated == 1): ?>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_import_product') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/products/import') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('/admin/products/import')); ?>">
            <span
                class="<?php echo e(request()->is('/admin/products/import') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-layer-group"></i>
            </span>
            <span class="px-2"><?php echo e(trans('labels.product_upload')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span
                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>
<?php endif; ?>
<?php endif; ?>
<li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_banner') == 1 || helper::check_menu($role_id, 'role_coupons') == 1 || helper::check_menu($role_id, 'role_top_deals') == 1 || helper::check_menu($role_id, 'role_firebase_notification') == 1 ? 'd-block' : 'd-none'); ?>">
    <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.promotions')); ?></h6>
</li>
<?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_banner') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/banner*') ? 'active' : ''); ?>"
            href="<?php echo e(URL::to('/admin/banner')); ?>" aria-expanded="false">
            <span class="<?php echo e(request()->is('admin/banner*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-image"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.banner')); ?></span>
        </a>
    </li>
<?php endif; ?>

<?php if(App\Models\SystemAddons::where('unique_identifier', 'coupon')->first() != null &&
        App\Models\SystemAddons::where('unique_identifier', 'coupon')->first()->activated == 1): ?>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_coupons') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/coupons*') ? 'active' : ''); ?>"
            href="<?php echo e(URL::to('/admin/coupons')); ?>" aria-expanded="false">
            <span class="<?php echo e(request()->is('admin/coupons*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-badge-percent"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.coupons')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>
<?php endif; ?>

<?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
    <?php if(App\Models\SystemAddons::where('unique_identifier', 'top_deals')->first() != null &&
            App\Models\SystemAddons::where('unique_identifier', 'top_deals')->first()->activated == 1): ?>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_top_deals') == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/top_deals*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/top_deals')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/top_deals*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-image"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.top_deals')); ?></span>
                <?php if(env('Environment') == 'sendbox'): ?>
                    <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                <?php endif; ?>
            </a>
        </li>


    <?php endif; ?>
    <?php if(App\Models\SystemAddons::where('unique_identifier', 'firebase_notification')->first() != null &&
            App\Models\SystemAddons::where('unique_identifier', 'firebase_notification')->first()->activated == 1): ?>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_firebase_notification') == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/notification*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/notification')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/notification*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-tags"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.firebase_notification')); ?></span>
                <?php if(env('Environment') == 'sendbox'): ?>
                    <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                <?php endif; ?>
            </a>
        </li>

    <?php endif; ?>
<?php endif; ?>
<?php if(App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1): ?>

<?php if(App\Models\SystemAddons::where('unique_identifier', 'tableqr')->first() != null &&
    App\Models\SystemAddons::where('unique_identifier', 'tableqr')->first()->activated == 1): ?>

<?php

    if ($user->allow_without_subscription == 1) {
        $tableqr = 1;
    } else {
        $tableqr = @helper::get_plan($vendor_id)->tableqr;
    }

?>

<?php if($tableqr == 1): ?>

    <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_tableqr') == 1 || helper::check_menu($role_id, 'role_area') == 1  ? 'd-block' : 'd-none'); ?>">
        <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3">
            <?php echo e(trans('labels.table_management')); ?></h6>
    </li>


    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_tableqr') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/tableqr*') ? 'active' : ''); ?>"
            href="<?php echo e(URL::to('/admin/tableqr')); ?>" aria-expanded="false">
            <span
                class="<?php echo e(request()->is('admin/tableqr*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-qrcode"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.tableqr')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span
                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>

    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_area') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/area*') ? 'active' : ''); ?>"
            href="<?php echo e(URL::to('/admin/area')); ?>" aria-expanded="false">
            <span class="<?php echo e(request()->is('admin/area*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-location-dot"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.area')); ?></span>
        </a>
    </li>

<?php endif; ?>

<?php endif; ?>
<?php else: ?>
<?php if(App\Models\SystemAddons::where('unique_identifier', 'tableqr')->first() != null &&
    App\Models\SystemAddons::where('unique_identifier', 'tableqr')->first()->activated == 1): ?>


<li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_tableqr') == 1 || helper::check_menu($role_id, 'role_area') == 1  ? 'd-block' : 'd-none'); ?>">
    <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.table_management')); ?>

    </h6>
</li>

<li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_tableqr') == 1  ? 'd-block' : 'd-none'); ?>">
    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/tableqr*') ? 'active' : ''); ?>"
        href="<?php echo e(URL::to('/admin/tableqr')); ?>" aria-expanded="false">
        <span class="<?php echo e(request()->is('admin/tableqr*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
            <i class="fa-solid fa-qrcode"></i>
        </span>
        <span class="nav-text px-2"><?php echo e(trans('labels.tableqr')); ?></span>
        <?php if(env('Environment') == 'sendbox'): ?>
            <span
                class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
        <?php endif; ?>
    </a>
</li>

<li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_area') == 1  ? 'd-block' : 'd-none'); ?>">
    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/area*') ? 'active' : ''); ?>"
        href="<?php echo e(URL::to('/admin/area')); ?>" aria-expanded="false">
        <span class="<?php echo e(request()->is('admin/area*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
            <i class="fa-solid fa-location-dot"></i>
        </span>
        <span class="nav-text px-2"><?php echo e(trans('labels.area')); ?></span>
        <?php if(env('Environment') == 'sendbox'): ?>
            <span
                class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
        <?php endif; ?>
    </a>
</li>

<?php endif; ?>
<?php endif; ?>
    <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_pricing_plans') == 1 || helper::check_menu($role_id, 'role_transaction') == 1 || helper::check_menu($role_id, 'role_payment_methods') == 1 || helper::check_menu($role_id, 'role_shipping_area') == 1 || helper::check_menu($role_id, 'role_working_hours') == 1 || helper::check_menu($role_id, 'role_custom_domains') == 1  ||  helper::check_menu($role_id, 'role_google_analytics') == 1  ? 'd-block' : 'd-none'); ?>">
        <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.business_management')); ?></h6>
    </li>

    <?php if(Auth::user()->type == 1 ||
            ((Auth::user()->type == 2 || Auth::user()->type == 4) &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)): ?>

        <?php if(Auth::user()->type == 1): ?>
            <li class="nav-item mb-2 fs-7">
                <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/tax*') ? 'active' : ''); ?>"
                    aria-current="page" href="<?php echo e(URL::to('/admin/tax')); ?>">

                    <span class="<?php echo e(request()->is('admin/plan') ? 'sidebariconbox' : 'sidebariconbox1'); ?>"><i
                            class="fa-solid fa-money-check-dollar"></i></span>
                    <span class="px-2"><?php echo e(trans('labels.tax')); ?></span>

                </a>
            </li>
        <?php endif; ?>
        <?php if($user->allow_without_subscription != 1 || Auth::user()->type == 1): ?>
            <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_pricing_plans') == 1  ? 'd-block' : 'd-none'); ?>">
                <a class="nav-link d-flex align-items-center  <?php echo e(request()->is('admin/plan*') ? 'active' : ''); ?>"
                    aria-current="page" href="<?php echo e(URL::to('admin/plan')); ?>">

                    <span class="<?php echo e(request()->is('admin/plan') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <!-- <i class="fa-solid fa-medal"></i> -->
                        <i class="fa-solid fa-bell"></i>
                    </span>
                    <span class="px-2"><?php echo e(trans('labels.pricing_plans')); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_transaction') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/transaction') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/transaction')); ?>">
                <span class="<?php echo e(request()->is('admin/transaction') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.transaction')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_payment_methods') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center  <?php echo e(request()->is('admin/payment') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/payment')); ?>">

                <span class="<?php echo e(request()->is('admin/payment') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-money-check-dollar-pen"></i> -->
                    <i class="fa-solid fa-hand-holding-dollar"></i>

                </span>
                <span class="px-2"><?php echo e(trans('labels.payment_methods')); ?></span>
            </a>
        </li>
    <?php endif; ?>

    <?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
        <!-- WALLET SYSTEM pour les restaurants -->
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/wallet*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/wallet')); ?>">
                <span class="<?php echo e(request()->is('admin/wallet*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-wallet"></i>
                </span>
                <span class="px-2">Mon Wallet</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_shipping_area') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/shipping-area*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/shipping-area')); ?>">
                <span class="<?php echo e(request()->is('admin/shipping-area*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.shipping_area')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_working_hours') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/time*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/time')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/time*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-business-time"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.working_hours')); ?></span>
            </a>
        </li>
    <?php endif; ?>

    <?php if(Auth::user()->type == 1): ?>
        <li class="nav-item mb-2 fs-7 dropdown multimenu">
            <a class="nav-link collapsed d-flex align-items-center  justify-content-between dropdown-toggle mb-1 <?php echo e(request()->is('admin/cities*') || request()->is('admin/areas*') ? 'active' : ''); ?>"
                href="#location" data-bs-toggle="collapse" role="button" aria-expanded="false"
                aria-controls="location">
                <div class="d-flex align-items-center">
                    <span
                        class=" <?php echo e(request()->is('admin/cities*') || request()->is('admin/areas*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </span>
                    <span class="multimenu-title px-2"><?php echo e(trans('labels.location')); ?></span>
                </div>
            </a>
            <ul class="collapse" id="location">
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link  <?php echo e(request()->is('admin/cities*') ? 'active' : ''); ?>" aria-current="page"
                        href="<?php echo e(URL::to('/admin/cities')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator">
                            <i class="fa-solid fa-circle-small"></i>

                            <?php echo e(trans('labels.cities')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/areas*') ? 'active' : ''); ?>" aria-current="page"
                        href="<?php echo e(URL::to('/admin/areas')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.areas')); ?></span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/store_categories*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/store_categories')); ?>">
                <span class="<?php echo e(request()->is('admin/store_categories*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-sharp fa-solid fa-list"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.store_categories')); ?></span>
            </a>
        </li>


    <?php endif; ?>
    <?php if(Auth::user()->type == 1): ?>
        <?php if(App\Models\SystemAddons::where('unique_identifier', 'custom_domain')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'custom_domain')->first()->activated == 1): ?>
            <li class="nav-item mb-2 fs-7">
                <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/custom_domain*') ? 'active' : ''); ?>"
                    aria-current="page" href="<?php echo e(URL::to('/admin/custom_domain')); ?>">

                    <span class="<?php echo e(request()->is('admin/custom_domain') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <i class="fa-solid fa-globe"></i>
                    </span>

                    <span class="nav-text px-2"><?php echo e(trans('labels.custom_domains')); ?></span>
                    <?php if(env('Environment') == 'sendbox'): ?>
                        <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                    <?php endif; ?>

                </a>
            </li>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(Auth::user()->type == 2 || Auth::user()->type == 4): ?>
        <?php if(App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1): ?>
            <?php if(App\Models\SystemAddons::where('unique_identifier', 'custom_domain')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'custom_domain')->first()->activated == 1): ?>
                <?php
                    $checkplan = App\Models\Transaction::where('vendor_id', $vendor_id)
                        ->orderByDesc('id')
                        ->first();
                    if ($user->allow_without_subscription == 1) {
                        $custom_domain = 1;
                    } else {
                        $custom_domain = @$checkplan->custom_domain;
                    }

                ?>
                <?php if($custom_domain == 1): ?>
                    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_custom_domains') == 1  ? 'd-block' : 'd-none'); ?>">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/custom_domain*') ? 'active' : ''); ?>"
                            aria-current="page" href="<?php echo e(URL::to('/admin/custom_domain')); ?>">
                            <span
                                class="<?php echo e(request()->is('admin/custom_domain*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                                <i class="fa-solid fa-globe"></i>
                                <!-- <i class="fa-solid fa-link"></i> -->
                            </span>
                            <span class="nav-text px-2"><?php echo e(trans('labels.custom_domains')); ?></span>
                            <?php if(env('Environment') == 'sendbox'): ?>
                                <span
                                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if(App\Models\SystemAddons::where('unique_identifier', 'custom_domain')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'custom_domain')->first()->activated == 1): ?>
                <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_custom_domains') == 1  ? 'd-block' : 'd-none'); ?>">
                    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/custom_domain*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/custom_domain')); ?>">
                        <span
                            class="<?php echo e(request()->is('admin/custom_domain*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                            <i class="fa-solid fa-globe"></i>
                            <!-- <i class="fa-solid fa-link"></i> -->
                        </span>
                        <span class="nav-text px-2"><?php echo e(trans('labels.custom_domains')); ?></span>
                        <?php if(env('Environment') == 'sendbox'): ?>
                            <span
                                class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(Auth::user()->type == '2' || Auth::user()->type == 4): ?>
        <?php if(App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1): ?>
            <?php if(App\Models\SystemAddons::where('unique_identifier', 'google_analytics')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'google_analytics')->first()->activated == 1): ?>
                <?php
                    $checkplan = App\Models\Transaction::where('vendor_id', $vendor_id)
                        ->orderByDesc('id')
                        ->first();
                    if ($user->allow_without_subscription == 1) {
                        $google_analytics = 1;
                    } else {
                        $google_analytics = @$checkplan->google_analytics;
                    }

                ?>
                <?php if($google_analytics == 1): ?>
                    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_google_analytics') == 1  ? 'd-block' : 'd-none'); ?>">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/google_analytics*') ? 'active' : ''); ?>"
                            aria-current="page" href="<?php echo e(URL::to('/admin/google_analytics')); ?>">
                            <span
                                class="<?php echo e(request()->is('admin/google_analytics*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                                <i class="fa-solid fa-bar-chart"></i>
                            </span>
                            <span class="nav-text px-2"><?php echo e(trans('labels.google_analytics')); ?></span>
                            <?php if(env('Environment') == 'sendbox'): ?>
                                <span
                                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if(App\Models\SystemAddons::where('unique_identifier', 'google_analytics')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'google_analytics')->first()->activated == 1): ?>
                <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_google_analytics') == 1  ? 'd-block' : 'd-none'); ?>">
                    <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/google_analytics*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/google_analytics')); ?>">
                        <span
                            class="<?php echo e(request()->is('admin/google_analytics*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                            <i class="fa-solid fa-bar-chart"></i>
                        </span>
                        <span class="nav-text px-2"><?php echo e(trans('labels.google_analytics')); ?></span>
                        <?php if(env('Environment') == 'sendbox'): ?>
                            <span
                                class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>

        <?php endif; ?>


        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_booking') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/booking*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/booking')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/booking*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-list-check"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.booking')); ?></span>
            </a>
        </li>

        <?php if(App\Models\SystemAddons::where('unique_identifier', 'custom_status')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'custom_status')->first()->activated == 1): ?>
            <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_custom_status') == 1  ? 'd-block' : 'd-none'); ?>">
                <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/custom_status*') ? 'active' : ''); ?>"
                    href="<?php echo e(URL::to('/admin/custom_status')); ?>" aria-expanded="false">
                    <span class="<?php echo e(request()->is('admin/custom_status*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <i class="fa-regular fa-clipboard-list-check"></i>
                    </span>
                    <span class="nav-text px-2"><?php echo e(trans('labels.custom_status')); ?></span>
                    <?php if(env('Environment') == 'sendbox'): ?>
                        <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                    <?php endif; ?>
                </a>
            </li>

        <?php endif; ?>
    <?php endif; ?>


    <?php if(App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
    App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1): ?>

<?php if(App\Models\SystemAddons::where('unique_identifier', 'employee')->first() != null &&
        App\Models\SystemAddons::where('unique_identifier', 'employee')->first()->activated == 1): ?>
    <?php

        $checkplan = App\Models\Transaction::where('vendor_id', $vendor_id)->orderByDesc('id')->first();

        if ($user->allow_without_subscription == 1) {
            $role_management = 1;
        } else {
            $role_management = @$checkplan->role_management;
        }

    ?>
    <?php if($role_management == 1): ?>
        
        <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_employees') == 1 || helper::check_menu($role_id, 'role_roles') == 1 ? 'd-block' : 'd-none'); ?>">
            <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.employee_management')); ?>

            </h6>
        </li>

        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_roles') == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/roles*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/roles')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/roles*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-user-secret"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.roles')); ?></span>
                <?php if(env('Environment') == 'sendbox'): ?>
                    <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_employees') == 1 ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/employees*') ? 'active' : ''); ?>"
                href="<?php echo e(URL::to('/admin/employees')); ?>" aria-expanded="false">
                <span class="<?php echo e(request()->is('admin/employees*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa fa-users"></i>
                </span>
                <span class="nav-text px-2"><?php echo e(trans('labels.employee')); ?></span>
                <?php if(env('Environment') == 'sendbox'): ?>
                    <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                <?php endif; ?>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
<?php else: ?>
<?php if(App\Models\SystemAddons::where('unique_identifier', 'employee')->first() != null &&
        App\Models\SystemAddons::where('unique_identifier', 'employee')->first()->activated == 1): ?>
    
    <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_employees') == 1 || helper::check_menu($role_id, 'role_roles') == 1 ? 'd-block' : 'd-none'); ?>">
        <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.employee_management')); ?>

        </h6>
    </li>

    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_roles') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/roles*') ? 'active' : ''); ?>"
            href="<?php echo e(URL::to('/admin/roles')); ?>" aria-expanded="false">
            <span class="<?php echo e(request()->is('admin/roles*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa-solid fa-user-secret"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.roles')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_employees') == 1 ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/employees*') ? 'active' : ''); ?>"
            href="<?php echo e(URL::to('/admin/employees')); ?>" aria-expanded="false">
            <span class="<?php echo e(request()->is('admin/employees*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <i class="fa fa-users"></i>
            </span>
            <span class="nav-text px-2"><?php echo e(trans('labels.employee')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>
<?php endif; ?>
<?php endif; ?>

    <?php if(Auth::user()->type == 1): ?>
        
        <li class="nav-item mt-3">
            <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.landing_page')); ?></h6>
        </li>
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/features*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('/admin/features')); ?>">
                <span class="<?php echo e(request()->is('admin/features') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-list"></i> -->
                    <i class="fa-solid fa-lightbulb"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.features')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/promotionalbanners*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('/admin/promotionalbanners')); ?>">
                <span class="<?php echo e(request()->is('admin/promotionalbanners') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-bullhorn"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.promotional_banners')); ?></span>
            </a>
        </li>

        <?php if(App\Models\SystemAddons::where('unique_identifier', 'blog')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'blog')->first()->activated == 1): ?>

            <li class="nav-item mb-2 fs-7">
                <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/blogs*') ? 'active' : ''); ?>"
                    aria-current="page" href="<?php echo e(URL::to('/admin/blogs')); ?>">
                    <span class="<?php echo e(request()->is('admin/blogs*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <i class="fa-solid fa-blog"></i>
                    </span>
                    <span class="nav-text px-2"><?php echo e(trans('labels.blogs')); ?></span>
                    <?php if(env('Environment') == 'sendbox'): ?>
                        <span class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                    <?php endif; ?>
                </a>
            </li>

        <?php endif; ?>
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/faqs*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('/admin/faqs')); ?>">
                <span class="<?php echo e(request()->is('admin/faqs*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-question"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.faqs')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/testimonials*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('/admin/testimonials')); ?>">
                <span class="<?php echo e(request()->is('admin/testimonials*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-comment-dots"></i> -->
                    <i class="fa-solid fa-star"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.testimonials')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/subscribers*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/subscribers')); ?>">
                <span class="<?php echo e(request()->is('admin/subscribers*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-envelope"></i> -->
                    <i class="fa-solid fa-envelope-open-text"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.subscribers')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/inquiries*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/inquiries')); ?>">

                <span class="<?php echo e(request()->is('admin/inquiries*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-id-badge"></i>
                    <!-- <i class="fa-solid fa-solid fa-address-book"></i> -->
                </span>
                <span class="px-2"><?php echo e(trans('labels.inquiries')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 dropdown multimenu">
            <a class="nav-link collapsed d-flex align-items-center justify-content-between dropdown-toggle mb-1 <?php echo e(request()->is('admin/`priv`acy-policy*') || request()->is('admin/terms-conditions*') || request()->is('admin/aboutus*') ? 'active' : ''); ?>"
                href="#pages" data-bs-toggle="collapse" role="button" aria-expanded="false"
                aria-controls="pages">
                <div class="d-flex align-items-center">
                    <span
                        class="<?php echo e(request()->is('admin/privacy-policy*') || request()->is('admin/terms-conditions*') || request()->is('admin/aboutus*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <!-- <i class="fa-solid fa-file-lines"></i> -->
                        <i class="fa-regular fa-file-lines"></i>
                    </span>
                    <span class="multimenu-title px-2"><?php echo e(trans('labels.cms_pages')); ?></span>
                </div>
            </a>
            <ul class="collapse" id="pages">
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/privacy-policy*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/privacy-policy')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.privacypolicy')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/refund-policy*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/refund-policy')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.refund_policy')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/terms-conditions*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/terms-conditions')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.terms')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/aboutus*') ? 'active' : ''); ?>" aria-current="page"
                        href="<?php echo e(URL::to('/admin/aboutus')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.about')); ?></span>
                    </a>
                </li>


            </ul>
        </li>
    <?php endif; ?>
    <li class="nav-item mt-3 <?php echo e(helper::check_menu($role_id, 'role_blogs') == 1 || helper::check_menu($role_id, 'role_subscribers') == 1 || helper::check_menu($role_id, 'role_inquiries') == 1  || helper::check_menu($role_id, 'role_cms_pages') == 1 || helper::check_menu($role_id, 'role_share') == 1 || helper::check_menu($role_id, 'role_settings') == 1  ? 'd-block' : 'd-none'); ?>">
        <h6 class="text-dark fw-500 mb-2 fs-7 text-uppercase mx-3"><?php echo e(trans('labels.other')); ?></h6>
    </li>
    <?php if(Auth::user()->type == '2' || Auth::user()->type == 4 ): ?>

        <?php if(App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1): ?>


            <?php if(App\Models\SystemAddons::where('unique_identifier', 'blog')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'blog')->first()->activated == 1): ?>

                <?php

                    if ($user->allow_without_subscription == 1) {
                        $blogs = 1;
                    } else {
                        $blogs = @helper::get_plan($vendor_id)->blogs;
                    }

                ?>

                <?php if($blogs == 1): ?>
                    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_blogs') == 1  ? 'd-block' : 'd-none'); ?>">
                        <a class="nav-link d-flex  align-items-center <?php echo e(request()->is('admin/blogs*') ? 'active' : ''); ?>"
                            aria-current="page" href="<?php echo e(URL::to('/admin/blogs')); ?>">
                            <span class="<?php echo e(request()->is('admin/blogs*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                                <i class="fa-solid fa-blog"></i>
                            </span>
                            <span class="nav-text px-2"><?php echo e(trans('labels.blogs')); ?></span>
                            <?php if(env('Environment') == 'sendbox'): ?>
                                <span
                                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

            <?php endif; ?>
        <?php else: ?>
            <?php if(App\Models\SystemAddons::where('unique_identifier', 'blog')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'blog')->first()->activated == 1): ?>

                <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_blogs') == 1  ? 'd-block' : 'd-none'); ?>">
                    <a class="nav-link d-flex  align-items-center <?php echo e(request()->is('admin/blogs*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/blogs')); ?>">
                        <span class="<?php echo e(request()->is('admin/blogs*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                            <i class="fa-solid fa-blog"></i>
                        </span>
                        <span class="nav-text px-2"><?php echo e(trans('labels.blogs')); ?></span>
                        <?php if(env('Environment') == 'sendbox'): ?>
                            <span
                                class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
                        <?php endif; ?>
                    </a>
                </li>

            <?php endif; ?>

        <?php endif; ?>

        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_subscribers') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/subscribers*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/subscribers')); ?>">
                <span class="<?php echo e(request()->is('admin/subscribers*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.subscribers')); ?></span>
            </a>
        </li>

        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_inquiries') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/inquiries*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/inquiries')); ?>">
                <span class="<?php echo e(request()->is('admin/inquiries*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-solid fa-address-book"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.inquiries')); ?></span>
            </a>
        </li>
        <li class="nav-item mb-2 fs-7 dropdown multimenu <?php echo e(helper::check_menu($role_id, 'role_cms_pages') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link collapsed d-flex align-items-center justify-content-between dropdown-toggle mb-1 <?php echo e(request()->is('admin/privacy-policy*') || request()->is('admin/terms-conditions*') || request()->is('admin/aboutus*') ? 'active' : ''); ?>"
                href="#pages" data-bs-toggle="collapse" role="button" aria-expanded="false"
                aria-controls="pages">
                <div class="d-flex align-items-center <?php echo e(helper::check_menu($role_id, 'role_cms_pages') == 1  ? 'd-block' : 'd-none'); ?>">
                    <span
                        class="<?php echo e(request()->is('admin/privacy-policy*') || request()->is('admin/terms-conditions*') || request()->is('admin/aboutus*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                        <!-- <i class="fa-solid fa-file-lines"></i> -->
                        <i class="fa-regular fa-file-lines"></i>
                    </span>
                    <span class="multimenu-title px-2"><?php echo e(trans('labels.cms_pages')); ?></span>
                </div>
            </a>
            <ul class="collapse" id="pages">
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/privacy-policy*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/privacy-policy')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.privacypolicy')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/refund-policy*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/refund-policy')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.refund_policy')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/terms-conditions*') ? 'active' : ''); ?>"
                        aria-current="page" href="<?php echo e(URL::to('/admin/terms-conditions')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.terms')); ?></span>
                    </a>
                </li>
                <li class="nav-item ps-4 mb-1">
                    <a class="nav-link <?php echo e(request()->is('admin/aboutus*') ? 'active' : ''); ?>" aria-current="page"
                        href="<?php echo e(URL::to('/admin/aboutus')); ?>">
                        <span class="d-flex align-items-center multimenu-menu-indicator"><i
                                class="fa-solid fa-circle-small"></i><?php echo e(trans('labels.about')); ?></span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_share') == 1  ? 'd-block' : 'd-none'); ?>">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/share*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('admin/share')); ?>">
                <span class="<?php echo e(request()->is('admin/share*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <i class="fa-solid fa-share-from-square"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.share')); ?></span>
            </a>
        </li>
    <?php endif; ?>
    <?php if((Auth::user()->type == '2' || Auth::user()->type == 4) && helper::listoflanguage()->count() > 1): ?>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_language_settings') == 1  ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/language-settings') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('admin/language-settings')); ?>">
            <span class="<?php echo e(request()->is('admin/language-settings') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <!-- <i class="fa-solid fa-gear"></i> -->
                <i class="fa-solid fa-language"></i>
            </span>
            <span class="px-2"><?php echo e(trans('labels.language-settings')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span
                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>


<?php endif; ?>
    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_settings') == 1  ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/settings') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('admin/settings')); ?>">
            <span class="<?php echo e(request()->is('admin/settings') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <!-- <i class="fa-solid fa-gear"></i> -->
                <i class="fa-solid fa-gears"></i>
            </span>
            <span class="px-2"><?php echo e(trans('labels.settings')); ?></span>
        </a>
    </li>

    <?php if(Auth::user()->type == '1'): ?>

    <li class="nav-item mb-2 fs-7 <?php echo e(helper::check_menu($role_id, 'role_language_settings') == 1  ? 'd-block' : 'd-none'); ?>">
        <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/language-settings') ? 'active' : ''); ?>"
            aria-current="page" href="<?php echo e(URL::to('admin/language-settings')); ?>">
            <span class="<?php echo e(request()->is('admin/language-settings') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                <!-- <i class="fa-solid fa-gear"></i> -->
                <i class="fa-solid fa-language"></i>
            </span>
            <span class="px-2"><?php echo e(trans('labels.language-settings')); ?></span>
            <?php if(env('Environment') == 'sendbox'): ?>
                <span
                    class="badge badge bg-danger float-right mr-1 mt-1"><?php echo e(trans('labels.addon')); ?></span>
            <?php endif; ?>
        </a>
    </li>

        <li class="nav-item mb-2 fs-7">
            <a class="nav-link d-flex align-items-center <?php echo e(request()->is('admin/apps*') ? 'active' : ''); ?>"
                aria-current="page" href="<?php echo e(URL::to('/admin/apps')); ?>">
                <span class="<?php echo e(request()->is('admin/apps*') ? 'sidebariconbox' : 'sidebariconbox1'); ?>">
                    <!-- <i class="fa-solid fa-rocket"></i> -->
                    <i class="fa-solid fa-puzzle-piece"></i>
                </span>
                <span class="px-2"><?php echo e(trans('labels.addons_manager')); ?></span>
            </a>
        </li>
    <?php endif; ?>
</ul>
<?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/layout/sidebarcommon.blade.php ENDPATH**/ ?>