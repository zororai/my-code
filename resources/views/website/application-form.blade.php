<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Application Form - {{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}</title>
    <link href="{{ asset(\App\WebsiteSetting::get('favicon', 'images/favicon.ico')) }}" rel="icon">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css')}}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ time() }}" />
    
    @php
        $siteLogo = \App\WebsiteSetting::get('site_logo', 'images/logo.png');
    @endphp
    
  <style>
        .navbar-nav.equal-spacing {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
            padding: 0 60px !important;
        }
        .navbar-nav.equal-spacing li {
            flex: 0 0 auto !important;
        }
        .navbar-nav.equal-spacing .logo-center {
            margin: 0 50px !important;
        }

        /* Mobile Navigation Fixes */
        @media (max-width: 991px) {
            .navbar-nav.equal-spacing {
                flex-direction: column !important;
                padding: 20px !important;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 10px;
                margin-top: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .navbar-nav.equal-spacing li {
                width: 100% !important;
                margin: 5px 0 !important;
                text-align: center;
            }

            .navbar-nav.equal-spacing .logo-center {
                order: -1 !important;
                margin: 15px 0 25px 0 !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
                background: transparent !important;
            }

            .navbar-nav.equal-spacing .logo-center .navbar-brand {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .navbar-nav.equal-spacing .logo-center img {
                width: 90px !important;
                height: 90px !important;
                display: block !important;
                margin: 0 auto !important;
                border-radius: 50% !important;
                box-shadow: 0 6px 12px rgba(45, 80, 22, 0.3) !important;
                border: 3px solid #2d5016 !important;
            }

            .navbar-nav.equal-spacing .nav-link {
                padding: 12px 20px !important;
                border-radius: 8px;
                background: #f8f9fa;
                margin: 3px 0;
                color: #333 !important;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .navbar-nav.equal-spacing .nav-link:hover,
            .navbar-nav.equal-spacing .nav-link.active {
                background: #2d5016 !important;
                color: white !important;
            }

            .dropdown-content {
                position: static !important;
                display: block !important;
                background: #e9ecef !important;
                box-shadow: none !important;
                border-radius: 5px !important;
                margin-top: 5px !important;
                padding: 10px !important;
            }

            .dropdown-content a {
                padding: 8px 15px !important;
                margin: 2px 0 !important;
                border-radius: 5px !important;
                background: white !important;
                color: #333 !important;
            }

            .dropdown-content a:hover {
                background: #2d5016 !important;
                color: white !important;
            }
        }
   

        .form-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .form-section h4 { color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 20px; }
        .form-control { margin-bottom: 10px; }
        .btn-add-document { padding: 6px 15px; }
        .document-row { margin-bottom: 10px; }
    </style>
</head>
<body id="inner_page" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <div id="preloader">
        <div class="loader"><img src="images/loader.gif" alt="#" /></div>
    </div>

      <header class="top-header">
        <nav class="navbar header-nav navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-wd" aria-controls="navbar-wd" aria-expanded="false" aria-label="Toggle navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="collapse navbar-collapse" id="navbar-wd">
                    <ul class="navbar-nav equal-spacing">
                        <li class="nav-item d-flex align-items-center">
                            <a class="navbar-brand logo-center" href="{{ route('website.index') }}">
                                <img style="height:80px; width:100px;" src="{{ asset($siteLogo) }}" alt="{{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}">
                            </a>
                        </li>
                        <li><a class="nav-link" href="{{ route('website.index') }}">Home</a></li>
                        <li><a class="nav-link" href="{{ route('website.about') }}">About</a></li>
                        <li class="navbar">
                            <a class="nav-link" href="javascript:void(0);" onclick="toggleDropdown('studentParentDropdown')">Parent/Student Portal</a>
                            <div class="dropdown-content" id="studentParentDropdown">
                                <a href="{{route('website.results') }}">Our Results</a>
                                <a href="{{ url('/logins') }}">Student Portal</a>
                            </div>
                        </li>
                        <li class="navbar">
                            <a class="nav-link" href="javascript:void(0);" onclick="toggleDropdown('admissionDropdown')">Admission</a>
                            <div class="dropdown-content" id="admissionDropdown">
                                <a href="{{ route('website.courses') }}">Our Subjects</a>
                                <a href="{{ route('website.application') }}">Application Form</a>
                            </div>
                        </li>
                        <li><a class="nav-link active" href="{{ route('shop.index') }}">Shop</a></li>
                        <li><a class="nav-link" href="{{ route('website.News') }}">News Letter</a></li>
                        <li><a class="nav-link" href="{{ route('website.contact') }}">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>


    <section class="inner_banner">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="full"><h3>Student Application Form</h3></div>
                </div>
            </div>
        </div>
    </section>

    <br><br>

    <div class="site-section">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('website.application.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- School Information -->
                <div class="form-section">
                    <h4>School You Wish to Apply For</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="school_applying_for">Select School</label>
                            <select name="school_applying_for" id="school_applying_for" class="form-control">
                                <option value="">Select School</option>
                                @php $sName = \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School'); @endphp
                                <option value="{{ $sName }}" {{ old('school_applying_for') == $sName ? 'selected' : '' }}>{{ $sName }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="previous_school">Enter Previous School Attended</label>
                            <input type="text" name="previous_school" id="previous_school" class="form-control" placeholder="Previous School Name" value="{{ old('previous_school') }}">
                        </div>
                    </div>
                </div>

                <!-- Student Information -->
                <div class="form-section">
                    <h4>Student Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="student_email">Student Email</label>
                            <input type="email" name="student_email" id="student_email" class="form-control" placeholder="Student Email" value="{{ old('student_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="student_phone">Student Phone Number</label>
                            <input type="tel" name="student_phone" id="student_phone" class="form-control" placeholder="Student Phone Number" value="{{ old('student_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="gender">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="">Select gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="applying_for_form">Applying for Form <span class="text-danger">*</span></label>
                            <select name="applying_for_form" id="applying_for_form" class="form-control" required>
                                <option value="">Applying for Form</option>
                                <option value="Form 1" {{ old('applying_for_form') == 'Form 1' ? 'selected' : '' }}>Form 1</option>
                                <option value="Form 2" {{ old('applying_for_form') == 'Form 2' ? 'selected' : '' }}>Form 2</option>
                                <option value="Form 3" {{ old('applying_for_form') == 'Form 3' ? 'selected' : '' }}>Form 3</option>
                                <option value="Form 4" {{ old('applying_for_form') == 'Form 4' ? 'selected' : '' }}>Form 4</option>
                                <option value="Form 5" {{ old('applying_for_form') == 'Form 5' ? 'selected' : '' }}>Form 5</option>
                                <option value="Form 6" {{ old('applying_for_form') == 'Form 6' ? 'selected' : '' }}>Form 6</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="religion">Religion</label>
                            <input type="text" name="religion" id="religion" class="form-control" placeholder="Religion" value="{{ old('religion') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="street_address">Street Address</label>
                            <input type="text" name="street_address" id="street_address" class="form-control" placeholder="Street Address" value="{{ old('street_address') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="residential_area">Select Residential Area (Optional)</label>
                            <select name="residential_area" id="residential_area" class="form-control">
                                <option value="">Select Residential Area</option>
                                <option value="Zimre Park" {{ old('residential_area') == 'Zimre Park' ? 'selected' : '' }}>Zimre Park</option>
                                <option value="Ruwa" {{ old('residential_area') == 'Ruwa' ? 'selected' : '' }}>Ruwa</option>
                                <option value="Harare" {{ old('residential_area') == 'Harare' ? 'selected' : '' }}>Harare</option>
                                <option value="Chitungwiza" {{ old('residential_area') == 'Chitungwiza' ? 'selected' : '' }}>Chitungwiza</option>
                                <option value="Epworth" {{ old('residential_area') == 'Epworth' ? 'selected' : '' }}>Epworth</option>
                                <option value="Other" {{ old('residential_area') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label>Select Subjects of Interest</label>
                            <div style="height: 200px; overflow-y: auto; border: 1px solid #ced4da; border-radius: 4px; padding: 15px; background: #fff;">
                                <!-- O LEVEL SUBJECTS -->
                                <h6 class="text-success font-weight-bold mb-2">🇿🇼 ZIMSEC O LEVEL SUBJECTS</h6>

                                <p class="mb-1"><strong>Core / Common Subjects</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="English Language" class="mr-1"> English Language</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Mathematics" class="mr-1"> Mathematics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Combined Science" class="mr-1"> Combined Science</label></div>
                                </div>

                                <p class="mb-1"><strong>Sciences</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Additional Mathematics" class="mr-1"> Additional Mathematics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Biology" class="mr-1"> Biology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Chemistry" class="mr-1"> Chemistry</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Physics" class="mr-1"> Physics</label></div>
                                </div>

                                <p class="mb-1"><strong>Commercial / Business Studies</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Accounting" class="mr-1"> Accounting</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Business Studies" class="mr-1"> Business Studies</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Economics" class="mr-1"> Economics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Commerce" class="mr-1"> Commerce</label></div>
                                </div>

                                <p class="mb-1"><strong>Humanities / Arts</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="History" class="mr-1"> History</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Geography" class="mr-1"> Geography</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Family & Religious Studies" class="mr-1"> Family & Religious Studies</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Sociology" class="mr-1"> Sociology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Heritage Studies" class="mr-1"> Heritage Studies</label></div>
                                </div>

                                <p class="mb-1"><strong>Languages</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Shona" class="mr-1"> Shona</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Ndebele" class="mr-1"> Ndebele</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Tonga" class="mr-1"> Tonga</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Venda" class="mr-1"> Venda</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Kalanga" class="mr-1"> Kalanga</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Sotho" class="mr-1"> Sotho</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="French" class="mr-1"> French</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Portuguese" class="mr-1"> Portuguese</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Chinese" class="mr-1"> Chinese</label></div>
                                </div>

                                <p class="mb-1"><strong>Practical / Technical Subjects</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Computer Science" class="mr-1"> Computer Science</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Computer Studies" class="mr-1"> Computer Studies</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Design & Technology" class="mr-1"> Design & Technology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Wood Technology" class="mr-1"> Wood Technology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Metal Technology" class="mr-1"> Metal Technology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Building Studies" class="mr-1"> Building Studies</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Technical Drawing" class="mr-1"> Technical Drawing</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Electrical Technology" class="mr-1"> Electrical Technology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Mechanical Technology" class="mr-1"> Mechanical Technology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Automotive Technology" class="mr-1"> Automotive Technology</label></div>
                                </div>

                                <p class="mb-1"><strong>Home & Creative Studies</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Fashion & Fabrics" class="mr-1"> Fashion & Fabrics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Food & Nutrition" class="mr-1"> Food & Nutrition</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Home Management" class="mr-1"> Home Management</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Art & Design" class="mr-1"> Art & Design</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Music" class="mr-1"> Music</label></div>
                                </div>

                                <p class="mb-1"><strong>Agricultural & Environmental</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Agriculture" class="mr-1"> Agriculture</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Environmental Science" class="mr-1"> Environmental Science</label></div>
                                </div>

                                <hr>

                                <!-- A LEVEL SUBJECTS -->
                                <h6 class="text-success font-weight-bold mb-2">🇿🇼 ZIMSEC A LEVEL SUBJECTS</h6>

                                <p class="mb-1"><strong>Sciences</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Mathematics" class="mr-1"> Mathematics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Pure Mathematics" class="mr-1"> Pure Mathematics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Further Mathematics" class="mr-1"> Further Mathematics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Biology" class="mr-1"> Biology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Chemistry" class="mr-1"> Chemistry</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Physics" class="mr-1"> Physics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Computer Science" class="mr-1"> Computer Science</label></div>
                                </div>

                                <p class="mb-1"><strong>Commercial / Business</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Accounting" class="mr-1"> Accounting</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Business Studies" class="mr-1"> Business Studies</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Economics" class="mr-1"> Economics</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Management of Business" class="mr-1"> Management of Business (MoB)</label></div>
                                </div>

                                <p class="mb-1"><strong>Humanities</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level History" class="mr-1"> History</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Geography" class="mr-1"> Geography</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Sociology" class="mr-1"> Sociology</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Family & Religious Studies" class="mr-1"> Family & Religious Studies</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Divinity" class="mr-1"> Divinity</label></div>
                                </div>

                                <p class="mb-1"><strong>Languages</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level English Language" class="mr-1"> English Language</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Literature in English" class="mr-1"> Literature in English</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Shona" class="mr-1"> Shona</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Ndebele" class="mr-1"> Ndebele</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level French" class="mr-1"> French</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Portuguese" class="mr-1"> Portuguese</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Chinese" class="mr-1"> Chinese</label></div>
                                </div>

                                <p class="mb-1"><strong>Agriculture & Applied Sciences</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Agriculture" class="mr-1"> Agriculture</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Environmental Management" class="mr-1"> Environmental Management</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Food Science" class="mr-1"> Food Science</label></div>
                                </div>

                                <p class="mb-1"><strong>Arts & Design</strong></p>
                                <div class="row mb-2">
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Art" class="mr-1"> Art</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="A Level Music" class="mr-1"> Music</label></div>
                                    <div class="col-md-4"><label class="form-check-label"><input type="checkbox" name="subjects_of_interest[]" value="Theatre Arts" class="mr-1"> Theatre Arts</label></div>
                                </div>
                            </div>
                            <small class="text-muted">Select all subjects you are interested in</small>
                        </div>
                    </div>
                </div>

                <!-- Guardian Information -->
                <div class="form-section">
                    <h4>Guardian Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="guardian_full_name">Guardian Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="guardian_full_name" id="guardian_full_name" class="form-control" placeholder="Guardian Full Name" value="{{ old('guardian_full_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="guardian_phone">Guardian Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="guardian_phone" id="guardian_phone" class="form-control" placeholder="Guardian Phone Number" value="{{ old('guardian_phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="guardian_email">Guardian Email</label>
                            <input type="email" name="guardian_email" id="guardian_email" class="form-control" placeholder="Guardian Email" value="{{ old('guardian_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="guardian_relationship">Relationship with Student <span class="text-danger">*</span></label>
                            <select name="guardian_relationship" id="guardian_relationship" class="form-control" required>
                                <option value="">Select relationship</option>
                                <option value="Father" {{ old('guardian_relationship') == 'Father' ? 'selected' : '' }}>Father</option>
                                <option value="Mother" {{ old('guardian_relationship') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                <option value="Guardian" {{ old('guardian_relationship') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                <option value="Uncle" {{ old('guardian_relationship') == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                <option value="Aunt" {{ old('guardian_relationship') == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                <option value="Grandparent" {{ old('guardian_relationship') == 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
                                <option value="Other" {{ old('guardian_relationship') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Other Information -->
                <div class="form-section">
                    <h4>Other Information</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="birth_entry_number">Birth Entry Number</label>
                            <input type="text" name="birth_entry_number" id="birth_entry_number" class="form-control" placeholder="Birth Entry Number" value="{{ old('birth_entry_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="dream_job">Dream Job</label>
                            <input type="text" name="dream_job" id="dream_job" class="form-control" placeholder="Dream Job" value="{{ old('dream_job') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="expected_start_date">Expected Start Date</label>
                            <input type="date" name="expected_start_date" id="expected_start_date" class="form-control" value="{{ old('expected_start_date') }}">
                        </div>
                    </div>
                </div>

                <!-- Documents Upload -->
                <div class="form-section">
                    <h4>Documents Upload</h4>
                    <div id="documents-container">
                        <div class="document-row row">
                            <div class="col-md-5">
                                <input type="text" name="document_names[]" class="form-control" placeholder="Document Name (e.g., Birth Certificate)">
                            </div>
                            <div class="col-md-5">
                                <input type="file" name="documents[]" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-success btn-add-document" onclick="addDocumentRow()">+</button>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Accepted formats: PDF, JPG, JPEG, PNG. Max size: 5MB per file.</small>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-4 mb-5">
                    <button type="submit" class="btn btn-primary btn-lg px-5">Submit Application</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer-box">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="footer_blog">
                        <div class="full margin-bottom_30">
                            <img style="height:80px;width:100px" src="{{ asset(\App\WebsiteSetting::get('footer_logo', 'images/logo.png')) }}" alt="image">
                        </div>
                        <div class="full white_fonts">
                            <p>Our Vision is to provide a well-groomed, enriched (in ideas) and productive learner given a firm foundation for tertiary and life challenges.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="footer_blog full white_fonts">
                        <h3>Contact us</h3>
                        <ul class="full">
                            <li><img src="images/i5.png"><span>6884 Mt Madecheche Road<br>Zimre Park</span></li>
                            <li><img src="images/i6.png"><span>info@roshs.co.zw</span></li>
                            <li><img src="images/i7.png"><span>+263 772 490 478</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div class="footer_bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p style="color: white">Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | {{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/custom.js"></script>
    <script>
        function addDocumentRow() {
            var container = document.getElementById('documents-container');
            var newRow = document.createElement('div');
            newRow.className = 'document-row row mt-2';
            newRow.innerHTML = '<div class="col-md-5"><input type="text" name="document_names[]" class="form-control" placeholder="Document Name"></div><div class="col-md-5"><input type="file" name="documents[]" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png"></div><div class="col-md-2"><button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">-</button></div>';
            container.appendChild(newRow);
        }
    </script>
</body>
</html>
