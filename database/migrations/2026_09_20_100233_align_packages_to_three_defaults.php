<?php

use App\Models\Package;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Đồng bộ danh mục Gói dịch vụ: chỉ giữ 3 gói bản quyền cốt lõi mặc định.
     */
    public function up(): void
    {
        Package::where('slug', 'goi-toan-nang-vip-enterprise')
            ->orWhere('id', 4)
            ->update(['is_active' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Package::where('slug', 'goi-toan-nang-vip-enterprise')
            ->orWhere('id', 4)
            ->update(['is_active' => true]);
    }
};
