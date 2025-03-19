<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MgmtAdmissionController;
use App\Http\Controllers\SelfAdmissionController;
use App\Http\Controllers\SpotcounsellingController;
use App\Http\Controllers\ManagementAdmissionController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/is-maintenance', [AuthController::class, 'maintenance']);

Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/validate-security-code', [AuthController::class, 'validateSecurityCode']);
Route::post('/spot-authenticate', [AuthController::class, 'spotlogin']);
Route::post('/spot-validate-security-code', [AuthController::class, 'validateSpotOtp']);

Route::prefix('master')->group(function () {
    Route::post('/district-list', [CommonController::class, 'allDistricts']);
    Route::post('/institute-stream-wise', [CommonController::class, 'allInstList']);
    Route::post('/institute-wise-stream', [CommonController::class, 'streamListinstListWise']);
    Route::post('/trade-list', [CommonController::class, 'streamList']);
    Route::post('/state-list', [CommonController::class, 'allStates']);
    Route::post('/religion-list', [CommonController::class, 'allReligions']);
    Route::post('/caste-list', [CommonController::class, 'allCastes']);
});

Route::prefix('student')->group(function () {
    Route::post('/profile-district-save', [StudentController::class, 'saveProfileDistrict']);
    Route::post('/choice-entry', [StudentController::class, 'choiceEntry']);
    Route::post('/choice-list', [StudentController::class, 'choiceList']);
    Route::post('/choice-remove', [StudentController::class, 'choiceRemove']);
    Route::post('/choice-up-down', [StudentController::class, 'choiceUpDown']);
    Route::post('/choice-lock-final-submit', [StudentController::class, 'choiceLockFinalSubmit']);
    Route::post('/activities', [StudentController::class, 'activities']);
    Route::get('/choice-fill-up-pdf', [StudentController::class, 'choicePdf']);
    Route::post('/allotement-details', [StudentController::class, 'allotementDetails']);
    Route::get('/check-status/{user_id}', [StudentController::class, 'checkRedirect']);
    Route::get('/allotment-pdf', [StudentController::class, 'allotmentPdf']);
    Route::get('/registration-pdf', [StudentController::class, 'registrationPdf']);
    Route::get('/accept-allotment', [StudentController::class, 'allotmentAccept']);
    Route::get('/upgrade-allotment', [StudentController::class, 'allotmentUpgrade']);

    //Spot admission student
    Route::post('/student-info', [StudentController::class, 'getSpotStudentInfo']);
    Route::post('/student-info-update', [StudentController::class, 'spotStudentInfoUpdate']);
    Route::get('/spot-student-status/{user_id}', [StudentController::class, 'getStudentOverallStatus']);
    Route::post('/spot-institute-list', [StudentController::class, 'allInstList']);
    Route::post('/spot-institute-wise-stream', [StudentController::class, 'streamListinstListWise']);
    Route::get('/spot-registration-pdf', [StudentController::class, 'SpotRegistrationPdf']);
    Route::post('/spot-choice-entry', [StudentController::class, 'spotchoiceEntry']);
    Route::post('/spot-choice-list', [StudentController::class, 'spotchoiceList']);
    Route::post('/spot-choice-remove', [StudentController::class, 'spotchoiceRemove']);
    Route::post('/spot-choice-up-down', [StudentController::class, 'spotchoiceUpDown']);
    Route::post('/spot-choice-lock-final-submit', [StudentController::class, 'spotchoiceLockFinalSubmit']);
});

Route::prefix('admin')->group(function () {
    Route::post('/alloted-students', [CommonController::class, 'allAllotedStudents']);
    Route::post('/student-allotment-details', [CommonController::class, 'StudentallotementDetails']);
    Route::post('/student-admission', [CommonController::class, 'studentAdmissionVerification']);
    Route::post('/admitted-students', [CommonController::class, 'allAdmittedStudents']);
    Route::post('/seat-matrix', [CommonController::class, 'seatMatrix']);
    Route::post('/inst-admin-list', [CommonController::class, 'allInstAdminList']);
    Route::post('/card-count', [DashboardController::class, 'countDashboardCards']);
    Route::post('/verify-registration', [CommonController::class, 'verifyRegistration']);

    Route::prefix('management-admission')->group(function () {
        Route::get('/schedule/{inst_code}', [ManagementAdmissionController::class, 'checkSchedule']);
        Route::get('/list/{college_code}', [ManagementAdmissionController::class, 'list']);
        Route::post('/submit', [ManagementAdmissionController::class, 'submit']);
        Route::get('/pdf/{appl_num}', [ManagementAdmissionController::class, 'pdf']);
        Route::post('/payment', [PaymentController::class, 'managementAdmissionPayment']);
    });

    Route::prefix('self-admission')->group(function () {
        Route::get('/schedule/{inst_code}', [SelfAdmissionController::class, 'checkSchedule']);
        Route::get('/list/{college_code}', [SelfAdmissionController::class, 'list']);
        Route::post('/submit', [SelfAdmissionController::class, 'submit']);
        Route::get('/pdf/{appl_num}', [SelfAdmissionController::class, 'pdf']);
        Route::post('/payment', [PaymentController::class, 'selfAdmissionPayment']);
    });

    //hill admission by college
    Route::post('/self-hill-admission-save', [MgmtAdmissionController::class, 'saveAdmissionHill']);
    Route::post('/stream-hill/{college_code}', [MgmtAdmissionController::class, 'getStreamSeatHill']);
    Route::post('/allocate-seat-hill', [MgmtAdmissionController::class, 'allocateSeatHill']);
    Route::post('/self-admission-hill/{college_code}', [MgmtAdmissionController::class, 'getAllSelfAdmissionHill']);
    Route::post('/stream-hill-category-seat/{college_code}/{trade_code}', [MgmtAdmissionController::class, 'getStreamCategorySeatHill']);

    //council admission by college
    Route::post('/council-admission-save', [MgmtAdmissionController::class, 'saveAdmissionCouncil']);
    Route::post('/stream-council/{college_code}', [MgmtAdmissionController::class, 'getStreamSeatCouncil']);
    Route::post('/allocate-seat-council', [MgmtAdmissionController::class, 'allocateSeatCouncil']);
    Route::post('/self-admission-council/{college_code}', [MgmtAdmissionController::class, 'getAllSelfAdmissionCouncil']);
    Route::post('/stream-council-category-seat/{college_code}/{trade_code}', [MgmtAdmissionController::class, 'getStreamCategorySeatCouncil']);

    //spot counselling
    Route::post('/student-info', [SpotcounsellingController::class, 'getSpotStudentInfo']);
    Route::post('/student-info-update-create', [SpotcounsellingController::class, 'spotStudentInfoUpdateOrCreate']);
    Route::post('/spot-register-students/{college_code}', [SpotcounsellingController::class, 'SpotRegisterStudentList']);

    //spot allotement
    Route::post('/student-course-info/{college_code}', [SpotcounsellingController::class, 'getSpotStudentInfoWithCourseInstWise']);
    Route::post('/course-wise-seat', [SpotcounsellingController::class, 'getCourseWiseSeat']);
    Route::post('/spot-allotment', [SpotcounsellingController::class, 'spotAllotmentByInst']);
    Route::post('/decrypt', [SpotcounsellingController::class, 'decryptAadhar']);
    Route::get('/spot-allotment-pdf', [SpotcounsellingController::class, 'spotPdf']);
    Route::post('/spot-admission-schedule', [SpotcounsellingController::class, 'checkSpotSchedule']);
    Route::post('/spot-attendance-schedule', [SpotcounsellingController::class, 'checkSpotAttendanceSchedule']);
    Route::get('/spot-rank-pdf/{college_code}', [SpotcounsellingController::class, 'SpotRankPdf']);
    Route::post('/encrypt', [SpotcounsellingController::class, 'encryptAadhar']);

    Route::prefix('reports')->group(function () {
        Route::post('/profile-register', [ReportController::class, 'getProfileRegister']);
        Route::post('/profile-update', [ReportController::class, 'getProfileUpdate']);
        Route::post('/choice-fillup', [ReportController::class, 'getProfileChoiceFillup']);
        Route::post('/choice-fillup-lock', [ReportController::class, 'getProfileChoiceFillupLock']);
        Route::post('/choice-fillup-payment', [ReportController::class, 'getProfileChoiceFillupPayment']);
        Route::post('/allotment/{college_code?}', [ReportController::class, 'getProfileAllotment']);
    });
});

Route::post('/pay-now', [PaymentController::class, 'payment']);
Route::post('/upgrade-pay-now', [PaymentController::class, 'paymentUpgrade']);
Route::post('/registration-pay-now', [PaymentController::class, 'paymentRegistration']);

//Spot Admission payment
Route::post('/pay-now-spot', [PaymentController::class, 'paymentSpot']);
Route::post('/pay-now-spot-counselling', [PaymentController::class, 'paymentSpotCounselling']);
Route::get('/reminder-mail/{type}/{duration}', [CommonController::class, 'sentBulkMail']);

// payment verification
Route::post('/verify-payment', [PaymentController::class, 'verifypayment']);
