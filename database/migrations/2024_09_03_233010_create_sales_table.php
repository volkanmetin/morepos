<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_method'); // cash, pos1, pos2
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_rate', 5, 2); // KDV oranı
            $table->decimal('tax_amount', 10, 2); // KDV tutarı
            $table->decimal('discount_amount', 10, 2)->default(0); // Toplam indirim tutarı
            $table->json('manual_discount')->nullable(); // Manuel indirim detayları
            $table->decimal('total', 10, 2);
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->json('product_snapshot')->nullable(); // Ürün verilerinin anlık görüntüsü
            $table->json('variant_snapshot')->nullable(); // Varyant verilerinin anlık görüntüsü
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
}; 