<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Point;

class SurveyParserController extends Controller
{
    public function uploadAndParse(Request $request)
    {
        // 1. Kiểm tra file đầu vào (bắt buộc phải là txt hoặc csv)
        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:2048', // Tối đa 2MB
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        
        // 2. Tách nội dung file thành từng dòng
        $lines = explode(PHP_EOL, $content);
        $parsedPoints = [];
        $errors = [];

        // 3. Quét từng dòng để bóc tách dữ liệu
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $separator = str_contains($line, ',') ? ',' : (str_contains($line, "\t") ? "\t" : ' ');
            $parts = preg_split("/[{$separator}]+/", $line);

            // Kịch bản 1: Máy trút ra tọa độ trực tiếp (X, Y, Z)
            // Chuẩn thường thấy: Tên điểm, Y(E), X(N), Z, Mã (Lưu ý đo đạc thường ngược X Y so với toán học)
            if (count($parts) >= 4 && is_numeric($parts[1]) && $parts[1] > 10000) {
                $parsedPoints[] = [
                    'point_name' => $parts[0],
                    'y_coord'    => (float) $parts[1], // Easting
                    'x_coord'    => (float) $parts[2], // Northing
                    'z_coord'    => (float) $parts[3],
                    'code'       => isset($parts[4]) ? $parts[4] : '',
                    'created_at' => now(), 'updated_at' => now(),
                ];
            } 
            // Kịch bản 2: Máy trút ra Góc & Cạnh (Tên điểm, Góc ngang, Khoảng cách ngang, Z)
            // Cần có tọa độ Trạm máy (Station) để nội suy - Giả định trạm máy ở (0,0) tạm thời
            elseif (count($parts) >= 3 && is_numeric($parts[1]) && $parts[1] < 360) {
                $angle_deg = (float) $parts[1];
                $distance = (float) $parts[2];
                
                // Chuyển độ sang radian để tính sin/cos
                $angle_rad = deg2rad($angle_deg);
                
                // Tính tọa độ nội suy (Giả định tịnh tiến từ trạm máy X0, Y0)
                $x0 = 0; $y0 = 0; // Thực tế sẽ lấy từ Form người dùng nhập
                $delta_x = $distance * cos($angle_rad);
                $delta_y = $distance * sin($angle_rad);

                $parsedPoints[] = [
                    'point_name' => $parts[0],
                    'x_coord'    => $x0 + $delta_x,
                    'y_coord'    => $y0 + $delta_y,
                    'z_coord'    => isset($parts[3]) ? (float) $parts[3] : 0,
                    'code'       => 'INTERPOLATED',
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
        }

        // 4. Lưu đồng loạt vào Supabase bằng Query Builder cho tốc độ tối đa
        if (count($parsedPoints) > 0) {
            DB::table('points')->insert($parsedPoints);
        }

        // 5. Trả kết quả JSON về cho Frontend
        return response()->json([
            'success' => true,
            'message' => 'Trút dữ liệu thành công!',
            'total_parsed' => count($parsedPoints),
            'errors' => $errors,
            'data' => $parsedPoints 
        ]);
    }
}