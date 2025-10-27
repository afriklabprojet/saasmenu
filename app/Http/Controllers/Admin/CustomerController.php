<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        if(Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }

        // Get customers based on vendor context or all customers for super admin
        if(Auth::user()->type == 1 && Auth::user()->id == 1) {
            // Super admin sees all customers
            $customers = Customer::orderBy('created_at', 'desc')->paginate(15);
        } else {
            // Vendors see customers related to their orders/restaurant
            $customers = Customer::orderBy('created_at', 'desc')->paginate(15);
        }

        return view('admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'required|in:1,2'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customer = Customer::findOrFail($id);
        $customer->update($request->only([
            'name', 'email', 'phone', 'address', 'city', 'postal_code', 'status'
        ]));

        return redirect()->route('admin.customers.index')
                        ->with('success', 'Customer updated successfully');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')
                        ->with('success', 'Customer deleted successfully');
    }
}
