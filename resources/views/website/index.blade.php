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
<meta name="google-site-verification" content="j0WLEeSR9tfA1xzwociTgtoABBROCrIIp_RJAFHuD9s" />

    <!-- Site Icons -->
    <link href="{{ asset(\App\WebsiteSetting::get('favicon', 'images/favicon.ico')) }}" rel="icon">
    <link rel="apple-touch-icon" href="#" />



    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}?v={{ time() }}" />
    <!-- Pogo Slider CSS -->
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
        $accentColor = \App\WebsiteSetting::get('accent_color', '#d69e2e');
        $headerBgColor = \App\WebsiteSetting::get('header_bg_color', '#ffffff');
        $footerBgColor = \App\WebsiteSetting::get('footer_bg_color', '#1a202c');
        $siteLogo = \App\WebsiteSetting::get('site_logo', 'images/logo.png');
        $footerLogo = \App\WebsiteSetting::get('footer_logo', 'images/logo.png');
    @endphp
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
            --accent-color: {{ $accentColor }};
            --header-bg-color: {{ $headerBgColor }};
            --footer-bg-color: {{ $footerBgColor }};
        }
        /* Apply theme colors */
        .btn-primary, .button-theme, .bg-theme, .main-btn {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .btn-primary:hover, .button-theme:hover {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }
        .text-primary, .text-theme, a.text-theme {
            color: var(--primary-color) !important;
        }
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        .section-title h2::after, .title-box h2::after {
            background-color: var(--primary-color) !important;
        }
        .footer, footer, .footer-section {
            background-color: var(--footer-bg-color) !important;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }
        .border-primary {
            border-color: var(--primary-color) !important;
        }
        /* Section backgrounds */
        .services-box:hover, .about-box:hover {
            border-color: var(--primary-color) !important;
        }
        .services-box .icon-box {
            background-color: var(--primary-color) !important;
        }
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

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">


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

                     <!-- Center Logo -->
                        <li class="nav-item d-flex align-items-center">
                            <a class="navbar-brand logo-center" href="{{ route('website.index') }}">
                                <img style="height:80px; width:100px;" src="{{ asset($siteLogo) }}" alt="Rose of Sharon High School">
                            </a>
                        </li>
                        <li><a class="nav-link active" href="{{ route('website.index') }}">Home</a></li>
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
                                <a href="{{ route('website.index') }}">Online Application</a>
                            </div>
                        </li>
                        <li><a class="nav-link" href="{{ route('shop.index') }}">Shop</a></li>
                        <li><a class="nav-link" href="{{ route('website.News') }}">News Letter</a></li>
                        <li><a class="nav-link" href="{{ route('website.contact') }}">Contact Us</a></li>
                        <li><a class="nav-link" href="{{ url('/logins') }}" style="background-color: var(--primary-color); color: white; padding: 8px 20px; border-radius: 5px; margin-left: 10px;">Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <!-- End header -->

    <!-- Start Banner -->
    @php
        $banner = App\Banner::first(); // Get the first banner entry
    @endphp

    <div class="ulockd-home-slider">
        <div class="container-fluid">
            <div class="row">
                <div class="pogoSlider" id="js-main-slider">
                    @if($banner && $banner->image_path_1)
                    <div class="pogoSlider-slide" style="background-image: url('{{ asset('storage/' . $banner->image_path_1) }}');">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="slide_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($banner && $banner->image_path_2)
                    <div class="pogoSlider-slide" style="background-image: url('{{ asset('storage/' . $banner->image_path_2) }}');">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="slide_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($banner && $banner->image_path_3)
                    <div class="pogoSlider-slide" style="background-image: url('{{ asset('storage/' . $banner->image_path_3) }}');">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="slide_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(!$banner || (!$banner->image_path_1 && !$banner->image_path_2 && !$banner->image_path_3))
                    <div class="pogoSlider-slide" style="background-image: url('{{ asset('images/slider-01.jpg') }}'); background-color: #1e3a5f;">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="slide_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <!-- .pogoSlider -->
            </div>
        </div>
    </div>
    <!-- End Banner -->
    <!-- section -->
	<div class="section tabbar_menu">
	   <div class="container">
	      <div class="row">
		      <div class="col-md-12">
			     <div class="tab_menu">
				    <ul>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i1.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_1_text', 'Roshs Life') }}</span></a></li>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i2.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_2_text', 'Graduation') }}</span></a></li>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i3.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_3_text', 'Athletics') }}</span></a></li>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i4.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_4_text', 'Social') }}</span></a></li>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i5.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_5_text', 'Location') }}</span></a></li>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i6.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_6_text', 'Call us') }}</span></a></li>
					   <li><a href="#"><span class="icon"><img src="{{ asset('images/i5.png') }}" alt="#" /></span><span>{{ \App\WebsiteSetting::get('home_tab_7_text', 'Email') }}</span></a></li>
					</ul>
				 </div>
			  </div>
		  </div>
	   </div>
	</div>
	<!-- end section -->
    <div class="section margin-top_50 ">
        <div class="container">
            <div class="row">
			    <div class="col-md-6">
                    <div class="full float-right_img">
                     <iframe src="{{ \App\WebsiteSetting::get('home_facebook_embed', 'https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fprofile.php%3Fid%3D100094334670439&tabs=timeline&width=500&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&appId=699810548919387') }}" width="500" height="500" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                    </div>
                </div>
                <div class="col-md-6 layout_padding_2">
                    <div class="full">
                        <div class="heading_main text_align_left">
						   <h2><span>{{ \App\WebsiteSetting::get('home_about_title', 'About Rose of Sharon') }}</h2>
                        </div>
						<div class="full">
						  <p>{{ \App\WebsiteSetting::get('home_about_text', 'Then Rose Of Sharon High School will know that he is the Lord God who lives in Zion his holy mountain, Rose of Sharon High School will remain forever and foreign mountains will drip with sweet wine and the hills will flow with milk, water will fill the streambeds of Rose of Sharon High School and the fountain will burst forth from the Lord\'s temple watering the arid valleys of Rose of Sharon High School. Rose of Sharon High School will remain forever and Rose of Sharon High School will endure through all future generations') }}</p>
						</div>
						<div class="full">
						   <a class="hvr-radial-out button-theme" href="Application.html">Apply</a>
						</div>
                    </div>
                </div>
			</div>
        </div>
    </div>

	<!-- section -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-6 layout_padding_2">
                    <div class="full">
                        <div class="heading_main text_align_left">
						   <h2><span>{{ \App\WebsiteSetting::get('home_director_name', 'Dr. Fatima Maruta') }}
                            </span > {{ \App\WebsiteSetting::get('home_director_title', 'DIRECTOR OF EDUCATION') }}</h2>
                        </div>
						<div class="full">
						  <p>{{ \App\WebsiteSetting::get('home_director_bio', 'Dr. Fatima Maruta is a holder of several accounting qualifications that include a Bachelor’s Degree in Accountancy from the University of Zimbabwe and Masters Degree in Business Adminstration from Bloomsburg University, PA USA. In year 2014, she was conferred with an Honorary Doctorate Degree in Humane Letters, DHL, from the International Institute of Philanthropy IIP in recognition of practical application of expertise in Humanities. In 2016 she was conferred with an Honorary Doctor of Arts Degree and an Honorary Master of Business Leadership Degree from International Women’s University in recognition of practical application of expertise in Humanities. In the same year, she was conferred an award as Zimbabwe Top female academic leader by the Zimbabwe Leadership Awards') }}</p>
						</div>
						<div class="full">
						   <a class="hvr-radial-out button-theme" href="about.html">About more</a>
						</div>
                    </div>
                </div>
				<div class="col-md-6">
                    <div class="full">
                        <img src="{{ asset(\App\WebsiteSetting::get('home_director_image', 'images/img2.png')) }}" alt="#" />
                    </div>
                </div>
            </div>
        </div>
    </div>
	<!-- end section -->
    <div class="section layout_padding padding_bottom-0">
        <div class="container">
            <div class="row">

              </div>
               <div class="row">
                <div class="col-lg-12">
                    <div id="achievementsCarousel" class="carousel slide" data-ride="carousel">

                        <!-- The slideshow -->
                        <div class="carousel-inner">
                            @if(isset($achievements) && $achievements->count() > 0)
                                @foreach($achievements->chunk(2) as $index => $chunk)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <div class="row">
                                        @foreach($chunk as $achievement)
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                           <div class="full blog_img_popular">
                                              <img class="img-responsive" src="{{ asset('storage/' . $achievement->image_path) }}" alt="{{ $achievement->student_name }}" />
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            @else
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
                            @endif
                        </div>

                        <!-- Left and right controls -->
                        <a class="carousel-control-prev" href="#achievementsCarousel" data-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </a>
                        <a class="carousel-control-next" href="#achievementsCarousel" data-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </a>

                    </div>
                </div>

            </div>
           </div>
        </div>
	<!-- section -->
    <div class="section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="full">
                        <div class="heading_main text_align_center">
						   <h2><span>{{ \App\WebsiteSetting::get('home_clubs_title', 'OUR CLUBS') }}</span></h2>
                        </div>
					  </div>
                </div>
				<div class="col-md-4">
                    <div class="full blog_img_popular">
                       <img class="img-responsive" src="{{ asset(\App\WebsiteSetting::get('home_club_1_image', 'images/drama.jpg')) }}" alt="#" />
					   <h4>{{ \App\WebsiteSetting::get('home_club_1_title', 'DRAMA') }}</h4>
                       <p>{{ \App\WebsiteSetting::get('home_club_1_text', 'This club forms the basis of raising facts on specified topics hence providing indispensable to people.') }}</p>
                    </div>
                </div>
				<div class="col-md-4">
                    <div class="full blog_img_popular">
                        <img class="img-responsive" src="{{ asset(\App\WebsiteSetting::get('home_club_2_image', 'images/inreaction.jpg')) }}" alt="#" />
						<h4>{{ \App\WebsiteSetting::get('home_club_2_title', 'INTERACT') }}</h4>
                        <p>{{ \App\WebsiteSetting::get('home_club_2_text', 'This club existed since 2012 at R.O.S.H. It is aimed at providing social support to the vulnerable.') }}</p>
                    </div>
                </div>
				<div class="col-md-4">
                    <div class="full blog_img_popular">
                        <img class="img-responsive" src="{{ asset(\App\WebsiteSetting::get('home_club_3_image', 'images/dedate.jpg')) }}" alt="#" />
						<h4>{{ \App\WebsiteSetting::get('home_club_3_title', 'DEBATE') }}</h4>
                        <p>{{ \App\WebsiteSetting::get('home_club_3_text', 'This club forms the basis of raising facts on specified topics hence providing indispensable to people.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<!-- end section -->
	<!-- section -->

	<!-- end section -->
	<!-- section -->
    <div class="section layout_padding padding_bottom-0">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="full">
                        <div class="heading_main text_align_left">
						   <h2><span>{{ \App\WebsiteSetting::get('home_declaration_title', 'SCHOOL DECLARATION') }}</span></h2>
                        </div>
						<div class="full">
						  <p>{{ \App\WebsiteSetting::get('home_declaration_text', 'Then Rose Of Sharon High School will know that he is the Lord God who lives in Zion his holy mountain, Rose of Sharon High School will remain forever and foreign mountains will drip with sweet wine and the hills will flow with milk, water will fill the streambeds of Rose of Sharon High School and the fountain will burst forth from the Lord’s temple watering the arid valleys of Rose of Sharon High School. Rose of Sharon High School will remain forever and Rose of Sharon High School will endure through all future generations.') }}</p>
						</div>
						<div class="full">
						   <a class="hvr-radial-out button-theme" href="success.html">See More</a>
						</div>
                    </div>
                </div>
				<div class="col-md-6">
                    <div class="full">
                        <img class="img-responsive" src="{{ asset(\App\WebsiteSetting::get('home_declaration_image', 'images/req.jpg')) }}" alt="#" />
                    </div>
                </div>
            </div>
        </div>
    </div>
	<!-- end section -->
	<!-- section -->
    <div class="section layout_padding padding_bottom-0">
        <div class="container">
            <div class="row">

			  </div>
               <div class="row">
                <div class="col-lg-12">
                    <div id="studentsCarousel" class="carousel slide" data-ride="carousel">

                        <!-- The slideshow -->
                        <div class="carousel-inner">
                            @if(isset($achievements) && $achievements->count() > 0)
                                @foreach($achievements->chunk(2) as $index => $chunk)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <div class="row">
                                        @foreach($chunk as $achievement)
                                        <div class="col-lg-6 col-md-6 col-sm-12">
									       <div class="full blog_img_popular" style="position: relative;">
                                              <img class="img-responsive" src="{{ asset('storage/' . $achievement->image_path) }}" alt="{{ $achievement->student_name }}" />
                                              <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to right, #c9302c, #1e7e34); color: white; padding: 10px 15px; font-weight: bold; font-size: 16px;">
                                                  {{ strtoupper($achievement->student_name) }}
                                              </div>
										    </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="carousel-item active">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
									       <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/img9.png" alt="#" />
										    </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="full blog_img_popular">
                                              <img class="img-responsive" src="images/img8.png" alt="#" />
										    </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Left and right controls -->
                        <a class="carousel-control-prev" href="#studentsCarousel" data-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </a>
                        <a class="carousel-control-next" href="#studentsCarousel" data-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </a>

                    </div>
                </div>

            </div>
           </div>
        </div>
	<!-- end section -->


    <!-- Start Footer -->
    <footer class="footer-box">
        <div class="container">

		   <div class="row">

		      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
			     <div class="footer_blog">
				    <div class="full margin-bottom_30">
				<img style="height:80px;width :100px" src="{{ asset($footerLogo) }}" alt="image">
					 </div>
					 <div class="full white_fonts">
					    <p>{{ \App\WebsiteSetting::get('footer_vision_text', 'Our Vision is provide a well-groomed, enriched (in ideas) and productive learner given a firm foundation for tertiary and life challenges.') }}</p>
					 </div>
				 </div>
			  </div>

			  <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
			       <div class="footer_blog footer_menu white_fonts">
						    <h3>Quick links</h3>
						    <ul>
							  <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_1', 'Join Us') }}</a></li>
							  <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_2', 'Maintenance') }}</a></li>
							  <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_3', 'Language Packs') }}</a></li>
							  <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_4', 'LearnPress') }}</a></li>
							  <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_5', 'Release Status') }}</a></li>
							</ul>
						 </div>
				 </div>

				 <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
				 <div class="footer_blog full white_fonts">
						     <h3>Newsletter</h3>

							 <div class="newsletter_form">
							    <form action="index.html">
								   <input type="email" placeholder="Your Email" name="#" required />
								   <button>Submit</button>
								</form>
							 </div>
						 </div>
					</div>

			  <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
				 <div class="footer_blog full white_fonts">
						     <h3>Contact us</h3>
							 <ul class="full">
			   <li><img src="images/i5.png"><span>  6884 Mt Madecheche Road<br>Zimre Park</span></li>
							   <li><img src="images/i6.png"><span>infor@roshs.co.zw</span></li>
							   <li><img src="images/i7.png"><span>+263 772 490 478</span></li>
							 </ul>
						 </div>
					</div>

		   </div>

        </div>
    </footer>
    <!-- End Footer -->

    <div class="footer_bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p style="color: white">
                    <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This website is made with <i class="icon-heart" aria-hidden="true"></i> by <a style="color: white" href="https://lotusdreammaker.co.zw" target="_blank" >Lotusdreammaker</a>
                    <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    </p>
                </div>
            </div>
        </div>
    </div>

    <a href="#" id="scroll-to-top" class="hvr-radial-out"><i class="fa fa-angle-up"></i></a>

    <!-- ALL JS FILES -->

    <script src="{{ asset('js/jquery.min.js') }}"></script>
	<script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <!-- ALL PLUGINS -->
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('js/jquery.pogo-slider.min.js') }}"></script>
    <script src="{{ asset('js/slider-index.js') }}"></script>
    <script src="{{ asset('js/smoothscroll.js') }}"></script>
    <script src="{{ asset('js/form-validator.min.js') }}"></script>
    <script src="{{ asset('js/contact-form-script.js') }}"></script>
    <script src="{{ asset('js/isotope.min.js') }}"></script>
    <script src="{{ asset('js/images-loded.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

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

<!-- WhatsApp Floating Button -->
<div class="whatsapp-float">
    <a href="https://wa.me/263772490478?text=Hello%20Rose%20of%20Sharon%20High%20School,%20I%20would%20like%20to%20inquire%20about..." target="_blank" class="whatsapp-btn">
        <i class="fab fa-whatsapp"></i>
    </a>
</div>

<style>
.whatsapp-float {
    position: fixed;
    width: 60px;
    height: 60px;
    bottom: 30px;
    right: 30px;
    background-color: #25d366;
    color: #FFF;
    border-radius: 50px;
    text-align: center;
    font-size: 30px;
    box-shadow: 2px 2px 3px #999;
    z-index: 1000;
    animation: pulse 2s infinite;
    transition: all 0.3s ease;
}

.whatsapp-float:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
}

.whatsapp-btn {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    border-radius: 50%;
}

.whatsapp-btn i {
    font-size: 28px;
    line-height: 60px;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}

/* Mobile responsiveness */
@media screen and (max-width: 768px) {
    .whatsapp-float {
        width: 50px;
        height: 50px;
        bottom: 20px;
        right: 20px;
        font-size: 25px;
    }

    .whatsapp-btn {
        width: 50px;
        height: 50px;
    }

    .whatsapp-btn i {
        font-size: 24px;
        line-height: 50px;
    }
}
</style>

<!-- Add Font Awesome for WhatsApp icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</body>

</html>
