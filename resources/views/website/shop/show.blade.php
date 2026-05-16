<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} - {{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }} Shop</title>
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
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
    
        .product-detail-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background: #f5f5f5;
            border-radius: 12px;
        }
        .product-info {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .product-price {
            font-size: 36px;
            font-weight: bold;
            color: #2d5016;
            margin: 20px 0;
        }
        .product-stock {
            font-size: 18px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .product-category {
            display: inline-block;
            padding: 8px 20px;
            background: #f0f0f0;
            border-radius: 20px;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .btn-contact {
            background: #2d5016;
            color: white;
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
            transition: all 0.3s;
        }
        .btn-contact:hover {
            background: #1a3009;
            color: white;
            text-decoration: none;
        }
        .related-product-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        .related-product-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .related-product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
        }
    </style>
</head>

<body id="inner_page" data-spy="scroll" data-target="#navbar-wd" data-offset="98">

    <!-- LOADER -->
    <div id="preloader">
        <div class="loader-container">
            <div class="progress-br float shadow">
                <div class="progress__item"></div>
            </div>
        </div>
    </div>

    <!-- Start header -->
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
                                <img style="height:80px; width:100px;" src="{{ asset($siteLogo) }}" alt="{{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}">
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
                    <div class="full"><h3>{{ $product->name }}</h3></div>
                     
                </div>
            </div>
        </div>
    </section>

    <!-- Product Detail Section -->
    <div id="product-detail" class="section wb" style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-detail-image">
                    @else
                        <div class="product-detail-image d-flex align-items-center justify-content-center">
                            <span style="color: #999; font-size: 120px;">📦</span>
                        </div>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-info">
                        @if($product->category)
                            <span class="product-category">{{ $product->category }}</span>
                        @endif
                        
                        <h1 style="font-size: 32px; font-weight: 700; color: #333; margin-bottom: 20px;">{{ $product->name }}</h1>
                        
                        <div class="product-price">${{ number_format($product->price, 2) }}</div>
                        
                        <div class="product-stock">
                            <i class="fa fa-check-circle"></i> 
                            <strong>{{ $product->quantity }} units available</strong>
                        </div>

                        @if($product->sku)
                            <p style="color: #666; margin-bottom: 10px;"><strong>SKU:</strong> {{ $product->sku }}</p>
                        @endif

                        @if($product->description)
                            <div style="margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                                <h4 style="color: #333; margin-bottom: 15px;">Product Description</h4>
                                <p style="color: #666; line-height: 1.8;">{{ $product->description }}</p>
                            </div>
                        @endif

                        <div style="margin-top: 30px;">
                            <a href="{{ route('website.contact') }}" class="btn-contact">
                                <i class="fa fa-envelope"></i> Contact Us to Purchase
                            </a>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <p style="margin: 0; color: #856404;">
                                <i class="fa fa-info-circle"></i> 
                                To purchase this item, please contact our school office or visit us in person.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            @if($relatedProducts->count() > 0)
                <div class="row mt-5">
                    <div class="col-12">
                        <h2 style="text-align: center; margin-bottom: 40px; color: #333;">Related Products</h2>
                    </div>
                    @foreach($relatedProducts as $related)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="related-product-card">
                                @if($related->image)
                                    <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}" class="related-product-image">
                                @else
                                    <div class="related-product-image d-flex align-items-center justify-content-center">
                                        <span style="color: #999; font-size: 48px;">📦</span>
                                    </div>
                                @endif
                                
                                <div style="padding: 15px;">
                                    <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 10px;">{{ $related->name }}</h4>
                                    <p style="font-size: 20px; font-weight: bold; color: #2d5016; margin-bottom: 10px;">${{ number_format($related->price, 2) }}</p>
                                    <a href="{{ route('shop.show', $related->id) }}" class="btn btn-sm" style="background: #2d5016; color: white; width: 100%;">View Details</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <h3>{{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}</h3>
                    <p>Quality education for a brighter future.</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h3>Quick Links</h3>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('website.index') }}">Home</a></li>
                        <li><a href="{{ route('website.about') }}">About</a></li>
                        <li><a href="{{ route('shop.index') }}">Shop</a></li>
                        <li><a href="{{ route('website.contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3>Contact Info</h3>
                    <p>Email: info@roseofsharonhs.com<br>Phone: +123 456 7890</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p>&copy; {{ date('Y') }} {{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    
    <script>
        function toggleDropdown(id) {
            var dropdown = document.getElementById(id);
            dropdown.classList.toggle('show');
        }
        
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
