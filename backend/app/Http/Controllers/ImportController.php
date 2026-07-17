<?php
// app/Http/Controllers/ImportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function importDanhSachChuyenDoi(Request $request)
    {
        // 1. Kiểm tra file upload
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');

        // 2. Dùng thư viện đọc dữ liệu thành mảng (Array)
        $data = Excel::toArray([], $file);
        
        // Giả sử sheet đầu tiên chứa dữ liệu
        $rows = $data[0]; 
        $insertData = [];

        // 3. Lặp qua các dòng (bỏ qua dòng tiêu đề) và map vào các cột của Database
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Bỏ qua Header

            $insertData[] = [
                'chi_nhanh_cu'  => $row[1], // Đã sửa tên cột ở đây
                'xa_cu'         => $row[2],
                'ma_xa_cu'      => $row[3],
                'to_bddc_cu'    => $row[4],
                'chi_nhanh_moi' => $row[6], // Và ở đây
                'xa_moi'        => $row[7],
                'ma_xa_moi'     => $row[8],
                'to_bddc_moi'   => $row[9],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // 4. Insert hàng loạt vào database Supabase (qua Eloquent/DB facade)
        DB::table('lich_su_chuyen_doi_to')->insert($insertData);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã import thành công ' . count($insertData) . ' dòng dữ liệu!',
        ]);
    }
    // Hàm lấy dữ liệu để hiển thị ra bảng
    public function getDanhSachChuyenDoi()
    {
        $data = DB::table('lich_su_chuyen_doi_to')
            ->whereNotNull('chi_nhanh_cu') // 1. Bỏ qua tất cả các dòng trống rỗng
            ->where('chi_nhanh_cu', '!=', 'Tên Chi nhánh VPĐKĐĐ cũ') // 2. Bỏ qua dòng tiêu đề bị lọt vào CSDL
            ->orderBy('id', 'asc') // 3. Sắp xếp từ trên xuống dưới (cũ nhất đến mới nhất) để đọc cho thuận mắt
            ->limit(100)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
    // Hàm xử lý trút file tọa độ không gian (Cánh cổng số 2)
    public function importToaDo(Request $request)
    {
        // 1. Nhận file từ Frontend
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        
        // 2. Đọc nội dung file Text
        $content = file_get_contents($file->getRealPath());
        $lines = explode("\n", $content);

        $coordinates = [];
        
        // 3. Lặp qua từng dòng để bóc tách X, Y
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue; // Bỏ qua dòng trống

            // Tách các cột bằng khoảng trắng hoặc phẩy
            $parts = preg_split('/[\s,]+/', $line);
            
            // Giả sử định dạng chuẩn: Tên_Điểm X Y ...
            // Ta lấy cột số 2 (index 1) làm X, cột số 3 (index 2) làm Y
            if (count($parts) >= 3) {
                $x = $parts[1];
                $y = $parts[2];
                
                if (is_numeric($x) && is_numeric($y)) {
                    // Cấu trúc của PostGIS yêu cầu định dạng X Y (cách nhau bởi khoảng trắng)
                    $coordinates[] = "$x $y";
                }
            }
        }

        // Một đa giác (Polygon) phải có ít nhất 3 điểm
        if (count($coordinates) < 3) {
            return response()->json([
                'status' => 'error', 
                'message' => 'File không đủ 3 điểm để tạo thành thửa đất!'
            ], 400);
        }

        // 4. Khép kín đa giác (Điểm cuối phải trùng với điểm đầu)
        if ($coordinates[0] !== end($coordinates)) {
            $coordinates[] = $coordinates[0];
        }

        // 5. Nặn thành chuỗi hình học (Well-Known Text)
        $polygonWKT = "POLYGON((" . implode(", ", $coordinates) . "))";

        // 6. Lưu thẳng vào DB với hàm không gian ST_GeomFromText của PostGIS
        DB::table('thua_dat')->insert([
            'ten_chu_su_dung' => 'Thửa đất import từ file: ' . $file->getClientOriginalName(),
            'geom' => DB::raw("ST_GeomFromText('$polygonWKT', 4326)"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã trút tọa độ và vẽ thành công 1 thửa đất vào bản đồ!',
            'wkt' => $polygonWKT // Trả về để tí nữa vẽ lên web
        ]);
    }
}