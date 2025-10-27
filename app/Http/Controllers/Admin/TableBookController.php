<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableBook;
use Illuminate\Http\Request;
use Auth;

class TableBookController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
      
        $bookings = TableBook::where('vendor_id',$vendor_id)->orderByDesc('id')->get();
        return view('admin.table_booking.index',compact('bookings'));
    }
}
