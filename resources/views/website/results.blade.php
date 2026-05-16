<!DOCTYPE html>
<html lang="en">
<!-- Basic -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="google-site-verification" content="2AYp_7df5X-y63gLw2QzPzTyNWqyaCuWkZbiGwS4iSw" />
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Site Metas -->
    <title>Rose Of Sharon High School </title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Site Icons -->
    <link href="{{ asset(\App\WebsiteSetting::get('favicon', 'images/favicon.ico')) }}" rel="icon">
    <link rel="apple-touch-icon" href="#" />



    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}?v={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/pogo-slider.min.css')}}?v={{ time() }}" />
    <!-- Site CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css')}}?v={{ time() }}" />
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ time() }}" />

    <!-- Dynamic Theme Colors -->
    @php
        $primaryColor = \App\WebsiteSetting::get('primary_color', '#2d5016');
        $secondaryColor = \App\WebsiteSetting::get('secondary_color', '#1a365d');
        $footerBgColor = \App\WebsiteSetting::get('footer_bg_color', '#1a202c');
        $siteLogo = \App\WebsiteSetting::get('site_logo', 'images/logo.png');
        $footerLogo = \App\WebsiteSetting::get('footer_logo', 'images/logo.png');
    @endphp
    <style>
        :root { --primary-color: {{ $primaryColor }}; --secondary-color: {{ $secondaryColor }}; --footer-bg-color: {{ $footerBgColor }}; }
        .btn-primary, .button-theme, .bg-theme, .main-btn { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
        .btn-primary:hover, .button-theme:hover { background-color: var(--secondary-color) !important; }
        .text-primary, .text-theme { color: var(--primary-color) !important; }
        .bg-primary { background-color: var(--primary-color) !important; }
        .footer, footer, .footer-section { background-color: var(--footer-bg-color) !important; }
    </style>

    <!-- Navigation Spacing Fix -->
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
    </style>

</head>

<body id="inner_page" data-spy="scroll" data-target="#navbar-wd" data-offset="98">

    <!-- LOADER -->
    <div id="preloader">
        <div class="loader">
            <img src="images/loader.gif" alt="#" />
        </div>
    </div>
    <!-- end loader -->
    <!-- END LOADER -->

   <!-- Start header -->
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
                                <img style="height:80px; width:100px;" src="{{ asset($siteLogo) }}" alt="Rose of Sharon High School">
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

	<!-- section -->

	<section class="inner_banner">
	  <div class="container">
	      <div class="row">
		      <div class="col-12">
			     <div class="full">
				     <h3>Our Results Show Room
			  </div>
		  </div>
	  </div>
	</section>

    <div class="section layout_padding padding_bottom-0">
        <div class="container">
            <div class="row">

              </div>
               <div class="row">
                <div class="col-lg-12">
                    <div id="demo" class="carousel slide" data-ride="carousel">

                        <!-- The slideshow -->
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                       <div class="full blog_img_popular">
                                          <img class="img-responsive" src="images/1 (1).jpg" alt="#" />

                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <div class="full blog_img_popular">
                                          <img class="img-responsive" src="images/1 (2).jpg" alt="#" />

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                       <div class="full blog_img_popular">
                                          <img class="img-responsive" src="images/1 (3).jpg" alt="#" />

                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <div class="full blog_img_popular">
                                          <img class="img-responsive" src="images/1 (4).jpg" alt="#" />

                                        </div>
                                    </div>
                                </div>
                            </div>
                                  <div class="carousel-item">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                       <div class="full blog_img_popular">
                                          <img class="img-responsive" src="images/1 (5).jpg" alt="#" />

                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <div class="full blog_img_popular">
                                          <img class="img-responsive" src="images/1 (2).jpg" alt="#" />

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Left and right controls -->
                        <a class="carousel-control-prev" href="#demo" data-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </a>
                        <a class="carousel-control-next" href="#demo" data-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </a>

                    </div>
                </div>

            </div>
           </div>
        </div>

        <div class="section layout_padding padding_bottom-0">
            <div class="container">
                <div class="row">

                  </div>
                   <div class="row">
                    <div class="col-lg-12">
                        <div id="demo" class="carousel slide" data-ride="carousel">

                            <!-- The slideshow -->
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                           <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/results/1 (1).jpg" alt="#" />

                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/results/1 (2).jpg" alt="#" />

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                           <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/results/1 (3).jpg" alt="#" />

                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/results/1 (4).jpg" alt="#" />

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                      <div class="carousel-item">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                           <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/results/1 (5).jpg" alt="#" />

                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/results/1 (6).jpg" alt="#" />

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Left and right controls -->
                            <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next" href="#demo" data-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </a>

                        </div>
                    </div>

                </div>
               </div>
            </div>

            <div class="section layout_padding padding_bottom-0">
                <div class="container">
                    <div class="row">

                      </div>
                       <div class="row">
                        <div class="col-lg-12">
                            <div id="demo" class="carousel slide" data-ride="carousel">

                                <!-- The slideshow -->
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                               <div class="full blog_img_popular">
                                                  <img class="img-responsive" src="images/results/1 (7).jpg" alt="#" />

                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="full blog_img_popular">
                                                  <img class="img-responsive" src="images/results/1 (8).jpg" alt="#" />

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                               <div class="full blog_img_popular">
                                                  <img class="img-responsive" src="images/results/1 (9).jpg" alt="#" />

                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="full blog_img_popular">
                                                  <img class="img-responsive" src="images/results/1 (10).jpg" alt="#" />

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                          <div class="carousel-item">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                               <div class="full blog_img_popular">
                                                  <img class="img-responsive" src="images/results/1 (11).jpg" alt="#" />

                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="full blog_img_popular">
                                                  <img class="img-responsive" src="images/results/1 (12).jpg" alt="#" />

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Left and right controls -->
                                <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </a>
                                <a class="carousel-control-next" href="#demo" data-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </a>

                            </div>
                        </div>

                    </div>
                   </div>
                </div>

                <div class="section layout_padding padding_bottom-0">
                    <div class="container">
                        <div class="row">

                          </div>
                           <div class="row">
                            <div class="col-lg-12">
                                <div id="demo" class="carousel slide" data-ride="carousel">

                                    <!-- The slideshow -->
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                   <div class="full blog_img_popular">
                                                      <img class="img-responsive" src="images/results/1 (13).jpg" alt="#" />

                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                    <div class="full blog_img_popular">
                                                      <img class="img-responsive" src="images/results/1 (14).jpg" alt="#" />

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                   <div class="full blog_img_popular">
                                                      <img class="img-responsive" src="images/results/1 (15).jpg" alt="#" />

                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                    <div class="full blog_img_popular">
                                                      <img class="img-responsive" src="images/results/1 (16).jpg" alt="#" />

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                              <div class="carousel-item">
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                   <div class="full blog_img_popular">
                                                      <img class="img-responsive" src="images/results/1 (17).jpg" alt="#" />

                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                    <div class="full blog_img_popular">
                                                      <img class="img-responsive" src="images/results/1 (18).jpg" alt="#" />

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Left and right controls -->
                                    <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </a>
                                    <a class="carousel-control-next" href="#demo" data-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </a>

                                </div>
                            </div>

                        </div>
                       </div>
                    </div>

                    <div class="section layout_padding padding_bottom-0">
                        <div class="container">
                            <div class="row">

                              </div>
                               <div class="row">
                                <div class="col-lg-12">
                                    <div id="demo" class="carousel slide" data-ride="carousel">

                                        <!-- The slideshow -->
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                                       <div class="full blog_img_popular">
                                                          <img class="img-responsive" src="images/results/1 (19).jpg" alt="#" />

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                                        <div class="full blog_img_popular">
                                                          <img class="img-responsive" src="images/results/1 (20).jpg" alt="#" />

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="carousel-item">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                                       <div class="full blog_img_popular">
                                                          <img class="img-responsive" src="images/results/1 (21).jpg" alt="#" />

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                                        <div class="full blog_img_popular">
                                                          <img class="img-responsive" src="images/results/1 (22).jpg" alt="#" />

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                  <div class="carousel-item">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                                       <div class="full blog_img_popular">
                                                          <img class="img-responsive" src="images/results/1 (23).jpg" alt="#" />

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- Left and right controls -->
                                        <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </a>
                                        <a class="carousel-control-next" href="#demo" data-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </a>

                                    </div>
                                </div>

                            </div>
                           </div>
                        </div>

                        <div class="section layout_padding padding_bottom-0">
                            <div class="container">
                                <div class="row">

                                  </div>
                                   <div class="row">
                                    <div class="col-lg-12">
                                        <div id="demo" class="carousel slide" data-ride="carousel">

                                            <!-- The slideshow -->
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <div class="row">
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                           <div class="full blog_img_popular">
                                                              <img class="img-responsive" src="images/alevel/1 (1).jpg" alt="#" />

                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                            <div class="full blog_img_popular">
                                                              <img class="img-responsive" src="images/alevel/1 (2).jpg" alt="#" />

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="carousel-item">
                                                    <div class="row">
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                           <div class="full blog_img_popular">
                                                              <img class="img-responsive" src="images/alevel/1 (3).jpg" alt="#" />

                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                            <div class="full blog_img_popular">
                                                              <img class="img-responsive" src="images/alevel/1 (4).jpg" alt="#" />

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                      <div class="carousel-item">
                                                    <div class="row">
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                           <div class="full blog_img_popular">
                                                              <img class="img-responsive" src="images/alevel/1 (5).jpg" alt="#" />

                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                                            <div class="full blog_img_popular">
                                                              <img class="img-responsive" src="images/alevel/1 (6).jpg" alt="#" />

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Left and right controls -->
                                            <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                                <span class="carousel-control-prev-icon"></span>
                                            </a>
                                            <a class="carousel-control-next" href="#demo" data-slide="next">
                                                <span class="carousel-control-next-icon"></span>
                                            </a>

                                        </div>
                                    </div>

                                </div>
                               </div>
                            </div>

                            <div class="section layout_padding padding_bottom-0">
                                <div class="container">
                                    <div class="row">

                                      </div>
                                       <div class="row">
                                        <div class="col-lg-12">
                                            <div id="demo" class="carousel slide" data-ride="carousel">

                                                <!-- The slideshow -->
                                                <div class="carousel-inner">
                                                    <div class="carousel-item active">
                                                        <div class="row">
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                               <div class="full blog_img_popular">
                                                                  <img class="img-responsive" src="images/alevel/1 (7).jpg" alt="#" />

                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                                <div class="full blog_img_popular">
                                                                  <img class="img-responsive" src="images/alevel/1 (8).jpg" alt="#" />

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="carousel-item">
                                                        <div class="row">
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                               <div class="full blog_img_popular">
                                                                  <img class="img-responsive" src="images/alevel/1 (9).jpg" alt="#" />

                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                                <div class="full blog_img_popular">
                                                                  <img class="img-responsive" src="images/alevel/1 (10).jpg" alt="#" />

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                          <div class="carousel-item">
                                                        <div class="row">
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                               <div class="full blog_img_popular">
                                                                  <img class="img-responsive" src="images/alevel/1 (11).jpg" alt="#" />

                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                                <div class="full blog_img_popular">
                                                                  <img class="img-responsive" src="images/alevel/1 (12).jpg" alt="#" />

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Left and right controls -->
                                                <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                                    <span class="carousel-control-prev-icon"></span>
                                                </a>
                                                <a class="carousel-control-next" href="#demo" data-slide="next">
                                                    <span class="carousel-control-next-icon"></span>
                                                </a>

                                            </div>
                                        </div>

                                    </div>
                                   </div>
                                </div>

                                <div class="section layout_padding padding_bottom-0">
                                    <div class="container">
                                        <div class="row">

                                          </div>
                                           <div class="row">
                                            <div class="col-lg-12">
                                                <div id="demo" class="carousel slide" data-ride="carousel">

                                                    <!-- The slideshow -->
                                                    <div class="carousel-inner">
                                                        <div class="carousel-item active">
                                                            <div class="row">
                                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                                   <div class="full blog_img_popular">
                                                                      <img class="img-responsive" src="images/alevel/1 (13).jpg" alt="#" />

                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                                    <div class="full blog_img_popular">
                                                                      <img class="img-responsive" src="images/alevel/1 (14).jpg" alt="#" />

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <div class="row">
                                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                                   <div class="full blog_img_popular">
                                                                      <img class="img-responsive" src="images/alevel/1 (15).jpg" alt="#" />

                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                                    <div class="full blog_img_popular">
                                                                      <img class="img-responsive" src="images/alevel/1 (16).jpg" alt="#" />

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                              <div class="carousel-item">
                                                            <div class="row">
                                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                                   <div class="full blog_img_popular">
                                                                      <img class="img-responsive" src="images/alevel/1 (17).jpg" alt="#" />

                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 col-md-6 col-sm-12">
                                                                    <div class="full blog_img_popular">
                                                                      <img class="img-responsive" src="images/alevel/1 (18).jpg" alt="#" />

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Left and right controls -->
                                                    <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                                        <span class="carousel-control-prev-icon"></span>
                                                    </a>
                                                    <a class="carousel-control-next" href="#demo" data-slide="next">
                                                        <span class="carousel-control-next-icon"></span>
                                                    </a>

                                                </div>
                                            </div>

                                        </div>
                                       </div>
                                    </div>


	<!-- end section -->
    @include('website.partials.footer')

    <a href="#" id="scroll-to-top" class="hvr-radial-out"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->
    <script src="js/jquery.min.js"></script>
	<script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.pogo-slider.min.js"></script>
    <script src="js/slider-index.js"></script>
    <script src="js/smoothscroll.js"></script>
    <script src="js/form-validator.min.js"></script>
    <script src="js/contact-form-script.js"></script>
    <script src="js/isotope.min.js"></script>
    <script src="js/images-loded.min.js"></script>
    <script src="js/custom.js"></script>
	<script>
	/* counter js */

(function ($) {
	$.fn.countTo = function (options) {
		options = options || {};

		return $(this).each(function () {
			// set options for current element
			var settings = $.extend({}, $.fn.countTo.defaults, {
				from:            $(this).data('from'),
				to:              $(this).data('to'),
				speed:           $(this).data('speed'),
				refreshInterval: $(this).data('refresh-interval'),
				decimals:        $(this).data('decimals')
			}, options);

			// how many times to update the value, and how much to increment the value on each update
			var loops = Math.ceil(settings.speed / settings.refreshInterval),
				increment = (settings.to - settings.from) / loops;

			// references & variables that will change with each update
			var self = this,
				$self = $(this),
				loopCount = 0,
				value = settings.from,
				data = $self.data('countTo') || {};

			$self.data('countTo', data);

			// if an existing interval can be found, clear it first
			if (data.interval) {
				clearInterval(data.interval);
			}
			data.interval = setInterval(updateTimer, settings.refreshInterval);

			// initialize the element with the starting value
			render(value);

			function updateTimer() {
				value += increment;
				loopCount++;

				render(value);

				if (typeof(settings.onUpdate) == 'function') {
					settings.onUpdate.call(self, value);
				}

				if (loopCount >= loops) {
					// remove the interval
					$self.removeData('countTo');
					clearInterval(data.interval);
					value = settings.to;

					if (typeof(settings.onComplete) == 'function') {
						settings.onComplete.call(self, value);
					}
				}
			}

			function render(value) {
				var formattedValue = settings.formatter.call(self, value, settings);
				$self.html(formattedValue);
			}
		});
	};

	$.fn.countTo.defaults = {
		from: 0,               // the number the element should start at
		to: 0,                 // the number the element should end at
		speed: 1000,           // how long it should take to count between the target numbers
		refreshInterval: 100,  // how often the element should be updated
		decimals: 0,           // the number of decimal places to show
		formatter: formatter,  // handler for formatting the value before rendering
		onUpdate: null,        // callback method for every time the element is updated
		onComplete: null       // callback method for when the element finishes updating
	};

	function formatter(value, settings) {
		return value.toFixed(settings.decimals);
	}
}(jQuery));

jQuery(function ($) {
  // custom formatting example
  $('.count-number').data('countToOptions', {
	formatter: function (value, options) {
	  return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
	}
  });

  // start all the timers
  $('.timer').each(count);

  function count(options) {
	var $this = $(this);
	options = $.extend({}, options || {}, $this.data('countToOptions') || {});
	$this.countTo(options);
  }
});
	</script>
    <script>
        function toggleDropdown() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.nav-link')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    <script>
        function toggleDropdown(dropdownId) {
            document.getElementById(dropdownId).classList.toggle("show");
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.nav-link')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>

</html>
