<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RouteDebuggingTest extends TestCase
{
    /** @test */
    public function debug_front_routes_controllers()
    {
        $routes = Route::getRoutes();
        $frontRoutes = collect($routes)->filter(function ($route) {
            $name = $route->getName();
            return $name && str_starts_with($name, 'front.');
        });

        echo "\n=== FRONT ROUTES DEBUG ===\n";

        $usedControllers = [];
        foreach ($frontRoutes as $route) {
            $action = $route->getAction();
            $name = $route->getName();
            $uri = $route->uri();

            if (isset($action['controller'])) {
                $controller = $action['controller'];
                $controllerBasename = class_basename($controller);
                $usedControllers[] = $controllerBasename;

                echo "Route: {$name} | URI: {$uri} | Controller: {$controllerBasename}\n";
            }
        }

        echo "\n=== CONTROLLERS USED ===\n";
        $uniqueControllers = array_unique($usedControllers);
        foreach ($uniqueControllers as $controller) {
            echo "- {$controller}\n";
        }

        $this->assertTrue(true);
    }

    /** @test */
    public function debug_cart_routes()
    {
        $routes = Route::getRoutes();

        echo "\n=== CART RELATED ROUTES ===\n";

        foreach ($routes as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            $action = $route->getAction();

            if (str_contains($uri, 'cart') || str_contains($name ?? '', 'cart')) {
                $controller = isset($action['controller']) ? class_basename($action['controller']) : 'Closure';
                echo "Route: {$name} | URI: {$uri} | Controller: {$controller}\n";
            }
        }

        $this->assertTrue(true);
    }
}
