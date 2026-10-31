<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 160) }}">
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
                <div class="loader-icon"><img src="{{ asset('theme/imgs/template/logo/logo-gradient.svg') }}" alt="Preloader"></div>
            </div>
        </div>
    </div>

    {{-- Navbar style-2: nav left | logo center | actions right --}}
    <header>
        <nav class="navbar navbar-expand-lg style-2">
            <div class="navbar-collapse d-none d-lg-block start" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link link-effect-1" href="{{ route('home') }}"><span>Home</span></a></li>
                    @foreach(\App\Models\Category::orderBy('name')->limit(4)->get() as $cat)
                    <li class="nav-item"><a class="nav-link link-effect-1" href="#"><span>{{ $cat->name }}</span></a></li>
                    @endforeach
                </ul>
            </div>
            <a class="navbar-brand" href="{{ route('home') }}">
                <img class="dark-mode-invert" src="{{ asset('theme/imgs/template/logo/logo-dark.svg') }}" alt="{{ config('app.name') }}">
            </a>
            <div class="navbar-collapse d-none d-lg-block end">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link dark-mode-toggle" href="#">
                            <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-dark rounded-8 button-effect-1" href="{{ route('login') }}">Sign In</a>
                    </li>
                </ul>
            </div>
            <div class="burger-icon burger-icon-white d-block d-lg-none">
                <span class="burger-icon-top"></span>
                <span class="burger-icon-mid"></span>
                <span class="burger-icon-bottom"></span>
            </div>
        </nav>
    </header>

    {{-- Mobile Sidebar --}}
    <div class="mobile-header-active mobile-header-wrapper-style perfect-scrollbar">
        <div class="mobile-header-wrapper-inner">
            <div class="mobile-header-top">
                <div class="mobile-header-logo">
                    <a href="{{ route('home') }}"><img src="{{ asset('theme/imgs/template/logo/logo-dark.svg') }}" alt="{{ config('app.name') }}"></a>
                </div>
                <div class="mobile-menu-close"><span class="close-icon"></span></div>
            </div>
            <div class="mobile-header-content-area">
                <div class="perfect-scroll">
                    <div class="mobile-menu-wrap mobile-header-border">
                        <nav>
                            <ul class="mobile-menu font-heading">
                                <li><a href="{{ route('home') }}">Home</a></li>
                                @foreach(\App\Models\Category::orderBy('name')->limit(6)->get() as $cat)
                                <li><a href="#">{{ $cat->name }}</a></li>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                    <div class="mobile-account mt-3">
                        <a class="btn btn-dark rounded-8 w-100 button-effect-1" href="{{ route('login') }}">Sign In</a>
                    </div>
                    <div class="sidebar-footer mt-4">
                        <div class="d-flex align-items-center gap-1 mb-2">
                            <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 6.5L12 12.25L18.5 6.5" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="fs-7 fw-semibold">Newsletter</span>
                        </div>
                        <h5 class="my-2">Subscribe our newsletter</h5>
                        <form>
                            <input type="email" class="form-control fs-7 mb-2 w-100" placeholder="Your email address">
                            <button type="submit" class="btn btn-dark w-100 button-effect-1">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidebar-overlay"></div>

    <main>
        {{-- Section 1: Header + Content + Sidebar --}}
        <section class="sec-1-single-3 pb-70 overflow-hidden">
            <div class="position-relative block-banner">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8">
                            {{-- Breadcrumb --}}
                            <nav aria-label="breadcrumb">
                                <ul class="breadcrumb list-unstyled d-flex flex-row gap-2 align-items-center m-0 ps-0 py-4">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('home') }}" class="text-600 fs-7 hover-dark">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="icon-shape icon-xxs">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                                                <path d="M6.125 4.5625L9.5625 7.84375L6.125 11.125" stroke="#626568" stroke-width="0.9375" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="breadcrumb-item active text-dark fs-7" aria-current="page">
                                        {{ $post->category->name ?? 'Blog' }}
                                    </li>
                                </ul>
                            </nav>

                            {{-- Article header --}}
                            <div class="card-title">
                                <div class="article card-info d-flex flex-wrap align-items-center gap-2 mt-4">
                                    @if($post->category)
                                    <a href="#" class="badge bg-1 fs-8">{{ $post->category->name }}</a>
                                    @endif
                                    <ul class="d-flex align-items-center text-600 m-0 ps-3">
                                        <li>
                                            <p class="fs-8 m-0">{{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 200)) }} mins read</p>
                                        </li>
                                    </ul>
                                    <h3>{{ $post->title }}</h3>
                                </div>
                                <div class="border-top"></div>
                                <div class="bottom mt-auto d-flex flex-wrap align-items-center gap-2 pt-4">
                                    @if($post->user)
                                    <a href="{{ route('author.show', $post->user->handle()) }}" class="author d-flex align-items-center gap-2">
                                        @if($post->user->avatar)
                                        <img class="avatar avatar-md rounded-circle" src="{{ Storage::url($post->user->avatar) }}" alt="{{ $post->user->name }}">
                                        @else
                                        <span class="avatar avatar-md rounded-circle bg-dark d-flex align-items-center justify-content-center text-white fw-bold fs-7">{{ strtoupper(substr($post->user->name, 0, 1)) }}</span>
                                        @endif
                                        <span class="fs-7 text-dark fw-regular">{{ $post->user->name }}</span>
                                    </a>
                                    @endif
                                    <ul class="d-flex align-items-center gap-4 text-600 m-0 ps-3">
                                        <li><p class="fs-8 m-0">{{ optional($post->published_at)->format('M d, Y') }}</p></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="row mt-5 g-4">
                    {{-- Main content --}}
                    <div class="col-lg-8">
                        @if($post->featured_image)
                        <img class="rounded-8 w-100 mb-4" src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}">
                        @endif

                        <div class="d-flex flex-column gap-3">
                            {!! $post->body !!}

                            <div class="border-top mt-5 mb-1"></div>

                            {{-- Tags + Share --}}
                            <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @foreach($post->tags as $tag)
                                    <a href="#" class="tag-item"><span>{{ $tag->name }}</span></a>
                                    @endforeach
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-dark">Share:</span>
                                    <div class="d-inline-flex group-social-icons mt-0 rounded-8">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="icon-shape icon-46">
                                            <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="10" height="17" viewBox="0 0 10 17" fill="none"><path d="M8.84863 9.20312H6.5415V16.0938H3.46533V9.20312H0.942871V6.37305H3.46533V4.18896C3.46533 1.72803 4.94189 0.34375 7.1875 0.34375C8.26416 0.34375 9.40234 0.559082 9.40234 0.559082V2.98926H8.14111C6.91064 2.98926 6.5415 3.72754 6.5415 4.52734V6.37305H9.2793L8.84863 9.20312Z" fill="black"/></svg>
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="icon-shape icon-46">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
                                        </a>
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="icon-shape icon-46">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Newsletter --}}
                            <div class="block-subscribe h-100 d-flex flex-column flex-lg-row gap-3 justify-content-between bg-200 wow img-custom-anim-top">
                                <div>
                                    <div class="block-title d-flex align-items-center gap-1 fs-7 text-600">
                                        <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 6.5L12 12.25L18.5 6.5" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span class="fs-7 fw-regular">Newsletter</span>
                                    </div>
                                    <div class="title">
                                        <h5 class="mb-0">Stay Informed with <br class="d-none d-lg-block">Top Headlines</h5>
                                    </div>
                                </div>
                                <div class="decorate-1 mix-blend-multiply bottom-0" data-background="{{ asset('theme/imgs/template/decorate-3.png') }}"></div>
                                <form action="#" class="position-relative">
                                    <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center mb-3">
                                        <input class="form-control" type="email" placeholder="Your email address">
                                        <button class="btn btn-dark button-effect-1" type="submit">Subscribe</button>
                                    </div>
                                    <input type="checkbox" id="subscribe-post">
                                    <label for="subscribe-post" class="text-600 fs-8">By clicking the button, you are agreeing with our <a href="#" class="text-dark">Term & Conditions</a></label>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="col-lg-4">
                        <div class="row">
                            {{-- Author card --}}
                            @if($post->user)
                            <div class="col-md-6 col-lg-12 col-12">
                                <div class="author-card">
                                    <div class="card-img mb-4 text-center">
                                        @if($post->user->avatar)
                                        <img class="rounded-circle avatar-154" src="{{ Storage::url($post->user->avatar) }}" alt="{{ $post->user->name }}">
                                        @else
                                        <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center text-white fw-bold fs-2 mx-auto" style="width:154px;height:154px;font-size:3rem;">{{ strtoupper(substr($post->user->name, 0, 1)) }}</div>
                                        @endif
                                    </div>
                                    <div class="card-body text-center">
                                        <h5 class="mb-3">{{ $post->user->name }}</h5>
                                        <p class="mb-4">{{ $post->user->bio ?? 'Step into a space where thoughts bloom, stories breathe, and imagination roams free.' }}</p>
                                        <p class="text-dark mb-0">Follow me</p>
                                        <div class="d-inline-flex group-social-icons mt-2">
                                            <a href="#" class="icon-shape icon-46">
                                                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="10" height="17" viewBox="0 0 10 17" fill="none"><path d="M8.84863 9.20312H6.5415V16.0938H3.46533V9.20312H0.942871V6.37305H3.46533V4.18896C3.46533 1.72803 4.94189 0.34375 7.1875 0.34375C8.26416 0.34375 9.40234 0.559082 9.40234 0.559082V2.98926H8.14111C6.91064 2.98926 6.5415 3.72754 6.5415 4.52734V6.37305H9.2793L8.84863 9.20312Z" fill="black"/></svg>
                                            </a>
                                            <a href="#" class="icon-shape icon-46">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
                                            </a>
                                            <a href="#" class="icon-shape icon-46">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>
                                            </a>
                                        </div>
                                        <div class="mt-3">
                                            <a href="{{ route('author.show', $post->user->handle()) }}" class="btn btn-outline-dark rounded-8 btn-sm button-effect-1">View all posts</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Weekly trending --}}
                            <div class="col-md-6 col-lg-12 col-12">
                                <div class="d-flex align-items-center gap-2 mt-5 mt-lg-5 mt-md-0 mb-3">
                                    <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M0.582044 11.7285C8.79451 13.4712 10.252 14.8614 12.125 22.7372C13.8067 14.8768 15.2308 13.4992 23.4018 11.8279C15.1894 10.0852 13.7319 8.69503 11.8589 0.81924C10.1769 8.67956 8.75306 10.0571 0.582044 11.7285Z" fill="#0E0E0F"/></svg>
                                    <h5 class="mb-0">Weekly trending</h5>
                                </div>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($recentPosts as $recent)
                                    <div class="hover-up">
                                        <div class="article card-10 style-2">
                                            <div class="hover-effect-1">
                                                <a href="{{ route('post.show', $recent->slug) }}" class="card-img">
                                                    @if($recent->featured_image)
                                                    <img class="w-100" src="{{ Storage::url($recent->featured_image) }}" alt="{{ $recent->title }}">
                                                    @else
                                                    <div class="w-100 bg-200" style="height:80px;border-radius:8px;"></div>
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="card-body">
                                                <a href="{{ route('post.show', $recent->slug) }}">
                                                    <h6 class="fs-7 mb-2 text-truncate-2">{{ $recent->title }}</h6>
                                                </a>
                                                <div class="d-flex align-items-center text-600">
                                                    <span class="fs-8">{{ optional($recent->published_at)->format('d M, Y') }}</span>
                                                    <ul class="ps-4 m-0">
                                                        <li><span class="fs-8">{{ max(1, (int) ceil(str_word_count(strip_tags($recent->body)) / 200)) }} mins read</span></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Popular tags --}}
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mt-5 mb-3">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Related Posts --}}
        @if($related->count())
        <section class="related-post sec-padding bg-white">
            <div class="container">
                <div class="row g-4">
                    <div class="col-12">
                        <h5 class="mb-0">Recommended for You</h5>
                    </div>
                    @foreach($related as $rel)
                    <div class="col-md-6 col-lg-3">
                        <div class="article card-5">
                            <div class="post-link">
                                <div class="hover-effect-1">
                                    <div class="position-relative card-img-top thumbnail">
                                        <a href="{{ route('post.show', $rel->slug) }}">
                                            @if($rel->featured_image)
                                            <img src="{{ Storage::url($rel->featured_image) }}" alt="{{ $rel->title }}" class="cover-image">
                                            @else
                                            <div class="cover-image bg-200"></div>
                                            @endif
                                        </a>
                                        @if($rel->category)
                                        <a href="#" class="badge bg-1 fs-8">{{ $rel->category->name }}</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-corner white no-border">
                                    <a href="{{ route('post.show', $rel->slug) }}" class="arrow-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <div class="curve-one"></div>
                                    <div class="curve-two"></div>
                                </div>
                            </div>
                            <div class="card-body mt-4">
                                <a href="{{ route('post.show', $rel->slug) }}" class="hover-underline">
                                    <h6 class="card-title mb-0">{{ $rel->title }}</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    </main>

    {{-- Footer --}}
    <footer>
        <div class="section-footer-4 overflow-hidden">
            <div class="container border-top-300">
                <div class="row g-5 sec-padding">
                    <div class="col-lg-4 col-md-8 pe-lg-5">
                        <div class="d-flex gap-2 align-items-center">
                            <a class="dark-mode-invert" href="{{ route('home') }}"><img src="{{ asset('theme/imgs/template/logo/logo-dark.svg') }}" alt="{{ config('app.name') }}"></a>
                        </div>
                        <p class="fs-7 text-dark mt-4">Blending tech, life, and culture. Stay informed with fresh trends, smart insights, and expert takes across every topic that matters.</p>
                        <div class="d-inline-flex group-social-icons bg-transparent mt-3">
                            <a href="#" class="icon-shape icon-46">
                                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="10" height="17" viewBox="0 0 10 17" fill="none"><path d="M8.84863 9.20312H6.5415V16.0938H3.46533V9.20312H0.942871V6.37305H3.46533V4.18896C3.46533 1.72803 4.94189 0.34375 7.1875 0.34375C8.26416 0.34375 9.40234 0.559082 9.40234 0.559082V2.98926H8.14111C6.91064 2.98926 6.5415 3.72754 6.5415 4.52734V6.37305H9.2793L8.84863 9.20312Z" fill="black"/></svg>
                            </a>
                            <a href="#" class="icon-shape icon-46">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
                            </a>
                            <a href="#" class="icon-shape icon-46">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>
                            </a>
                        </div>
                        <p class="fs-8 mb-0 mt-4">© {{ date('Y') }} — {{ config('app.name') }}. All Rights Reserved.</p>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-4">
                            <div class="col-lg-4 col-md-4 col-6">
                                <h6 class="mb-3">Categories</h6>
                                <ul class="list-unstyled ps-0">
                                    @foreach(\App\Models\Category::orderBy('name')->limit(6)->get() as $footerCat)
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="#">{{ $footerCat->name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-lg-4 col-md-4 col-6">
                                <h6 class="mb-3">Quick Links</h6>
                                <ul class="list-unstyled ps-0">
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('home') }}">Home</a></li>
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="#">About</a></li>
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="#">Contact</a></li>
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('login') }}">Login</a></li>
                                </ul>
                            </div>
                            <div class="col-lg-4 col-12">
                                <h6 class="mb-3">Recent Posts</h6>
                                <ul class="list-unstyled ps-0">
                                    @foreach($recentPosts->take(4) as $rp)
                                    <li class="mb-3">
                                        <a class="text-500 hover-dark button-effect-1 text-truncate d-block" href="{{ route('post.show', $rp->slug) }}">{{ $rp->title }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

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
    <script src="{{ asset('theme/js/vendors/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/odometer.min.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>
</body>
</html>
