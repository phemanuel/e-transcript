<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\MyCustomEmail;
use App\Mail\PaymentEmail;
use Illuminate\Support\Facades\Mail;
use PDF;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class MailController extends Controller
{
    //

    public function index()
    {
        try {
            // Retrieve email address from the session
            $email_address = session('email');
            $full_name = session('full_name');
            $email_token = session('email_token');
                      

            $data['email'] = $email_address;
            $data['full_name'] = $full_name ;
            $data['email_token'] = $email_token;            
            $data['title'] = 'Email Verification';

            // Load the PDF
            $pdf = PDF::loadview('emails.sendmail', $data);

            $data['pdf'] = $pdf;

            // Send the email
            Mail::to($data['email'])->send(new MyCustomEmail($data));

            // Success: Email sent
            //session()->flash('success', 'Account setup successful! You can login to complete your profile.');

            return redirect('email-verify');
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error during mail: ' . $e->getMessage()); 
            // Error handling: Handle the error and display an error message
            session()->flash('error', 'An error occurred while sending the email.');

            return redirect()->route('login');
        }
    }

    public function emailVerify()
    {
        $email_address = session('email');
        $email_message = session('email_message');  
        return view('auth.email-verify')
                ->with('email', $email_address)
                ->with('message', $email_message);
    }

    public function emailNotVerify(Request $request)
    {
        $validatedData = $request->validate([            
            'email' => 'required|email',            
        ]);

        $email_address = $validatedData['email'];  
        session(['email' => $email_address]);
        
        return redirect('resend-verification-email');     
    }

    public function resendEmailVerification()
    {
        $email = session('email');

        try {
            // Find the user by email
            $user = User::where('email', $email)->first();
    
            if ($user) {
                // Generate a new verification token
                $newToken = Str::random(40);
                session(['email_token' => $newToken]);
                // Update the user's verification token in the database
                $user->remember_token = $newToken;
                $user->save();  
                
                $email_message = "A new verification email has been sent to you, kindly follow instructions to continue, 
                please check both your inbox and spam folder.";
                session(['email_message' => $email_message]);
                // Flash a success message and redirect
                //Session::flash('success', 'A new verification email has been sent. Please check your email for the link.');
                return redirect('send-mail');
            } else {
                // Flash an error message for invalid email
                Session::flash('error', 'Email not found. Please check your email address and try again.');
                return redirect()->route('login');
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Error during email verification resend: ' . $e->getMessage());    
            
            return redirect('email-verify');
        }
    }

    public function resendVerification(Request $request)
    {
        $email = $request->input('email');
        session(['email' => $email]);

        try {
            // Find the user by email
            $user = User::where('email', $email)->first();
    
            if ($user) {
                // Generate a new verification token
                $newToken = Str::random(40);
                session(['email_token' => $newToken]);
                // Update the user's verification token in the database
                $user->remember_token = $newToken;
                $user->save();  
                
                $email_message = "A new verification email has been sent to you, kindly follow instructions to continue, 
                please check both your inbox and spam folder.";
                session(['email_message' => $email_message]);
                // Flash a success message and redirect
                //Session::flash('success', 'A new verification email has been sent. Please check your email for the link.');
                return redirect('send-mail');
            } else {
                // Flash an error message for invalid email
                Session::flash('error', 'Email not found. Please check your email address and try again.');
                return redirect()->route('login');
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Error during email verification resend: ' . $e->getMessage());    
            
            return redirect('email-verify');
        }
    }

    public function emailVerifyDone($token)
    {
        try {
            // Find the user with the given token
            $user = User::where('remember_token', $token)->first();

            if ($user) {
                // Mark the email as verified
                $user->email_verified_at = now();
                $user->email_verified_status = 1;
                $user->save();

                // Flash a success message and redirect
                Session::flash('success', 'Email has been verified. You can now login to complete your profile.');
                return redirect('/');
            } else {
                // Flash an error message for invalid token
                Session::flash('error', 'Invalid verification token. Please click on the button below to resend the verification link.');
                return redirect('email-verify');
            }
        } catch (Exception $e) {
            // Handle the exception, log it, and redirect as needed
            // For example, you can log the error and redirect to an error page
            Log::error('Error during email verification: ' . $e->getMessage());
            return redirect('email-verify');
        }
    }

    public function mailSuccess($transaction_id)
    {   
        try {
            $transaction = PaymentTransaction::where('transaction_id', $transaction_id)->first();       
            //-------------
            $data = [
                'email' => $transaction->email,
                'full_name' => $transaction->full_name,
                'transaction_id' => $transaction->transaction_id,
                'request_id' => $transaction->request_id,
                'amount' => $transaction->amount,
                'amount_due' => $transaction->amount_due,
                'transaction_status' => $transaction->transaction_status,
                'response_code' => $transaction->response_code,
                'response_status' => $transaction->response_status,
                'title' => 'E-Transcript',
                'transaction_message' => 'Your transaction was successful',
                'flicks_transaction_id' => $transaction->flicks_transaction_id,
            ];

            // $data['email'] = $email_address;
            // $data['full_name'] = "Dear ". $full_name . ",";
            // $data['title'] ='OYSCHST WALLET';
            // $data['body'] ='Account has been created successfully';

            $pdf = PDF::loadview('emails.payment-mail',$data);

            $data['pdf'] = $pdf;
            Mail::to($data['email'])->send(new PaymentEmail($data));

            //dd('Email sent');
            session()->flash('success', 'Your transaction was successful, payment details has been sent to your email.');
            
            return redirect()->route('payment-status');
        } catch (\Exception $e) {
        // Error handling: Handle the error and display an error message
        session()->flash('error', 'An error occurred while sending the email.');

        return redirect()->route('payment-status');
         }
    }

    public function mailFailed($transaction_id)
    {   
        try {
        $transaction = PaymentTransaction::where('transaction_id', $transaction_id)->first();       
        //-------------
        $flicks_transref = session('flicks_transref');
        $response_code = session('response_code');
        $response_desc = session('response_desc');

        $data = [
            'email' => $transaction->email,
            'full_name' => $transaction->full_name,
            'transaction_id' => $transaction->transaction_id,
            'request_id' => $transaction->request_id,
            'amount' => $transaction->amount,
            'amount_due' => $transaction->amount_due,
            'transaction_status' => $transaction->transaction_status,
            'response_code' => $response_code,
            'response_status' => $response_desc,
            'title' => 'E-Transcript',
            'transaction_message' => 'Your transaction was not successful',
            'flicks_transaction_id' => $flicks_transref,
        ];
        $pdf = PDF::loadview('emails.payment-mail',$data);

        $data['pdf'] = $pdf;
        Mail::to($data['email'])->send(new PaymentEmail($data));

        //dd('Email sent');
        session()->flash('error', 'Your transaction was not successful, payment details has been sent to your email.');
        
        return redirect()->route('payment-status');

        } catch (\Exception $e) {
        // Error handling: Handle the error and display an error message
        session()->flash('error', 'An error occurred while sending the email.');

        return redirect()->route('payment-status');
        }
    }

}
