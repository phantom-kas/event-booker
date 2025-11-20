<?php

use App\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
  Route::post('/bookings/{id}/payment', [PaymentsController::class, 'pay']);
   Route::get('/payments/{id}', [PaymentsController::class, 'show']);

})
?>