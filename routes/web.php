<?php
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutLearnAgainController;
use App\Http\Controllers\CheckoutServiceController;
use App\Http\Controllers\FeeController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ForgerPasswordController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\SendEmailController;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    return view('welcome');
});

    Route::get('/formCreateSchedule', [ClassroomController::class, 'formCreateScheduleforClassroom']);
Route::post('/renderScheduleForClassroom', [ClassRoomController::class, 'renderScheduleForClassroom'])->name('renderScheduleForClassroom');


// Route::get('total_momo', [CheckoutController::class, 'momo_payment']);


Route::post('/payment-callback', [CheckoutController::class, 'handleCallback']);
Route::get('/payment-success', [CheckoutController::class, 'handleCallback']);

//view
Route::get('/forgot-password', [ForgetPasswordController::class,'forgetPassword'])
                                            ->name('forget.password');
//trả email
Route::post('/forgot-password', [ForgetPasswordController::class,'forgetPasswordPost'])
                                            ->name('forget.password.post');

// view đổi mật khẩu
Route::get('/reset-password', [ForgetPasswordController::class,'resetPassword'])
                                            ->name('reset.password');
Route::post('/reset-password',[ForgetPasswordController::class, 'resetPasswordPost'])
                                            ->name('reset.password.post');



// Route::get('total_momo/learn-again',    [CheckoutLearnAgainController::class, 'momo_payment']);
// Route::get('total_momo/service',        [CheckoutServiceController::class, 'momo_payment']);


Route::get('payment-callback/service', [CheckoutServiceController::class, 'handleCallback']);
Route::get('payment-success/service', [CheckoutServiceController::class, 'PaymentSuccess']);

Route::post('/payment-callback/learn-again', [CheckoutLearnAgainController::class, 'handleCallback']);
Route::get('/payment-success/learn-again', [CheckoutLearnAgainController::class, 'handleCallback']);


Route::post('/send-email/learn-again/{id}/{subject_code}',  [SendEmailController::class, 'sendMailLearnAgain']);


// Route::get('total_vnpay/service', [CheckoutServiceController::class, 'vnpay_payment']);


// Route::get('button_payment', function(){
//     return view('test');
// });

// Route::get('return-vnpay', [CheckoutServiceController::class, 'vnpay_payment_return']);

// Route::get('failed-vnpay', [CheckoutServiceController::class, 'vnpay_payment_fail'])->name('payment.failed');
// Route::get('success-vnpay', [CheckoutServiceController::class, 'vnpay_payment_success'])->name('payment.success');

