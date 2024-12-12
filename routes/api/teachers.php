<?php
use Illuminate\Support\Facades\Route;
// Route::middleware('role:2')->prefix('teacher')->as('teacher.')->group(function () {
//         // Lịch dạy của giảng viên
//         Route::get('schedules', [TeacherScheduleController::class, 'index']);
//         // Lịch dạy của giảng viên trong 1 lớp học
//         Route::get('classrooms/{classcode}/schedules', [TeacherScheduleController::class, 'listSchedulesForClassroom']);

//         Route::get('classrooms', [TeacherClassroomController::class, 'index']);
//         Route::get('classrooms/{classcode}', [TeacherClassroomController::class, 'show']);
//         Route::get('classrooms/{classcode}/students', [TeacherStudentController::class, 'listStudentForClassroom']);

//         Route::get('/attendances', [TeacherAttendanceController::class, 'index']);
//         Route::get('/attendances/{classCode}', [TeacherAttendanceController::class, 'show']);
//         Route::get('/attendances/edit/{classCode}', [TeacherAttendanceController::class, 'edit']);
//         Route::post('/attendances/{classCode}', [TeacherAttendanceController::class, 'store']);
//         Route::put('/attendances/{classCode}', [TeacherAttendanceController::class, 'update']);
//         Route::get('/attendances/showAllAttendance/{classCode}', [TeacherAttendanceController::class, 'showAllAttendance']);
//         Route::get('/attendances/{classCode}/{date}', [TeacherAttendanceController::class, 'showAttendanceByDate']);

//         Route::get('/grades/{id}', [TeacherGradesController::class, 'index']);
//         Route::get('/grades', [TeacherGradesController::class, 'getTeacherClass']);
//         Route::put('/grades/{id}', [TeacherGradesController::class, 'update']);


//         Route::apiResource('newsletters', TeacherNewsletterController::class);
//         Route::post('copyNewsletter/{code}', [TeacherNewsletterController::class, 'copyNewsletter']);
//         Route::post('/newsletters/updateActive/{code}', [NewsletterController::class, 'updateActive']);
//         Route::apiResource('categories', CategoryController::class);

//         Route::controller(TeacherAttendanceController::class)->group(function () {
//             Route::post('/import-attendances', 'importAttendance');
//             Route::get('/export-attendances', 'exportAttendance');
//         });



//     });
