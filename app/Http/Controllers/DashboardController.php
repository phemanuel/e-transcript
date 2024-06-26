<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTrack;
use App\Models\UserRequests;
use App\Models\UserProgramme;
use App\Models\UserClearance;
use App\Models\TranscriptFee;
use App\Models\PaymentTransaction;
use App\Models\TranscriptUpload;
use App\Models\UserYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user_id = auth()->user()->id;
        // Query user's tracks for the authenticated user's email with pagination
        $user_track = UserTrack::where('user_id', '=', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(3);
            
        // Query user request for the authenticated user's email with pagination
        $user_request = UserRequests::where('user_id', '=', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(3);

        // Query user's tracks for the authenticated user's email with pagination
        $user_payment = PaymentTransaction::where('user_id', '=', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

            // Query user's transcript
        $user_transcript = TranscriptUpload::where('user_id', '=', $user_id)
        ->get();
        
       return view('dashboard.dashboard', compact('user_track', 'user_request'
       ,'user_payment','user_transcript'));        
    }
    public function indexAdmin()
    {
        $user_id = auth()->user()->id;
        // Query all admin user
        $users = User::where('user_type', '=', 'admin')->get();

        // Query all user request 
        //$user_requests = UserRequests::orderBy('created_at', 'desc')->paginate(10);  
        // Query successful payment transactions
        $successful_transactions = PaymentTransaction::where('transaction_status', 'Successful')->get();

        // Extract request IDs from successful transactions
        $successful_request_ids = $successful_transactions->pluck('request_id');

        // Query user requests using the request IDs 
        $user_requests = UserRequests::whereIn('request_id', $successful_request_ids)
            ->orderByRaw("CASE 
                                WHEN certificate_status = 'In progress' THEN 1
                                WHEN certificate_status = 'Processing' THEN 2
                                WHEN certificate_status = 'Ready for pick-up' THEN 3
                                ELSE 4
                            END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);


        // Query user's transcript
        $user_transcript = TranscriptUpload::all();
    
        
       return view('dashboard.dashboard-admin', compact('users', 'user_requests','user_transcript'));        
    }

    public function userRequest()
    {
        $requestId = "#" .uniqid();
        $years = UserYear::all();
        $programmes = UserProgramme::orderBy('programme', 'asc')->get();

        return view('dashboard.user-request', ['requestId' => $requestId,
        'years' => $years,
        'programmes' => $programmes]);
    }    

    public function userRequestAction(Request $request)
    {     
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'request_id' => 'required|string',
                'matric_no' => 'required|string',
                'programme' => 'required|string',
                'clearance_no' => 'required|string',
                'grad_year' => 'required|string',
                'phone_no' => 'required|string',
                'destination_address' => 'required|string',
                'certificate_name' => 'required|string',
            ]);

            // Check if the provided clearance_no exists in the UserClearance model
            $clearanceExists = UserClearance::where('clearance_no', $validatedData['clearance_no'])
                                ->where('user_name', $validatedData['matric_no'])
                                ->exists();

            // If the clearance_no doesn't exist, return back with an error message
            if (!$clearanceExists) {
                return redirect()->route('user-request')->with('error', 'The provided clearance number is invalid.');
                // return response()->json([
                //     'error' => 'The Clearance number is invalid.',
                // ]);
            }

            // Create UserRequests record
            $user = UserRequests::create([
                'user_id' => auth()->user()->id,
                'request_id' => $validatedData['request_id'],
                'email' => auth()->user()->email,
                'matric_no' => $validatedData['matric_no'],
                'programme' => $validatedData['programme'],   
                'clearance_no' => $validatedData['clearance_no'],
                'graduation_year' => $validatedData['grad_year'],
                'phone_no' => $validatedData['phone_no'], 
                'destination_address' => $validatedData['destination_address'],   
                'certificate_name' => $validatedData['certificate_name'],         
                'certificate_status' => "In progress",
            ]);  

            // Create UserTrack record
            $userTrack = UserTrack::create([
                'user_id' => auth()->user()->id,
                'request_id' => $validatedData['request_id'],
                'certificate_status' => "In progress",
                'approved_by' => auth()->user()->first_name,
                'comments' => "A transcript request has been started by you."
            ]);
            session(['request_id' => $validatedData['request_id'],
            'matric_no' => $validatedData['matric_no'],
            'full_name' => $validatedData['certificate_name'],
            'programme' => $validatedData['programme'],
            'phone_no' => $validatedData['phone_no'], 
            ]); 
                    

            // Redirect to user-payment route upon successful creation
            return redirect('user-payment');
        } catch (ValidationException $e) {
            // Validation failed. Redirect back with validation errors.
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            // Log the error
            Log::error('Error during transcript request: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()->with('error', 'An error occurred during transcript request. Please try again.');
        }
    }

    public function userPayment()
    {   
        $yearkeep = date('Y');
        $monthkeep = date('m');
        $daykeep = date('d');
        $transactionId = "TF" . $yearkeep.$monthkeep.$daykeep . substr(uniqid(), 7, 5);
        
        $transcriptFees = TranscriptFee::all();        
        $requestId = session('request_id');
        $matricNo = session('matric_no');
        $fullName = session('full_name');
        $programme = session('programme');
        $phoneNo = session('phone_no');
        $product_desc = "Transcript Payment Fee";        
        
        //------PAYMENT INTEGRATION DETAILS--------------
        $merchant_id = "FLKOYSCHST001";
        $product_id = "FLKISWCP001";
        $product_description = $product_desc;
        foreach ($transcriptFees as $transcriptFee) {            
            $transcriptFee = $transcriptFee->fee_amount;   
            $amounttopay = $transcriptFee;        
            $payamount = ($transcriptFee + 350); //===added transaction fee
            $amount = ($transcriptFee + 350)* 100; //=====final value        
        }
        
        $transaction_id = $transactionId;
        session(['pay_amount' => ($amount/100)]);
        $currency = "566";
        $response_url = "http://127.0.0.1:8000/payment-check/";
        $notify_url = ""; 
        $name = trim($fullName) ;
        $email = auth()->user()->email;
        $customer_id = $matricNo;
        $phone_no = $phoneNo; 
        $secretKey = "c4502d31091fdd578dbdde27e09cc490942d4565c7b53323ced238d59aa3ae43";
        $payment_params = json_encode(['Transcript Payment Fee' => ['amount' => $amounttopay, 'code' => 'FLKACCOYLV001']
        ]);

        $string2hash = $merchant_id . $product_id . $product_description
        . $amount . $currency . $transaction_id . $customer_id . $name . $email . $phone_no
        . $payment_params . $response_url . $secretKey;

        $hashed_string = hash('sha256', $string2hash);

        //----create a transaction record----
        $paymentTransaction = PaymentTransaction::create([
            'user_id' => auth()->user()->id,
            'request_id' => $requestId,
            'matric_no' => $matricNo,
            'full_name' => $fullName,
            'phone_no' => $phoneNo,
            'programme' => $programme,
            'email' => auth()->user()->email,
            'amount' => $transcriptFee,
            'amount_due' => $transcriptFee + 350,
            'transaction_id' => $transactionId,
            'transaction_type' => "Transcript Payment",
            'transaction_status' => "Pending",
            'transaction_date' => date("Y-m-d H:i:s"),
            'response_code' => "",
            'response_status' => "",
            'flicks_transaction_id' => "",
            
        ]);

        return view('dashboard.user-payment', ['requestId' => $requestId, 
        'transcriptFee' => $transcriptFees, 'transactionId' => $transactionId,
        'merchant_id' => $merchant_id, 'product_id' => $product_id, 
        'product_description' => $product_description, 'amount' => $amount,
        'currency' => $currency, 'response_url' => $response_url,
        'matric_no' => $matricNo, 'phone_no' => $phone_no,
        'payment_params' => $payment_params, 'hashed_string' => $hashed_string, 'name' => $name,
        'pay_amount' => $payamount
        ]);
        
    }  

    public function paymentCheck(Request $request) 

    {
        // Validate and retrieve transaction_id
        $rqr_transref = isset($_REQUEST['transaction_id']) ? $_REQUEST['transaction_id'] : null;

        // Check if 'pay_amount' session value is set
        $rqr_amount = session()->has('pay_amount') ? session('pay_amount') : null;

        if (!$rqr_transref || !$rqr_amount) {
            // Handle missing or invalid input
            // Redirect or return an error response as needed
            return redirect('payment-error')->with('error', 'Invalid transaction data');
        }

        // Initialize variables
        $merchant_id = "FLKOYSCHST001";
        $product_id = "FLKISWCP001";
        $requeryAmt = $rqr_amount * 100; // Ensure amount is in kobo
        $secretKey = "c4502d31091fdd578dbdde27e09cc490942d4565c7b53323ced238d59aa3ae43";
        $rqryTransaction_id = $rqr_transref;
        $requeryString2hash = $merchant_id . $product_id . $rqryTransaction_id . $secretKey;
        $requeryHashedValue = hash('sha256', $requeryString2hash);
        $setRequestHeaders = ["Hash: " . $requeryHashedValue];

        $requery = "https://flickspay.flickstechnologies.com/flk/collections/requery";
        $q = "/{$merchant_id}/{$product_id}/{$rqryTransaction_id}/{$requeryAmt}";
        $requeryURL = $requery . $q;

        // Make a check call to Interswitch
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requeryURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $setRequestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, false);
        $data = curl_exec($ch);

        // Check for cURL errors
        if ($data === false) {
            // Handle cURL errors
            // Redirect or return an error response as needed
            return redirect('payment-error')->with('error', 'cURL request failed');
        }

        // Decode the JSON response
        $result = json_decode($data);

        // Check if decoding was successful
        if ($result === null) {
            // Handle JSON decoding errors
            // Redirect or return an error response as needed
            return redirect('payment-error')->with('error', 'Error decoding JSON response');
        }

        // Check if expected fields exist in the response
        if (!isset($result->ResponseCode) || !isset($result->ResponseDesc) || !isset($result->FLKTranxRef)) {
            // Handle missing fields in the response
            // Redirect or return an error response as needed
            return redirect('payment-error')->with('error', 'Missing fields in JSON response');
        }

        // Extract response data
        $ResponseCode = $result->ResponseCode;
        $ResponseDesc = $result->ResponseDesc;
        $flicks_transref = $result->FLKTranxRef;

        // Store response data in session
        session([
            'flicks_transref' => $flicks_transref,
            'response_code' => $ResponseCode,
            'response_desc' => $ResponseDesc,
            'pay_amount' => $rqr_amount,
            'transaction_id' => $rqr_transref,
        ]);

        // Check response code and redirect accordingly
        if ($ResponseCode !== "00") {
            // Failed Transaction
            PaymentTransaction::where('transaction_id', $rqr_transref)->update([
                'response_code' => $ResponseCode,
                'response_status' => $ResponseDesc,
                'transaction_status' => 'Failed', 
                'flicks_transaction_id' => $flicks_transref,
            ]);
           
            return redirect()->route('send-mail-fail', ['transaction_id' => $rqr_transref]);
        } else {
            // Successful Transaction
            PaymentTransaction::where('transaction_id', $rqr_transref)->update([
                'response_code' => $ResponseCode,
                'response_status' => $ResponseDesc,
                'transaction_status' => 'Successful', 
                'flicks_transaction_id' => $flicks_transref,
            ]);
            
            return redirect()->route('send-mail-success', ['transaction_id' => $rqr_transref]);
        }
    }

    public function paymentError()
    {
        $flicks_transref = session('flicks_transref');
        $ResponseCode = session('response_code');
        $ResponseDesc = session('response_desc');
        $payAmount = session('pay_amount');
        $transactionID = session('transaction_id');

        return view('dashboard.payment-error', ['transactionID' =>$transactionID,'flicks_transref' => $flicks_transref,
         'ResponseCode'=>$ResponseCode, 'ResponseDesc'=>$ResponseDesc, 'PayAmount'=>$payAmount]);
    }

    public function paymentReport()
    {   
        $user_id = auth()->user()->id;
        $paymentTransaction = PaymentTransaction::where('user_id', '=', $user_id)
        ->orderBy('created_at', 'desc')
        ->paginate(5);
        return view('dashboard.payment-report', compact('paymentTransaction'));
    }

    public function paymentStatus()
    {
        $flicks_transref = session('flicks_transref');
        $ResponseCode = session('response_code');
        $ResponseDesc = session('response_desc');
        $payAmount = session('pay_amount');
        $totalAmountDue = session('pay_amount') + 350;
        $transactionID = session('transaction_id');

        if ($ResponseCode === "00") {
            $transactionMessage = "Your transaction was successful, payment details has been sent to your email.";
        }
        else {
            $transactionMessage = "Your transaction was not successful, payment details has been sent to your email.";
        }

        return view('dashboard.payment-status-page', ['transactionID' =>$transactionID,'flicks_transref' => $flicks_transref,
         'ResponseCode'=>$ResponseCode, 'ResponseDesc'=>$ResponseDesc, 'amount'=>$payAmount,
          'transaction_message'=>$transactionMessage, 'amount_due'=>$totalAmountDue]);
    }

    public function contactUs()
    {
        return view('dashboard.contact-us');
    }

    public function transcriptRequest()
    {
        $user_id = auth()->user()->id;
        // Query all admin user
        $users = User::where('user_type', '=', 'admin')->get();
        
        // Query successful payment transactions
        $successful_transactions = PaymentTransaction::where('transaction_status', 'Successful')->get();

        // Extract request IDs from successful transactions
        $successful_request_ids = $successful_transactions->pluck('request_id');

        $user_requests = UserRequests::whereIn('request_id', $successful_request_ids)
            ->orderByRaw("CASE 
                                WHEN certificate_status = 'In progress' THEN 1
                                WHEN certificate_status = 'Processing' THEN 2
                                WHEN certificate_status = 'Ready for pick-up' THEN 3
                                ELSE 4
                            END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Query user's transcript
        $user_transcript = TranscriptUpload::all();
    
        
       return view('dashboard.transcript-request', compact('users', 'user_requests', 'user_transcript'));        
    }    
    
    public function transcriptRequestView(Request $request, string $id)
    {
        $user_requests = UserRequests::where('id', '=', $id)->get();
        // Retrieve the UserRequest record by ID
        $user_request = UserRequests::findOrFail($id);        
        // Extract the request_id from the retrieved UserRequest record
        $request_id = $user_request->request_id;
        
        // Retrieve payment transaction details using the request_id
        $payment_transaction_details = PaymentTransaction::where('request_id', $request_id)->get();

        // Query user's tracks for the authenticated user's email with pagination
        $user_track = UserTrack::where('request_id', '=', $request_id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);
    
        return view('dashboard.transcript-request-view', compact('payment_transaction_details', 
        'user_requests', 'user_track'));  
    }
    
    public function transcriptRequestAction(Request $request, string $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'comment' => 'required|string',
            'transcript_status' => 'required|string',
            
        ]);

        $user_request = UserRequests::findOrFail($id);                
        $request_id = $user_request->request_id;
        $user_id = $user_request->user_id;

        // Check if an upload has been made for the request_id
        $transcriptUpload = TranscriptUpload::where('request_id', $request_id)->first();

        if ($transcriptUpload) {
            // If an upload has already been done, redirect back with a message
            return redirect()->back()->with('error', 'Transcript has already been uploaded for this request.');
        }
        
        // Create UserTrack record
        $userTrack = UserTrack::create([
            'user_id' => $user_id,
            'request_id' => $request_id,
            'certificate_status' => $validatedData['transcript_status'],
            'approved_by' => "admin",
            'comments' => $validatedData['comment'],
        ]);

        //--Update user request data-----
        UserRequests::where('id', $id)->update([
            'certificate_status' => $validatedData['transcript_status'],            
        ]);

        return redirect()->route('admin-dashboard')->with('success', 'Transcript request update successful.');
    }

    public function Users()
    {
        $users = User::where('user_type', '=', 'admin')->paginate(10);

        return view('auth.users', compact('users'));  

    }

    public function addUser()
    {
        return view('auth.add-user');
    }

    public function addUserAction(Request $request)
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
                'email_verified_status' => 1,
                'login_attempts' => 0,
                'remember_token' => $email_token,
                // 'user_picture' => 'profile_pictures/blank.jpg',
                'user_type' => 'admin',                
            ]);

            // $email_message = "We have sent instructions to verify your email, kindly follow instructions to continue, 
            // please check both your inbox and spam folder.";
            // session(['email' => $validatedData['email']]);
            // session(['full_name' => $validatedData['first_name']]);
            // session(['email_token' => $email_token]);
            // session(['email_message' => $email_message]);


            return redirect()->route('admin-dashboard')->with('success', 'User has been created successfully.');
        } catch (ValidationException $e) {
            // Validation failed. Redirect back with validation errors.
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            // Log the error
            Log::error('Error during user registration: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during registration. Please try again.');
        }
    }

    public function transcriptUpload(Request $request, string $id)
    {
        $user_requests = UserRequests::where('id', '=', $id)->get();
        // Retrieve the UserRequest record by ID
        $user_request = UserRequests::findOrFail($id);        
        // Extract the request_id from the retrieved UserRequest record
        $request_id = $user_request->request_id;
        
        // Retrieve payment transaction details using the request_id
        $payment_transaction_details = PaymentTransaction::where('request_id', $request_id)->get();

        // Query user's tracks for the authenticated user's email with pagination
        $user_track = UserTrack::where('request_id', '=', $request_id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);
    
        return view('dashboard.transcript-upload', compact('payment_transaction_details', 
        'user_requests', 'user_track'));  
    }

    public function transcriptUploadAction(Request $request, string $id) 
    {
        try {
            $validatedData = $request->validate([
                'comment' => 'required|string',
                'transcript_status' => 'required|string',
                'transcript_file' => 'nullable|mimes:pdf',
            ]);       

            $user_request = UserRequests::findOrFail($id);                
            $request_id = $user_request->request_id;
            $user_id = $user_request->user_id;
            $email = $user_request->email;

            // Check if an upload has been made for the request_id
            $transcriptUpload = TranscriptUpload::where('request_id', $request_id)->first();

            if ($transcriptUpload) {
                // If an upload has already been done, redirect back with a message
                return redirect()->back()->with('error', 'Transcript has already been uploaded for this request.');
            }

            // Proceed with file upload only if a file has been uploaded
            if ($request->hasFile('transcript_file')) {
                $userCertificateFile = $request->file('transcript_file');
                $request_id_new = str_replace('#', '', $request_id);
                // Generate filenames                 
                $userCertificateFilename = $request_id_new . '_transcript.' . $userCertificateFile->getClientOriginalExtension();
                // Store file
                $certificatePath = $userCertificateFile->storeAs('transcript', $userCertificateFilename, 'public');
            }

            // Create UserTranscript record
            $userTranscript = TranscriptUpload::create([
                'user_id' => $user_id,
                'request_id' => $request_id,
                'email' => $email,
                'upload_by' => "admin",
                'status' => "successful",
                'transcript_dir' => $certificatePath ?? null, 
            ]);

            // Create UserTrack record
            $userTrack = UserTrack::create([
                'user_id' => $user_id,
                'request_id' => $request_id,
                'certificate_status' => $validatedData['transcript_status'],
                'approved_by' => "admin",
                'comments' => $validatedData['comment'],
            ]);

            //--Update user request data-----
            UserRequests::where('id', $id)->update([
                'certificate_status' => $validatedData['transcript_status'],            
            ]); 

            return redirect()->route('admin-dashboard')->with('success', 'User transcript upload successful.');
        } catch (ValidationException $e) {
            // Validation failed. Redirect back with validation errors.
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            // Log the error
            $errorMessage = 'Error-User transcript upload: ' . $e->getMessage();
            Log::error($errorMessage);

            return redirect()->back()->with('error', 'An error occurred during User transcript upload. Please try again.');
        }
    }

    public function userTranscriptUpload()
    {
        $user_id = auth()->user()->id;
        // Query all admin users
        $users = User::where('user_type', '=', 'admin')->get();
        
        // Query successful payment transactions
        $successful_transactions = PaymentTransaction::where('transaction_status', 'Successful')->get();

        // Extract request IDs from successful transactions
        $successful_request_ids = $successful_transactions->pluck('request_id');

        // Query successful transcript uploads
        $transcript_uploads = TranscriptUpload::where('status', 'Successful')->get();

        // Extract request IDs from successful transcript uploads
        $successful_request_ids = $transcript_uploads->pluck('request_id');

        // Query user requests based on successful request IDs
        $user_requests = UserRequests::whereIn('request_id', $successful_request_ids)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add date of upload to each user request
        foreach ($user_requests as $user_request) {
            $transcript_upload = TranscriptUpload::where('request_id', $user_request->request_id)->first();
            $user_request->date_of_upload = $transcript_upload ? $transcript_upload->created_at : null;
        }

        // Query user's transcript uploads
        $user_transcript = TranscriptUpload::all();

        // Pass variables to the view
        return view('dashboard.user-transcript-upload', compact('users', 'user_requests', 'user_transcript', 'transcript_uploads'));
    }
   

}
