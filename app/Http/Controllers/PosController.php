<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Services\CouponService;
use App\Services\SettingService;
use App\Enums\SettingKey;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    protected $couponService;
    protected $settingService;

    public function __construct(CouponService $couponService, SettingService $settingService)
    {
        $this->couponService = $couponService;
        $this->settingService = $settingService;
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
            $taxRate = $this->settingService->get(SettingKey::TAX_RATE->value, 10);

            return Inertia::render('Pos/Sale', [
                'customer' => $customer,
                'taxRate' => (float) $taxRate,
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

    public function selectCustomer(Customer $customer)
    {
        // Son 1 saatteki bekleyen satışı kontrol et
        $pendingSale = Sale::where('customer_id', $customer->id)
            ->where('status', SaleStatus::PENDING)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->latest()
            ->first();

        if ($pendingSale) {
            return redirect()->route('pos.sale', ['uuid' => $pendingSale->uuid]);
        }

        // Yeni bekleyen satış oluştur
        $sale = Sale::create([
            'user_id' => auth()->id(),
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethod::CASH,
            'status' => SaleStatus::PENDING,
            'subtotal' => 0,
            'tax_rate' => $this->settingService->get(SettingKey::TAX_RATE->value, 18),
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 0
        ]);

        return redirect()->route('pos.sale', ['uuid' => $sale->uuid]);
    }

    public function sale(string $uuid)
    {
        $sale = Sale::where('uuid', $uuid)
            ->where('status', SaleStatus::PENDING)
            ->firstOrFail();

        return Inertia::render('Pos/Sale', [
            'sale' => $sale,
            'customer' => $sale->customer,
            'taxRate' => (float) $sale->tax_rate,
            'paymentMethods' => PaymentMethod::options()
        ]);
    }
}
