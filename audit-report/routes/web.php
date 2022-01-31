<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('file_upload');
});
// Route::get('/', [FileUploadController::class, 'ParseAndSaveExcelData'])->name('save-excel');
Route::post('/file-upload', [FileUploadController::class, 'uploadToServer'])->name('file-upload');
Route::post('/save-excel', [FileUploadController::class, 'ParseAndSaveExcelData'])->name('save-excel');
Route::get('/report', [HomeController::class, 'index'])->name('report');
Route::post('/wcag-issues-chart', [HomeController::class, 'WCAG_Issues_Chart'])->name('wcag-issues-chart');
Route::post('/user-impact-chart', [HomeController::class, 'Severity_Chart'])->name('user-impact-chart');
Route::post('/top-ten-issues-2-0-chart', [HomeController::class, 'TopTenIssues_2_0'])->name('top-ten-issues-2-0-chart');
Route::post('/top-ten-issues-2-1-chart', [HomeController::class, 'TopTenIssues_2_1'])->name('top-ten-issues-2-1-chart');
Route::post('/top-ten-issues-2-1-chart', [HomeController::class, 'TopTenIssues_2_1'])->name('top-ten-issues-2-1-chart');
Route::post('/wcag-conformance-level-a-chart', [HomeController::class, 'Conformance_Level_a'])->name('wcag-conformance-level-a-chart');
Route::post('/wcag-conformance-level-aa-chart', [HomeController::class, 'Conformance_Level_aa'])->name('wcag-conformance-level-aa-chart');
//
Route::post('/save-chart-img', [HomeController::class, 'SaveChartImg'])->name('save-chart-img');
//
Route::get('/pdf-preview', [HomeController::class, 'PdfView'])->name('pdf-preview');
Route::get('/export-as-pdf', [FileUploadController::class, 'ExportAsPdf'])->name('export-as-pdf');
// Route::get('/get-data/{file}', [HomeController::class, 'GetExcelData'])->name('get-data');
Route::get('/summary/{file}', [HomeController::class, 'GetExcelData'])->name('preview');
Route::get('/export-summary/{file}', [HomeController::class, 'ExportSummary'])->name('export-sumary');
