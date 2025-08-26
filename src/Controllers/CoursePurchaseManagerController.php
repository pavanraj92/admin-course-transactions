<?php

namespace admin\course_transactions\Controllers;

use admin\course_transactions\Models\CoursePurchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CoursePurchaseManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admincan_permission:course_purchases_manager_list')->only(['index']);
        $this->middleware('admincan_permission:course_purchases_manager_view')->only(['show']);
    }

    public function index(Request $request)
    {
        try {
            $purchases = CoursePurchase::with(['user', 'course', 'transaction'])
                ->filter($request->only('status', 'keyword'))
                ->sortable()
                ->paginate(CoursePurchase::getPerPageLimit())
                ->withQueryString();

            $statuses = ['pending', 'completed', 'cancelled'];

            return view('transaction::admin.purchase.index', compact('purchases', 'statuses'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load purchases: ' . $e->getMessage());
        }
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        try {
            $purchase = CoursePurchase::with(['user', 'course', 'transaction'])->findOrFail($id);

            return view('transaction::admin.purchase.show', compact('purchase'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load purchase details: ' . $e->getMessage());
        }
    }

    public function edit(CoursePurchase $coursePurchase)
    {
        //
    }

    public function update(Request $request, CoursePurchase $coursePurchase)
    {
        // Update logic here
    }

    public function destroy(CoursePurchase $coursePurchase)
    {
        //
    }
}
