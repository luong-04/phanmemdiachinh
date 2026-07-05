<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại liên kết (tạm thời cho phép null để dễ test)
            $table->foreignId('user_id')->nullable();
            $table->foreignId('parcel_id')->nullable();
            
            // Dữ liệu đo đạc chi tiết
            $table->string('point_name')->nullable(); // Tên điểm (M1, M2...)
            // Tọa độ VN-2000 cần độ chính xác cao, dùng decimal 15 chữ số, 4 số thập phân
            $table->decimal('x_coord', 15, 4)->nullable(); 
            $table->decimal('y_coord', 15, 4)->nullable();
            $table->decimal('z_coord', 15, 4)->default(0); // Độ cao mặc định là 0
            $table->string('code')->nullable(); // Mã địa vật
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};