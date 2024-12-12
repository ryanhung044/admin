<?php
use Illuminate\Support\Facades\Route;

// Route::middleware('role:3')->prefix('student')->as('student.')->group(function () {
//         Route::controller(StudentClassroomController::class)->group(function () {
//             Route::get('/classrooms', 'index');
//             Route::get('/classrooms/{class_code}', 'show');
//         });
//         Route::get('notifications', [StudentNewsletterController::class, 'showNoti']);


//         // Các route cho lịch học
//         Route::controller(StudentScheduleController::class)->group(function () {
//             Route::get('schedules', 'index');
//             Route::get('/classrooms/{class_code}/schedules', 'schedulesOfClassroom');
//             Route::get('/transferSchedules', 'transferSchedules');
//             Route::post('/listSchedulesCanBeTransfer', 'listSchedulesCanBeTransfer');
//             Route::post('/handleTransferSchedule', 'handleTransferSchedule');
//         });

//         Route::get('attendances', [StudentAttendanceController::class, 'index']);

//         Route::get('/grades', [StudentGradesController::class, 'index']);

//         Route::get('scoreTableByPeriod', [StudentScoreController::class, 'bangDiemTheoKy']);
//         Route::get('scoreTable', [StudentScoreController::class, 'bangDiem']);

//         Route::get('newsletters', [StudentNewsletterController::class, 'index']);
//         Route::get('newsletters/{code}', [StudentNewsletterController::class, 'show']);
//         Route::get('newsletters/{cateCode}', [StudentNewsletterController::class, 'showCategory']);
//         Route::apiResource('transaction', TransactionController::class);
//         Route::get('getListDebt', [FeeController::class, 'getListDebt']);
//     });
