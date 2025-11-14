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
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
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
        .error-box {
            background: #ffebee;
            padding: 15px;
            border-left: 4px solid #f44336;
            margin: 15px 0;
            font-family: monospace;
            font-size: 12px;
            color: #c62828;
        }
        .button {
            display: inline-block;
            background: #2196F3;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #1976D2;
        }
        .support-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
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
        <h1>⚠️ Report Generation Failed</h1>
    </div>

    <div class="content">
        <div class="message">
            <p>Hello {{ $user->name }},</p>

            <p>We're sorry to inform you that there was an error generating your requested report.</p>

            <p><strong>Report Type:</strong> {{ ucfirst(str_replace('_', ' ', $report_type)) }}</p>

            <div class="error-box">
                <strong>Error Details:</strong><br>
                {{ $error }}
            </div>

            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our technical team has been automatically notified</li>
                <li>We're investigating the issue and will resolve it as soon as possible</li>
                <li>You can try generating the report again in a few minutes</li>
            </ul>

            <div class="support-info">
                <p><strong>Need immediate assistance?</strong></p>
                <p>Contact our support team:</p>
                <ul style="margin: 10px 0;">
                    <li>📧 Email: support@restrosaas.com</li>
                    <li>💬 Live Chat: Available in your dashboard</li>
                    <li>📞 Phone: Available during business hours</li>
                </ul>
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/dashboard/reports" class="button">🔄 Try Again</a>
            </p>
        </div>

        <p>We apologize for the inconvenience and appreciate your patience.</p>

        <p>
            Best regards,<br>
            <strong>RestroSaaS Support Team</strong>
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
