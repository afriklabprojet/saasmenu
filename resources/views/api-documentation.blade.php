<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestroSaaS Addons API Documentation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
            color: #374151;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            color: #1f2937;
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .header p {
            color: #6b7280;
            margin: 0;
            font-size: 1.1rem;
        }
        .addons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .addon-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #3b82f6;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .addon-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .addon-card h3 {
            margin: 0 0 1rem 0;
            color: #1f2937;
            display: flex;
            align-items: center;
            font-size: 1.25rem;
        }
        .addon-icon {
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        .addon-card p {
            color: #6b7280;
            margin: 0 0 1rem 0;
            line-height: 1.6;
        }
        .endpoints {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .endpoints li {
            background: #f9fafb;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        .method {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            margin-right: 0.75rem;
            min-width: 45px;
            text-align: center;
            font-size: 0.75rem;
        }
        .method.get { background: #dcfce7; color: #166534; }
        .method.post { background: #dbeafe; color: #1e40af; }
        .method.put { background: #fef3c7; color: #92400e; }
        .method.delete { background: #fee2e2; color: #dc2626; }
        .swagger-link {
            text-align: center;
            margin: 2rem 0;
        }
        .swagger-btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background 0.2s ease;
        }
        .swagger-btn:hover {
            background: #2563eb;
        }
        .auth-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .auth-section h2 {
            margin: 0 0 1rem 0;
            color: #1f2937;
        }
        .auth-code {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 RestroSaaS Addons API</h1>
            <p>Comprehensive API documentation for all 8 priority addons</p>
        </div>

        <div class="swagger-link">
            <a href="/api/documentation" class="swagger-btn">
                📚 Interactive Swagger Documentation
            </a>
        </div>

        <div class="auth-section">
            <h2>🔐 Authentication</h2>
            <p>All API endpoints require authentication using either:</p>
            <div class="auth-code">
<strong>Option 1: API Key (Header)</strong>
X-API-Key: your_restaurant_api_key

<strong>Option 2: Bearer Token (Sanctum)</strong>
Authorization: Bearer your_sanctum_token
            </div>
        </div>

        <div class="addons-grid">
            <div class="addon-card">
                <h3><span class="addon-icon">💳</span>POS System</h3>
                <p>Comprehensive point-of-sale terminal management with session handling and transaction processing.</p>
                <ul class="endpoints">
                    <li><span class="method get">GET</span>/api/pos/terminals</li>
                    <li><span class="method post">POST</span>/api/pos/sessions</li>
                    <li><span class="method get">GET</span>/api/pos/sessions/{id}</li>
                    <li><span class="method post">POST</span>/api/pos/sessions/{id}/cart</li>
                    <li><span class="method post">POST</span>/api/pos/sessions/{id}/checkout</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">🎯</span>Loyalty Program</h3>
                <p>Customer loyalty management with points, rewards, and tier-based benefits system.</p>
                <ul class="endpoints">
                    <li><span class="method get">GET</span>/api/loyalty/programs</li>
                    <li><span class="method post">POST</span>/api/loyalty/programs/{id}/members</li>
                    <li><span class="method get">GET</span>/api/loyalty/members/{id}</li>
                    <li><span class="method post">POST</span>/api/loyalty/members/{id}/transactions</li>
                    <li><span class="method get">GET</span>/api/loyalty/members/{id}/transactions</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">📱</span>Table QR System</h3>
                <p>QR code generation and management for table ordering with comprehensive scan analytics.</p>
                <ul class="endpoints">
                    <li><span class="method get">GET</span>/api/tableqr/tables</li>
                    <li><span class="method get">GET</span>/api/tableqr/tables/{id}</li>
                    <li><span class="method post">POST</span>/api/tableqr/tables</li>
                    <li><span class="method post">POST</span>/api/tableqr/scan</li>
                    <li><span class="method get">GET</span>/api/tableqr/analytics</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">📊</span>Import/Export Tools</h3>
                <p>Data import and export functionality supporting CSV, Excel, and JSON formats with validation.</p>
                <ul class="endpoints">
                    <li><span class="method get">GET</span>/api/import-export/jobs</li>
                    <li><span class="method post">POST</span>/api/import-export/import</li>
                    <li><span class="method post">POST</span>/api/import-export/export</li>
                    <li><span class="method get">GET</span>/api/import-export/jobs/{id}</li>
                    <li><span class="method get">GET</span>/api/import-export/download/{id}</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">🔔</span>Push Notifications</h3>
                <p>Firebase-powered push notification system with device management and analytics.</p>
                <ul class="endpoints">
                    <li><span class="method post">POST</span>/api/notifications/send</li>
                    <li><span class="method get">GET</span>/api/notifications/devices</li>
                    <li><span class="method post">POST</span>/api/notifications/register-device</li>
                    <li><span class="method get">GET</span>/api/notifications/history</li>
                    <li><span class="method get">GET</span>/api/notifications/analytics</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">💰</span>PayPal Integration</h3>
                <p>Complete PayPal payment processing with webhook handling and transaction management.</p>
                <ul class="endpoints">
                    <li><span class="method post">POST</span>/api/paypal/create-order</li>
                    <li><span class="method post">POST</span>/api/paypal/capture-payment</li>
                    <li><span class="method get">GET</span>/api/paypal/transactions</li>
                    <li><span class="method post">POST</span>/api/paypal/refund</li>
                    <li><span class="method post">POST</span>/webhooks/paypal</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">👤</span>Social Login</h3>
                <p>Facebook and Google OAuth authentication with seamless user profile integration.</p>
                <ul class="endpoints">
                    <li><span class="method get">GET</span>/api/auth/facebook</li>
                    <li><span class="method get">GET</span>/api/auth/facebook/callback</li>
                    <li><span class="method get">GET</span>/api/auth/google</li>
                    <li><span class="method get">GET</span>/api/auth/google/callback</li>
                    <li><span class="method post">POST</span>/api/auth/social-login</li>
                </ul>
            </div>

            <div class="addon-card">
                <h3><span class="addon-icon">🔥</span>Firebase Services</h3>
                <p>Complete Firebase integration for real-time notifications and cloud messaging.</p>
                <ul class="endpoints">
                    <li><span class="method post">POST</span>/api/firebase/send-notification</li>
                    <li><span class="method post">POST</span>/api/firebase/register-token</li>
                    <li><span class="method get">GET</span>/api/firebase/analytics</li>
                    <li><span class="method post">POST</span>/api/firebase/test-connection</li>
                    <li><span class="method post">POST</span>/webhooks/firebase</li>
                </ul>
            </div>
        </div>

        <div class="auth-section">
            <h2>🚀 Getting Started</h2>
            <p><strong>1. Obtain API Key:</strong> Contact your restaurant admin to get an API key with proper addon permissions.</p>
            <p><strong>2. Test Health Endpoint:</strong> Start with <code>GET /api/health</code> to verify your API access.</p>
            <p><strong>3. Explore Endpoints:</strong> Use the interactive Swagger documentation for detailed request/response examples.</p>
            <p><strong>4. Rate Limits:</strong> All endpoints are rate limited. Check response headers for limit information.</p>
        </div>
    </div>
</body>
</html>
