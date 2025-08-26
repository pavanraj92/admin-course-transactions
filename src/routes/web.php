<?php

use Illuminate\Support\Facades\Route;
use admin\course_transactions\Controllers\TransactionManagerController;
use admin\course_transactions\Controllers\CoursePurchaseManagerController;

Route::name('admin.')->middleware(['web', 'admin.auth'])->group(function () {
    Route::resource('transactions', TransactionManagerController::class);
    Route::resource('course-purchases', CoursePurchaseManagerController::class);
});
