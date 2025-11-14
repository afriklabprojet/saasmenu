<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .message {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #45a049;
        }
        .report-info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Your Report is Ready!</h1>
    </div>

    <div class="content">
        <div class="message">
            <p>Hello {{ $user->name }},</p>

            <p>Good news! The report you requested has been successfully generated and is now ready for download.</p>

            <div class="report-info">
                <strong>Report Type:</strong> {{ ucfirst(str_replace('_', ' ', $report_type)) }}<br>
                <strong>Generated:</strong> {{ now()->format('F d, Y \a\t H:i:s') }}
            </div>

            <p style="text-align: center;">
                <a href="{{ $download_link }}" class="button">📥 Download Report</a>
            </p>

            <p style="font-size: 12px; color: #666;">
                <strong>Note:</strong> This download link will be valid for 7 days. Please download your report before it expires.
            </p>
        </div>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <p>
            Best regards,<br>
            <strong>RestroSaaS Team</strong>
        </p>
    </div>

    <div class="footer">
        <p>
            © {{ now()->year }} RestroSaaS. All rights reserved.<br>
            You received this email because you requested a report from your RestroSaaS account.
        </p>
    </div>
</body>
</html>
