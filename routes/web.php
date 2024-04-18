<?php
use App\Http\Controllers\MailController;
use App\Http\Controllers\AuthController;
//use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomForgotPasswordController;
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

//------Reset Password Route
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');
Route::post('/forgot-password', [CustomForgotPasswordController::class, 'forgotPassword'])
    ->middleware('guest')
    ->name('password.email');
Route::get('/reset-password/{token}/{email}', function ($token,$email) {
    return view('auth.user-reset-password', ['token' => $token , 'email' => $email]);
})->middleware('guest')->name('password.reset');
Route::post('/reset-password', [CustomForgotPasswordController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');

    // Home route
    Route::get('/', [AuthController::class, 'home'])->name('home');
    // Login and signup routes
    Route::get('user-login', [AuthController::class, 'login'])->name('login');
    Route::post('user-login', [AuthController::class, 'loginAction'])->name('login.action');
    
    Route::get('signup', [AuthController::class, 'signup'])->name('signup');
    Route::post('signup', [AuthController::class, 'signupAction'])->name('signup.action');

    //----Auth routes--

    Route::middleware('auth')->group(function () {       
    
        // Logout route
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        
        // Profile routes
        Route::get('account-setting/{id}', [AuthController::class, 'profileUpdate'])->name('account-setting');
        Route::get('profile-picture', [AuthController::class, 'profilePicture'])->name('profile-picture');
        Route::post('profile-picture-update', [AuthController::class, 'profilePictureUpdate'])->name('profile-picture-update');

        //-----User Dashboard-----
        Route::get('user-dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');        
        Route::get('user-request', [DashboardController::class, 'userRequest'])
        ->name('user-request');
        Route::post('user-request', [DashboardController::class, 'userRequestAction'])
        ->name('user-request.action'); 
        Route::get('contact-us', [DashboardController::class, 'contactUs'])
        ->name('contact-us');
        //--Payment routes--
        Route::get('user-payment', [DashboardController::class, 'userPayment'])
        ->name('user-payment');
        Route::get('payment-check', [DashboardController::class, 'paymentCheck'])
        ->name('payment-check');
        Route::get('payment-error', [DashboardController::class, 'paymentError'])
        ->name('payment-error');
        Route::get('payment-report', [DashboardController::class, 'paymentReport'])
        ->name('payment-report');    
        Route::get('payment-status', [DashboardController::class, 'paymentStatus'])
        ->name('payment-status');  
        //Admin Dashboard----
        Route::get('admin-dashboard', [DashboardController::class, 'indexAdmin'])
        ->name('admin-dashboard');   
        Route::get('admin-account-setting/{id}', [AuthController::class, 'profileUpdateAdmin'])
        ->name('admin-account-setting'); 
        Route::get('transcript-request', [DashboardController::class, 'transcriptRequest'])
        ->name('transcript-request'); 
        Route::get('transcript-request/{id}', [DashboardController::class, 'transcriptRequestView'])
        ->name('transcript-request-view'); 
        Route::get('users', [DashboardController::class, 'Users'])
        ->name('users'); 
        

        //--Send mail routes
        Route::get('send-mail-fail/{transaction_id}', [MailController::class, 'mailFailed'])
        ->name('send-mail-fail');
        Route::get('send-mail-success/{transaction_id}', [MailController::class, 'mailSuccess'])
        ->name('send-mail-success');
        //---User change password --------------------------------
        Route::get('change-password', [AuthController::class, 'changePassword'])
            ->name('change-password');  
    });

     
        //===========Verify email address routes================================
    Route::get('email-verify', [MailController::class, 'emailVerify'])
    ->name('email-verify');
    Route::get('email-verify-done/{token}', [MailController::class, 'emailVerifyDone'])
    ->name('email-verify-done');
    Route::post('resend-verification-email', [MailController::class, 'resendEmailVerification'])
    ->name('resend-verification-email');
    Route::post('resend-verification', [MailController::class, 'resendVerification'])
    ->name('resend-verification');
    Route::post('email-not-verify', [MailController::class, 'emailNotVerify'])
    ->name('email-not-verify');
    
    //----send mail route
    Route::get('send-mail', [MailController::class, 'index'])
    ->name('send-mail');

    Route::get('user-check', [DashboardController::class, 'userCheck'])
        ->name('user-check');
   