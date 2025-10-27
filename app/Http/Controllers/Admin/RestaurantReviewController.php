<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantReviewController extends Controller
{
    public function index()
    {
        return view('admin.restaurant-review.index');
    }

    public function create()
    {
        return view('admin.restaurant-review.create');
    }

    public function store(Request $request)
    {
        // Logique de création
        return redirect()->route('admin.restaurant-review.index');
    }

    public function show($id)
    {
        return view('admin.restaurant-review.show');
    }

    public function edit($id)
    {
        return view('admin.restaurant-review.edit');
    }

    public function update(Request $request, $id)
    {
        // Logique de mise à jour
        return redirect()->route('admin.restaurant-review.index');
    }

    public function destroy($id)
    {
        // Logique de suppression
        return redirect()->route('admin.restaurant-review.index');
    }
}
