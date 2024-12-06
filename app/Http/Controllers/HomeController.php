<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'title' => 'Welcome',
        ]);
    }

    public function redirectToTenant(): Response
    {
        $tenants = Auth::user()->tenants;

        /*
        if ($tenants->count() === 1) {
            if(request()->inertia()) {
                return inertia_location('https://' . $tenants->first()->domain);
                //return response('', 302)->header('X-Inertia-Location', 'https://' . $tenants->first()->domain);
            }

            return redirect()->away('https://' . $tenants->first()->domain);

            //return Inertia::location('https://' . $tenants->first()->domain);
            //return redirect()->away('https://' . $tenants->first()->domain);
        }
        */

        return Inertia::render('Auth/SelectTenant', [
            'title' => 'Select Tenant',
            'tenants' => Auth::user()->tenants,
        ]);

        /*
        $tenant = Auth::user()->tenants()->first();

        if ($tenant) {
            return redirect()->away('https://' . $tenant->domain);
        }

        return redirect()->away(config('app.url'));
        */
    }

    public function selectTenant(Request $request, $tenantId)
    {
        try {
            $tenant = Auth::user()->tenants()->findOrFail($tenantId);

            $tenant->makeCurrent();

            $request->session()->put(config('multitenancy.session_tenant_key'), $tenant->getKey());

            //return Inertia::location('https://' . $tenant->domain);
            return response()->json(['message' => 'Tenant selected successfully', 'url' => 'https://'.$tenant->domain]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }
    }
}
