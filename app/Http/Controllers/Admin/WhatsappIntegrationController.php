<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhatsappIntegrationController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp-integration.index');
    }

    public function create()
    {
        return view('admin.whatsapp-integration.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.whatsapp-integration.index');
    }

    public function show($id)
    {
        return view('admin.whatsapp-integration.show');
    }

    public function edit($id)
    {
        return view('admin.whatsapp-integration.edit');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.whatsapp-integration.index');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.whatsapp-integration.index');
    }
}
