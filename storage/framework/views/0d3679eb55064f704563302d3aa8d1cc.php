<!DOCTYPE html>
<html lang="en" dir="<?php echo e(session()->get('direction') == 2 ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="<?php echo e(helper::appdata('')->meta_title); ?>" />
    <meta property="og:description" content="<?php echo e(helper::appdata('')->meta_description); ?>" />
    <meta property="og:image" content='<?php echo e(helper::image_path(helper::appdata('')->og_image)); ?>' />
    <?php if(config('app.env') === 'production'): ?>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <?php endif; ?>

    <!-- Base URL for asset paths -->
    <base href="<?php echo e(url('/')); ?>/">

    <link rel="icon" type="image" sizes="16x16" href="<?php echo e(helper::image_path(helper::appdata('')->favicon)); ?>"><!-- Favicon icon -->
    <title><?php echo e(helper::appdata('')->landing_website_title); ?></title>
    <!-- Font Awesome icon css-->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/all.min.css')); ?>">

    <!-- owl carousel css -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/owl.carousel.min.css')); ?>">

    <!-- owl carousel css -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/owl.theme.default.min.css')); ?>">

    <!-- Poppins fonts -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/fonts/poppins.css')); ?>">

    <!-- bootstrap-icons css -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/bootstrap-icons.css')); ?>">

    <!-- bootstrap css -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/bootstrap.min.css')); ?>">

    <!-- style css -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/style.css')); ?>">

    <!-- responsive css -->

    <link rel="stylesheet" href="<?php echo e(asset(env('ASSETSPATHURL').'landing/css/responsive.css')); ?>">
    <style>
        :root {

            /* Color */
            --bs-primary: <?php echo e(helper::appdata('')->primary_color); ?>;
            --bs-secondary : <?php echo e(helper::appdata('')->secondary_color); ?>;

        }

        /* Custom Animations and Styles */
        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .key-features .card {
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .key-features .card:hover {
            transform: scale(1.02);
        }

        /* Gradient Backgrounds */
        .gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #667eea 100%);
        }

        .gradient-success {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Hero Section Enhancement */
        .home-banner {
            min-height: 500px;
            display: flex;
            align-items: center;
        }

        .banner-title {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CTA Button Enhancement */
        .btn-secondary {
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        /* Stats Counter Animation */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fun-fact-number {
            animation: countUp 0.6s ease forwards;
        }

        /* Accordion Styles */
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: var(--bs-primary);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }

        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230d6efd'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        /* Statistics Section Styles */
        .statistics .stat-card {
            transition: transform 0.3s ease;
        }

        .statistics .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-number {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        /* Why Choose Us Section */
        .why-choose-us .card:hover {
            transform: translateY(-5px);
        }

        .why-choose-us .feature-icon {
            transition: all 0.3s ease;
        }

        .why-choose-us .card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Trust Badges Section */
        .trust-badges {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .trust-item {
            transition: all 0.3s ease;
            padding: 15px;
            border-radius: 10px;
        }

        .trust-item:hover {
            transform: translateY(-5px);
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .trust-item i {
            transition: all 0.3s ease;
        }

        .trust-item:hover i {
            transform: scale(1.2) rotate(5deg);
        }

        /* Contact Form Enhancements */
        .contact .form-control:focus {
            background-color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
            border-color: var(--bs-primary);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #667eea 100%);
        }

        .hover-primary:hover {
            color: var(--bs-primary) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 2rem !important;
            }

            .banner-title {
                font-size: 1.75rem !important;
            }

            .display-5 {
                font-size: 1.5rem !important;
            }

            .trust-item h6 {
                font-size: 0.85rem;
            }

            .trust-item i {
                font-size: 1.5rem !important;
            }
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body>
    <?php echo $__env->make('landing.layout.preloader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('landing.layout.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div>

        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <?php echo $__env->make('landing.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Modal -->
    <div class="d-flex align-items-center float-end">
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content search-modal-content rounded-5">
                    <div class="modal-header border-0 px-4 align-items-center">
                        <h3 class="page-title mb-0 d-block d-md-none">search</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 mb-0">
                        <form class="" action="<?php echo e(URL::to('stores')); ?>" method="get">

                            <div class="col-12">
                                <div class="row align-items-center justify-content-between">

                                    <div class="col-6 d-none d-lg-block">
                                        <div class="Search-left-img">
                                            <img src="<?php echo e(url(env('ASSETSPATHURL').'landing/images/search.webp')); ?>" alt="search-left-img" class="w-100 object-fit-cover search-left-img">
                                        </div>
                                    </div>


                                    <div class="col-12 col-lg-6">
                                        <div class="search-content text-capitalize">
                                            <h4 class="fs-2 text-dark fw-bolder mb-2 d-none d-md-block"><?php echo e(trans('labels.search')); ?></h4>
                                            <p class="fs-6"><?php echo e(trans('labels.search_title')); ?></p>
                                        </div>
                                    <div class="select-input-box">
                                        <select name="store" class="py-2 input-width px-2 mt-4 mb-1 w-100 border rounded-5 fs-7" id="store">
                                            <option value=""><?php echo e(trans('landing.select_store_category')); ?></option>
                                            <?php $__currentLoopData = @helper::storecategory(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($store->name); ?>"  <?php echo e(request()->get('store') == $store->name ? 'selected' : ''); ?>><?php echo e($store->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                        <select name="city" id="city" class="py-2 input-width px-2 mt-2 mb-1 w-100 border rounded-5 fs-7">
                                            <option value="" data-value="<?php echo e(URL::to('/stores?city=' . '&area=' . request()->get('area'))); ?>" data-id="0" selected><?php echo e(trans('landing.select_city')); ?></option>

                                            <?php $__currentLoopData = helper::get_city(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($city->name); ?>" data-value="<?php echo e(URL::to('/stores?city=' . request()->get('city') . '&area=' . request()->get('area'))); ?>" data-id=<?php echo e($city->id); ?> <?php echo e(request()->get('city') == $city->name ? 'selected' : ''); ?>><?php echo e($city->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>

                                        <select name="area" id="area" class="py-2 input-width px-2 mt-2 mb-1 w-100 border rounded-5 fs-7">
                                            <option value=""><?php echo e(trans('landing.select_area')); ?></option>
                                            <?php if(request()->get('area')): ?>
                                            <option value="<?php echo e(request()->get('area')); ?>" selected><?php echo e(request()->get('area')); ?></option>
                                            <?php endif; ?>


                                        </select>

                                        <div class="search-btn-group">
                                            <div class="d-flex justify-content-between align-items-center mt-5">
                                                <a type="submit" class="btn-primary bg-danger w-100 rounded-3 rounded-3 m-1 text-center" data-bs-dismiss="modal"><?php echo e(trans('labels.cancel')); ?> </a>
                                                <input type="submit" class="btn-primary w-100 rounded-3 rounded-3 m-1 text-center" value="<?php echo e(trans('labels.submit')); ?>" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(env('Environment') == 'sendbox'): ?>
    <button type="button" class="demo_label main-button border-0 d-none" data-bs-toggle="modal" data-bs-target="#demo-modal">
    <i class="fa fa-info-circle"></i>
    <span>Note</span></button>
    <?php endif; ?>
    <!--Modal: order-modal-->
    <div class="modal fade" id="demo-modal" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-notify modal-info" role="document">
            <div class="modal-content text-center">
                <div class="modal-header d-flex justify-content-center">
                    <h5>Script License Information</h5>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row mb-3">
                            <div class="col-md-6 border-line text-danger">
                                <h4>Regular License</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    You can not create subscription plans
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    You can not charge your end customers using subscription plans
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-success">
                                <h4 class="mt-3 mt-sm-0">Extended License</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    You can create subscription plans
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    You can charge your end customers using subscription plans
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <h5 class="mb-3">Script Installation & Configuration Service</h5>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6 border-line text-danger">
                                <h4>Regular License</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    One time installation service (cPanel OR Plesk based hosting server) : $49
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    One time installation service (Any other hosting server) : Contact us for pricing
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-success">
                                <h4 class="mt-3 mt-sm-0">Extended License</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    One time installation service (cPanel OR Plesk based hosting server) : FREE
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    One time installation service (Any other hosting server) : Contact us for pricing
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <h5 class="mb-3">Script Addons Information</h5>
                        <p class="text-info">(We have installed all addons in the demo script. You will get the addons as mentioned below)</p>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4 border-line text-danger">
                                <h4>Regular License</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    No addons available
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 border-line ms-auto text-success">
                                <h4 class="mt-3 mt-sm-0">Extended License</h4>
                                <small class="text-primary">(You will get below mentioned 7 addons free)</small>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Google Analytics
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Customer Login
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Blogs
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Language
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Coupons
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Custom Domain
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Themes
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 ms-auto text-dark">
                                <h4>Priemum Addons</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    PayPal
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    MyFatoorah
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    Mercado Pago
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    toyyibPay
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    POS (Point Of Sale)
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    Telegram
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    Table QR
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-dark "></i>
                                    PWA (Progressive Web App)
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6 border-line text-danger">
                                <h4>Notes</h4>
                                <hr>
                                <ul class="text-start">
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    Any third party configuration service will be charged extra (Example : Email Configuration, Custom Domain Configuration, Social Login Configuration, Google Analytics Configuration, Google reCaptcha Configuration, etc…)
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-danger "></i>
                                    If you have any questions regarding LICENSE, INSTALLATION & ADDONS then please contact us
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-success">
                                <h4 class="mt-3 mt-sm-0">Contact Information</h4>
                                <hr>
                                <ul class="text-start">
                                <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Email : <a href="mailto: infotechgravity@gmail.com" target="_blank"> infotechgravity@gmail.com</a>
                                    </li>
                                    <li> <i class="fa-regular fa-circle-check text-success "></i>
                                    Whatsapp : <a href="https://api.whatsapp.com/send?text=Hello I found your from Demo&phone=919499874557" target="_blank">+91 9499874557</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <a href="https://1.envato.market/R5mbvb" target="_blank" class="btn btn-danger m-1">Buy Regular License</a>
                    <a href="https://1.envato.market/3eoEDd" target="_blank" class="btn btn-success m-1">Buy Extended License</a>
                    <a href="https://rb.gy/nc1f9" target="_blank" class="btn btn-dark m-1">Buy Priemum Addons</a>
                    <button type="button" class="btn btn-info m-1" data-bs-dismiss="modal">Continue to Demo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- whatsapp modal start -->
    <?php if(helper::appdata(1)->contact != ""): ?>
    <input type="checkbox" id="check">
    <div class="whatsapp_icon <?php echo e(session()->get('direction') == 2 ? 'whatsapp_icon_rtl' : 'whatsapp_icon_ltr'); ?>">
        <label class="chat-btn" for="check">
            <i class="fa-brands fa-whatsapp comment"></i>
            <i class="fa fa-close close"></i>
        </label>
    </div>

    <!--Start of Tawk.to Script-->
    <?php if(helper::appdata('')->tawk_on_off == 1 ): ?>
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src =
                    'https://embed.tawk.to/65d7258a9131ed19d9700056/<?php echo e(helper::appdata('')->tawk_widget_id); ?>';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
    <?php endif; ?>
    <!--Start of Tawk.to Script-->

    <div class="<?php echo e(session()->get('direction') == 2 ? 'wrapper_rtl' : 'wrapper'); ?>  wp_chat_box d-none">
        <div class="msg_header">
            <h6><?php echo e(helper::appdata('')->website_title); ?></h6>
        </div>
        <div class="text-start p-3 bg-msg">
            <div class="card p-2 msg">
                How can I help you ?
            </div>
        </div>
        <div class="chat-form">

            <form action="https://api.whatsapp.com/send" method="get" target="_blank" class="d-flex align-items-center d-grid gap-2">
                <textarea class="form-control" name="text" placeholder="Your Text Message"></textarea>
                <input type="hidden" name="phone" value="<?php echo e(helper::appdata('')->contact); ?>">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <!-- whatsapp modal end -->




    <!-- Jquery Min js -->
    <script>
        let direction = "<?php echo e(session()->get('direction')); ?>";

    </script>

    <script src="<?php echo e(asset(env('ASSETSPATHURL').'landing/js/jquery.min.js')); ?>"></script>

    <!-- Bootstrap js -->

    <script src="<?php echo e(asset(env('ASSETSPATHURL').'landing/js/bootstrap.bundle.min.js')); ?>"></script>

    <!-- owl carousel js -->

    <script src="<?php echo e(asset(env('ASSETSPATHURL').'landing/js/owl.carousel.min.js')); ?>"></script>

    <!-- custom js -->

    <script src="<?php echo e(asset(env('ASSETSPATHURL').'landing/js/custom.js')); ?>"></script>

    <?php echo $__env->yieldContent('scripts'); ?>

    <script>
        var areaurl = "<?php echo e(URL::to('admin/getarea')); ?>";
        var select = "<?php echo e(trans('landing.select_area')); ?>";
        var areaname = "<?php echo e(request()->get('area')); ?>";
        var env = "<?php echo e(env('Environment')); ?>";

        $(document).ready(function() {
            $('.whatsapp_icon').on("click",function(event)
            {
                $(".wp_chat_box").toggleClass("d-none");
            });

        // Statistics Counter Animation
        function animateCounter($element, target) {
            let current = 0;
            const increment = target / 100;
            const duration = 2000; // 2 seconds
            const stepTime = duration / 100;

            const timer = setInterval(function() {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                $element.text(Math.floor(current).toLocaleString());
            }, stepTime);
        }

        // Trigger counter animation when section becomes visible
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.stat-number');
                    counters.forEach(counter => {
                        const target = parseInt(counter.getAttribute('data-target'));
                        const $counter = $(counter);
                        animateCounter($counter, target);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const statsSection = document.querySelector('.statistics');
        if (statsSection) {
            observer.observe(statsSection);
        }

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            if(target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 80
                }, 1000);
            }
        });

        // if (env == "sendbox") {
        //     $(window).on("load", function () {
        //         "use strict";
        //         var info = localStorage.getItem("info-show");
        //         if (info != 'yes') {
        //             jQuery("#demo-modal").modal('show');
        //             localStorage.setItem("info-show", 'yes');
        //         }
        //     });
        // }

        // Auto-scroll to contact section if there's a message
        <?php if(session('success') || session('error') || $errors->any()): ?>
            setTimeout(function() {
                const contactSection = document.getElementById('contect-us');
                if (contactSection) {
                    contactSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        <?php endif; ?>

        }); // End of $(document).ready()
    </script>

    <?php if(!empty(helper::appdata(1)->tracking_id)): ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e(helper::appdata(1)->tracking_id); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '<?php echo e(helper::appdata(1)->tracking_id); ?>');
        </script>
    <?php endif; ?>


</body>
<?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/landing/layout/default.blade.php ENDPATH**/ ?>