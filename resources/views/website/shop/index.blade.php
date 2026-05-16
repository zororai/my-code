<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop - {{ \App\WebsiteSetting::get('site_name', 'Rose Of Sharon High School') }}</title>
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
  
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #f5f5f5;
        }
        .product-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            min-height: 50px;
        }
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #2d5016;
            margin-bottom: 10px;
        }
        .product-stock {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .btn-shop {
            background: #2d5016;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            text-align: center;
        }
        .btn-shop:hover {
            background: #1a3009;
            color: white;
            text-decoration: none;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .category-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #f0f0f0;
            border-radius: 20px;
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
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

    <!-- Page Header -->

      
    <section class="inner_banner">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="full"><h3>School Shop</h3></div>
                </div>
            </div>
        </div>
    </section>
    

    <!-- Shop Section -->
    <div id="shop" class="section wb" style="padding: 60px 0;">
        <div class="container">
            
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="{{ route('shop.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="font-weight-bold">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold">Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Sort By</label>
                            <select name="sort_by" class="form-control">
                                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
                                <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-shop btn-block">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('shop.index') }}" class="btn btn-secondary btn-block">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="product-card">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-image">
                                @else
                                    <div class="product-image d-flex align-items-center justify-content-center" style="background: #f5f5f5;">
                                        <span style="color: #999; font-size: 48px;">📦</span>
                                    </div>
                                @endif
                                
                                <div class="product-body">
                                    @if($product->category)
                                        <span class="category-badge">{{ $product->category }}</span>
                                    @endif
                                    
                                    <h3 class="product-title">{{ $product->name }}</h3>
                                    
                                    <div class="product-price">${{ number_format($product->price, 2) }}</div>
                                    
                                    <div class="product-stock">
                                        <i class="fa fa-check-circle" style="color: #28a745;"></i> 
                                        {{ $product->quantity }} in stock
                                    </div>
                                    
                                    @if($product->description)
                                        <p class="product-description">{{ Str::limit($product->description, 80) }}</p>
                                    @endif
                                    
                                    <a href="{{ route('shop.show', $product->id) }}" class="btn-shop">View Details</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-center">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <h3 style="color: #999;">No products available at the moment</h3>
                        <p>Please check back later or adjust your filters.</p>
                    </div>
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
