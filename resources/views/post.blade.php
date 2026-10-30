<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $post->title }} — Niralagam</title>
    <meta name="description" content="{{ $post->excerpt ?? Str::limit(strip_tags($post->content ?? ''), 155) }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('niralagam_logo.png') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/bootstrap-grid.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/carouselTicker.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/main.css') }}">
    <style>
        .post-content img { max-width: 100%; border-radius: 8px; margin: 1.5rem 0; }
        .post-content h2,.post-content h3,.post-content h4 { margin-top: 1.75rem; margin-bottom: .75rem; }
        .post-content p { margin-bottom: 1.25rem; line-height: 1.85; }
        .post-content ul,.post-content ol { padding-left: 1.5rem; margin-bottom: 1.25rem; }
        .post-content blockquote { border-left: 4px solid #6c5ce7; padding: 1rem 1.5rem; margin: 1.5rem 0; background: var(--bg-200,#f5f5f5); border-radius: 0 8px 8px 0; }
        .post-content pre { background: #1e1e2e; color: #cdd6f4; padding: 1.5rem; border-radius: 10px; overflow-x: auto; margin: 1.5rem 0; }
        .post-content code { font-family: monospace; font-size: .9em; }
        .banner.start-50 { width: 50%; height: 100%; object-fit: cover; position: absolute; top: 0; right: 0; border-radius: 0 0 0 24px; }
    </style>
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

    {{-- Navbar style-2 (left nav | center logo | right actions) --}}
    <header>
        <nav class="navbar navbar-expand-lg style-2">
            <div class="navbar-collapse d-none d-lg-block start" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link link-effect-1" href="{{ route('home') }}"><span>Home</span></a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link link-effect-1" href="{{ route('admin.dashboard') }}"><span>Admin</span></a>
                    </li>
                    @endauth
                </ul>
            </div>
            <a class="navbar-brand fw-bold fs-3" href="{{ route('home') }}">
                <img class="dark-mode-invert" src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:36px;border-radius:8px;">
            </a>
            <div class="d-flex align-items-center ms-auto gap-4">
                <div class="group-btn-right d-flex align-items-center">
                    <div class="dark-light-switcher">
                        <svg class="svg-white dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0018 0.666664C4.85543 0.666664 0.666656 4.85406 0.666656 10.0004C0.666656 15.1468 4.85543 19.3356 10.0018 19.3356C15.1482 19.3356 19.337 15.1468 19.337 10.0004C19.337 4.85406 15.1482 0.666664 10.0018 0.666664ZM10.7212 2.13629C14.7526 2.4982 17.8982 5.87269 17.8982 10.0004C17.8982 14.1282 14.7526 17.5027 10.7212 17.8646V2.13629Z" fill="#75787D"/></svg>
                        <svg class="svg-dark dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0018 0.666992C4.85543 0.666992 0.666656 4.85439 0.666656 10.0008C0.666656 15.1471 4.85543 19.3359 10.0018 19.3359C15.1482 19.3359 19.337 15.1471 19.337 10.0008C19.337 4.85439 15.1482 0.666992 10.0018 0.666992ZM10.7212 2.13662C14.7526 2.49852 17.8982 5.87302 17.8982 10.0008C17.8982 14.1285 14.7526 17.503 10.7212 17.8649V2.13662Z" fill="#0E0E0F"/></svg>
                    </div>
                    <a href="#" class="navbar-toggler">
                        <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none"><path d="M6.5 5.19999C6.5 4.48205 7.08206 3.89999 7.8 3.89999H24.7C25.4179 3.89999 26 4.48205 26 5.19999C26 5.91794 25.4179 6.49999 24.7 6.49999H7.8C7.08206 6.49999 6.5 5.91789 6.5 5.19999ZM24.7 11.7H1.3C0.582055 11.7 0 12.2821 0 13C0 13.7179 0.582055 14.3 1.3 14.3H24.7C25.4179 14.3 26 13.7179 26 13C26 12.2821 25.4179 11.7 24.7 11.7ZM24.7 19.5H13C12.2821 19.5 11.7 20.082 11.7 20.8C11.7 21.5179 12.2821 22.1 13 22.1H24.7C25.4179 22.1 26 21.5179 26 20.8C26 20.082 25.4179 19.5 24.7 19.5Z" fill="#0E0E0F"/></svg>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    {{-- Mobile sidebar --}}
    <div class="sidebar-left">
        <div class="header-sidebar d-flex align-items-center justify-content-between py-3">
            <a href="{{ route('home') }}" class="sidebar-brand">
                <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:32px;border-radius:7px;">
            </a>
            <a href="#" class="close-sidebar">
                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17.25 6.75L6.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.75 6.75L17.25 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
        <ul class="sidebar-nav list-unstyled ps-0">
            <li class="nav-item"><a class="nav-link mb-2" href="{{ route('home') }}">Home</a></li>
            @auth
            <li class="nav-item"><a class="nav-link mb-2" href="{{ route('admin.dashboard') }}">Admin Panel</a></li>
            @endauth
        </ul>
        <div class="sidebar-footer">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 6.5L12 12.25L18.5 6.5" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="fs-7 fw-semibold">Newsletter</span>
                </div>
                <h5 class="my-2">Subscribe our newsletter</h5>
                <p>Get the latest posts delivered right to your inbox.</p>
            </div>
            <form action="#">
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

        {{-- ── Single Post Hero (sec-1-single-2) ── --}}
        <section class="sec-1-single-2 pb-70 overflow-hidden">
            <div class="position-relative block-banner">

                {{-- Featured image floats right on desktop --}}
                @if($post->featured_image)
                <img class="banner start-50 position-lg-absolute d-lg-block d-none" src="{{ $post->featured_image }}" alt="{{ $post->title }}">
                @endif

                <div class="container">
                    <div class="row">
                        <div class="col-lg-6 pe-lg-5">

                            {{-- Breadcrumb --}}
                            <nav aria-label="breadcrumb">
                                <ul class="breadcrumb list-unstyled d-flex flex-row gap-2 align-items-center m-0 ps-0 py-4">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('home') }}" class="text-600 fs-7 hover-dark">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="icon-shape icon-xxs">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M6.125 4.5625L9.5625 7.84375L6.125 11.125" stroke="#626568" stroke-width="0.9375" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </li>
                                    @if($post->category)
                                    <li class="breadcrumb-item active text-dark fs-7" aria-current="page">{{ $post->category->name }}</li>
                                    @else
                                    <li class="breadcrumb-item active text-dark fs-7" aria-current="page">Article</li>
                                    @endif
                                </ul>
                            </nav>

                            {{-- Card title block --}}
                            <div class="card-title">
                                <div class="article card-info d-flex flex-wrap align-items-center gap-2 mt-4">
                                    @if($post->category)
                                    <a href="#" class="badge bg-1 fs-8">{{ $post->category->name }}</a>
                                    @endif
                                    @if($post->tags->isNotEmpty())
                                    <a href="#" class="badge bg-2 fs-8">{{ $post->tags->first()->name }}</a>
                                    @endif
                                    <ul class="d-flex align-items-center text-600 m-0 ps-3">
                                        <li>
                                            <p class="fs-8 m-0">{{ max(1, (int)ceil(str_word_count(strip_tags($post->content ?? '')) / 200)) }} mins read</p>
                                        </li>
                                    </ul>
                                    <h2>{{ $post->title }}</h2>
                                </div>

                                <div class="border-top"></div>

                                <div class="bottom mt-auto d-flex flex-wrap align-items-center gap-2 pt-4">
                                    <a href="#" class="author d-flex align-items-center gap-2">
                                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6c5ce7,#a29bfe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <span class="text-white fw-bold" style="font-size:13px;">{{ strtoupper(substr($post->user->name ?? 'N', 0, 1)) }}</span>
                                        </div>
                                        <span class="fs-7 text-dark fw-regular">{{ $post->user->name ?? 'Niralagam' }}</span>
                                    </a>
                                    <ul class="d-flex align-items-center gap-4 text-600 m-0 ps-3">
                                        <li>
                                            <p class="fs-8 m-0">{{ $post->published_at?->format('M j, Y') }}</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                        {{-- Featured image visible on mobile only --}}
                        <div class="col-lg-6 d-block d-lg-none pb-4">
                            @if($post->featured_image)
                            <img class="banner w-100 rounded-3" src="{{ $post->featured_image }}" alt="{{ $post->title }}" style="max-height:300px;object-fit:cover;">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Article body --}}
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 col-md-10 offset-lg-1 offset-md-1">
                        <div class="d-flex flex-column gap-3">

                            @if($post->excerpt)
                            <p class="text-dark fs-6 fw-regular lh-lg">{{ $post->excerpt }}</p>
                            @endif

                            <div class="post-content">
                                {!! $post->content !!}
                            </div>

                            <div class="border-top mt-5 mb-1"></div>

                            {{-- Tags + Share --}}
                            <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between mb-4">
                                @if($post->tags->isNotEmpty())
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @foreach($post->tags as $tag)
                                    <a href="#" class="tag-item">
                                        <span>{{ $tag->name }}</span>
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                <div></div>
                                @endif

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

                            {{-- Newsletter inline block --}}
                            <div class="block-subscribe h-100 d-flex flex-column flex-lg-row gap-3 justify-content-between bg-200 wow img-custom-anim-top">
                                <div>
                                    <div class="block-title d-flex align-items-center gap-1 fs-7 text-600">
                                        <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 6.5L12 12.25L18.5 6.5" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span class="fs-7 fw-regular">Newsletter</span>
                                    </div>
                                    <div class="title">
                                        <h5 class="mb-0">Stay Informed with<br class="d-none d-lg-block"> Top Headlines</h5>
                                    </div>
                                </div>
                                <form action="#" class="position-relative">
                                    <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center mb-3">
                                        <input class="form-control" type="email" placeholder="Your email address">
                                        <button class="btn btn-dark button-effect-1" type="submit">Subscribe</button>
                                    </div>
                                    <span class="text-600 fs-8">You'll only receive updates on new posts — no spam.</span>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Related / Recommended Posts ── --}}
        @if($related->isNotEmpty())
        <section class="related-post sec-padding bg-white">
            <div class="container">
                <div class="row g-4">
                    <div class="col-12">
                        <h5 class="mb-0">Recommended for You</h5>
                    </div>
                    @foreach($related as $rel)
                    <div class="col-md-6 col-lg-4">
                        <div class="article card-5">
                            <div class="post-link">
                                <div class="hover-effect-1">
                                    <div class="position-relative card-img-top thumbnail">
                                        <a href="{{ route('post.show', $rel->slug) }}">
                                            @if($rel->featured_image)
                                                <img src="{{ $rel->featured_image }}" alt="{{ $rel->title }}" class="cover-image">
                                            @else
                                                <div class="cover-image" style="background:linear-gradient(135deg,#e0e7ff,#f0f4ff);min-height:200px;display:flex;align-items:center;justify-content:center;">
                                                    <svg width="40" height="40" fill="none" stroke="#a5b4fc" stroke-width="1" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                                </div>
                                            @endif
                                        </a>
                                        @if($rel->category)
                                        <a href="#" class="badge bg-1 fs-8">{{ $rel->category->name }}</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-corner white no-border">
                                    <a href="{{ route('post.show', $rel->slug) }}" class="arrow-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                    <div class="curve-one"></div>
                                    <div class="curve-two"></div>
                                </div>
                            </div>
                            <div class="card-body mt-4">
                                <a href="{{ route('post.show', $rel->slug) }}" class="hover-underline">
                                    <h6 class="card-title mb-2">{{ $rel->title }}</h6>
                                </a>
                                <div class="d-flex align-items-center text-600 gap-3">
                                    <span class="fs-8">{{ $rel->published_at?->format('M j, Y') }}</span>
                                    <span class="fs-8">{{ max(1, (int)ceil(str_word_count(strip_tags($rel->content ?? '')) / 200)) }} mins read</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

    </main>

    {{-- Footer (section-footer-4) --}}
    <footer>
        <div class="section-footer-4 overflow-hidden">
            <div class="container border-top-300">
                <div class="row g-5 sec-padding">
                    <div class="col-lg-4 col-md-8 pe-lg-5">
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:36px;border-radius:8px;">
                            </a>
                            <p class="fs-7 m-0">Stories worth reading.</p>
                        </div>
                        <p class="fs-7 text-dark mt-4">Blending tech, life, and culture. Stay informed with fresh insights and expert takes across every topic that matters.</p>
                        <p class="fs-8 mb-0 mt-4">© {{ date('Y') }} — Niralagam. All Rights Reserved.</p>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-4">
                            <div class="col-lg-3 col-md-4 col-6">
                                <h6 class="mb-3">Quick Links</h6>
                                <ul class="list-unstyled ps-0">
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('home') }}">Home</a></li>
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('home') }}#latest">Latest Posts</a></li>
                                    @auth
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('admin.dashboard') }}">Admin Panel</a></li>
                                    @else
                                    <li class="mb-3"><a class="text-500 hover-dark button-effect-1" href="{{ route('login') }}">Login</a></li>
                                    @endauth
                                </ul>
                            </div>
                            @if($recentPosts->isNotEmpty())
                            <div class="col-lg-5 col-md-4 col-6">
                                <h6 class="mb-3">Recent Posts</h6>
                                <ul class="list-unstyled ps-0">
                                    @foreach($recentPosts->take(4) as $rp)
                                    <li class="mb-3">
                                        <a class="text-500 hover-dark button-effect-1 d-block text-truncate" href="{{ route('post.show', $rp->slug) }}" style="max-width:240px;">{{ $rp->title }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if($tags->isNotEmpty())
                            <div class="col-lg-4 col-12">
                                <h6 class="mb-3">Tags</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($tags->take(8) as $tag)
                                    <a href="#" class="badge px-3 py-2 fs-8" style="background:var(--bg-200,#f0f0f0)!important;color:var(--text-dark,#222)!important;border-radius:20px;font-weight:500;">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            @endif
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
