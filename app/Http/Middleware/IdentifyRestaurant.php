<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyRestaurant
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurantId = $request->header('X-Restaurant-ID');

        if (!$restaurantId && auth()->check()) {
            $restaurantId = auth()->user()->restaurants()->first()?->id;
        }

        if ($restaurantId) {
            app()->instance('current_restaurant_id', $restaurantId);
        }

        return $next($request);
    }
}
