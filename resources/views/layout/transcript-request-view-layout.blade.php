<!DOCTYPE html>
<html lang="en">


<!-- index.html  21 Nov 2019 03:44:50 GMT -->
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>@yield('pageTitle')</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{asset('dashboard/assets/css/app.min.css')}}">
  <!-- Template CSS -->
  <link rel="stylesheet" href="{{asset('dashboard/assets/css/style.css')}}">
  <link rel="stylesheet" href="{{asset('dashboard/assets/css/components.css')}}">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="{{asset('dashboard/assets/css/custom.css')}}">
  <link rel='shortcut icon' type='image/x-icon' href="{{asset('dashboard/assets/img/favicon.png')}}" />
  <style>
    .black-link {
    color: black;
    font-weight: bold;
    }

    .black-link:hover {
        color: black;

    }
  </style>
</head>

<body>
  <!-- <div class="loader"></div> -->
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar sticky">
        <div class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn"> <i data-feather="align-justify"></i></a></li>
            <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
              </a></li>
           
          </ul>
        </div>
        <ul class="navbar-nav navbar-right">        
          <li class="dropdown"><a href="#" data-toggle="dropdown"
              class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image" src="{{ asset('dashboard/assets/img/blank.jpg') }}" alt="Profile Picture"
                class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
              <div class="dropdown-title">Hello {{auth()->user()->first_name}}</div> 
              <a href="{{ route('admin-account-setting', ['id' => auth()->user()->id]) }}" class="dropdown-item has-icon"> <i class="fas fa-cog"></i>
                Account Settings
              </a>
              <div class="dropdown-divider"></div>
              <a href="{{route('logout')}}" class="dropdown-item has-icon text-danger"> <i class="fas fa-sign-out-alt"></i>
                Logout
              </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="{{route('dashboard')}}"> <img alt="image" src="{{asset('dashboard/assets/img/logo.png')}}" class="header-logo" /> <span
                class="logo-name">E-Transcript</span>
            </a>
          </div>
          <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown">
              <a href="{{route('admin-dashboard')}}" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
            </li>
            <li class="dropdown active">
              <a href="{{route('transcript-request')}}" class="nav-link"><i data-feather="briefcase"></i><span>Transcript Requests</span></a>
            </li>
            <li class="dropdown">
              <a href="{{ route('admin-account-setting', ['id' => auth()->user()->id]) }}" class="nav-link"><i data-feather="command"></i><span>Account Settings</span></a>
            </li> <li class="dropdown">
              <a href="{{route('users')}}" class="nav-link"><i data-feather="mail"></i><span>Users</span></a>
            </li>              
            
          </ul>
        </aside>
      </div>
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
        @if(session('success'))
                    <div class="alert alert-success">
                      {{ session('success') }}
                    </div>
                  @elseif(session('error'))
                    <div class="alert alert-danger">
                      {{ session('error') }}
                    </div>
                    @endif	

          <div class="row">

          <div class="col-md-6 col-lg-12 col-xl-6">
              <div class="card">
                <div class="card-header">
                  <h4>Transcript Request</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table width="100%" border="0" cellspacing="3">
                    @if ($user_requests->count() > 0)
			@foreach ($user_requests as $rd)
                        <form action="{{ route('transcript-request.action', $rd->id) }}"method="post">
                            @csrf                                                  
                      <tr>
                        <td>
                        <label>Request ID</label>
                        <p>{{ $rd->request_id }}</p>                  
                        </td>    
                        <td>
                        <label>Email Address</label>
                        <p>{{ $rd->email }}</p>                    
                        </td>           
                        </tr>
                        <tr>
                        <td>
                        <label>Matric No</label>
                        <p>{{ $rd->matric_no }}</p>
                        </td>  
                        <td>
                        <label>Programme</label>
                        <p>{{ $rd->email }}</p>                    
                        </td>             
                        </tr>
                        <tr>
                        <td>
                        <label>Graduation Year</label>
                        <p>{{ $rd->graduation_year }}</p>
                        </td>  
                        <td>
                        <label>Clearance No</label>
                        <p>{{ $rd->clearance_no }}</p>                    
                        </td>             
                        </tr>
                        <tr>
                        <td>
                        <label>Certificate Name</label>
                        <p>{{ $rd->certificate_name }}</p>
                        </td>  
                        <td>
                        <label>Date</label>
                        <p>{{ $rd->created_at }}</p>                    
                        </td>             
                        </tr>
                        @endforeach
		@else
		<tr>
			<td colspan="8">Request Details not available.</td>
		</tr>
		@endif             
<hr>
        <tr>
                            <td><strong><u>Payment Information</u></strong></td>
                        </tr> 
                        @if ($payment_transaction_details->count() > 0)
			@foreach ($payment_transaction_details as $rs)                     
                      <tr>
                        <td>
                        <label>Transaction ID</label>
                        <p>{{ $rs->transaction_id }}</p>                    
                        </td> 
                        <td>
                        <label>Amount</label>
                        <p>=N={{ number_format($rs->amount,2) }}</p>                    
                        </td>              
                        </tr>
                        <tr>
                        <td>
                        <label>Status</label>
                        <p>{{ $rs->transaction_status }}</p>                    
                        </td> 
                        <td>
                        <label>Payment Date</label>
                        <p>{{ $rs->created_at }}</p>                    
                        </td>              
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        @endforeach
		@else
		<tr>
			<td colspan="8">Transaction Details not available.</td>
		</tr>
		@endif    
    
        
        <tr>
            <td>
            <div class="form-group">
                    <label>Comment</label>
                    <textarea class="form-control" name="comment" required>{{ old('comment') }}</textarea>
                </div>  
                @error('comment')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror 
            </td>            
        </tr>
        <tr>
        <td>
            <div class="form-group">
                    <label>Transcript Status</label>
                    <select name="transcript_status" id="" class="form-control" required>
                        <option value="Processing">Processing</option>
                        <option value="Ready for pick-up">Ready for pick-up</option>
                    </select>
                </div>  
                @error('transcript_status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror 
            </td>
        </tr>
                     </table> 
                     <div class="card-footer text-right"> 
                    <input class="btn btn-primary mr-1" type="submit" value="Proceed"></input>
                  </div>          
                        </form>
                    </table>
                  </div>
                </div>
              </div>
            </div>


            <div class="col-md-6 col-lg-12 col-xl-6">
              <!-- Support tickets -->
              <div class="card">
                <div class="card-header">
                  <h4>Request Status</h4>                  
                </div>
                @if ($user_track->count() > 0)
			@foreach ($user_track as $rs)
                <div class="card-body">                 
                  <div class="support-ticket media pb-1 mb-3">                 
                    <img src="{{asset('dashboard/assets/img/blank.jpg')}}" class="user-img mr-2" alt="">
                    <div class="media-body ml-3">
                      <div class="badge badge-pill badge-info mb-1 float-right">{{$rs->certificate_status}}</div>
                      <span class="font-weight-bold">{{$rs->request_id}}</span>
                      <!-- <a href="javascript:void(0)">About template page load speed</a> -->
                      <p class="my-1">{{$rs->comments}}</p>
                      <small class="text-muted">Updated by <span class="font-weight-bold font-13">{{$rs->approved_by}}</span>
                        &nbsp;&nbsp; on {{ \Carbon\Carbon::parse($rs->created_at)->format('F j, Y, h:i A') }}

</small>
                    </div>                                     
                  </div>                 
                </div>  
                @endforeach 
		@else
    <p>Transcript status not available.</p>		
		@endif            
               <p> {{ $user_track->appends(['tab' => 'transcript-status'])->links() }}</p>
              </div>
              <!-- Support tickets -->
            </div>
            
          </div>
        </section>
        <div class="settingSidebar">
          <a href="javascript:void(0)" class="settingPanelToggle"> <i class="fa fa-spin fa-cog"></i>
          </a>
          <div class="settingSidebar-body ps-container ps-theme-default">
            <div class=" fade show active">
              <div class="setting-panel-header">Setting Panel
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Select Layout</h6>
                <div class="selectgroup layout-color w-50">
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout" checked>
                    <span class="selectgroup-button">Light</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                    <span class="selectgroup-button">Dark</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                <div class="selectgroup selectgroup-pills sidebar-color">
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="1" class="selectgroup-input select-sidebar">
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="2" class="selectgroup-input select-sidebar" checked>
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Dark Sidebar"><i class="fas fa-moon"></i></span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Color Theme</h6>
                <div class="theme-setting-options">
                  <ul class="choose-theme list-unstyled mb-0">
                    <li title="white" class="active">
                      <div class="white"></div>
                    </li>
                    <li title="cyan">
                      <div class="cyan"></div>
                    </li>
                    <li title="black">
                      <div class="black"></div>
                    </li>
                    <li title="purple">
                      <div class="purple"></div>
                    </li>
                    <li title="orange">
                      <div class="orange"></div>
                    </li>
                    <li title="green">
                      <div class="green"></div>
                    </li>
                    <li title="red">
                      <div class="red"></div>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                      id="mini_sidebar_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Mini Sidebar</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                      id="sticky_header_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Sticky Header</span>
                  </label>
                </div>
              </div>
              <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                  <i class="fas fa-undo"></i> Restore Default
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          <a href="https://oyschst.edu.ng" target="_blank">Oyo State College of Health, Science and Technology</a></a>
        </div>
        <div class="footer-right">
        </div>
      </footer>
    </div>
  </div>
  <!-- General JS Scripts -->
  <script src="{{asset('dashboard/assets/js/app.min.js')}}"></script>
  <!-- JS Libraies -->
  <script src="{{asset('dashboard/assets/bundles/apexcharts/apexcharts.min.js')}}"></script>
  <!-- Page Specific JS File -->
  <script src="{{asset('dashboard/assets/js/page/index.js')}}"></script>
  <!-- Template JS File -->
  <script src="{{asset('dashboard/assets/js/scripts.js')}}"></script>
  <!-- Custom JS File -->
  <script src="{{asset('dashboard/assets/js/custom.js')}}"></script>
</body>


<!-- index.html  21 Nov 2019 03:47:04 GMT -->
</html>