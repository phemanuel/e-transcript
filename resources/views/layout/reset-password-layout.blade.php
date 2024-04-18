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

.style1 {color: #000000}

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
      @if(auth()->guest())
			<form class="form-detail" action="{{ route('password.update') }}" method="post" id="myform">
      @csrf	
							<input type="hidden" name="token" value="{{ $token }}">
            <h2><strong><u>Password Reset</u></strong></h2>  
           <strong><p>Enter your new password, confirm and submit.</p></strong> 
            @if(session('success'))
						<div class="alert alert-success">
							{{ session('success') }}
						</div>
          @elseif(session('error'))
						<div class="alert alert-danger">
							{{ session('error') }}
						</div>
						@endif	
            <br>
            <div class="form-row">
					<label for="new_password">Email Address: </label>
					<label for="new_password"><strong>{{ $email }}</strong> </label><br><br>
				  </div>	
				<div class="form-row">
					<label for="new_password">New Password</label>
					<input type="password" name="password" id="password" class="input-text" required>
				</div>
        @error('password')
									<span class="invalid-feedback">{{ $message }}</span>
				@enderror		
        <div class="form-row">
					<label for="new_password">Confirm New Password</label>
					<input type="password" name="password_confirmation" id="password_confirmation" class="input-text" required>
				</div>		        
				<div class="form-row-last">
        <input type="hidden" name="email" value="{{ $email }}">
					<input type="submit" name="Login" class="register" value="{{ __('Reset Password') }}">					
			  </div>
              <div class="form-row-last">
              <p><span class="style3">Back to<a href="{{route('login')}}" class="style2">Login</a></span></p>
              </div>
                  
		    </form>         
        @else
    <p>You are already logged in. You cannot reset your password while logged in.</p>
@endif
</div>
	
		
</body>
</html>