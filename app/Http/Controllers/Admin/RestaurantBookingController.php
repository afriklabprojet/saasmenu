<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantBookingController extends Controller
{
    public function index()
    {
        return view('admin.restaurant-booking.index');
    }

    public function create()
    {
        return view('admin.restaurant-booking.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.restaurant-booking.index');
    }

    public function show($id)
    {
        return view('admin.restaurant-booking.show');
    }

    public function edit($id)
    {
        return view('admin.restaurant-booking.edit');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.restaurant-booking.index');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.restaurant-booking.index');
    }
}
