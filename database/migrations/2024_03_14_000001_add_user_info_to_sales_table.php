<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('user_id')->after('tenant_id')->constrained()->onDelete('restrict');
            $table->string('ip_address')->nullable()->after('notes');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->json('manual_discount')->nullable()->change(); // Mevcut kolonu json tipine çeviriyoruz
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'ip_address', 'user_agent']);
            $table->string('manual_discount')->nullable()->change();
        });
    }
}; 