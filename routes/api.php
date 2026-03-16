<?php

use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Classroom\ClassroomController;
use App\Http\Controllers\ClassSchedule\ClassScheduleController;
use App\Http\Controllers\Assistance\AssistanceController;
use App\Http\Controllers\Assistance\AssistanceStudentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\CourseStudentController;
use App\Http\Controllers\Concept\ConceptController;
use App\Http\Controllers\Course\CourseConceptController;
use App\Http\Controllers\Debtors\DebtorController;
use App\Http\Controllers\Evaluation\EvaluationController;
use App\Http\Controllers\Evaluation\EvalutionStudentController;
use App\Http\Controllers\Languaje\LanguajeController;
use App\Http\Controllers\Languaje\LanguajeCourseController;
use App\Http\Controllers\Languaje\LanguajeLevelController;
use App\Http\Controllers\Level\LevelController;
use App\Http\Controllers\Level\LevelCourseController;
use App\Http\Controllers\Month\MonthController;
use App\Http\Controllers\Payments\FixedCostController;
use App\Http\Controllers\Payments\InvoiceController;
use App\Http\Controllers\Payments\TeacherPaymentController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SchoolYear\SchoolYearController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentCourseController;
use App\Http\Controllers\Student\StudentFixController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\TypeAssistance\TypeAssistanceController;
use App\Http\Controllers\TypeCourse\TypeCourseController;
use App\Http\Controllers\TypeEvaluation\TypeEvaluationController;
use App\Http\Controllers\TypeExpense\TypeExpenseController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Year\YearController;
use App\Http\Middleware\isAdmin;
use App\Http\Middleware\isUser;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('auth/me',[AuthController::class, 'me']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::get('schoolYears/current',[SchoolYearController::class,'getCurretYear']);
Route::get('reports/teachers', [ReportController::class, 'getReportTeacher'])->middleware(['isAdmin']);
Route::get('reports/assistencesByCourse', [ReportController::class, 'getReportAssistancesByCourse'])->middleware(['isAdmin']);
Route::get('reports/assistencesByStudent', [ReportController::class, 'getReportAssistancesByStudent'])->middleware(['isAdmin']);
Route::get('reports/reportCompleteByStudent', [ReportController::class, 'getReportCompleteByStudent'])->middleware(['isAdmin']);
Route::get('reports/reportPaymentTeacher', [ReportController::class, 'getReportPaymentTeacher'])->middleware(['isAdmin']);
Route::get('reports/reportInvoicesByLanguajeCourse', [ReportController::class, 'getReporybyLanguajeCourse'])->middleware(['isAdmin']);
Route::get('reports/downloadReportByStudent', [ReportController::class, 'downloadReportByStudent'])->middleware(['isAdmin']);
Route::get('reports/downloadReportByCourse', [ReportController::class, 'downloadReportByCourse'])->middleware(['isAdmin']);
Route::get('reports/downloadReportByInvoice/{invoice}', [ReportController::class, 'downloadReportByInvoice'])->middleware(['isAdmin']);
Route::get('reports/downloadReportCompleteByStudent', [ReportController::class, 'downloadReportCompleteByStudent'])->middleware(['isAdmin']);
Route::get('reports/downloadInvoicesByLanguajeCourse', [ReportController::class, 'downloadInvoicesByLanguajeCourse'])->middleware(['isAdmin']);
Route::get('reports/downloadPaymentTeacher', [ReportController::class, 'downloadPaymentTeacher'])->middleware(['isAdmin']);
Route::get('reports/downloadReceiptByPaymentTeacher/{paymentTeacher}', [ReportController::class, 'downloadReceiptByPaymentTeacher']);
Route::get('reports/downloadReportAnalytical', [ReportController::class, 'downloadReportAnalytical'])->middleware(['isAdmin']);
Route::get('reports/reportAnalytical', [ReportController::class, 'reportAnalytical'])->middleware(['isAdmin']);
Route::get('reports/reportLanguajeLevelCourse', [ReportController::class, 'getReportLanguajeLevelCourse'])->middleware(['isAdmin']);
Route::get('reports/downloadReportLanguajesLevelsCourses', [ReportController::class, 'downloadReportLanguajesLevelsCourses'])->middleware(['isAdmin']);

Route::get('getLastInvoicePay', [StudentController::class, 'getLastInvoicePay'])->middleware(['isAdmin']);
Route::get('getAmountCourse', [LevelController::class, 'getAmountCourse'])->middleware(['isAdmin']);
Route::get('months', [MonthController::class, 'index'])->middleware(['isAdmin']);
Route::get('years', [YearController::class, 'index'])->middleware(['isAdmin']);
Route::put('schoolYears/createYearCurrentWithCoursesEnabled',[SchoolYearController::class,'createYearCurrentWithCoursesEnabled'])->middleware(['isAdmin']);
Route::get('schoolYears/duplicateCourses/preview',[SchoolYearController::class,'previewDuplicateCourses'])->middleware(['isAdmin']);
Route::delete('schoolYears/duplicateCourses',[SchoolYearController::class,'deleteDuplicateCourses'])->middleware(['isAdmin']);
Route::get('invoices/getInvoiceByStudentAndCourse',[InvoiceController::class,'getInvoiceByStudentAndCourse'])->middleware(['isAdmin']);
Route::get('invoices/getMoraForMonth',[InvoiceController::class,'getMoraForMonth'])->middleware(['isAdmin']);
Route::resource('schoolYears',SchoolYearController::class,['only'=>['index','update','store']])->middleware(['isAdmin']);
Route::resource('languajes',LanguajeController::class,['only'=>['index','store','update','destroy']])->middleware(['isAdmin']);
Route::resource('languajes.courses',LanguajeCourseController::class,['only'=>['index']])->middleware(['isAdmin']);
Route::resource('languajes.levels',LanguajeLevelController::class,['only'=>['index']])->middleware(['isAdmin']);

Route::resource('levels',LevelController::class,['only'=>['index','store','update','destroy']])->middleware(['isUser']);
Route::resource('levels.courses',LevelCourseController::class,['only'=>['index']])->middleware(['isAdmin']);

Route::resource('teachers',TeacherController::class,['only'=>['index','store','update','destroy']])->middleware(['isUser']);
Route::resource('courses',CourseController::class,['only'=>['index','store','show','update','destroy']])->middleware(['isUser']);
Route::resource('courses.concepts',CourseConceptController::class,['only'=>['index']])->middleware(['isUser']);
Route::resource('courses.students',CourseStudentController::class,['only'=>['index']])->middleware(['isUser']);
Route::get('students/swapped-names', [StudentFixController::class, 'swappedNames'])->middleware(['isAdmin']);
Route::post('students/fix-swapped-names', [StudentFixController::class, 'fixSwappedNames'])->middleware(['isAdmin']);
Route::get('students/counts', [StudentController::class, 'counts'])->middleware(['isUser']);
Route::resource('students',StudentController::class,['only'=>['index','show','store','update','destroy']])->middleware(['isUser']);
Route::resource('students.courses',StudentCourseController::class,['only'=>['store','update','destroy']])->middleware(['isUser']);
Route::resource('typeExpenses',TypeExpenseController::class,['only'=>['index','store','update','destroy']]);
Route::resource('typeAssistances',TypeAssistanceController::class,['only'=>['index','store','update','destroy']])->middleware(['isUser']);
Route::resource('typeEvaluations',TypeEvaluationController::class,['only'=>['index','store','update','destroy']])->middleware(['isUser']);
Route::resource('typeCourses',TypeCourseController::class,['only'=>['index','store','update','destroy']]);
Route::get('evaluations/counts', [EvaluationController::class, 'counts'])->middleware(['isUser']);
Route::resource('evaluations',EvaluationController::class,['only'=>['index','store','update','destroy']])->middleware(['isUser']);
Route::resource('evaluations.students',EvalutionStudentController::class,['only'=>['store','update','destroy']])->middleware(['isUser']);
Route::resource('fixedCosts',FixedCostController::class,['only'=>['index','store','update','destroy']])->middleware(['isAdmin']);
Route::resource('paymentTeachers',TeacherPaymentController::class,['only'=>['index','store','update','destroy']])->middleware(['isAdmin']);
Route::resource('invoices',InvoiceController::class,['only'=>['index','store','update','destroy']])->middleware(['isAdmin']);
Route::resource('concepts',ConceptController::class,['only'=>['index']])->middleware(['isAdmin']);
Route::get('assistances/counts', [AssistanceController::class, 'counts'])->middleware(['isUser']);
Route::resource('assistances',AssistanceController::class,['only'=>['index','store','update','destroy']])->middleware(['isUser']);
Route::resource('assistances.students',AssistanceStudentController::class,['only'=>['store','update']])->middleware(['isUser']);
Route::get('debtors/auto', [DebtorController::class, 'auto'])->middleware(['isUser']);
Route::resource('debtors',DebtorController::class,['only'=>['index']])->middleware(['isAdmin']);
Route::resource('users',UserController::class,['only'=>['index','show','store','update','destroy']])->middleware(['isAdmin']);

Route::get('settings/interest-rate', [SettingController::class, 'getInterestRate'])->middleware(['isAdmin']);
Route::put('settings/interest-rate', [SettingController::class, 'updateInterestRate'])->middleware(['isAdmin']);
Route::resource('classrooms',ClassroomController::class,['only'=>['index','store','update','destroy']])->middleware(['isAdmin']);
Route::resource('classSchedules',ClassScheduleController::class,['only'=>['index','store','update','destroy']])->middleware(['isAdmin']);
Route::post('classSchedules/bulk', [ClassScheduleController::class, 'storeBulk'])->middleware(['isAdmin']);
Route::get('calendar', [CalendarController::class, 'index'])->middleware(['isUser']);

