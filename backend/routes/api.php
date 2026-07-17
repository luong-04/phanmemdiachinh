<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SurveyParserController;
use App\Http\Controllers\ImportController;

// Route nhận file Excel từ Next.js gửi sang
Route::post('/import-chuyen-doi', [ImportController::class, 'importDanhSachChuyenDoi']);
// Route lấy dữ liệu hiển thị
Route::get('/get-chuyen-doi', [ImportController::class, 'getDanhSachChuyenDoi']);

// Route nhận file trút tọa độ
Route::post('/parse-survey-file', [SurveyParserController::class, 'uploadAndParse']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Route trút dữ liệu tọa độ từ máy đo đạc
Route::post('/import-toa-do', [ImportController::class, 'importToaDo']);
