<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Models\PricingPlan;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\TaxRequest;
use App\Http\Requests\Admin\StatusChangeRequest;
use App\Services\AuditService;
use DB;
class TaxController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $gettax = Tax::where('vendor_id', $vendor_id)->where('is_deleted',2)->orderBy('reorder_id')->get();
        return view('admin.tax.index', compact("gettax"));
    }
    public function add(Request $request)
    {
        return view('admin.tax.add');
    }
    public function save(TaxRequest $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }

        $tax = new Tax();
        $tax->vendor_id = $vendor_id;
        $tax->name = $request->validated('name');
        $tax->type = $request->validated('type');
        $tax->tax = $request->validated('tax');
        $tax->is_available = 1;
        $tax->is_deleted = 2;
        $tax->save();

        // Audit log pour la création de taxe
        AuditService::logAdminAction(
            'CREATE_TAX',
            'Tax',
            $request->validated(),
            $tax->id
        );

        return redirect('admin/tax/')->with('success', trans('messages.success'));
    }
    public function edit(Request $request)
    {
        $edittax = Tax::where('id', $request->id)->first();
        return view('admin.tax.edit', compact("edittax"));
    }
    public function update(TaxRequest $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }

        $tax = Tax::where('id', $request->validated('id'))->first();

        if (!$tax) {
            return redirect('admin/tax')->with('error', 'Taxe non trouvée.');
        }

        $oldData = $tax->toArray();

        $tax->vendor_id = $vendor_id;
        $tax->name = $request->validated('name');
        $tax->type = $request->validated('type');
        $tax->tax = $request->validated('tax');
        $tax->update();

        // Audit log pour la modification de taxe
        AuditService::logAdminAction(
            'UPDATE_TAX',
            'Tax',
            [
                'old_data' => $oldData,
                'new_data' => $request->validated()
            ],
            $tax->id
        );

        return redirect('admin/tax')->with('success', trans('messages.success'));
    }
    public function change_status(StatusChangeRequest $request)
    {
        $affected = Tax::where('id', $request->validated('id'))
            ->update(['is_available' => $request->validated('status')]);

        if ($affected === 0) {
            return redirect('admin/tax')->with('error', 'Taxe non trouvée.');
        }

        // Audit log pour le changement de statut
        AuditService::logAdminAction(
            'CHANGE_TAX_STATUS',
            'Tax',
            [
                'tax_id' => $request->validated('id'),
                'new_status' => $request->validated('status')
            ],
            $request->validated('id')
        );

        return redirect('admin/tax')->with('success', trans('messages.success'));
    }
    public function delete(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $checktax = Tax::where('id', $request->id)->first();
        if(Auth::user()->type == 1)
        {
            // SÉCURISÉ: Utilisation de paramètres liés pour éviter l'injection SQL
            $getplan = PricingPlan::whereRaw("FIND_IN_SET(?, REPLACE(tax, '|', ',')) > 0", [$checktax->id])->get();
            foreach($getplan as $plan)
            {
                $tax_id = explode('|', $plan->tax);
                $key = array_search($checktax->id, $tax_id);
                if ($key !== false) {
                    unset($tax_id[$key]);
                    PricingPlan::where('id',$plan->id)->update(array('tax' => implode('|', $tax_id)));
                }
            }
        }
        else{
            // SÉCURISÉ: Utilisation de paramètres liés pour éviter l'injection SQL
            $getproduct = Item::whereRaw("FIND_IN_SET(?, REPLACE(tax, '|', ',')) > 0", [$checktax->id])->get();
            foreach($getproduct as $product)
            {
                $tax = explode('|', $product->tax);
                $key = array_search($checktax->id, $tax);
                if ($key !== false) {
                    unset($tax[$key]);
                    Item::where('vendor_id', $vendor_id)->update(array('tax' => implode('|', $tax)));
                }
            }
        }
        $checktax->delete();
        return redirect('admin/tax')->with('success', trans('messages.success'));
    }
    public function reorder_tax(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $gettax = Tax::where('vendor_id', $vendor_id)->get();
        foreach ($gettax as $tax) {
            foreach ($request->order as $order) {
                $tax = Tax::where('id', $order['id'])->first();
                $tax->reorder_id = $order['position'];
                $tax->save();
            }
        }
        return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
    }

}
