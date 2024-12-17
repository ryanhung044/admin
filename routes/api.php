<?php

use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\CheckoutLearnAgainController;
use App\Http\Controllers\ForgetPasswordController;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\AssessmentItem;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\ScoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Admin\CategoryNewsletter;
use App\Http\Controllers\AssessmentItemController;
use App\Http\Controllers\GetDataForFormController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\PointHeadController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SchoolRoomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\FeedbackController;

use App\Http\Controllers\SendEmailController;
use App\Http\Controllers\Teacher\ScheduleController as TeacherScheduleController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutServiceController;
use App\Http\Controllers\Teacher\ScheduleController;
use App\Http\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\TeacherGradesController;
use App\Http\Controllers\Teacher\NewsletterController as TeacherNewsletterController;
use App\Http\Controllers\Student\ScoreController as StudentScoreController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Student\ClassroomController as StudentClassroomController;
use App\Http\Controllers\StudentGradesController;
use App\Http\Controllers\Student\NewsletterController as StudentNewsletterController;
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Http\Controllers\Student\ServiceController;
use App\Http\Controllers\Student\ExamDayController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [AuthController::class, 'login']);
Route::get('automaticClassroom', [CategoryController::class, 'automaticClassroom']);
Route::post('getListClassByRoomAndSession', [CategoryController::class, 'getListClassByRoomAndSession']);
Route::get('addStudent', [CategoryController::class, 'addStudent']);
Route::get('addTeacher', [CategoryController::class, 'addTeacher']);
Route::get('generateSchedule', [CategoryController::class, 'generateSchedule']);
Route::get('/students/{student_code}', [StudentController::class, 'show']);
Route::apiResource('teachers', TeacherController::class);
Route::get('generateAttendances', [CategoryController::class, 'generateAttendances']);

// Route::apiResource('majors', MajorController::class);
// Route::get('getListMajor/{type}', [MajorController::class, 'getListMajor']);
Route::controller(TeacherClassroomController::class)->group(function () {
    Route::post('/import-scores', 'importScore');
    Route::get('/export-scores', 'exportScore');
});

Route::middleware('auth:sanctum')->group(function () {
    // Lấy thông tin tài khoản đang đăng  nhập
    Route::get('/user', function (Request $request) {
        try {
            $user = User::with([
                'course' => function ($query) {
                    $query->select('cate_code', 'cate_name', 'value');
                },
                'semester' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'narrow_major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                }
            ])->where('user_code', $request->user()->user_code)
                ->first();


            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi không xác định'
            ], 500);
        }
    });
    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route::apiResource('grades', GradesController::class);
    Route::get('grades/{classCode}', [GradesController::class, 'index']);
    Route::patch('grades/{id}', [GradesController::class, 'update']);

    // Khu vực admin
    Route::middleware('role:0')->prefix('/admin')->as('admin.')->group(function () {

        Route::apiResource('sessions', SessionController::class);
        Route::delete('sessions/{code}', [SessionController::class, 'destroy']);
        Route::post('sessions/{code}', [SessionController::class, 'update']);

        // khóa học
        Route::apiResource('course', CourseController::class);
        Route::put('course/{code}', [CourseController::class, 'update']);
        Route::delete('course/{code}', [CourseController::class, 'destroy']);

        Route::apiResource('teachers', TeacherController::class);
        Route::post('teachers/updateActive/{userCode}', [TeacherController::class, 'updateActive']);

        Route::apiResource('students', StudentController::class);
        Route::post('students/updateActive/{userCode}', [StudentController::class, 'updateActive']);

        Route::controller(StudentController::class)->group(function () {
            Route::post('import-students', 'importStudents');
            Route::get('export-students', 'exportStudents');
        });
        
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/subjects/{subject_code}', [SubjectController::class, 'show']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{subject_code}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject_code}', [SubjectController::class, 'destroy']);
        Route::apiResource('classrooms', ClassroomController::class);
        Route::post('/classrooms/updateActive/{classCode}', [ClassroomController::class, 'updateActive']);
        Route::controller(ClassroomController::class)->group(function () {
            Route::post('classrooms/handleStep1', 'handleStep1');
            Route::post('classrooms/renderSchedules', 'renderSchedules');
            Route::post('classrooms/renderRoomsAndTeachers', 'renderRoomsAndTeachers');
            Route::post('classrooms/handleStep2', 'handleStep2');
        });

        Route::controller(\App\Http\Controllers\Admin\ScheduleController::class)->group(function () {
            Route::get('transfer_schedule_timeframe', 'transfer_schedule_timeframe');
            Route::post('create_transfer_schedule_timeframe', 'create_transfer_schedule_timeframe');
            Route::get('classrooms/{class_code}/schedules', 'schedulesOfClassroom');
            Route::get('teachers/{teacher_code}/schedules', 'schedulesOfTeacher');
            Route::get('students/{student_code}/schedules', 'schedulesOfStudent');
        });


        Route::get('/majors/{major_code}/teachers', [MajorController::class, 'renderTeachersAvailable']);
        Route::apiResource('majors', MajorController::class);
        Route::get('getAllMajor/{type}', [MajorController::class, 'getAllMajor']);
        Route::apiResource('newsletters', NewsletterController::class);
        Route::post('copyNewsletter/{code}', [NewsletterController::class, 'copyNewsletter']);
        Route::post('/newsletters/updateActive/{code}', [NewsletterController::class, 'updateActive']);
        Route::apiResource('assessment', AssessmentItemController::class);
        Route::get('score/{id}', [ScoreController::class, 'create']);
        Route::apiResource('categories', CategoryController::class);
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/listParentCategories', 'listParentCategories');
            Route::get('/listChildrenCategories/{parent_code}', 'listChildrenCategories');
        });
        Route::get('getAllCategory/{type}', [CategoryController::class, 'getAllCategory']);
        Route::get('getListCategory/{type}', [CategoryController::class, 'getListCategory']);
        Route::post('uploadImage', [CategoryController::class, 'uploadImage']);

        Route::apiResource('semester', SemesterController::class);


        Route::get('classrooms/{class_code}/grades', [GradesController::class, 'show']);
        Route::patch('classrooms/{class_code}/grades', [GradesController::class, 'update']);
        Route::apiResource('schoolrooms', SchoolRoomController::class);
        Route::post('updateActive/{id}', [CategoryController::class, 'updateActive']);
        Route::apiResource('pointheads', PointHeadController::class);
        // Route::apiResource('newsletters', NewsletterController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::put('/attendances/{class_code}', [AttendanceController::class, 'update']);

        Route::apiResource('categoryNewsletters', CategoryNewsletter::class);
        Route::get('/majors/{major_code}/teachers', [MajorController::class, 'renderTeachersAvailable']);
        Route::put('/major/bulk-update-type', [MajorController::class, 'bulkUpdateType']);
        Route::apiResource('majors', MajorController::class);
        Route::get('getAllMajor/{type}', [MajorController::class, 'getAllMajor']);
        Route::apiResource('newsletters', NewsletterController::class);
        Route::post('copyNewsletter/{code}', [NewsletterController::class, 'copyNewsletter']);
        Route::put('/newsletters/bulk-update-type', [NewsletterController::class, 'bulkUpdateType']);
        Route::apiResource('assessment', AssessmentItemController::class);
        Route::get('score/{id}', [ScoreController::class, 'create']);
        Route::apiResource('categories', CategoryController::class);
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/listParentCategories', 'listParentCategories');
            Route::get('/listChildrenCategories/{parent_code}', 'listChildrenCategories');
        });
        Route::get('getAllCategory/{type}', [CategoryController::class, 'getAllCategory']);
        Route::get('getListCategory/{type}', [CategoryController::class, 'getListCategory']);
        Route::post('uploadImage', [CategoryController::class, 'uploadImage']);

        Route::apiResource('semesters', SemesterController::class);
        Route::apiResource('grades', GradesController::class);
        Route::get('grades', [GradesController::class, 'getByParam']);
        // Route::put('grades/{id}', [GradesController::class, 'update']);
        Route::apiResource('schoolrooms', SchoolRoomController::class);
        Route::put('/schoolrooms/bulk-update-type', [SchoolRoomController::class, 'bulkUpdateType']);
        Route::post('updateActive/{id}', [CategoryController::class, 'updateActive']);
        Route::apiResource('pointheads', PointHeadController::class);
        Route::put('/pointheads/bulk-update-type', [PointHeadController::class, 'bulkUpdateType']);
        // Route::apiResource('newsletters', NewsletterController::class);
        // Route::apiResource('attendances', AttendanceController::class);
        Route::apiResource('categoryNewsletters', CategoryNewsletter::class);
        Route::put('/newsletter/bulk-update-type', [CategoryNewsletter::class, 'bulkUpdateType']);
        Route::apiResource('fees', FeeController::class);


        Route::get('services',               [ServiceController::class, 'getAllServices']);
        Route::get('services/{id}',          [ServiceController::class, 'ServiceInformation']);
        Route::put('services/changeStatus/{id}',  [ServiceController::class, 'changeStatus']);
    });

    Route::middleware('role:2')->prefix('teacher')->as('teacher.')->group(function () {
        // Lịch dạy của giảng viên
        Route::controller(TeacherScheduleController::class)->group(function () {
            Route::get('schedules', 'listSchedulesForTeacher');
            // Lịch dạy của giảng viên trong 1 lớp học
            Route::get('classrooms/{classcode}/schedules', 'listSchedulesForClassroom');
        });

        Route::controller(TeacherClassroomController::class)->group(function () {
            Route::get('classrooms', 'index');
            Route::get('classrooms/{classcode}', 'show');
        });
        Route::get('classrooms/{classcode}/students', [TeacherStudentController::class, 'listStudentForClassroom']);

        Route::controller(ExamController::class)->group(function () {
            Route::get('classrooms/{classcode}/examdays', 'listExamDays');
            Route::post('classrooms/{classcode}/examdays', 'store');
        });


        Route::get('/attendances', [TeacherAttendanceController::class, 'index']);
        Route::get('/attendances/{classCode}', [TeacherAttendanceController::class, 'show']);
        Route::get('/attendances/edit/{classCode}', [TeacherAttendanceController::class, 'edit']);
        Route::post('/attendances/{classCode}', [TeacherAttendanceController::class, 'store']);
        Route::put('/attendances/{classCode}', [TeacherAttendanceController::class, 'update']);
        Route::get('/attendances/showAllAttendance/{classCode}', [TeacherAttendanceController::class, 'showAllAttendance']);
        Route::get('/attendances/{classCode}/{date}', [TeacherAttendanceController::class, 'showAttendanceByDate']);

        Route::get('/grades/{id}', [TeacherGradesController::class, 'index']);
        Route::get('/grades', [TeacherGradesController::class, 'getTeacherClass']);
        Route::put('/grades/{id}', [TeacherGradesController::class, 'update']);


        Route::apiResource('newsletters', TeacherNewsletterController::class);
        Route::post('copyNewsletter/{code}', [TeacherNewsletterController::class, 'copyNewsletter']);
        Route::post('/newsletters/updateActive/{code}', [NewsletterController::class, 'updateActive']);
        Route::apiResource('categories', CategoryController::class);

        Route::controller(TeacherAttendanceController::class)->group(function () {
            Route::post('/import-attendances', 'importAttendance');
            Route::get('/export-attendances', 'exportAttendance');
        });
    });

    Route::middleware('role:3')->prefix('student')->as('student.')->group(function () {
        Route::controller(StudentClassroomController::class)->group(function () {
            Route::get('/classrooms', 'index');
            Route::get('/classrooms/{class_code}', 'show');
        });
        Route::get('notifications', [StudentNewsletterController::class, 'showNoti']);


        // Các route cho lịch học
        Route::controller(StudentScheduleController::class)->group(function () {
            Route::get('schedules', 'index');
            Route::get('/classrooms/{class_code}/schedules', 'schedulesOfClassroom');
            Route::get('/transferSchedules', 'transferSchedules');
            Route::post('/listSchedulesCanBeTransfer', 'listSchedulesCanBeTransfer');
            Route::post('/handleTransferSchedule', 'handleTransferSchedule');
        });
        Route::get('/examDays', [ExamDayController::class, 'index']);

        Route::get('attendances', [StudentAttendanceController::class, 'index']);

        Route::get('/grades', [StudentGradesController::class, 'index']);

        Route::get('scoreTableByPeriod', [StudentScoreController::class, 'bangDiemTheoKy']);
        Route::get('scoreTable', [StudentScoreController::class, 'bangDiem']);

        Route::get('newsletters', [StudentNewsletterController::class, 'index']);
        Route::get('newsletters/{code}', [StudentNewsletterController::class, 'show']);
        Route::get('newsletters/{cateCode}', [StudentNewsletterController::class, 'showCategory']);
        Route::apiResource('transaction', TransactionController::class);
        Route::get('getListDebt', [FeeController::class, 'getListDebt']);

        Route::post('services/learn-again',    [ServiceController::class, "LearnAgain"]);
        Route::get('services/getListLearnAgain',    [ServiceController::class, "getListLearnAgain"]);
        Route::post('send-email/learn-again/{id}',  [SendEmailController::class, 'sendMailLearnAgain']);


        Route::post('change-password', [AuthController::class, 'changePassword']);

        Route::get('get-info', [ServiceController::class, 'StudentsInfoOld']);

        // dịch vụ cung cấp bảng điểm
        Route::post('services/register/dang-ky-cap-bang-diem',      [ServiceController::class, 'provideScoreboard']);
        // dịch vụ thay đổi thông tin
        Route::post('services/register/dang-ky-thay-doi-thong-tin', [ServiceController::class, 'ChangeInfo']);
        Route::get('services',      [ServiceController::class, 'getAllServicesByStudent']);

        Route::delete('services/delete/{id}',[ServiceController::class, 'cancelServiceByStudent']);


    });

    // Các route phục vụ cho form
    Route::controller(GetDataForFormController::class)->group(function () {
        Route::get('/listCoursesForForm', 'listCoursesForFrom');
        Route::get('/listSemestersForForm', 'listSemestersForForm');
        Route::get('/listMajorsForForm', 'listMajorsForForm');
        Route::get('/listParentMajorsForForm', 'listParentMajorsForForm');
        Route::get('/listChildrenMajorsForForm/{parent_code}', 'listChildrenMajorsForForm');
        Route::get('/listSubjectsToMajorForForm/{major_code}',  'listSubjectsToMajorForForm');
        Route::get('/subjects/{semester_code}/{major_code}', 'listSubjectsToSemesterAndMajorForForm');
        Route::get('/listSessionsForForm', 'listSessionsForForm');
        Route::get('/listRoomsForForm', 'listRoomsForForm');
        Route::get('/listSubjectsForForm', 'listSubjectsForForm');
    });
});

Route::apiResource('transaction', TransactionController::class);
Route::apiResource('wallet', WalletController::class);
Route::apiResource('feedback', FeedbackController::class);

Route::get('send-email', [SendEmailController::class, 'sendMailFee']);
Route::get('send-email2', [SendEmailController::class, 'sendMailFeeUser']);

// DashboardAdmin
Route::get('count-info',        [DashboardController::class, 'getCountInfo']);
Route::get('count-student',     [DashboardController::class, 'getStudentCountByMajor']);
Route::get('status-fee-date',   [DashboardController::class, 'getStatusFeesByDate']);
Route::get('status-fee-all',    [DashboardController::class, 'getStatusFeesAll']);
Route::get('status-attendances', [DashboardController::class, 'getStatusAttendances']);

// Admin
// Student
Route::post('services/change-major/{user_code}',            [ServiceController::class, 'changeMajor']);

// cung cấp thẻ sinh viên
Route::post('services/register/dang-ky-cap-lai-the',        [ServiceController::class, 'provideStudentCard']);

Route::apiResource('fees', FeeController::class);
//momo học phí
Route::get('momo-payment', [CheckoutController::class, 'momo_payment']);

//momo học lại
Route::get('total_momo/learn-again', [CheckoutLearnAgainController::class, 'momo_payment']);

// quên mật khẩu
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgetPasswordPost']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPasswordPost']);

Route::get('total_vnpay/service',       [CheckoutServiceController::class,  'vnpay_payment']);
Route::get('total_momo/service',        [CheckoutServiceController::class,  'momo_payment']);
Route::get('return-vnpay', [CheckoutController::class, 'vnpay_payment_return']);


Route::get('total_momo/service',        [CheckoutServiceController::class, 'momo_payment']);


// api dẫn đến trang thanh toán vnpay của dịch vụ
Route::get('total_vnpay/service', [CheckoutServiceController::class, 'vnpay_payment'])
    ->name('total_vnpay_service');

Route::get('return-vnpay/service', [CheckoutServiceController::class, 'vnpay_payment_return']);
Route::get('failed-vnpay', [CheckoutServiceController::class, 'vnpay_payment_fail'])->name('payment.failed');
Route::get('success-vnpay', [CheckoutServiceController::class, 'vnpay_payment_success'])->name('payment.success');
