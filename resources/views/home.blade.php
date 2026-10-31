<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niralagam — Blog</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('niralagam_logo.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Z3Z0LEKRP4"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-Z3Z0LEKRP4');
    </script>

    <link rel="stylesheet" href="{{ asset('theme/css/vendors/bootstrap-grid.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/carouselTicker.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/main.css') }}">
</head>
<body>

    {{-- Preloader --}}
    <div id="preloader">
        <div id="loader" class="loader">
            <div class="loader-container">
                <div class="loader-icon">
                    <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:60px;border-radius:12px;">
                </div>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <header>
        <nav class="navbar style-3">
            <div class="container">
                <div class="header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a class="navbar-brand fw-bold fs-3" href="{{ route('home') }}">
                            <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:36px;border-radius:8px;">
                        </a>
                    </div>
                    <div class="navbar-collapse d-none d-lg-block" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('home') }}"><span>Home</span></a>
                            </li>
                            @if($categories->isNotEmpty())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle link-effect-1" href="#"><span>Categories</span></a>
                                <ul class="dropdown-menu">
                                    @foreach($categories as $cat)
                                    <li><a class="dropdown-item" href="#">{{ $cat->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="#latest"><span>Latest</span></a>
                            </li>
                            @auth
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('admin.dashboard') }}"><span>Admin</span></a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        @guest
                        <a href="{{ route('login') }}" class="btn btn-dark btn-sm rounded-pill px-3 py-1 fs-8 fw-semibold d-none d-lg-inline-flex align-items-center gap-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                            Login
                        </a>
                        @endguest
                        <div class="group-btn-right d-flex align-items-center">
                            <div class="dark-light-switcher">
                                <svg class="svg-white dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0018 0.666664C4.85543 0.666664 0.666656 4.85406 0.666656 10.0004C0.666656 15.1468 4.85543 19.3356 10.0018 19.3356C15.1482 19.3356 19.337 15.1468 19.337 10.0004C19.337 4.85406 15.1482 0.666664 10.0018 0.666664ZM10.7212 2.13629C14.7526 2.4982 17.8982 5.87269 17.8982 10.0004C17.8982 14.1282 14.7526 17.5027 10.7212 17.8646V2.13629Z" fill="#75787D"/></svg>
                                <svg class="svg-dark dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0018 0.666992C4.85543 0.666992 0.666656 4.85439 0.666656 10.0008C0.666656 15.1471 4.85543 19.3359 10.0018 19.3359C15.1482 19.3359 19.337 15.1471 19.337 10.0008C19.337 4.85439 15.1482 0.666992 10.0018 0.666992ZM10.7212 2.13662C14.7526 2.49852 17.8982 5.87302 17.8982 10.0008C17.8982 14.1285 14.7526 17.503 10.7212 17.8649V2.13662Z" fill="#0E0E0F"/></svg>
                            </div>
                            <a href="#" class="navbar-toggler icon-shape">
                                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none"><path d="M6.5 5.19999C6.5 4.48205 7.08206 3.89999 7.8 3.89999H24.7C25.4179 3.89999 26 4.48205 26 5.19999C26 5.91794 25.4179 6.49999 24.7 6.49999H7.8C7.08206 6.49999 6.5 5.91789 6.5 5.19999ZM24.7 11.7H1.3C0.582055 11.7 0 12.2821 0 13C0 13.7179 0.582055 14.3 1.3 14.3H24.7C25.4179 14.3 26 13.7179 26 13C26 12.2821 25.4179 11.7 24.7 11.7ZM24.7 19.5H13C12.2821 19.5 11.7 20.082 11.7 20.8C11.7 21.5179 12.2821 22.1 13 22.1H24.7C25.4179 22.1 26 21.5179 26 20.8C26 20.082 25.4179 19.5 24.7 19.5Z" fill="#0E0E0F"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Mobile Sidebar --}}
    <div class="sidebar-left">
        <div class="header-sidebar d-flex align-items-center justify-content-between py-3">
            <a href="{{ route('home') }}" class="sidebar-brand fw-bold fs-3">
                <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:32px;border-radius:7px;">
            </a>
            <a href="#" class="close-sidebar">
                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17.25 6.75L6.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.75 6.75L17.25 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
        <ul class="sidebar-nav list-unstyled ps-0">
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('home') }}">Home</a>
            </li>
            @if($categories->isNotEmpty())
            <li class="nav-item collapse">
                <a class="nav-link mb-2 collapse-toggle" href="#">Categories</a>
                <ul class="collapse-menu d-flex flex-column gap-1 list-unstyled">
                    @foreach($categories as $cat)
                    <li><a class="collapse-item" href="#">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </li>
            @endif
            @auth
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('admin.dashboard') }}">Admin Panel</a>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('login') }}">Login</a>
            </li>
            @endauth
        </ul>
        <div class="sidebar-footer">
            <div class="">
                <div class="d-flex align-items-center">
                    <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 6.5L12 12.25L18.5 6.5" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="fs-7 fw-semibold">Newsletter</span>
                </div>
                <h5 class="my-2">Subscribe our newsletter</h5>
                <p>Get the latest posts delivered to your inbox.</p>
            </div>
            <form action="#" class="w-auto">
                <input type="email" class="form-control fs-7 mb-2 w-100" placeholder="Your email address">
                <button type="submit" class="btn btn-dark w-100 button-effect-1">Subscribe</button>
            </form>
        </div>
        <div class="text-center">
            <p class="fs-7">{{ date('Y') }} &copy; <span class="text-dark">Niralagam.</span> All Rights Reserved</p>
        </div>
    </div>
    <div class="sidebar-overlay"></div>

    <main>

        {{-- ── Hero Section (index-2 style) ── --}}
        <section class="sec-1-home-2 sec-padding">
            <div class="container">
                <div class="row mt-2 g-4">

                    {{-- Large featured post — card-11 --}}
                    @if($heroPost)
                    <div class="col-lg-6">
                        <div class="article card-11">
                            <div class="hover-effect-1">
                                <div class="card-img-top thumbnail">
                                    <a href="{{ route('post.show', $heroPost->slug) }}">
                                        @if($heroPost->featured_image)
                                            <img src="{{ $heroPost->featured_image }}" alt="{{ $heroPost->title }}" class="cover-image">
                                        @else
                                            <div class="cover-image" style="background:linear-gradient(135deg,#e0e7ff,#f0f4ff);min-height:380px;display:flex;align-items:center;justify-content:center;">
                                                <svg width="64" height="64" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                            </div>
                                        @endif
                                    </a>
                                    @if($heroPost->category)
                                    <a href="#" class="badge bg-2 fs-8">{{ $heroPost->category->name }}</a>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body hover-up">
                                <div class="card-corner">
                                    <a href="{{ route('post.show', $heroPost->slug) }}" class="arrow-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                    <div class="curve-one"></div>
                                    <div class="curve-two"></div>
                                </div>
                                <div class="left pe-5">
                                    <a href="{{ route('post.show', $heroPost->slug) }}" class="hover-underline">
                                        <h5 class="card-title mb-0 text-truncate-2">{{ $heroPost->title }}</h5>
                                    </a>
                                    @if($heroPost->excerpt)
                                    <p class="card-text text-600 fs-7 mb-0 mt-4 text-truncate-2">{{ $heroPost->excerpt }}</p>
                                    @endif
                                    <div class="bottom mt-auto d-flex flex-wrap align-items-center gap-2 pt-4">
                                        <a href="{{ route('post.show', $heroPost->slug) }}" class="author d-flex align-items-center gap-2">
                                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6c5ce7,#a29bfe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <span class="text-white fw-bold" style="font-size:11px;">{{ strtoupper(substr($heroPost->user->name ?? 'N', 0, 1)) }}</span>
                                            </div>
                                            <span class="fs-7 text-dark fw-regular">{{ $heroPost->user->name ?? 'Niralagam' }}</span>
                                        </a>
                                        <ul class="d-flex align-items-center gap-4 text-600 m-0 ps-3">
                                            <li><p class="fs-8 m-0">{{ $heroPost->published_at?->format('M j, Y') }}</p></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 4 small card-5 posts — col-lg-3 × 2 --}}
                    @php $heroSmall = $trendingSmall->take(4); @endphp

                    <div class="col-lg-3">
                        <div class="row g-4">
                            @foreach($heroSmall->take(2) as $hs)
                            <div class="col-lg-12 col-md-6">
                                <div class="article card-5">
                                    <div class="post-link">
                                        <div class="hover-effect-1">
                                            <div class="position-relative card-img-top thumbnail">
                                                <a href="{{ route('post.show', $hs->slug) }}">
                                                    @if($hs->featured_image)
                                                        <img src="{{ $hs->featured_image }}" alt="{{ $hs->title }}" class="cover-image">
                                                    @else
                                                        <div class="cover-image" style="background:linear-gradient(135deg,#e0e7ff,#f0f4ff);min-height:180px;display:flex;align-items:center;justify-content:center;">
                                                            <svg width="36" height="36" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                                        </div>
                                                    @endif
                                                </a>
                                                @if($hs->category)
                                                <a href="#" class="badge bg-1 fs-8">{{ $hs->category->name }}</a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-corner white no-border">
                                            <a href="{{ route('post.show', $hs->slug) }}" class="arrow-box">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                            <div class="curve-one"></div>
                                            <div class="curve-two"></div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-4">
                                        <a href="{{ route('post.show', $hs->slug) }}" class="hover-underline">
                                            <h6 class="card-title mb-0">{{ $hs->title }}</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="row g-4">
                            @foreach($heroSmall->skip(2)->take(2) as $hs)
                            <div class="col-lg-12 col-md-6">
                                <div class="article card-5">
                                    <div class="post-link">
                                        <div class="hover-effect-1">
                                            <div class="position-relative card-img-top thumbnail">
                                                <a href="{{ route('post.show', $hs->slug) }}">
                                                    @if($hs->featured_image)
                                                        <img src="{{ $hs->featured_image }}" alt="{{ $hs->title }}" class="cover-image">
                                                    @else
                                                        <div class="cover-image" style="background:linear-gradient(135deg,#e0e7ff,#f0f4ff);min-height:180px;display:flex;align-items:center;justify-content:center;">
                                                            <svg width="36" height="36" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                                        </div>
                                                    @endif
                                                </a>
                                                @if($hs->category)
                                                <a href="#" class="badge bg-1 fs-8">{{ $hs->category->name }}</a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-corner white no-border">
                                            <a href="{{ route('post.show', $hs->slug) }}" class="arrow-box">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                            <div class="curve-one"></div>
                                            <div class="curve-two"></div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-4">
                                        <a href="{{ route('post.show', $hs->slug) }}" class="hover-underline">
                                            <h6 class="card-title mb-0">{{ $hs->title }}</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ── Top Trending Section ── --}}
        <section class="sec-6-home-2 pb-70 sec-padding">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex">
                                <div class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M0.582044 11.7285C8.79451 13.4712 10.252 14.8614 12.125 22.7372C13.8067 14.8768 15.2308 13.4992 23.4018 11.8279C15.1894 10.0852 13.7319 8.69503 11.8589 0.81924C10.1769 8.67956 8.75306 10.0571 0.582044 11.7285Z" fill="#0E0E0F"/></svg>
                                    <h5 class="mb-0 text-anime-style-2">Top Trending</h5>
                                </div>
                                <p class="fs-7 ms-3 mb-2 d-none d-lg-block text-anime-style-1">Today's Most Popular Stories</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($trendingPost)
                <div class="row mt-2 g-4">
                    <div class="col-lg-6">
                        <div class="article card-1">
                            <div class="hover-effect-1">
                                <div class="card-img-top thumbnail">
                                    <a href="{{ route('post.show', $trendingPost->slug) }}">
                                        @if($trendingPost->featured_image)
                                            <img src="{{ $trendingPost->featured_image }}" alt="{{ $trendingPost->title }}" class="cover-image">
                                        @else
                                            <div class="cover-image" style="background:linear-gradient(135deg,#e0e7ff,#f0f4ff);display:flex;align-items:center;justify-content:center;min-height:300px;">
                                                <svg width="64" height="64" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                            </div>
                                        @endif
                                    </a>
                                    @if($trendingPost->category)
                                    <a href="#" class="badge bg-2 fs-8 position-absolute top-0 start-0 m-3">{{ $trendingPost->category->name }}</a>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body position-relative hover-up">
                                <div class="card-corner">
                                    <a href="{{ route('post.show', $trendingPost->slug) }}" class="arrow-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                    <div class="curve-one"></div>
                                    <div class="curve-two"></div>
                                </div>
                                <div class="left">
                                    <a href="{{ route('post.show', $trendingPost->slug) }}" class="hover-underline">
                                        <h5 class="card-title mb-0">{{ $trendingPost->title }}</h5>
                                    </a>
                                    @if($trendingPost->excerpt)
                                    <p class="card-text text-600 fs-7 mb-0 mt-4 text-truncate-3">{{ $trendingPost->excerpt }}</p>
                                    @endif
                                    <div class="bottom mt-auto d-flex flex-wrap align-items-center gap-2 pt-4">
                                        <a href="#" class="author d-flex align-items-center gap-2">
                                            <span class="fs-7 text-dark fw-regular">{{ $trendingPost->user->name ?? 'Niralagam' }}</span>
                                        </a>
                                        <ul class="d-flex align-items-center gap-4 text-600 m-0 ps-3">
                                            <li><p class="fs-8 m-0">{{ $trendingPost->published_at?->format('M j, Y') }}</p></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="row g-4">
                            @foreach($featuredTwo as $fp)
                            <div class="col-12 col-md-6">
                                <div class="article card-5">
                                    <div class="post-link">
                                        <div class="hover-effect-1">
                                            <div class="position-relative card-img-top thumbnail">
                                                <a href="{{ route('post.show', $fp->slug) }}">
                                                    @if($fp->featured_image)
                                                        <img src="{{ $fp->featured_image }}" alt="{{ $fp->title }}" class="cover-image">
                                                    @else
                                                        <div class="cover-image" style="background:linear-gradient(135deg,#e0e7ff,#f0f4ff);min-height:180px;display:flex;align-items:center;justify-content:center;">
                                                            <svg width="40" height="40" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                                        </div>
                                                    @endif
                                                </a>
                                                @if($fp->category)
                                                <a href="#" class="badge bg-1 fs-8">{{ $fp->category->name }}</a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-corner white no-border">
                                            <a href="{{ route('post.show', $fp->slug) }}" class="arrow-box">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                            <div class="curve-one"></div>
                                            <div class="curve-two"></div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-4">
                                        <a href="{{ route('post.show', $fp->slug) }}" class="hover-underline">
                                            <h6 class="card-title mb-0">{{ $fp->title }}</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            @foreach($trendingSmall->skip(2)->take(2) as $ts)
                            <div class="mt-3">
                                <div class="article card-6">
                                    <a href="{{ route('post.show', $ts->slug) }}" class="thumbnail overflow-hidden">
                                        @if($ts->featured_image)
                                            <img src="{{ $ts->featured_image }}" alt="{{ $ts->title }}">
                                        @else
                                            <div style="width:80px;height:80px;background:linear-gradient(135deg,#e0e7ff,#f0f4ff);border-radius:8px;flex-shrink:0;"></div>
                                        @endif
                                    </a>
                                    <div class="card-body mt-md-0 mt-4">
                                        <a href="{{ route('post.show', $ts->slug) }}" class="hover-underline">
                                            <h6 class="card-title mb-2">{{ $ts->title }}</h6>
                                        </a>
                                        <ul class="d-flex align-items-center gap-4 text-600 m-0 ps-0">
                                            <li class="list-unstyled"><p class="fs-8 m-0">{{ $ts->published_at?->format('M j, Y') }}</p></li>
                                            <li><p class="fs-8 m-0">{{ max(1, (int)ceil(str_word_count(strip_tags($ts->content ?? '')) / 200)) }} mins read</p></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>

        {{-- ── Latest Posts + Sidebar ── --}}
        <section class="sec-2-home-3 overflow-hidden" id="latest">
            <div class="container">
                <div class="row g-lg-4 g-5">

                    {{-- Main column --}}
                    <div class="col-lg-8">
                        <div class="section-title d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex">
                                <div class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M0.582044 11.7285C8.79451 13.4712 10.252 14.8614 12.125 22.7372C13.8067 14.8768 15.2308 13.4992 23.4018 11.8279C15.1894 10.0852 13.7319 8.69503 11.8589 0.81924C10.1769 8.67956 8.75306 10.0571 0.582044 11.7285Z" fill="#0E0E0F"/></svg>
                                    <h5 class="mb-0 text-anime-style-2">Latest</h5>
                                </div>
                                <p class="fs-7 ms-3 mb-2 d-none d-lg-block text-anime-style-1">Real-Time Updates That Matter</p>
                            </div>
                        </div>

                        <div class="row mt-2 g-4">
                            @forelse($latestPosts as $post)
                            <div class="col-12">
                                <div class="article card-9 d-flex flex-md-row align-items-stretch flex-column">
                                    <a href="{{ route('post.show', $post->slug) }}" class="card-img-top">
                                        <span class="hover-effect-1">
                                            @if($post->featured_image)
                                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="cover-image thumbnail">
                                            @else
                                                <div class="cover-image thumbnail" style="background:linear-gradient(135deg,#f0f4ff,#e0e7ff);min-height:160px;display:flex;align-items:center;justify-content:center;">
                                                    <svg width="40" height="40" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                                </div>
                                            @endif
                                        </span>
                                    </a>
                                    <div class="card-body">
                                        <div class="card-corner">
                                            <a href="{{ route('post.show', $post->slug) }}" class="arrow-box">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                            <div class="curve-one"></div>
                                            <div class="curve-two"></div>
                                        </div>
                                        <div class="left">
                                            <div class="card-info d-flex align-items-center mb-3">
                                                @if($post->category)
                                                <a href="#" class="badge bg-1 fs-8">{{ $post->category->name }}</a>
                                                @endif
                                                <ul class="d-flex align-items-center text-600 m-0 ps-4">
                                                    <li><p class="fs-8 m-0">{{ max(1, (int)ceil(str_word_count(strip_tags($post->content ?? '')) / 200)) }} mins read</p></li>
                                                </ul>
                                            </div>
                                            <a href="{{ route('post.show', $post->slug) }}" class="hover-underline">
                                                <h5 class="card-title mb-0 mt-3 text-truncate-2">{{ $post->title }}</h5>
                                            </a>
                                            @if($post->excerpt)
                                            <p class="card-text text-600 fs-7 mb-0 mt-3 text-truncate-2">{{ $post->excerpt }}</p>
                                            @endif
                                            <div class="bottom mt-auto d-flex flex-wrap align-items-center gap-2 pt-4">
                                                <span class="fs-8 text-600">{{ $post->published_at?->format('M j, Y') }}</span>
                                                <span class="fs-8 text-600">· {{ $post->user->name ?? 'Niralagam' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-5">
                                <p class="text-600 fs-7">No posts published yet.</p>
                            </div>
                            @endforelse
                        </div>

                        {{-- Pagination --}}
                        @if($latestPosts->hasPages())
                        <div class="row pt-5">
                            <div class="col-12 d-flex justify-content-start align-items-center">
                                <nav>
                                    <ul class="pagination gap-2">
                                        @if($latestPosts->onFirstPage())
                                        <li class="page-item">
                                            <span class="icon-lg pagination_item rounded-circle icon-shape" style="opacity:.4;cursor:default;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M9.49993 6.5L4.78564 11L9.49993 15.5" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.2143 11H5" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                        </li>
                                        @else
                                        <li class="page-item">
                                            <a class="icon-lg pagination_item rounded-circle icon-shape" href="{{ $latestPosts->previousPageUrl() }}#latest">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M9.49993 6.5L4.78564 11L9.49993 15.5" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.2143 11H5" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                        </li>
                                        @endif

                                        @foreach(range(1, $latestPosts->lastPage()) as $page)
                                        <li class="page-item">
                                            @if($page == $latestPosts->currentPage())
                                            <span class="icon-lg pagination_item rounded-circle icon-shape fs-18 fw-semi-bold current">{{ $page }}</span>
                                            @else
                                            <a class="icon-lg pagination_item rounded-circle icon-shape fs-18 fw-semi-bold" href="{{ $latestPosts->url($page) }}#latest">{{ $page }}</a>
                                            @endif
                                        </li>
                                        @endforeach

                                        @if($latestPosts->hasMorePages())
                                        <li class="page-item">
                                            <a class="icon-lg pagination_item rounded-circle icon-shape" href="{{ $latestPosts->nextPageUrl() }}#latest">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M12.5 6.5L17.2143 11L12.5 15.5" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.9999 11H4.78564" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                        </li>
                                        @else
                                        <li class="page-item">
                                            <span class="icon-lg pagination_item rounded-circle icon-shape" style="opacity:.4;cursor:default;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M12.5 6.5L17.2143 11L12.5 15.5" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.9999 11H4.78564" stroke="#0E0E0F" stroke-width="1.28571" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                        </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Sidebar --}}
                    <div class="col-lg-4">
                        {{-- Weekly trending --}}
                        @if($sidebarPosts->isNotEmpty())
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M0.582044 11.7285C8.79451 13.4712 10.252 14.8614 12.125 22.7372C13.8067 14.8768 15.2308 13.4992 23.4018 11.8279C15.1894 10.0852 13.7319 8.69503 11.8589 0.81924C10.1769 8.67956 8.75306 10.0571 0.582044 11.7285Z" fill="#0E0E0F"/></svg>
                            <h5 class="mb-0">Recent Posts</h5>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mb-5">
                            @foreach($sidebarPosts as $sp)
                            <div class="hover-up w-100">
                                <div class="article card-10 style-2">
                                    <div class="hover-effect-1">
                                        <a href="{{ route('post.show', $sp->slug) }}" class="card-img">
                                            @if($sp->featured_image)
                                                <img class="w-100" src="{{ $sp->featured_image }}" alt="{{ $sp->title }}">
                                            @else
                                                <div style="width:80px;min-width:80px;height:80px;background:linear-gradient(135deg,#e0e7ff,#f0f4ff);border-radius:8px;"></div>
                                            @endif
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <a href="{{ route('post.show', $sp->slug) }}">
                                            <h6 class="fs-7 mb-2 text-truncate-2">{{ $sp->title }}</h6>
                                        </a>
                                        <div class="d-flex align-items-center text-600">
                                            <span class="fs-8">{{ $sp->published_at?->format('d M, Y') }}</span>
                                            <ul class="ps-4 m-0">
                                                <li><span class="fs-8">{{ max(1, (int)ceil(str_word_count(strip_tags($sp->content ?? '')) / 200)) }} mins read</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Popular tags --}}
                        @if($tags->isNotEmpty())
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M0.582044 11.7285C8.79451 13.4712 10.252 14.8614 12.125 22.7372C13.8067 14.8768 15.2308 13.4992 23.4018 11.8279C15.1894 10.0852 13.7319 8.69503 11.8589 0.81924C10.1769 8.67956 8.75306 10.0571 0.582044 11.7285Z" fill="#0E0E0F"/></svg>
                            <h5 class="mb-0">Popular tags</h5>
                        </div>
                        <ul class="list-unstyled d-flex flex-wrap gap-3 ps-0">
                            @foreach($tags as $tag)
                            <li class="carouselTicker__item mx-0">
                                <a href="#" class="tag-item link-effect-2">
                                    <span class="text">
                                        <span class="text1">{{ $tag->name }}</span>
                                        <span class="text2">{{ $tag->name }}</span>
                                    </span>
                                    <span class="number">
                                        <span class="odometer text-nowrap" data-count="{{ $tag->posts_count }}"></span>
                                    </span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Newsletter Section ── --}}
        <section class="sec-3-home-3 sec-padding position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex justify-content-center">
                        <div class="block-newsletter style-2 position-relative">
                            <div class="newsletter text-center">
                                <div class="block-title">
                                    <h4 class="mb-4 text-anime-style-3">Subscribe to our newsletter</h4>
                                </div>
                                <form action="#" class="position-relative">
                                    <div class="d-flex flex-wrap flex-md-nowrap justify-content-center gap-2 align-items-center mb-4">
                                        <input class="form-control" type="email" placeholder="Your email address">
                                        <button class="btn btn-dark button-effect-1" type="submit">Subscribe</button>
                                    </div>
                                    <span class="fs-7 fw-regular">You'll only receive valuable news updates—no spam.</span>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- Footer --}}
    <footer>
        <div class="section-footer-3 bg-200 overflow-hidden">
            <div class="container">
                <div class="row g-5 sec-padding">
                    <div class="col-lg-4 pe-lg-5">
                        <div class="d-flex gap-2 align-items-end">
                            <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:36px;border-radius:8px;">
                            <p class="fs-7 m-0">Stories worth reading.</p>
                        </div>
                        <p class="fs-7 text-dark mt-4">Blending tech, life, and culture. Stay informed with fresh insights and expert takes across every topic that matters.</p>
                        @auth
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-sm button-effect-1 mt-2">Admin Panel →</a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-dark btn-sm button-effect-1 mt-2">Login →</a>
                        @endauth
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-4">
                            @if($categories->isNotEmpty())
                            <div class="col-lg-4 col-md-4 col-6">
                                <h6 class="mb-3">Categories</h6>
                                <ul class="list-unstyled ps-0">
                                    @foreach($categories->take(6) as $cat)
                                    <li class="mb-3">
                                        <a class="text-500 hover-dark button-effect-1" href="#">{{ $cat->name }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            <div class="col-lg-4 col-md-4 col-6">
                                <h6 class="mb-3">Quick Links</h6>
                                <ul class="list-unstyled ps-0">
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('home') }}">Home</a></li>
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="#latest">Latest Posts</a></li>
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('login') }}">Login</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="bottom-footer3 d-flex flex-wrap justify-content-lg-between justify-content-center align-items-center gap-lg-5 gap-3">
                            <p class="text-500 m-0">{{ date('Y') }} Copyright &copy; <span class="text-dark">Niralagam</span>. All Rights Reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Scroll top --}}
    <div class="btn-scroll-top">
        <svg class="progress-square svg-content" width="100%" height="100%" viewBox="0 0 40 40">
            <path d="M20 1a19 19 0 1 1 0 38 19 19 0 0 1 0-38"/>
        </svg>
    </div>

    <script src="{{ asset('theme/js/vendors/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/aos.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/wow.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/headhesive.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/smart-stick-nav.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/gsap.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/ScrollToPlugin.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/ScrollTrigger.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/Splitetext.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/jquery.carouselTicker.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/jquery.odometer.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/jquery.appear.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/color-modes.js') }}"></script>
    <script src="{{ asset('theme/js/gsap-custom.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/image-hover-effects.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>
</body>
</html>
