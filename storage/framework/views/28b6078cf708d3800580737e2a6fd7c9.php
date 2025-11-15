<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2196F3;
        }
        .header h1 {
            margin: 0;
            color: #2196F3;
            font-size: 24px;
        }
        .period {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .summary {
            background: #E3F2FD;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #2196F3;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
        }
        .summary-value {
            font-size: 18px;
            color: #2196F3;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #2196F3;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .amount {
            font-weight: bold;
            color: #4CAF50;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e($title); ?></h1>
    </div>

    <div class="period">
        Period: <?php echo e($period['start']->format('d M Y')); ?> to <?php echo e($period['end']->format('d M Y')); ?>

    </div>

    <div class="summary">
        <h3>Sales Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Sales</div>
                <div class="summary-value">$<?php echo e(number_format($summary['total_sales'], 2)); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Orders</div>
                <div class="summary-value"><?php echo e($summary['total_orders']); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Tax Collected</div>
                <div class="summary-value">$<?php echo e(number_format($summary['total_tax'], 2)); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Delivery Fees</div>
                <div class="summary-value">$<?php echo e(number_format($summary['total_delivery'], 2)); ?></div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Subtotal</th>
                <th>Tax</th>
                <th>Delivery</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($order->created_at->format('d M Y')); ?></td>
                <td>#<?php echo e($order->id); ?></td>
                <td><?php echo e($order->user->name ?? 'Guest'); ?></td>
                <td>$<?php echo e(number_format($order->total - $order->tax - $order->delivery_fee, 2)); ?></td>
                <td>$<?php echo e(number_format($order->tax, 2)); ?></td>
                <td>$<?php echo e(number_format($order->delivery_fee, 2)); ?></td>
                <td class="amount">$<?php echo e(number_format($order->total, 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="footer">
        Generated on <?php echo e(now()->format('d M Y H:i:s')); ?> | RestroSaaS Sales Report
    </div>
</body>
</html>
<?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/reports/sales.blade.php ENDPATH**/ ?>