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
            border-bottom: 2px solid #9C27B0;
        }
        .header h1 {
            margin: 0;
            color: #9C27B0;
            font-size: 24px;
        }
        .period {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .summary {
            background: #F3E5F5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #9C27B0;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 50%;
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
            color: #9C27B0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #9C27B0;
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
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-active {
            background: #4CAF50;
            color: white;
        }
        .badge-inactive {
            background: #999;
            color: white;
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
        <h3>Customer Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Customers</div>
                <div class="summary-value"><?php echo e($summary['total_customers']); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Active Customers</div>
                <div class="summary-value"><?php echo e($summary['active_customers']); ?></div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Joined Date</th>
                <th>Total Orders</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($customer->name); ?></td>
                <td><?php echo e($customer->email); ?></td>
                <td><?php echo e($customer->phone ?? 'N/A'); ?></td>
                <td><?php echo e($customer->created_at->format('d M Y')); ?></td>
                <td><?php echo e($customer->orders_count); ?></td>
                <td>
                    <span class="badge <?php echo e($customer->orders_count > 0 ? 'badge-active' : 'badge-inactive'); ?>">
                        <?php echo e($customer->orders_count > 0 ? 'Active' : 'Inactive'); ?>

                    </span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="footer">
        Generated on <?php echo e(now()->format('d M Y H:i:s')); ?> | RestroSaaS Customers Report
    </div>
</body>
</html>
<?php /**PATH /Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/reports/customers.blade.php ENDPATH**/ ?>