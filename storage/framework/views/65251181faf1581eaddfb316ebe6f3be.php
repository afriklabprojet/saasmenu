<?php $__env->startSection('content'); ?>
<?php
    if(Auth::user()->type == 4)
    {
        $vendor_id = Auth::user()->vendor_id;
    }else{
        $vendor_id = Auth::user()->id;
    }
    
?>
    <div class="row justify-content-between align-items-center mb-3">
        <div class="col-12 col-md-4">
            <h5 class="pages-title fs-2 align-items-center"><?php echo e(request()->is('admin/report*') ? trans('labels.report') :
                trans('labels.orders')); ?>

            </h5>
            <div class="d-flex">
                <?php echo $__env->make('admin.layout.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

        </div>
        <div class="col-12 col-md-8">
            <?php if(request()->is('admin/report*')): ?>
            <form action="<?php echo e(URL::to('/admin/report')); ?>" class="mb-">
                <div class="input-group col-md-12 ps-0 justify-content-end">
                    <div class="input-group-append col-auto px-1 pb-2 pb-xl-0">
                        <input type="date" class="form-control rounded-5 px-4 bg-white" name="startdate" <?php if(isset($_GET['startdate'])): ?>
                            value="<?php echo e($_GET['startdate']); ?>" <?php endif; ?> required>
                    </div>
                    <div class="input-group-append col-auto px-1 pb-2 pb-xl-0">
                        <input type="date" class="form-control rounded-5 px-4 bg-white" name="enddate" <?php if(isset($_GET['enddate'])): ?>
                            value="<?php echo e($_GET['enddate']); ?>" <?php endif; ?> required>
                    </div>
                    <div class="input-group-append pb-2 pb-xl-0 px-1">
                        <button class="btn btn-primary rounded-5 px-4" type="submit"><?php echo e(trans('labels.fetch')); ?></button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
   
    <?php echo $__env->make('admin.orders.statistics', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row mb-7">
        <div class="col-12">
            <div class="card border-0 my-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <?php echo $__env->make('admin.orders.orderstable', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <!-- Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel"><?php echo e(trans('labels.record_payment')); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action=" <?php echo e(URL::to('admin/orders/payment_status-' . '2')); ?>" method="post"
                        enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <div>
                                <input type="hidden" id="booking_number" name="booking_number" value="">
                                <label for="modal_total_amount" class="form-label">
                                    <?php echo e(trans('labels.total')); ?> <?php echo e(trans('labels.amount')); ?>

                                </label>
                                <input type="text" class="form-control numbers_only" name="modal_total_amount" id="modal_total_amount" disabled
                                    value="">
    
                                <label for="modal_amount" class="form-label mt-2">
                                    <?php echo e(trans('labels.cash_received')); ?>

                                </label>
                                <input type="text" class="form-control numbers_only" name="modal_amount" id="modal_amount" value="" 
                                    onkeyup="validation($(this).val())">
                                <label for="modal_amount" class="form-label mt-2">
                                    <?php echo e(trans('labels.change_amount')); ?>

                                </label>
                                <input type="number" class="form-control" name="ramin_amount" id="ramin_amount" value="" readonly>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-secondary"><?php echo e(trans('labels.submit')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
       
        function codpayment(booking_number, grand_total) {
            $('#modal_total_amount').val(grand_total);
            $('#booking_number').val(booking_number);
            $('#paymentModal').modal('show');
        }

        function validation(value) {
            var remaining = $('#modal_total_amount').val() - value;
            $('#ramin_amount').val(remaining.toFixed(2));
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/orders/index.blade.php ENDPATH**/ ?>