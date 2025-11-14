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
            border-bottom: 2px solid #FF9800;
        }
        .header h1 {
            margin: 0;
            color: #FF9800;
            font-size: 24px;
        }
        .summary {
            background: #FFF3E0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #FF9800;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #FF9800;
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
        .stock-low {
            color: #F44336;
            font-weight: bold;
        }
        .stock-ok {
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
        <h1>{{ $title }}</h1>
    </div>

    <div class="summary">
        <h3>Inventory Overview</h3>
        <p>This report will display inventory levels when data is available.</p>
    </div>

    @if(!empty($data) && count($data) > 0)
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->stock }}</td>
                <td class="{{ $item->stock < 10 ? 'stock-low' : 'stock-ok' }}">
                    {{ $item->stock < 10 ? 'Low Stock' : 'In Stock' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #999; padding: 40px;">
        No inventory data available for this period.
    </p>
    @endif

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i:s') }} | RestroSaaS Inventory Report
    </div>
</body>
</html>
