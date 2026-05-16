<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Application Submitted - {{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}</title>
    <link href="{{ asset(\App\WebsiteSetting::get('favicon', 'images/favicon.ico')) }}" rel="icon">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css')}}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ time() }}" />
</head>
<body id="inner_page">
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
                    <div class="full"><h3>Application Submitted</h3></div>
                </div>
            </div>
        </div>
    </section>

    <br><br><br>

    <div class="site-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="card shadow-lg">
                        <div class="card-body py-5">
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#28a745" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </div>
                            <h2 class="text-success mb-3">Thank You!</h2>
                            <h4 class="mb-4">Your Application Has Been Submitted Successfully</h4>
                            <p class="text-muted mb-4">
                                We have received your application and will review it shortly.
                                You will be contacted via phone or email regarding the status of your application.
                            </p>
                            <hr>
                            <p class="mb-4">
                                <strong>What happens next?</strong><br>
                                Our admissions team will review your application and documents.
                                You may receive a call or text message regarding your application status.
                            </p>
                            <a href="{{ route('website.index') }}" class="btn btn-primary btn-lg px-5">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br><br><br>

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
</body>
</html>
