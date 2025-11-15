<?php $__env->startSection('content'); ?>

<body class="bg-white">
    <div class="wrapper">
        <section>
            <div class="row justify-content-center align-items-center g-0 h-100vh">
                <div class="col-lg-6 col-12 bg-white">
                    <div class="row horizontal-schroll g-0 vh-100 d-flex justify-content-center align-items-center">
                        <div class="col-md-8 col-lg-10 col-xl-7">
                            <div class="card overflow-hidden border-0 w-100 bg-transparent">
                                <div class="card-body pt-4">
                                    <h4 class="fw-bold text-dark fs-1 pb-0 mb-0"><?php echo e(trans('labels.welcome_back')); ?></h4>
                                    <?php if(helper::appdata('')->vendor_register == 1): ?>
                                    <div class="d-flex align-items-center py-3">
                                        <p class="fs-7 text-center fw-500 text-muted"><?php echo e(trans('labels.dont_have_account')); ?></p>
                                        <a href="<?php echo e(URL::to('admin/register')); ?>" class="text-primary fw-semibold px-1"><?php echo e(trans('labels.register')); ?></a>
                                    </div>
                                    <?php endif; ?>
                                    <form class="my-3" method="POST" action="<?php echo e(URL::to('admin/checklogin-normal')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="form-group">
                                            <label for="email" class="form-label"><?php echo e(trans('labels.email')); ?><span class="text-danger"> * </span></label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="<?php echo e(trans('labels.email')); ?>" required>
                                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="password" class="form-label"><?php echo e(trans('labels.password')); ?><span class="text-danger"> * </span></label>
                                            <input type="password" class="form-control" name="password" id="password" placeholder="<?php echo e(trans('labels.password')); ?>" required>
                                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger"><?php echo e($message); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="text-end mb-1">
                                            <a href="<?php echo e(URL::to('admin/forgot_password?redirect=admin')); ?>" class="fs-8 fw-600">
                                                <i class="fa-solid fa-lock-keyhole mx-2 fs-7"></i><?php echo e(trans('labels.forgot_password')); ?>?
                                            </a>
                                        </div>
                                        <div class="row align-items-center g-2">
                                            <div class="">
                                                <button class="btn btn-primary mt-2 w-100" type="submit"><?php echo e(trans('labels.login')); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                    <?php if(env('Environment') == 'sendbox'): ?>
                                    <div class="form-group mt-3">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>Admin<br>admin@gmail.com</td>
                                                    <td>123456</td>
                                                    <td><button class="btn btn-info btn-sm" onclick="fillData('admin@gmail.com','123456')"><?php echo e(trans('labels.copy')); ?></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Vendor<br>theme1@yopmail.com</td>
                                                    <td>123456</td>
                                                    <td><button class="btn btn-info btn-sm" onclick="fillData('theme1@yopmail.com','123456')"><?php echo e(trans('labels.copy')); ?></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row align-items-center g-2">
                                        <?php if(env('Environment') == 'sendbox'): ?>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/chicken-store')); ?>" target="_blank">Chicken Shop</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/the-pizza')); ?>" target="_blank">The Pizza</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/burger-shop')); ?>" target="_blank">Burger Shop</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/cake-shop')); ?>" target="_blank">Cake Shop</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/cafe-amore')); ?>" target="_blank">Cafe Amore</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/scoop-haven')); ?>" target="_blank">Scoop Haven</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/bar-bliss')); ?>" target="_blank">Bar Bliss</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/the-kitchens')); ?>" target="_blank">The Kitchens</a>
                                        </div>
                                        <div class="col-4">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/tea-shop')); ?>" target="_blank">Tea Shop</a>
                                        </div>
                                        <div class="col-12">
                                            <a class="btn btn-dark mt-2 w-100" href="<?php echo e(URL::to('/the-chocolate')); ?>" target="_blank">The Chocolate</a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $googleAddon = App\Models\SystemAddons::where('unique_identifier', 'google_login')->first();
                                    $facebookAddon = App\Models\SystemAddons::where('unique_identifier', 'facebook_login')->first();
                                    ?>
                                    <?php if((($googleAddon && $googleAddon->activated == 1) || ($facebookAddon && $facebookAddon->activated == 1)) &&
                                         (@helper::appdata('')->google_login == 1 || @helper::appdata('')->facebook_login == 1)): ?>
                                        <div class="d-flex align-items-center my-3 m-auto">
                                            <div class="line"></div>
                                            <p class="text-center mx-2 fs-7 m-0 fw-600">OR</p>
                                            <div class="line"></div>
                                        </div>

                                        <?php echo $__env->make('components.social-login-buttons', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12 d-none d-lg-block">
                    <div class="vh-100 d-flex justify-content-center align-items-center m-auto">
                        <img src="<?php echo e(url(env('ASSETSPATHURL').'admin-assets/images/about/login.jpg')); ?>" alt="" class="formimg">
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php $__env->stopSection(); ?>
    <?php $__env->startSection('scripts'); ?>
    <script>
        function fillData(email, password) {
            "use strict";
            $('#email').val(email);
            $('#password').val(password);
        }
    </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.auth_default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>