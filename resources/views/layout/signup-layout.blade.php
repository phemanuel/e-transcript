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
	<style>
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
</style>
</head>
<body class="form-v7">
	<div class="page-content">
		<div class="form-v7-content">
	  <div class="form-left">
				<img src="{{asset('login/images/form-v7.jpg')}}" alt="form">
				<p class="text-1 style1">&nbsp;</p>		
			</div>
			<form class="form-detail" action="{{ route('signup.action')}}#" method="post" id="myform">
        @csrf
            <h2><strong><u>Sign Up</u></strong></h2>
            
            <br>
            @if(session('success'))
						<div class="alert alert-success">
							{{ session('success') }}
						</div>
          @elseif(session('error'))
						<div class="alert alert-danger">
							{{ session('error') }}
						</div>
						@endif
						<div class="form-row">
					<label for="last_name">Last Name</label>
					<input type="text" name="last_name" id="last_name" class="input-text" value="{{ old('last_name') }}">
					@error('last_name')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
				</div>

				<div class="form-row">
					<label for="first_name">First Name</label>
					<input type="text" name="first_name" id="first_name" class="input-text" value="{{ old('first_name') }}">
					@error('first_name')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
				</div>

				<div class="form-row">
					<label for="email">Email Address</label>
					<input type="text" name="email" id="email" class="input-text" required pattern="[^@]+@[^@]+.[a-zA-Z]{2,6}" value="{{ old('email') }}">
					@error('email')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
				</div>

				<div class="form-row">
					<label for="password">Password <strong> (minimum of 8 characters)</strong></label>
					<input type="password" name="password" id="password" class="input-text" required>          
				</div>
        @error('password')
									<span class="invalid-feedback">{{ $message }}</span>
									@enderror
				<div class="form-row">
					<label for="comfirm_password">Confirm Password</label>
					<input type="password" name="password_confirmation" id="password_confirmation" class="input-text" required>
				</div>
        @error('password_confirmation')
									<span class="invalid-feedback">{{ $message }}</span>
									@enderror
				<div class="form-row-last">
					<input type="submit" name="register" class="register" value="Register">					
			  </div>
              <div class="form-row-last">
              <p><span class="style3">Already have an account,<a href="{{route('home')}}" class="style2">Log in</a></span></p>
              </div>
                  
		    </form>         
        
</div>
	
</body>
</html>