<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thua_dat', function (Blueprint $table) {
            $table->id();
            $table->string('to_ban_do', 50)->nullable()->comment('Số thứ tự tờ bản đồ');
            $table->string('so_thua', 50)->nullable()->comment('Số thứ tự thửa đất');
            $table->string('ten_chu_su_dung')->nullable()->comment('Tên chủ sử dụng đất');
            $table->string('loai_dat', 20)->nullable()->comment('Mục đích sử dụng (ONT, CLN, LUC...)');
            $table->decimal('dien_tich_phap_ly', 10, 2)->nullable()->comment('Diện tích trên giấy tờ (m2)');
            $table->string('dia_chi')->nullable()->comment('Địa chỉ thửa đất');
            
            // Cột cực kỳ quan trọng: Lưu hình học đa giác (Polygon) của thửa đất
            $table->geometry('geom', 'polygon', 4326)->nullable()->comment('Tọa độ không gian WGS84');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thua_dat');
    }
};