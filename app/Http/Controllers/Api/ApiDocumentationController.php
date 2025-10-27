<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="RestroSaaS Addons API",
 *     description="Comprehensive API for RestroSaaS addon system including POS, Loyalty Programs, Table QR, Import/Export, Notifications, PayPal Integration, Social Login, and Firebase Push Notifications.",
 *     @OA\Contact(
 *         email="support@restro-saas.com",
 *         name="RestroSaaS Support"
 *     ),
 *     @OA\License(
 *         name="Commercial License",
 *         url="https://restro-saas.com/license"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://api.restro-saas.com",
 *     description="Production API Server"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use your Sanctum authentication token"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="api_key",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="Restaurant-specific API key for addon access"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 *
 * @OA\Tag(
 *     name="POS System",
 *     description="Point of Sale terminal management, sessions, and transactions"
 * )
 *
 * @OA\Tag(
 *     name="Loyalty Program",
 *     description="Customer loyalty program management, points, and rewards"
 * )
 *
 * @OA\Tag(
 *     name="Table QR",
 *     description="Table QR code generation, management, and scanning analytics"
 * )
 *
 * @OA\Tag(
 *     name="Import/Export",
 *     description="Data import/export functionality for menus, customers, and orders"
 * )
 *
 * @OA\Tag(
 *     name="Notifications",
 *     description="Push notification management and Firebase integration"
 * )
 *
 * @OA\Tag(
 *     name="PayPal",
 *     description="PayPal payment processing and webhook handling"
 * )
 *
 * @OA\Tag(
 *     name="Social Login",
 *     description="Facebook and Google OAuth authentication"
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="timestamp", type="string", format="date-time"),
 *         @OA\Property(property="api_version", type="string", example="1.0.0")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(property="field", type="array", @OA\Items(type="string"))
 *     ),
 *     @OA\Property(property="code", type="integer", example=422)
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=75)
 * )
 *
 * @OA\Schema(
 *     schema="Restaurant",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Pizza Palace"),
 *     @OA\Property(property="slug", type="string", example="pizza-palace"),
 *     @OA\Property(property="email", type="string", example="contact@pizzapalace.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin", "restaurant_admin", "restaurant_manager", "restaurant_staff", "customer"}, example="restaurant_admin"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ApiDocumentationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/health",
     *     summary="API Health Check",
     *     description="Check if the API is running and healthy",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="healthy"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(
     *                 property="addons",
     *                 type="object",
     *                 @OA\Property(property="pos", type="boolean", example=true),
     *                 @OA\Property(property="loyalty", type="boolean", example=true),
     *                 @OA\Property(property="tableqr", type="boolean", example=true),
     *                 @OA\Property(property="import_export", type="boolean", example=true),
     *                 @OA\Property(property="notifications", type="boolean", example=true),
     *                 @OA\Property(property="paypal", type="boolean", example=true),
     *                 @OA\Property(property="social_login", type="boolean", example=true),
     *                 @OA\Property(property="firebase", type="boolean", example=true)
     *             )
     *         )
     *     )
     * )
     */
    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'addons' => [
                'pos' => true,
                'loyalty' => true,
                'tableqr' => true,
                'import_export' => true,
                'notifications' => true,
                'paypal' => true,
                'social_login' => true,
                'firebase' => true,
            ]
        ]);
    }

    /**
     * Display API documentation
     */
    public function documentation()
    {
        return view('api-documentation');
    }
}
