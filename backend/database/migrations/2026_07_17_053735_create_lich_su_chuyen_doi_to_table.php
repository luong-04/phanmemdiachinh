<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_su_chuyen_doi_to', function (Blueprint $table) {
            $table->id();
            $table->string('chi_nhanh_cu')->nullable()->comment('Tên Chi nhánh VPĐKĐĐ cũ');
            $table->string('xa_cu')->nullable()->comment('Tên đơn vị hành chính cấp xã cũ');
            $table->string('ma_xa_cu', 20)->nullable()->comment('Mã xã cũ');
            $table->string('to_bddc_cu', 50)->nullable()->comment('Tờ BĐĐC cũ');
            $table->string('ty_le_cu', 50)->nullable()->comment('Tỷ lệ cũ');
            
            $table->string('chi_nhanh_moi')->nullable()->comment('Tên Chi nhánh VPĐKĐĐ mới');
            $table->string('xa_moi')->nullable()->comment('Tên đơn vị hành chính cấp xã mới');
            $table->string('ma_xa_moi', 20)->nullable()->comment('Mã xã mới');
            $table->string('to_bddc_moi', 50)->nullable()->comment('Tờ BĐĐC mới');
            $table->string('ty_le_moi', 50)->nullable()->comment('Tỷ lệ mới');
            
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_su_chuyen_doi_to');
    }
};