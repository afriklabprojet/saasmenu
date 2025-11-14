<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            margin: 0;
            color: #4CAF50;
            font-size: 24px;
        }
        .period {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .summary {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #4CAF50;
        }
        .summary-item {
            display: inline-block;
            width: 32%;
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
            color: #333;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #4CAF50;
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
        .status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-pending { background: #FFC107; color: white; }
        .status-processing { background: #2196F3; color: white; }
        .status-completed { background: #4CAF50; color: white; }
        .status-cancelled { background: #F44336; color: white; }
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
        <h1>{{ $title }}</h1>
    </div>

    <div class="period">
        Period: {{ $period['start']->format('d M Y') }} to {{ $period['end']->format('d M Y') }}
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-item">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value">{{ $summary['total_orders'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">${{ number_format($summary['total_revenue'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Average Order Value</div>
            <div class="summary-value">${{ number_format($summary['average_order_value'], 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $order)
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                <td>{{ $order->user->name ?? 'Guest' }}</td>
                <td>{{ $order->orderitems->count() }}</td>
                <td>${{ number_format($order->total, 2) }}</td>
                <td>
                    <span class="status status-{{ $order->status }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i:s') }} | RestroSaaS Orders Report
    </div>
</body>
</html>
