<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index()
    {
        return Inertia::render('Pos/CustomerSearch');
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('query');
        
        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->take(10)
            ->get();

        return response()->json($customers);
    }

    public function createCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $customer = Customer::create($validated);
        
        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function pos($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            return Inertia::render('Pos/Sale', [
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pos.index')->with('error', 'Customer not found');
        }
    }

    public function checkCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $result = $this->couponService->check($request->code);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }
}
