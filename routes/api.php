<?php

use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SelfAdmissionController;
use App\Http\Controllers\SpotAdmissionController;
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

Route::post('/spot-authenticate', [SpotAdmissionController::class, 'spotAthenticate']);
Route::post('/spot-otp-verification', [SpotAdmissionController::class, 'spotOtpVerification']);

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
    Route::get('/spot-college-list', [SpotAdmissionController::class, 'spotCollegeList']);
    Route::get('/spot-student-data/{id}/{phone}', [SpotAdmissionController::class, 'spotStudentData']);
    Route::post('/spot-info-update', [SpotAdmissionController::class, 'spotInfoUpdate']);
    Route::post('/spot-admission-payment', [PaymentController::class, 'spotAdmissionPayment']);
    Route::post('/spot-admission-submit', [SpotAdmissionController::class, 'spotAdmissionSubmit']);
    Route::get('/spot-admission-pdf/{id}/{phone}', [SpotAdmissionController::class, 'spotAdmissionPdf']);

    Route::post('/student-info-update', [StudentController::class, 'studentInfoUpdate']);
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
        Route::post('/remove', [ManagementAdmissionController::class, 'remove']);
        Route::post('/update', [ManagementAdmissionController::class, 'update']);
        Route::get('/pdf/{appl_num}', [ManagementAdmissionController::class, 'pdf']);
        Route::post('/payment', [PaymentController::class, 'managementAdmissionPayment']);
    });

    Route::prefix('self-admission')->group(function () {
        Route::get('/schedule/{inst_code}', [SelfAdmissionController::class, 'checkSchedule']);
        Route::get('/list/{college_code}', [SelfAdmissionController::class, 'list']);
        Route::post('/submit', [SelfAdmissionController::class, 'submit']);
        Route::post('/remove', [SelfAdmissionController::class, 'remove']);
        Route::post('/update', [SelfAdmissionController::class, 'update']);
        Route::get('/pdf/{appl_num}', [SelfAdmissionController::class, 'pdf']);
        Route::post('/payment', [PaymentController::class, 'selfAdmissionPayment']);
    });

    Route::prefix('spot-admission')->group(function () {
        Route::get('/schedule', [SpotAdmissionController::class, 'checkSchedule']);
        Route::get('/list/{inst_code}', [SpotAdmissionController::class, 'list']);
        Route::get('caste-list/{inst_code}/{stu_caste}', [SpotAdmissionController::class, 'casteList']);
        Route::post('/accept', [SpotAdmissionController::class, 'accept']);
        Route::post('/payment', [PaymentController::class, 'spotAdmissionCollegePayment']);
    });

    Route::prefix('reports')->group(function () {
        Route::post('/profile-register', [ReportController::class, 'getProfileRegister']);
        Route::post('/profile-update', [ReportController::class, 'getProfileUpdate']);
        Route::post('/choice-fillup', [ReportController::class, 'getProfileChoiceFillup']);
        Route::post('/choice-fillup-lock', [ReportController::class, 'getProfileChoiceFillupLock']);
        Route::post('/choice-fillup-payment', [ReportController::class, 'getProfileChoiceFillupPayment']);
        Route::post('/allotment/{college_code?}', [ReportController::class, 'getProfileAllotment']);
    });

    Route::get('admission-data', [ReportController::class, 'admissionData']);
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
Route::get('caste-list/{inst_code}/{stu_caste}', [SpotAdmissionController::class, 'casteList']);

Route::get('test-seat', function () {
    return Institute::select('i_code', 'i_name')
        ->whereIn('i_code', [
            'AMDP',
            'BCPS',
            'BWP',
            'DKDC',
            'DMB',
            'JHPP',
            'MBPC',
            'MLMS',
            'MPI',
            'MTP',
            'NBIP',
            'NCPB',
            'RAIP',
            'RNT',
            'SBIP',
            'SNCP',
            'SRCE',
            'SRCP'
        ])
        ->with([
            'managementSeat:sm_inst_code,m_mgmt,mgmt,a_mgmt',
            'selfSeat:sm_inst_code,m_gen,gen,a_gen'
        ])->withCount([
            'managements' =>  function (Builder $query) {
                $query->where('is_admission_payment', 1);
            },
            'selfs' =>  function (Builder $query) {
                $query->where('is_admission_payment', 1);
            },
        ])->get()
        ->map(function ($data) {
            $seats = [
                'AMDP' => 42,
                'BCPS' => 30,
                'BWP' => 44,
                'DKDC' => 45,
                'DMB' => 18,
                'JHPP' => 45,
                'MBPC' => 43,
                'MLMS' => 45,
                'MPI' => 22,
                'MTP' => 37,
                'NBIP' => 26,
                'NCPB' => 19,
                'RAIP' => 30,
                'RNT' => 45,
                'SBIP' => 11,
                'SNCP' => 41,
                'SRCE' => 6,
                'SRCP' => 22
            ];

            return [
                'inst_code' => $data->i_code,
                'inst_name' => $data->i_name,
                'management' => [
                    'admission' => $data->managements_count,
                    'total' => 15,
                    'alloted' => optional($data->managementSeat)->a_mgmt,
                    'available' => optional($data->managementSeat)->mgmt,
                ],
                'self' => [
                    'admission' => $data->selfs_count,
                    'total' => array_key_exists($data->i_code, $seats) ? $seats[$data->i_code] : null,
                    'alloted' => optional($data->selfSeat)->a_gen,
                    'available' => optional($data->selfSeat)->gen,
                ]
            ];
        })->sortBy('inst_code')->values();
});
// Route::post('fromsubmit',[CommonController::class,'fromsubmit']);
// Route::get('getdata/{id}', [CommonController::class, 'getdata']);   
Route::post('register_student', [CommonController::class, 'registerstudent']);
Route::post('checkelegiblity', [CommonController::class, 'eligibility']);
Route::post('/register_pay-now', [PaymentController::class, 'registerpayment']);

