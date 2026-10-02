<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// ==========================================
// DEPLOYMENT ROUTES FOR HOSTINGER (NO TERMINAL)
// ==========================================

Route::get('/run-migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'message' => 'Database successfully updated without data loss!',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/clear-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return response()->json([
            'status' => 'success',
            'message' => 'Server Cache cleared successfully!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/fix-unknown-sites', function () {
    $updatedIn = 0;
    $updatedOut = 0;
    
    // 1. Fix Material IN records
    $materialsIn = \App\Models\MaterialIn::whereNull('site_id')->get();
    foreach ($materialsIn as $mat) {
        $reports = \App\Models\DailyReport::where('date', $mat->date)->get();
        if ($reports->count() > 0) {
            $mat->site_id = $reports->first()->site_id;
            $mat->save();
            $updatedIn++;
        }
    }
    
    // 2. Fix Material OUT records
    $materialsOut = \App\Models\MaterialOut::whereNull('site_id')->get();
    foreach ($materialsOut as $mat) {
        $reports = \App\Models\DailyReport::where('date', $mat->date)->get();
        if ($reports->count() > 0) {
            $mat->site_id = $reports->first()->site_id;
            $mat->save();
            $updatedOut++;
        }
    }
    
    return "Success! Mapped $updatedIn Material IN records and $updatedOut Material OUT records to their correct sites based on their dates.";
});
