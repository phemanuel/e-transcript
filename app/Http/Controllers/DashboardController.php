<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTrack;
use App\Models\UserRequests;
use App\Models\UserProgramme;
use App\Models\UserClearance;
use App\Models\TranscriptFee;
use App\Models\PaymentTransaction;
use App\Models\UserYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        
       return view('dashboard.dashboard', compact('user_track', 'user_request'
       ,'user_payment'));        
    }
    public function indexAdmin()
    {
        $user_id = auth()->user()->id;
        // Query all admin user
        $users = User::where('user_type', '=', 'admin')->get();

        // Query all user request 
        $user_requests = UserRequests::orderBy('created_at', 'desc')->paginate(10);        
        
       return view('dashboard.dashboard-admin', compact('users', 'user_requests'
       ));        
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

    
}
