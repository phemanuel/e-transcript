<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSkill;
use App\Models\UserEducation;
use App\Models\UserExperience;
use App\Models\UserService;
use App\Models\UserPortfolio;
use App\Models\FailedLogins;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Foundation\Auth\ThrottlesLogins;
//use Image;

class AuthController extends Controller
{
    //
    public function home()
    {
        return view('auth.user-login');
    }

    public function login()
    {
        return view('auth.user-login');
    }

    public function signup()
    {
        return view('auth.user-signup');
    }

    public function signupAction(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'last_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $email_token =Str::random(40);            

            $user = User::create([
                'last_name' => $validatedData['last_name'],
                'first_name' => $validatedData['first_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),                
                'email_verified_status' => 0,
                'login_attempts' => 0,
                'remember_token' => $email_token,
                // 'user_picture' => 'profile_pictures/blank.jpg',
                'user_type' => 'user',                
            ]);

            $email_message = "We have sent instructions to verify your email, kindly follow instructions to continue, 
            please check both your inbox and spam folder.";
            session(['email' => $validatedData['email']]);
            session(['full_name' => $validatedData['first_name']]);
            session(['email_token' => $email_token]);
            session(['email_message' => $email_message]);


            return redirect('send-mail');
        } catch (ValidationException $e) {
            // Validation failed. Redirect back with validation errors.
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            // Log the error
            Log::error('Error during user registration: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during registration. Please try again.');
        }
    }

    public function loginAction(Request $request)
    {
        try {
            validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ])->validate();
                
            $userLog = User::where('email', $request->input('email'))->first();
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                //---check the no of attempts=====
                if($userLog->login_attempts < 5){
                    //--increment the number of attempts
                    $userLog->increment('login_attempts');
                    // Log the failed login attempt into the failed_logins table.
                    FailedLogins::create([
                    'ip_address' => $request->ip(),
                    'email' => $request->input('email'),
                ]);
                }
                elseif($userLog->login_attempts >= 5){
                    return redirect()->route('user-locked')->with('seconds', '60');
                }                
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed')
                ]);
            }            

            // User is authenticated at this point
            $user = Auth::user();
            //---reset the user login attempts----
            Auth::user()->update(['login_attempts' => 0]);
        
            if ($user->email_verified_status == 1) {
                // Email is verified, proceed with login
                $request->session()->regenerate();
                if($user->user_type == 'admin'){
                    return redirect()->route('admin-dashboard');
                }
                elseif($user->user_type == 'user'){
                    return redirect()->route('dashboard');
                }
                
            } else {                    
                // Email is not verified, return a flash message
                //Auth::logout(); // Log the user out since the email is not verified                    
                $email_address = $request->email;         
                 return view('auth.email-not-verify');
                 
            }
        } catch (ValidationException $e) {
            // Handle the validation exception
            // You can redirect back with errors or do other appropriate error handling
            return redirect()->route('login')->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            // Handle other exceptions, log them, and redirect to an error page
            Log::error('Error during login: ' . $e->getMessage());
            return redirect()->route('login');
        }    
    }

    

    //----update user profile
    public function profileUpdate(Request $request, string $id)
    {
        return view('auth.account-setting');
        // try {
        //     $validatedData = $request->validate([
        //         'last_name' => 'required|string|max:255',
        //         'first_name' => 'required|string|max:255',
        //     ]);        
            

        //     $user = User::findOrFail($id);
        //     $user->full_name = $validatedData['full_name'];
        //     $user->user_phone = $validatedData['user_phone'];
        //     $user->country_code = $validatedData['country_code'];
        //     $user->user_name = $validatedData['user_name'];
        //     $user->user_about = $validatedData['user_about'];
        //     $user->user_category = $validatedData['user_category'];
        //     $user->user_url = 'https://meetme.kingsconsult.com.ng/'.$uniqueUsername;
        //     $user->user_name_link =  $uniqueUsername;
        
        //     $user->save();
        
        //     return redirect()->route('user-about')->with('success', 'Profile update successful.');
        // } catch (\Exception $e) {
        //     // Handle the exception, log the error, and return with an error message
        //     \Log::error('Error updating user profile: ' . $e->getMessage());
            
        //     $errorMessage = 'Error updating user profile: ' . $e->getMessage();
        //     Log::error($errorMessage);

        //     return redirect()->route('user-about')->with('error', $errorMessage);
        //     //return redirect()->route('user-about')->with('error', 'An error occurred while updating your profile. Please try again.');
        // }
        
    
    }

    public function profileUpdateAdmin(Request $request, string $id)
    {
        return view('auth.admin-account-setting');
    
    }

    //----update user profile
    public function profileUpdateSocial(Request $request, string $id)
    {
        try {
            $rules = [
                'user_facebook' => 'url|nullable',
                'user_instagram' => 'url|nullable',
                'user_twitter' => 'url|nullable',
                'user_linkedin' => 'url|nullable',
            ];
        
            $validatedData = $request->validate($rules);
        
            $user = User::findOrFail($id);
            $user->update($validatedData);
        
            return redirect()->route('user-about')->with('success-new', 'Socials update successful.');
        } catch (\Exception $e) {
            // Handle the exception, log the error, and return with an error message
            \Log::error('Error updating social links: ' . $e->getMessage());
        
            return redirect()->route('user-about')->with('error', 'An error occurred while updating your social links. Please try again.');
        }        
    
    }

    public function profilePicture()
    {
        return view('dashboard.user-profile-picture');

    }

    //update profile picture
    public function profilePictureUpdate(Request $request)
    {
        $user = auth()->user();

        try {
            if ($request->hasFile('profile_picture')) {
                $userPictureFile = $request->file('profile_picture');
                $profilePictureFile = $request->file('profile_picture');
        
                $username = $user->user_name_link; // Get the user's username
        
                // Generate filenames for both pictures
                $userPictureFilename = $username . '_user_picture.' . $userPictureFile->getClientOriginalExtension();
                $profilePictureFilename = $username . '_profile_picture.' . $profilePictureFile->getClientOriginalExtension();
        
                // Store both pictures with the customized filenames
                $userPicturePath = $userPictureFile->storeAs('profile_pictures', $userPictureFilename, 'public');
                $profilePicturePath = $profilePictureFile->storeAs('profile_pictures', $profilePictureFilename, 'public');
        
                // Resize and save the user_picture
                $userPicture = Image::make(public_path('storage/' . $userPicturePath));
                $userPicture->fit(300, 300); // Adjust dimensions as needed
                $userPicture->save();
        
                // Resize and save the profile_picture
                $profilePicture = Image::make(public_path('storage/' . $profilePicturePath));
                $profilePicture->fit(668, 690); // Adjust dimensions as needed
                $profilePicture->save();
        
                // Update user's profile pictures in the database
                $user->user_picture = $userPicturePath;
                $user->profile_picture = $profilePicturePath;
                $user->save();
        
                return redirect()->route('profile-picture')->with('success', 'Profile picture updated successfully.');
            }
        
            return redirect()->route('profile-picture')->with('error', 'Both user and profile picture must be provided.');
        } catch (\Exception $e) {
            // Handle the exception, log the error, and return with an error message
            \Log::error('Error updating profile pictures: ' . $e->getMessage());
        
            return redirect()->route('profile-picture')->with('error', 'An error occurred while updating profile pictures. Please try again.');
        }  

    }

    public function userPortfolio($username)
    {
        // Fetch the user based on the provided username
        $user = User::where('user_name_link', $username)->first();

        if (!$user) {
            // Handle the case where the user is not found
            // You can return a custom error message, redirect, or take other actions here
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ]);
        }

        // Fetch user skills
        $userSkills = UserSkill::where('user_id', $user->id)
        ->paginate(5);
        // Fetch user education
        $userEducation = userEducation::where('user_id', $user->id)
        ->orderBy('college_year', 'desc') 
        ->get();
        // Fetch user work experience
        $userExperience = UserExperience::where('user_id', $user->id)
        ->orderBy('company_year', 'desc') 
        ->get();
        // Fetch user services
        $userService = UserService::where('user_id', $user->id)
        ->orderBy('user_service', 'asc') 
        ->get();
        // Fetch user projects by image
        $userPortfolioImage = UserPortfolio::where('user_id', $user->id)
        ->where('file_type','Image')
        ->orderBy('file_name', 'asc') 
        ->get();
        // Fetch user projects by document
        $userPortfolioDocument = UserPortfolio::where('user_id', $user->id)
        ->where('file_type','Document')
        ->orderBy('file_name', 'asc') 
        ->get();

        return view('portfolio.user-page', 
        compact('userSkills', 'userEducation', 'userExperience', 'user','userService'
        ,'userPortfolioImage','userPortfolioDocument'));
    }

    

    public function changePassword(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('forgot-password');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');


    }
    
    public function userLocked()
    {
        return view('auth.locked-out')->with('seconds', '60');
    }
}