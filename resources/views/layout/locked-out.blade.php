<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>@yield('pageTitle')</title>
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<!-- Font-->
	<link rel="stylesheet" type="text/css" href="{{asset('login/css/opensans-font.css')}}">
	<link rel="stylesheet" type="text/css" href="{{asset('login/fonts/line-awesome/css/line-awesome.min.css')}}">
	<!-- Jquery -->
	<link rel="stylesheet" href="https://jqueryvalidation.org/files/demo/site-demos.css">
	<!-- Main Style Css -->
    <link rel="stylesheet" href="{{asset('login/css/style.css')}}"/>
    <style type="text/css">
<!--
.style1 {color: #000000}
-->
    </style>
    <style>
        body {
          background-image: url({{asset('login/images/login-bg.jpg')}});
            /* Additional styling */
            background-size: cover; /* Adjust as needed */
            background-repeat: no-repeat; /* Adjust as needed */
        }
    .style2 {
	font-size: 12px;
	font-weight: bold;
}
    .style3 {font-size: 12px}
    </style>
</head>
<body class="form-v7">
	<div class="page-content">
		<div class="form-v7-content">
	  <div class="form-left">
				<img src="{{asset('login/images/form-v7.jpg')}}" alt="form">
				<p class="text-1 style1">&nbsp;</p>		
			</div><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>@yield('pageTitle')</title>
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<!-- Font-->
	<link rel="stylesheet" type="text/css" href="{{asset('login/css/opensans-font.css')}}">
	<link rel="stylesheet" type="text/css" href="{{asset('login/fonts/line-awesome/css/line-awesome.min.css')}}">
	<!-- Jquery -->
	<link rel="stylesheet" href="https://jqueryvalidation.org/files/demo/site-demos.css">
	<!-- Main Style Css -->
    <link rel="stylesheet" href="{{asset('login/css/style.css')}}"/>
	<link href="{{asset('loginback/vendor/images/favicon.png')}}" rel="icon">
    <style type="text/css">
<!--
.style1 {color: #000000}
-->
    </style>
    <style>
        body {
          background-image: url({{asset('login/images/login-bg.jpg')}});
            /* Additional styling */
            background-size: cover; /* Adjust as needed */
            background-repeat: no-repeat; /* Adjust as needed */
        }
    .style2 {
	font-size: 12px;
	font-weight: bold;
}
    .style3 {font-size: 12px}
    </style>
	<<style>
    /* Success Alert */
    .alert.alert-success {
        background-color: #28a745; /* Green background color */
        color: #fff; /* White text color */
        padding: 10px; /* Padding around the text */
        border-radius: 5px; /* Rounded corners */
    }

    /* Error Alert */
    .alert.alert-danger {
        background-color: #dc3545; /* Red background color */
        color: #fff; /* White text color */
        padding: 10px; /* Padding around the text */
        border-radius: 5px; /* Rounded corners */
    }
</>
</head>
<body class="form-v7">
	<div class="page-content">
		<div class="form-v7-content">
	  <div class="form-left">
				<img src="{{asset('login/images/form-v7.jpg')}}" alt="form">
				<p class="text-1 style1">&nbsp;</p>		
			</div>
			<form class="form-detail" action="{{ route('home') }}" method="post" id="myform">
        @csrf
            <h2><strong><u>Account Locked</u></strong></h2>           
            		
				<div class="form-row">					
                <p>For your account's security, we have temporarily locked access due to multiple unsuccessful login attempts. 
                    This is a precautionary measure to protect your account from unauthorized access.</p>
                    <h4>What can you do ?</h4>
                    <hr>
                    
                    <p>1. If you've forgotten your password or suspect unauthorized login attempts, you can <a href="{{route('password.request')}}">reset your password</a>, to regain access to your account.</p>
                    <p>2. If you believe this lockout is an error or need immediate assistance, please contact our support team at [support email:meetme@kingsconsult.com.ng].</p>
                    <hr>
                    <p>Thank you for understanding, and we apologize for any inconvenience.</p>   
        
				<div class="form-row-last">
					<input type="submit" name="Login" class="register" value="Back to Login">					
			  </div>             
                  
		    </form> 
</div>
	
		
</body>
</html>