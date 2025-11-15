<table class="table table-striped table-bordered py-3 zero-configuration w-100">
    <thead>
        <tr class="fw-500 py-3">
          
            <td><?php echo e(trans('labels.srno')); ?></td>
            <?php if(request()->is('admin/customers*') && (Auth::user()->type == 1)): ?>
            <td><?php echo e(trans('labels.vendor_title')); ?></td>
            <?php endif; ?>
            <td><?php echo e(trans('labels.order_number')); ?></td>
            <td><?php echo e(trans('labels.date_time')); ?></td>
            <td><?php echo e(trans('labels.grand_total')); ?></td>
            <td><?php echo e(trans('labels.payment_type')); ?></td>
            <td><?php echo e(trans('labels.order_type')); ?></td>
            <td><?php echo e(trans('labels.status')); ?></td>
            <td><?php echo e(trans('labels.created_date')); ?></td>
            <td><?php echo e(trans('labels.updated_date')); ?></td>
            <?php if(Auth::user()->type == 2): ?>
            <td><?php echo e(trans('labels.action')); ?></td>

            <?php endif; ?>
          
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; ?>
        <?php $__currentLoopData = $getorders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orderdata): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr id="dataid<?php echo e($orderdata->id); ?>" class="fs-7">
            <td><?php echo $i++; ?></td>
            <?php if(request()->is('admin/customers*') && (Auth::user()->type == 1)): ?>
            <td><?php echo e($orderdata['vendorinfo']->name); ?></td>
            <?php endif; ?>
            <td> 
            <div class="d-flex justify-content-between">
                        <a class="text-dark fw-700" href="<?php echo e(URL::to('admin/orders/invoice/' . $orderdata->order_number)); ?>"> <?php echo e($orderdata->order_number); ?> </a>
                        <?php if($orderdata->vendor_note != ""): ?>
                        <a href="javascript:void(0)"
                            class="btn btn-outline-primary btn-sm" tooltip="<?php echo e($orderdata->vendor_note); ?>">
                            <i class="fa-solid fa-clipboard"></i>
                        </a>
                        <?php endif; ?>
                    </div>  
                
            </td>
            <td>
                <?php if($orderdata->order_type == 3): ?>
                    <?php echo e(helper::date_format($orderdata->created_at,$vendor_id)); ?>

                <?php else: ?>
                    <?php echo e(helper::date_format($orderdata->delivery_date,$vendor_id)); ?> <br>
                    <?php echo e($orderdata->delivery_time); ?>

                <?php endif; ?>

            </td>
            <td><?php echo e(helper::currency_formate($orderdata->grand_total, $vendor_id)); ?></td>
            <td>
                <?php if($orderdata->payment_type == 0): ?>
                    <?php echo e(trans('labels.online')); ?> </br>
                <?php else: ?>
                    <?php echo e(@helper::getpayment($orderdata->payment_type, $orderdata->vendor_id)->payment_name); ?> 
                    <?php if(in_array($orderdata->payment_type, [2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15])): ?>
                    : <?php echo e($orderdata->payment_id); ?>

                    <?php endif; ?></br>
                <?php endif; ?>
                <?php if($orderdata->payment_status == 1): ?>
                    <small class="text-danger"><i class="far fa-clock"></i>
                        <?php echo e(trans('labels.unpaid')); ?></small>
                <?php else: ?>
                    <small class="text-success"><i class="far fa-clock"></i>
                        <?php echo e(trans('labels.paid')); ?></small>
                <?php endif; ?>
            </td>
          
            <td>
                    <?php if($orderdata->order_type == 1): ?>
                        <?php echo e(trans('labels.delivery')); ?>

                    <?php elseif($orderdata->order_type == 2): ?>
                        <?php echo e(trans('labels.pickup')); ?>

                    <?php elseif($orderdata->order_type == 3): ?>
                        <?php echo e(trans('labels.table')); ?>

                        (<?php echo e($orderdata->dinein_tablename != '' ? $orderdata->dinein_tablename : '-'); ?>)
                    <?php elseif($orderdata->order_type == 4): ?>
                        <?php echo e(trans('labels.pos')); ?>

                  
                    <?php endif; ?>
                </td>
          
            <td>
                        <?php if($orderdata->status_type == '1'): ?>
                            <span
                                class="badge bg-warning"><?php echo e(@helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name); ?></span>
                        <?php elseif($orderdata->status_type == '2'): ?>
                            <span
                                class="badge bg-info"><?php echo e(@helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name); ?></span>
                        <?php elseif($orderdata->status_type == '3'): ?>
                            <span
                                class="badge bg-success"><?php echo e(@helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name); ?></span>
                        <?php elseif($orderdata->status_type == '4'): ?>
                            <span
                                class="badge bg-danger"><?php echo e(@helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name); ?></span>
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </td>
         
         
            <td><?php echo e(helper::date_format($orderdata->created_at,$vendor_id)); ?><br>
                <?php echo e(helper::time_format(@$orderdata->created_at,$vendor_id)); ?>

                </td>
                <td>
                   
                    <?php echo e(helper::date_format(@$orderdata->updated_at,$vendor_id)); ?><br>
                    <?php echo e(helper::time_format(@$orderdata->updated_at,$vendor_id)); ?>

                </td>
            <?php if(Auth::user()->type == 2): ?>
            <td>
                <?php if(Auth::user()->type == 2): ?>
                <a href="<?php echo e(URL::to('admin/orders/print/' . $orderdata->order_number)); ?>" class="btn btn-sm btn-success btn-success btn-size" tooltip="<?php echo e(trans('labels.print')); ?>">
 
                    <i class="fa-solid fa-print"></i>
                </a>
                <?php endif; ?>
                <a class="btn btn-sm btn-dark btn-size" tooltip="<?php echo e(trans('labels.view')); ?>" href="<?php echo e(URL::to('admin/orders/invoice/' . $orderdata->order_number)); ?>">    <i class="fa-regular fa-eye"></i> 
                </a>
                <?php if(Auth::user()->type == 2 ): ?>
                        <?php if(($orderdata->payment_type == 1 || $orderdata->payment_type == 6) && $orderdata->payment_status == 1 && $orderdata->status_type == 3 &&  $orderdata->status_type !=4): ?>
                            <a class="btn btn-sm btn-secondary btn-size"
                                onclick="codpayment('<?php echo e($orderdata->order_number); ?>','<?php echo e($orderdata->grand_total); ?>')"
                                tooltip="<?php echo e(trans('labels.payment')); ?>"><i class="fa-solid fa-file-invoice-dollar"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
            </td>
            <?php endif; ?>
          
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/orders/orderstable.blade.php ENDPATH**/ ?>