<?php

use App\Http\Controllers\AttributeGroupController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Product2Controller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CategoryController;
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SaleController;
Route::domain(config('app.domain'))->middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
    Route::get('/', [HomeController::class, 'redirectToTenant']);
    //Route::get('/', [HomeController::class, 'redirectToTenant'])->name('home');
    Route::post('select-tenant/{tenantId}', [HomeController::class, 'selectTenant'])->name('select-tenant');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'tenant'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('set-locale', [LocaleController::class, 'setLocale'])->name('setLocale');
    Route::get('search', [SearchController::class, 'search'])->name('search');

    Route::get('test', function () {
        $file = storage_path('app/public/toplu2.xlsx');
        DB::beginTransaction();
        try {
            Excel::import(new ProductsImport, $file);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    });

    Route::get('ikas', function () {

        /*
        $response = Http::post('https://morekibris.myikas.com/api/admin/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => '1352f158-cde0-4670-8576-d9d34b28537d',
            'client_secret' => 's_D9zdDN7LUGysSNdcwDxTCaxh2e40d72b4a674decaf15d26b93455226',
        ]);
        $token = $response->json()['access_token'];
        ray($response->json());
        */

        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjEzNTJmMTU4LWNkZTAtNDY3MC04NTc2LWQ5ZDM0YjI4NTM3ZCIsImVtYWlsIjoibW9yZXBvcyIsImZpcnN0TmFtZSI6Im1vcmVwb3MiLCJsYXN0TmFtZSI6IiIsInN0b3JlTmFtZSI6Im1vcmVraWJyaXMiLCJtZXJjaGFudElkIjoiMWI1NWM4YTEtZmZhYi00MjU5LTg1YmMtYWY5MTgxZDI3ZDMyIiwiZmVhdHVyZXMiOlsxMCwxMSwxMiwyLDIwMSwzLDQsNSw3LDgsOV0sImF1dGhvcml6ZWRBcHBJZCI6IjEzNTJmMTU4LWNkZTAtNDY3MC04NTc2LWQ5ZDM0YjI4NTM3ZCIsInR5cGUiOjQsImV4cCI6MTczNDgzMTYwODU4NywiaWF0IjoxNzM0ODE3MjA4NTg3LCJpc3MiOiIxYjU1YzhhMS1mZmFiLTQyNTktODViYy1hZjkxODFkMjdkMzIiLCJzdWIiOiIxMzUyZjE1OC1jZGUwLTQ2NzAtODU3Ni1kOWQzNGIyODUzN2QifQ.49XrV735NllTfcTENCPYdeXJkAibeVKcr-awO8-WW6k';

        $response = Http::withToken($token)
            ->post('https://api.myikas.com/api/v1/admin/graphql', [
                'query' => 'query {
                    listProduct(id: {eq: "4c3e7ee0-457e-4efa-9578-b407240cba32"}) {
                        count
                        data {
                            id
                            name
                            createdAt
                            totalStock
                            variants {
                                id
                                sku
                                barcodeList
                                images {
                                    imageId
                                    fileName
                                    isMain
                                    order
                                }
                                prices {
                                    currency
                                    buyPrice
                                    sellPrice
                                    discountPrice
                                }
                                stocks {
                                    id
                                    variantId
                                    stockLocationId
                                    stockCount
                                }
                            }
                        }
                    }
                }'
            ])
            ->json();

        return $response;
        dd($response);

    });

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/search-customers', [PosController::class, 'searchCustomers'])->name('search-customers');
        Route::post('/customers', [PosController::class, 'createCustomer'])->name('create-customer');
        Route::get('/sale/{customerId}', [PosController::class, 'pos'])->name('sale');
        Route::post('/check-coupon', [PosController::class, 'checkCoupon'])->name('check-coupon');
    });

    // Vendors
    Route::prefix('vendors')->as('vendor.')->group(function () {
        Route::get('/', [VendorController::class, 'index'])->name('index');
        Route::get('data', [VendorController::class, 'getData'])->name('data');
        Route::get('{id}', [VendorController::class, 'show'])->name('show');
        Route::post('/', [VendorController::class, 'store'])->name('store');
        Route::put('{id}', [VendorController::class, 'update'])->name('update');
        Route::delete('{id}', [VendorController::class, 'destroy'])->name('destroy');
    });

    // Products
    Route::prefix('products')->as('product.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('data', [ProductController::class, 'getData'])->name('data');
        Route::get('search', [SearchController::class, 'products'])->name('search');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('create', [ProductController::class, 'store']);
        Route::post('upload-image', [ProductController::class, 'uploadImage'])->name('upload-image');
        Route::get('{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('{id}', [ProductController::class, 'update'])->name('update');
        Route::get('{id}', [ProductController::class, 'show'])->name('show');
        Route::delete('{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Warehouses
    Route::prefix('warehouses')->as('warehouse.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('data', [WarehouseController::class, 'getData'])->name('data');
        Route::get('{id}', [WarehouseController::class, 'show'])->name('show');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::put('{id}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('{id}', [WarehouseController::class, 'destroy'])->name('destroy');
    });

    // Brands
    Route::prefix('brands')->as('brand.')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::get('data', [BrandController::class, 'getData'])->name('data');
        Route::get('{id}', [BrandController::class, 'show'])->name('show');
        Route::post('/', [BrandController::class, 'store'])->name('store');
        Route::put('{id}', [BrandController::class, 'update'])->name('update');
        Route::delete('{id}', [BrandController::class, 'destroy'])->name('destroy');
    });

    // Customers
    Route::prefix('customers')->as('customer.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('data', [CustomerController::class, 'getData'])->name('data');
        Route::get('{id}', [CustomerController::class, 'show'])->name('show');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::put('{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('{id}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    // Attribute Groups
    Route::prefix('attribute-groups')->as('attribute-group.')->group(function () {
        Route::get('/', [AttributeGroupController::class, 'index'])->name('index');
        Route::get('data', [AttributeGroupController::class, 'getData'])->name('data');
        Route::get('{id}', [AttributeGroupController::class, 'show'])->name('show');
        Route::post('/', [AttributeGroupController::class, 'store'])->name('store');
        Route::put('{id}', [AttributeGroupController::class, 'update'])->name('update');
        Route::delete('{id}', [AttributeGroupController::class, 'destroy'])->name('destroy');
    });

    // Categories
    Route::prefix('categories')->as('category.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('list', [CategoryController::class, 'list'])->name('list');
        Route::get('data', [CategoryController::class, 'getData'])->name('data');
        Route::get('{id}', [CategoryController::class, 'show'])->name('show');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::resource('products2', Product2Controller::class);

    //Route::get('products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    //Route::get('/products/{product}', [ProductController::class, 'show'])->name('product.show');

    // Sales Routes
    Route::resource('sales', SaleController::class)->only(['index', 'store', 'show']);
});

//Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
//Route::get('/pos/products', [PosController::class, 'getProducts'])->name('pos.products');

//Route::get('/search', [SearchController::class, 'search'])->name('search');
