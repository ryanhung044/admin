<?php
use Illuminate\Support\Facades\Route;

// Route::middleware('role:0')->prefix('/admin')->as('admin.')->group(function () {
    //     Route::apiResource('teachers', TeacherController::class);
    //     Route::apiResource('students', StudentController::class);
    //     Route::controller(StudentController::class)->group(function () {
    //         Route::post('import-students', 'importStudents');
    //         Route::get('export-students', 'exportStudents');
    //     });
    //     Route::get('/subjects', [SubjectController::class, 'index']);
    //     Route::get('/subjects/{id}', [SubjectController::class, 'show']);
    //     Route::post('/subjects', [SubjectController::class, 'store']);
    //     Route::put('/subjects/{id}', [SubjectController::class, 'update']);
    //     Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);
    //     Route::apiResource('classrooms', ClassroomController::class);
    //     Route::post('/classrooms/updateActive/{classCode}', [ClassroomController::class, 'updateActive']);
    //     Route::controller(ClassroomController::class)->group(function () {
    //         Route::post('classrooms/handleStep1', 'handleStep1');
    //         Route::post('classrooms/renderSchedules', 'renderSchedules');
    //         Route::post('classrooms/renderRoomsAndTeachers', 'renderRoomsAndTeachers');
    //         Route::post('classrooms/handleStep2', 'handleStep2');


    //     Route::controller(\App\Http\Controllers\Admin\ScheduleController::class)->group(function(){
    //         Route::get('transfer_schedule_timeframe', 'transfer_schedule_timeframe');
    //         Route::post('create_transfer_schedule_timeframe', 'create_transfer_schedule_timeframe');
    //         Route::get('classrooms/{class_code}/schedules', 'schedulesOfClassroom');
    //         Route::get('teachers/{teacher_code}/schedules', 'schedulesOfTeacher');
    //         Route::get('students/{student_code}/schedules', 'schedulesOfStudent');
    //     });

    //     Route::controller(\App\Http\Controllers\Admin\ScheduleController::class)->group(function () {
    //         Route::get('classrooms/{class_code}/schedules', 'schedulesOfClassroom');
    //         Route::get('teachers/{teacher_code}/schedules', 'schedulesOfTeacher');
    //         Route::get('students/{student_code}/schedules', 'schedulesOfStudent');
    //     });
    //     Route::get('/majors/{major_code}/teachers', [MajorController::class, 'renderTeachersAvailable']);
    //     Route::apiResource('majors', MajorController::class);
    //     Route::get('getAllMajor/{type}', [MajorController::class, 'getAllMajor']);
    //     Route::apiResource('newsletters', NewsletterController::class);
    //     Route::post('copyNewsletter/{code}', [NewsletterController::class, 'copyNewsletter']);
    //     Route::post('/newsletters/updateActive/{code}', [NewsletterController::class, 'updateActive']);
    //     Route::apiResource('assessment', AssessmentItemController::class);
    //     Route::get('score/{id}', [ScoreController::class, 'create']);
    //     Route::apiResource('categories', CategoryController::class);
    //     Route::controller(CategoryController::class)->group(function () {
    //         Route::get('/listParentCategories', 'listParentCategories');
    //         Route::get('/listChildrenCategories/{parent_code}', 'listChildrenCategories');
    //     });
    //     Route::get('getAllCategory/{type}', [CategoryController::class, 'getAllCategory']);
    //     Route::get('getListCategory/{type}', [CategoryController::class, 'getListCategory']);
    //     Route::post('uploadImage', [CategoryController::class, 'uploadImage']);

    //     Route::apiResource('semesters', SemesterController::class);

    //     Route::apiResource('course', CourseController::class);
    //     Route::apiResource('sessions', SessionController::class);
    //     Route::delete('sessions/{code}',[SessionController::class,'destroy']);

    //     Route::get('classrooms/{class_code}/grades', [GradesController::class, 'show']);
    //     Route::patch('classrooms/{class_code}/grades', [GradesController::class, 'update']);
    //     Route::apiResource('schoolrooms', SchoolRoomController::class);
    //     Route::post('updateActive/{id}', [CategoryController::class, 'updateActive']);
    //     Route::apiResource('pointheads', PointHeadController::class);
    //     // Route::apiResource('newsletters', NewsletterController::class);
    //     Route::apiResource('attendances', AttendanceController::class);
    //     Route::apiResource('categoryNewsletters', CategoryNewsletter::class);

    //     // Route::controller(ClassroomController::class)->group(function () {
    //     //     Route::post('classrooms/handle_step1', 'handleStep1');
    //     //     Route::post('classrooms/handle_step2', 'handleStep2');
    //     //     Route::post('classrooms/handle_step3', 'handleStep3');
    //     // });
    //     Route::get('/majors/{major_code}/teachers', [MajorController::class, 'renderTeachersAvailable']);
    //     Route::put('/major/bulk-update-type', [MajorController::class, 'bulkUpdateType']);
    //     Route::apiResource('majors', MajorController::class);
    //     Route::get('getAllMajor/{type}', [MajorController::class, 'getAllMajor']);
    //     Route::apiResource('newsletters', NewsletterController::class);
    //     Route::post('copyNewsletter/{code}', [NewsletterController::class, 'copyNewsletter']);
    //     Route::put('/newsletters/bulk-update-type', [NewsletterController::class, 'bulkUpdateType']);
    //     Route::apiResource('assessment', AssessmentItemController::class);
    //     Route::get('score/{id}', [ScoreController::class, 'create']);
    //     Route::apiResource('categories', CategoryController::class);
    //     Route::controller(CategoryController::class)->group(function () {
    //         Route::get('/listParentCategories', 'listParentCategories');
    //         Route::get('/listChildrenCategories/{parent_code}', 'listChildrenCategories');
    //     });
    //     Route::get('getAllCategory/{type}', [CategoryController::class, 'getAllCategory']);
    //     Route::get('getListCategory/{type}', [CategoryController::class, 'getListCategory']);
    //     Route::post('uploadImage', [CategoryController::class, 'uploadImage']);

    //     Route::apiResource('semesters', SemesterController::class);
    //     Route::apiResource('grades', GradesController::class);
    //     Route::get('grades', [GradesController::class, 'getByParam']);
    //     Route::patch('grades/{id}', [GradesController::class, 'update']);
    //     Route::apiResource('schoolrooms', SchoolRoomController::class);
    //     Route::put('/schoolrooms/bulk-update-type', [SchoolRoomController::class, 'bulkUpdateType']);
    //     Route::post('updateActive/{id}', [CategoryController::class, 'updateActive']);
    //     Route::apiResource('pointheads', PointHeadController::class);
    //     Route::put('/pointheads/bulk-update-type', [PointHeadController::class, 'bulkUpdateType']);
    //     // Route::apiResource('newsletters', NewsletterController::class);
    //     Route::apiResource('attendances', AttendanceController::class);
    //     Route::apiResource('categoryNewsletters', CategoryNewsletter::class);
    //     Route::put('/newsletter/bulk-update-type', [CategoryNewsletter::class, 'bulkUpdateType']);
    //     Route::apiResource('fees', FeeController::class);
    // });
    // });
