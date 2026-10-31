<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->meta_title ?: $post->title }} — {{ config('app.name', 'Niralagam') }}</title>
    <meta name="description" content="{{ $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 160)) }}">
    <link rel="canonical" href="{{ route('post.show', $post->slug) }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->meta_title ?: $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 160)) }}">
    <meta property="og:url" content="{{ route('post.show', $post->slug) }}">
    @php
        $ogImg = null;
        if (!empty($post->og_image)) {
            $ogImg = str_starts_with($post->og_image, 'http') ? $post->og_image : Storage::url($post->og_image);
        } elseif (!empty($post->featured_image)) {
            $ogImg = str_starts_with($post->featured_image, 'http') ? $post->featured_image : Storage::url($post->featured_image);
        }
    @endphp
    @if($ogImg)
    <meta property="og:image" content="{{ $ogImg }}">
    @endif
    @if($post->published_at)
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @endif
    @if($post->user)
    <meta property="article:author" content="{{ $post->user->name }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->meta_title ?: $post->title }}">
    <meta name="twitter:description" content="{{ $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 160)) }}">
    @if($ogImg)
    <meta name="twitter:image" content="{{ $ogImg }}">
    @endif

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('niralagam_logo.png') }}">

    {{-- Theme CSS --}}
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/bootstrap-grid.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/carouselTicker.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/vendors/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/main.css') }}">

    <style>
        /* ==========================================================================
           Reading Progress Indicator
           ========================================================================== */
        .reading-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
            z-index: 99999;
            transition: width 0.1s ease-out;
        }

        /* ==========================================================================
           Post Typography & Layout
           ========================================================================== */
        .single-post-wrapper {
            position: relative;
            background-color: var(--tc-system-white);
        }
        .post-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 9999px;
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .post-header-badge:hover {
            background-color: #2563eb;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .post-header-badge {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        [data-bs-theme="dark"] .post-header-badge:hover {
            background-color: #3b82f6;
            color: #ffffff !important;
        }

        .post-main-title {
            font-size: clamp(2rem, 3.8vw, 2.85rem);
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.02em;
            color: var(--tc-neutral-900);
            margin-top: 1rem;
            margin-bottom: 1.25rem;
            word-break: break-word;
        }

        .post-lead-excerpt {
            font-size: clamp(1.1rem, 2vw, 1.25rem);
            line-height: 1.65;
            color: var(--tc-neutral-600);
            margin-bottom: 1.75rem;
            font-weight: 400;
        }

        /* Byline bar */
        .post-byline-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
            border-top: 1px solid var(--tc-neutral-200);
            border-bottom: 1px solid var(--tc-neutral-200);
            margin-bottom: 2rem;
        }

        .author-avatar-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }
        .author-initials-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #3730a3;
            flex-shrink: 0;
        }
        [data-bs-theme="dark"] .author-initials-circle {
            background: linear-gradient(135deg, #312e81 0%, #1e1b4b 100%);
            color: #c7d2fe;
        }

        /* Share buttons */
        .share-btn-item {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--tc-neutral-200);
            background: var(--tc-system-white);
            color: var(--tc-neutral-700);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 0;
        }
        .share-btn-item:hover {
            border-color: var(--tc-neutral-400);
            color: var(--tc-neutral-900);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .share-btn-item svg {
            width: 17px;
            height: 17px;
            fill: currentColor;
        }

        /* Featured Image Container */
        .post-featured-wrapper {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2.5rem;
            background-color: var(--tc-neutral-100);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
        }
        .post-featured-wrapper img {
            width: 100%;
            height: auto;
            max-height: 540px;
            object-fit: cover;
            display: block;
        }

        /* ==========================================================================
           Article Body Content Styling (Clean & Legible)
           ========================================================================== */
        .post-content {
            font-size: 1.125rem;
            line-height: 1.85;
            color: var(--tc-neutral-800);
            overflow-x: hidden;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .post-content p {
            margin-bottom: 1.6rem;
        }
        .post-content h2 {
            font-size: clamp(1.5rem, 2.6vw, 1.95rem);
            font-weight: 700;
            color: var(--tc-neutral-900);
            margin-top: 2.75rem;
            margin-bottom: 1.15rem;
            line-height: 1.35;
        }
        .post-content h3 {
            font-size: clamp(1.25rem, 2.2vw, 1.55rem);
            font-weight: 700;
            color: var(--tc-neutral-900);
            margin-top: 2.25rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .post-content h4, .post-content h5, .post-content h6 {
            font-weight: 600;
            color: var(--tc-neutral-900);
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
        }
        .post-content ul, .post-content ol {
            padding-left: 1.5rem;
            margin-bottom: 1.75rem;
        }
        .post-content li {
            margin-bottom: 0.5rem;
            line-height: 1.75;
        }
        .post-content img {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 12px;
            margin: 2rem auto;
            display: block;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }
        .post-content blockquote {
            position: relative;
            margin: 2.25rem 0;
            padding: 1.25rem 1.75rem;
            border-left: 4px solid #3b82f6;
            background: rgba(59, 130, 246, 0.04);
            border-radius: 0 12px 12px 0;
            font-style: italic;
            font-size: 1.15rem;
            color: var(--tc-neutral-800);
        }
        .post-content blockquote p:last-child {
            margin-bottom: 0;
        }
        .post-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            font-size: 0.95rem;
        }
        .post-content table th,
        .post-content table td {
            padding: 10px 14px;
            border: 1px solid var(--tc-neutral-200);
            text-align: left;
        }
        .post-content table th {
            background-color: var(--tc-neutral-100);
            font-weight: 600;
            color: var(--tc-neutral-900);
        }
        .post-content .table-responsive-wrapper {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 2rem 0;
        }
        .post-content pre {
            background: #1e293b;
            color: #f8fafc;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            overflow-x: auto;
            white-space: pre;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 0.92rem;
            line-height: 1.65;
            margin: 2rem 0;
            position: relative;
        }
        .post-content :not(pre) > code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: rgba(148, 163, 184, 0.15);
            color: #2563eb;
            padding: 0.2em 0.45em;
            border-radius: 6px;
            font-size: 0.88em;
        }
        [data-bs-theme="dark"] .post-content :not(pre) > code {
            background: rgba(148, 163, 184, 0.25);
            color: #60a5fa;
        }
        .post-content iframe,
        .post-content video,
        .post-content embed {
            max-width: 100%;
            border-radius: 12px;
            display: block;
            margin: 2rem auto;
        }

        /* Post tags */
        .post-tag-item {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 9999px;
            background: var(--tc-neutral-100);
            color: var(--tc-neutral-700);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .post-tag-item:hover {
            background: var(--tc-neutral-900);
            color: var(--tc-system-white) !important;
            transform: translateY(-1px);
        }

        /* Post Bottom Share Card */
        .post-share-card {
            background: var(--tc-neutral-50);
            border: 1px solid var(--tc-neutral-200);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
        }

        /* Author bio card beneath post */
        .author-box-bottom {
            background: var(--tc-system-white);
            border: 1px solid var(--tc-neutral-200);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        /* ==========================================================================
           Sidebar Components
           ========================================================================== */
        @media (min-width: 992px) {
            .sticky-sidebar-wrapper {
                position: -webkit-sticky;
                position: sticky;
                top: 85px;
            }
        }
        .sidebar-card-box {
            background: var(--tc-system-white);
            border: 1px solid var(--tc-neutral-200);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }
        .sidebar-widget-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--tc-neutral-900);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--tc-neutral-200);
        }

        .recent-post-row {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            transition: transform 0.2s ease;
        }
        .recent-post-row:hover {
            transform: translateX(3px);
        }
        .recent-thumb-img {
            width: 72px;
            height: 72px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--tc-neutral-100);
        }
        .recent-thumb-placeholder {
            width: 72px;
            height: 72px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--tc-neutral-100) 0%, var(--tc-neutral-200) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tc-neutral-500);
        }
        .recent-post-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--tc-neutral-900);
            margin-bottom: 4px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.2s;
        }
        .recent-post-row:hover .recent-post-title {
            color: #2563eb;
        }

        .sidebar-tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 8px;
            background: var(--tc-neutral-50);
            border: 1px solid var(--tc-neutral-200);
            color: var(--tc-neutral-700);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar-tag-pill:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff !important;
            transform: translateY(-2px);
        }
        .sidebar-tag-pill .badge-count {
            font-size: 11px;
            opacity: 0.75;
        }

        /* Sidebar Newsletter Box */
        .sidebar-newsletter-box {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.75rem;
        }

        /* Copy link tooltip feedback */
        .copy-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 99999;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.25s ease;
            pointer-events: none;
        }
        .copy-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* ==========================================================================
           Recommended Cards
           ========================================================================== */
        .recommended-card {
            background: var(--tc-system-white);
            border: 1px solid var(--tc-neutral-200);
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }
        .recommended-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            border-color: var(--tc-neutral-300);
        }
        .recommended-img-link {
            display: block;
            position: relative;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background: var(--tc-neutral-100);
        }
        .recommended-img-link img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .recommended-card:hover .recommended-img-link img {
            transform: scale(1.05);
        }
        .recommended-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 2;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 6px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            color: #ffffff;
            text-decoration: none;
        }

        /* Mobile tweaks */
        @media (max-width: 575px) {
            .post-byline-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            .post-share-card {
                flex-direction: column;
            }
            .post-content {
                font-size: 1.05rem;
            }
        }
    </style>
</head>
<body>

    {{-- Reading progress bar at top of window --}}
    <div id="readingProgress" class="reading-progress-bar"></div>

    {{-- Preloader --}}
    <div id="preloader">
        <div id="loader" class="loader">
            <div class="loader-container">
                <div class="loader-icon">
                    <img src="{{ asset('niralagam_logo.png') }}" alt="{{ config('app.name', 'Niralagam') }}" style="height:60px;border-radius:12px;" onerror="this.style.display='none';">
                </div>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <header>
        <nav class="navbar style-3">
            <div class="container">
                <div class="header d-flex align-items-center justify-content-between">
                    {{-- Brand --}}
                    <div class="d-flex align-items-center">
                        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-3" href="{{ route('home') }}">
                            <img src="{{ asset('niralagam_logo.png') }}" alt="{{ config('app.name', 'Niralagam') }}" style="height:36px;border-radius:8px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <span style="display:none;" class="site-title-fallback fw-bold fs-4 text-dark">{{ config('app.name', 'Niralagam') }}</span>
                        </a>
                    </div>

                    {{-- Navigation Links (Desktop) --}}
                    <div class="navbar-collapse d-none d-lg-block" id="navbarNav">
                        @php $navCats = \App\Models\Category::orderBy('name')->limit(5)->get(); @endphp
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('home') }}"><span>Home</span></a>
                            </li>
                            @if($navCats->isNotEmpty())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle link-effect-1" href="javascript:void(0);"><span>Categories</span></a>
                                <ul class="dropdown-menu">
                                    @foreach($navCats as $cat)
                                    <li><a class="dropdown-item" href="{{ route('home', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('home') }}#latest"><span>Latest</span></a>
                            </li>
                            @auth
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('admin.dashboard') }}"><span>Admin</span></a>
                            </li>
                            @endauth
                        </ul>
                    </div>

                    {{-- Right Controls (Theme switcher + Mobile toggle) --}}
                    <div class="d-flex align-items-center gap-3">
                        <div class="group-btn-right d-flex align-items-center">
                            {{-- Dark / Light theme toggle --}}
                            <div class="dark-light-switcher" role="button" aria-label="Toggle Dark Mode" title="Toggle theme">
                                <svg class="svg-white dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0018 0.666664C4.85543 0.666664 0.666656 4.85406 0.666656 10.0004C0.666656 15.1468 4.85543 19.3356 10.0018 19.3356C15.1482 19.3356 19.337 15.1468 19.337 10.0004C19.337 4.85406 15.1482 0.666664 10.0018 0.666664ZM10.7212 2.13629C14.7526 2.4982 17.8982 5.87269 17.8982 10.0004C17.8982 14.1282 14.7526 17.5027 10.7212 17.8646V2.13629Z" fill="#75787D"/></svg>
                                <svg class="svg-dark dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10.0018 0.666992C4.85543 0.666992 0.666656 4.85439 0.666656 10.0008C0.666656 15.1471 4.85543 19.3359 10.0018 19.3359C15.1482 19.3359 19.337 15.1471 19.337 10.0008C19.337 4.85439 15.1482 0.666992 10.0018 0.666992ZM10.7212 2.13662C14.7526 2.49852 17.8982 5.87302 17.8982 10.0008C17.8982 14.1285 14.7526 17.503 10.7212 17.8649V2.13662Z" fill="#0E0E0F"/></svg>
                            </div>
                            {{-- Mobile Hamburger --}}
                            <a href="javascript:void(0);" class="navbar-toggler icon-shape" role="button" aria-label="Open mobile menu">
                                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none"><path d="M6.5 5.19999C6.5 4.48205 7.08206 3.89999 7.8 3.89999H24.7C25.4179 3.89999 26 4.48205 26 5.19999C26 5.91794 25.4179 6.49999 24.7 6.49999H7.8C7.08206 6.49999 6.5 5.91789 6.5 5.19999ZM24.7 11.7H1.3C0.582055 11.7 0 12.2821 0 13C0 13.7179 0.582055 14.3 1.3 14.3H24.7C25.4179 14.3 26 13.7179 26 13C26 12.2821 25.4179 11.7 24.7 11.7ZM24.7 19.5H13C12.2821 19.5 11.7 20.082 11.7 20.8C11.7 21.5179 12.2821 22.1 13 22.1H24.7C25.4179 22.1 26 21.5179 26 20.8C26 20.082 25.4179 19.5 24.7 19.5Z" fill="#0E0E0F"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Mobile Offcanvas Drawer --}}
    <div class="sidebar-left">
        <div class="header-sidebar d-flex align-items-center justify-content-between py-3">
            <a href="{{ route('home') }}" class="sidebar-brand fw-bold fs-4">
                <img src="{{ asset('niralagam_logo.png') }}" alt="{{ config('app.name', 'Niralagam') }}" style="height:32px;border-radius:7px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display:none;" class="site-title-fallback fw-bold fs-4 text-dark">{{ config('app.name', 'Niralagam') }}</span>
            </a>
            <a href="javascript:void(0);" class="close-sidebar" role="button" aria-label="Close sidebar">
                <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17.25 6.75L6.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.75 6.75L17.25 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        @php $mobileCats = \App\Models\Category::orderBy('name')->limit(8)->get(); @endphp
        <ul class="sidebar-nav list-unstyled ps-0">
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('home') }}">Home</a>
            </li>
            @if($mobileCats->isNotEmpty())
            <li class="nav-item collapse">
                <a class="nav-link mb-2 collapse-toggle" href="javascript:void(0);">Categories</a>
                <ul class="collapse-menu d-flex flex-column gap-1 list-unstyled">
                    @foreach($mobileCats as $cat)
                    <li><a class="collapse-item" href="{{ route('home', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('home') }}#latest">Latest Articles</a>
            </li>
            @auth
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('login') }}">Sign In</a>
            </li>
            @endauth
        </ul>

        <div class="sidebar-footer">
            <div>
                <h6 class="my-2 fw-bold">Stay Updated</h6>
                <p class="fs-8 text-muted mb-3">Get the latest stories delivered straight to your inbox.</p>
            </div>
            <form onsubmit="handleNewsletterSubmit(event, this)">
                <input type="email" class="form-control fs-7 mb-2 w-100" placeholder="Your email address" required>
                <button type="submit" class="btn btn-dark w-100 button-effect-1 fs-7">Subscribe</button>
                <div class="subscribe-feedback fs-8 text-success mt-2" style="display:none;">✓ Thank you for subscribing!</div>
            </form>
        </div>
        <div class="text-center mt-3">
            <p class="fs-8 text-muted">{{ date('Y') }} &copy; <span class="text-dark fw-semibold">{{ config('app.name', 'Niralagam') }}</span>. All Rights Reserved</p>
        </div>
    </div>
    <div class="sidebar-overlay"></div>

    {{-- Main Article Container --}}
    <main class="single-post-wrapper py-4 py-md-5">
        <div class="container">
            {{-- Breadcrumb Navigation --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb list-unstyled d-flex flex-wrap align-items-center gap-2 m-0 ps-0 fs-8">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}" class="text-muted text-decoration-none hover-dark">Home</a>
                    </li>
                    <li class="breadcrumb-item text-muted">/</li>
                    @if($post->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['category' => $post->category->slug]) }}" class="text-muted text-decoration-none hover-dark">{{ $post->category->name }}</a>
                    </li>
                    <li class="breadcrumb-item text-muted">/</li>
                    @endif
                    <li class="breadcrumb-item active text-dark fw-medium text-truncate" style="max-width: 260px;" aria-current="page">
                        {{ $post->title }}
                    </li>
                </ol>
            </nav>

            <div class="row g-4 g-lg-5">
                {{-- Left: Article Body (col-lg-8) --}}
                <div class="col-lg-8" style="min-width: 0;">
                    <article class="post-article">
                        {{-- Post Header --}}
                        <header class="post-header">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                @if($post->category)
                                <a href="{{ route('home', ['category' => $post->category->slug]) }}" class="post-header-badge">
                                    {{ $post->category->name }}
                                </a>
                                @endif
                                <span class="d-inline-flex align-items-center gap-1 text-muted fs-8">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ max(1, (int) ceil(str_word_count(strip_tags($post->content)) / 200)) }} mins read
                                </span>
                                @if($post->published_at)
                                <span class="text-muted fs-8">•</span>
                                <span class="text-muted fs-8">{{ $post->published_at->format('M d, Y') }}</span>
                                @endif
                            </div>

                            <h1 class="post-main-title">{{ $post->title }}</h1>

                            @if(!empty($post->excerpt))
                            <p class="post-lead-excerpt">{{ $post->excerpt }}</p>
                            @endif

                            {{-- Post Byline & Quick Sharing Strip --}}
                            @php
                                $authorAvatar = null;
                                if (!empty($post->user?->avatar)) {
                                    $authorAvatar = str_starts_with($post->user->avatar, 'http')
                                        ? $post->user->avatar
                                        : Storage::url($post->user->avatar);
                                }
                                $authorName = $post->user?->name ?? 'Editorial Team';
                                $nameParts = array_values(array_filter(explode(' ', $authorName)));
                                $authorInitials = strtoupper(substr($nameParts[0] ?? 'N', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                            @endphp
                            <div class="post-byline-bar">
                                <div class="d-flex align-items-center gap-3">
                                    @if($post->user)
                                    <a href="{{ route('author.show', $post->user->handle()) }}" class="d-flex align-items-center gap-3 text-decoration-none">
                                        @if($authorAvatar)
                                        <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="author-avatar-circle" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="author-initials-circle" style="display:none;">{{ $authorInitials }}</div>
                                        @else
                                        <div class="author-initials-circle">{{ $authorInitials }}</div>
                                        @endif
                                        <div>
                                            <span class="d-block fw-bold text-dark fs-7 hover-primary">{{ $authorName }}</span>
                                            <span class="d-block text-muted fs-8">Author</span>
                                        </div>
                                    </a>
                                    @else
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="author-initials-circle">{{ $authorInitials }}</div>
                                        <div>
                                            <span class="d-block fw-bold text-dark fs-7">{{ $authorName }}</span>
                                            <span class="d-block text-muted fs-8">Author</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Quick Share Group --}}
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-8 fw-semibold text-muted d-none d-sm-inline">Share:</span>
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Twitter / X --}}
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" class="share-btn-item" title="Share on X">
                                            <svg viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
                                        </a>
                                        {{-- LinkedIn --}}
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="share-btn-item" title="Share on LinkedIn">
                                            <svg viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>
                                        </a>
                                        {{-- Facebook --}}
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="share-btn-item" title="Share on Facebook">
                                            <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        </a>
                                        {{-- WhatsApp --}}
                                        <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" class="share-btn-item" title="Share on WhatsApp">
                                            <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.004c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        </a>
                                        {{-- Copy Link --}}
                                        <button type="button" class="share-btn-item" onclick="copyCurrentUrl(this)" title="Copy post link" aria-label="Copy link">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </header>

                        {{-- Featured Image (Gracefully hidden if empty or if image fails to load) --}}
                        @php
                            $featuredUrl = null;
                            if (!empty($post->featured_image)) {
                                $featuredUrl = str_starts_with($post->featured_image, 'http')
                                    ? $post->featured_image
                                    : Storage::url($post->featured_image);
                            }
                        @endphp
                        @if($featuredUrl)
                        <div class="post-featured-wrapper">
                            <img src="{{ $featuredUrl }}" alt="{{ $post->title }}" class="img-fluid" loading="eager" onerror="this.closest('.post-featured-wrapper').style.display='none';">
                        </div>
                        @endif

                        {{-- Post HTML Body --}}
                        <div class="post-content">
                            {!! $post->content !!}
                        </div>

                        {{-- Tags List --}}
                        @if($post->tags->isNotEmpty())
                        <div class="d-flex flex-wrap align-items-center gap-2 pt-4 mt-4 border-top">
                            <span class="fs-7 fw-semibold text-muted me-1">Tags:</span>
                            @foreach($post->tags as $tag)
                            <a href="{{ route('home', ['tag' => $tag->slug]) }}" class="post-tag-item">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                        @endif

                        {{-- Bottom Share Callout --}}
                        <div class="post-share-card mt-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Share this article</h6>
                                <p class="mb-0 fs-8 text-muted">Enjoyed this story? Share it with colleagues and friends.</p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fs-8 d-inline-flex align-items-center gap-1">
                                    Tweet
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fs-8 d-inline-flex align-items-center gap-1">
                                    LinkedIn
                                </a>
                                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fs-8 d-inline-flex align-items-center gap-1">
                                    WhatsApp
                                </a>
                                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 fs-8" onclick="copyCurrentUrl(this)">
                                    Copy Link
                                </button>
                            </div>
                        </div>

                        {{-- Author Bio Box (Bottom) --}}
                        @if($post->user)
                        <div class="author-box-bottom mt-4">
                            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                                <a href="{{ route('author.show', $post->user->handle()) }}" class="flex-shrink-0">
                                    @if($authorAvatar)
                                    <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="rounded-circle" style="width:70px;height:70px;object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="rounded-circle fw-bold" style="display:none;align-items:center;justify-content:center;width:70px;height:70px;font-size:1.6rem;background:#e0e7ff;color:#3730a3;">{{ $authorInitials }}</div>
                                    @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:70px;height:70px;font-size:1.6rem;background:#e0e7ff;color:#3730a3;">{{ $authorInitials }}</div>
                                    @endif
                                </a>
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                        <h5 class="mb-0 fw-bold">
                                            <a href="{{ route('author.show', $post->user->handle()) }}" class="text-decoration-none text-dark hover-primary">{{ $post->user->name }}</a>
                                        </h5>
                                        <a href="{{ route('author.show', $post->user->handle()) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fs-8">View all posts</a>
                                    </div>
                                    <p class="text-muted fs-7 mb-2">{{ $post->user->bio ?? 'Writing and sharing stories on technology, growth, and ideas.' }}</p>
                                    @if(!empty($post->user->website))
                                    <a href="{{ $post->user->website }}" target="_blank" rel="noopener noreferrer" class="fs-8 text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        {{ parse_url($post->user->website, PHP_URL_HOST) ?? $post->user->website }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </article>
                </div>

                {{-- Right: Sidebar (col-lg-4) --}}
                <div class="col-lg-4">
                    <aside class="sticky-sidebar-wrapper">
                        {{-- Author Sidebar Widget --}}
                        @if($post->user)
                        <div class="sidebar-card-box text-center">
                            <div class="mb-3">
                                <a href="{{ route('author.show', $post->user->handle()) }}" class="d-inline-block">
                                    @if($authorAvatar)
                                    <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="rounded-circle shadow-sm" style="width:96px;height:96px;object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="rounded-circle fw-bold mx-auto shadow-sm" style="display:none;align-items:center;justify-content:center;width:96px;height:96px;font-size:2rem;background:#e0e7ff;color:#3730a3;">{{ $authorInitials }}</div>
                                    @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto shadow-sm" style="width:96px;height:96px;font-size:2rem;background:#e0e7ff;color:#3730a3;">{{ $authorInitials }}</div>
                                    @endif
                                </a>
                            </div>
                            <h5 class="fw-bold mb-1">
                                <a href="{{ route('author.show', $post->user->handle()) }}" class="text-decoration-none text-dark hover-primary">{{ $post->user->name }}</a>
                            </h5>
                            <p class="fs-8 text-muted mb-3">{{ $post->user->bio ?? 'Step into a space where thoughts bloom and stories breathe.' }}</p>
                            @if(!empty($post->user->website))
                            <div class="mb-3">
                                <a href="{{ $post->user->website }}" target="_blank" rel="noopener noreferrer" class="fs-8 text-muted hover-dark text-decoration-none d-inline-flex align-items-center gap-1">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    {{ parse_url($post->user->website, PHP_URL_HOST) ?? 'Website' }}
                                </a>
                            </div>
                            @endif
                            <a href="{{ route('author.show', $post->user->handle()) }}" class="btn btn-outline-dark btn-sm rounded-pill w-100 button-effect-1">
                                View Author Profile
                            </a>
                        </div>
                        @endif

                        {{-- Recent Articles Widget --}}
                        @if($recentPosts->isNotEmpty())
                        <div class="sidebar-card-box">
                            <div class="sidebar-widget-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Recent Articles</span>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                @foreach($recentPosts as $recent)
                                @php
                                    $recentImg = null;
                                    if (!empty($recent->featured_image)) {
                                        $recentImg = str_starts_with($recent->featured_image, 'http')
                                            ? $recent->featured_image
                                            : Storage::url($recent->featured_image);
                                    }
                                @endphp
                                <a href="{{ route('post.show', $recent->slug) }}" class="recent-post-row">
                                    @if($recentImg)
                                    <img src="{{ $recentImg }}" alt="{{ $recent->title }}" class="recent-thumb-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="recent-thumb-placeholder" style="display:none;">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    @else
                                    <div class="recent-thumb-placeholder">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    @endif
                                    <div style="min-width: 0; flex-grow: 1;">
                                        @if($recent->category)
                                        <span class="fs-9 text-uppercase fw-bold text-primary d-block mb-1">{{ $recent->category->name }}</span>
                                        @endif
                                        <h6 class="recent-post-title">{{ $recent->title }}</h6>
                                        <span class="fs-9 text-muted">{{ optional($recent->published_at)->format('M d, Y') }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Popular Tags Widget --}}
                        @if($tags->isNotEmpty())
                        <div class="sidebar-card-box">
                            <div class="sidebar-widget-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                <span>Popular Topics</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($tags as $t)
                                <a href="{{ route('home', ['tag' => $t->slug]) }}" class="sidebar-tag-pill">
                                    <span>#{{ $t->name }}</span>
                                    <span class="badge-count text-muted">({{ $t->posts_count }})</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Newsletter Widget --}}
                        <div class="sidebar-newsletter-box">
                            <div class="d-inline-flex p-2 rounded-3 bg-white bg-opacity-10 mb-3">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <h5 class="fw-bold mb-2 text-white">Subscribe to Newsletter</h5>
                            <p class="fs-8 text-white-50 mb-3">Never miss high-quality stories, insights, and news delivered directly to you.</p>
                            <form onsubmit="handleNewsletterSubmit(event, this)">
                                <div class="mb-2">
                                    <input type="email" class="form-control form-control-sm border-0 fs-8" placeholder="Your email address" required style="padding: 10px 14px; border-radius: 8px;">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fs-8 fw-semibold py-2">Get Updates</button>
                                <div class="subscribe-feedback fs-8 text-success mt-2" style="display:none;">✓ Thank you for subscribing!</div>
                            </form>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </main>

    {{-- Recommended / Related Posts Section --}}
    @if($related->isNotEmpty())
    <section class="py-5 bg-light-subtle border-top">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    <h4 class="mb-0 fw-bold">Recommended for You</h4>
                </div>
                <a href="{{ route('home') }}" class="text-decoration-none fs-8 fw-semibold text-muted hover-dark d-none d-sm-inline">Explore all →</a>
            </div>

            <div class="row g-4">
                @foreach($related as $rel)
                @php
                    $relImg = null;
                    if (!empty($rel->featured_image)) {
                        $relImg = str_starts_with($rel->featured_image, 'http')
                            ? $rel->featured_image
                            : Storage::url($rel->featured_image);
                    }
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="recommended-card">
                        <a href="{{ route('post.show', $rel->slug) }}" class="recommended-img-link">
                            @if($relImg)
                            <img src="{{ $relImg }}" alt="{{ $rel->title }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-100 h-100 text-muted" style="display:none;align-items:center;justify-content:center;background:#e2e8f0;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            </div>
                            @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted" style="background:#e2e8f0;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            </div>
                            @endif
                            @if($rel->category)
                            <span class="recommended-badge">{{ $rel->category->name }}</span>
                            @endif
                        </a>
                        <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                            <h5 class="fs-6 fw-bold mb-2" style="line-height: 1.4;">
                                <a href="{{ route('post.show', $rel->slug) }}" class="text-decoration-none text-dark hover-primary">{{ $rel->title }}</a>
                            </h5>
                            <div class="mt-auto pt-2 d-flex align-items-center justify-content-between fs-8 text-muted border-top">
                                <span>{{ optional($rel->published_at)->format('M d, Y') }}</span>
                                <span>{{ max(1, (int) ceil(str_word_count(strip_tags($rel->content)) / 200)) }} min read</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Footer --}}
    <footer>
        <div class="section-footer-4 overflow-hidden">
            <div class="container border-top-300">
                <div class="row g-4 g-lg-5 sec-padding">
                    <div class="col-lg-4 col-md-6 pe-lg-4">
                        <div class="d-flex gap-2 align-items-center mb-3">
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('niralagam_logo.png') }}" alt="{{ config('app.name', 'Niralagam') }}" style="height:36px;border-radius:8px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                <span style="display:none;" class="site-title-fallback fw-bold fs-4 text-dark">{{ config('app.name', 'Niralagam') }}</span>
                            </a>
                        </div>
                        <p class="fs-7 text-muted">Blending tech, life, and culture. Stay informed with fresh trends, smart insights, and expert takes across every topic that matters.</p>
                        <p class="fs-8 text-muted mb-0 mt-3">&copy; {{ date('Y') }} — {{ config('app.name', 'Niralagam') }}. All Rights Reserved.</p>
                    </div>

                    <div class="col-lg-8 col-md-6">
                        <div class="row g-4">
                            <div class="col-6 col-sm-4">
                                <h6 class="mb-3 fw-bold">Categories</h6>
                                <ul class="list-unstyled ps-0 fs-8">
                                    @foreach(\App\Models\Category::orderBy('name')->limit(6)->get() as $footerCat)
                                    <li class="mb-2">
                                        <a class="text-muted hover-dark text-decoration-none" href="{{ route('home', ['category' => $footerCat->slug]) }}">{{ $footerCat->name }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-6 col-sm-4">
                                <h6 class="mb-3 fw-bold">Quick Links</h6>
                                <ul class="list-unstyled ps-0 fs-8">
                                    <li class="mb-2"><a class="text-muted hover-dark text-decoration-none" href="{{ route('home') }}">Home</a></li>
                                    <li class="mb-2"><a class="text-muted hover-dark text-decoration-none" href="{{ route('home') }}#latest">Latest Posts</a></li>
                                    @auth
                                    <li class="mb-2"><a class="text-muted hover-dark text-decoration-none" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                                    @else
                                    <li class="mb-2"><a class="text-muted hover-dark text-decoration-none" href="{{ route('login') }}">Author Login</a></li>
                                    @endauth
                                    @if($post->user)
                                    <li class="mb-2"><a class="text-muted hover-dark text-decoration-none" href="{{ route('author.show', $post->user->handle()) }}">About the Author</a></li>
                                    @endif
                                </ul>
                            </div>
                            <div class="col-12 col-sm-4">
                                <h6 class="mb-3 fw-bold">Recent Posts</h6>
                                <ul class="list-unstyled ps-0 fs-8">
                                    @foreach($recentPosts->take(4) as $rp)
                                    <li class="mb-2">
                                        <a class="text-muted hover-dark text-decoration-none text-truncate d-block" href="{{ route('post.show', $rp->slug) }}">{{ $rp->title }}</a>
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

    {{-- Back to top button --}}
    <div class="btn-scroll-top" role="button" aria-label="Scroll to top" title="Scroll to top">
        <svg class="progress-square svg-content" width="100%" height="100%" viewBox="0 0 40 40">
            <path d="M20 1a19 19 0 1 1 0 38 19 19 0 0 1 0-38"/>
        </svg>
    </div>

    {{-- Toast feedback notification for copy action --}}
    <div id="copyToast" class="copy-toast">Link copied to clipboard!</div>

    {{-- Scripts --}}
    <script src="{{ asset('theme/js/vendors/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/aos.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/wow.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/headhesive.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/smart-stick-nav.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/jquery.appear.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/jquery.odometer.min.js') }}"></script>
    <script src="{{ asset('theme/js/vendors/color-modes.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>

    <script>
        // Reading Progress Indicator
        window.addEventListener('scroll', function () {
            const article = document.querySelector('.post-article');
            if (!article) return;
            const articleTop = article.offsetTop;
            const articleHeight = article.offsetHeight;
            const scrollY = window.scrollY;
            const windowHeight = window.innerHeight;

            let progress = 0;
            if (scrollY > articleTop) {
                const totalScrollable = articleHeight - windowHeight;
                if (totalScrollable > 0) {
                    progress = Math.min(100, Math.max(0, ((scrollY - articleTop) / totalScrollable) * 100));
                } else {
                    progress = 100;
                }
            }
            const bar = document.getElementById('readingProgress');
            if (bar) {
                bar.style.width = progress + '%';
            }
        });

        // Copy Current URL with visual feedback
        function copyCurrentUrl(button) {
            const url = window.location.href;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(showToast).catch(() => fallbackCopy(url));
            } else {
                fallbackCopy(url);
            }
        }

        function fallbackCopy(text) {
            const temp = document.createElement('textarea');
            temp.value = text;
            temp.style.position = 'fixed';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.select();
            try {
                document.execCommand('copy');
                showToast();
            } catch (err) {
                alert('Could not copy link: ' + text);
            }
            document.body.removeChild(temp);
        }

        function showToast() {
            const toast = document.getElementById('copyToast');
            if (!toast) return;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2200);
        }

        // Newsletter submission handler
        function handleNewsletterSubmit(e, form) {
            e.preventDefault();
            const input = form.querySelector('input[type="email"]');
            const feedback = form.querySelector('.subscribe-feedback');
            if (input && feedback) {
                input.value = '';
                feedback.style.display = 'block';
                setTimeout(() => {
                    feedback.style.display = 'none';
                }, 4000);
            }
        }

        // Auto-wrap post tables for horizontal responsiveness
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.post-content table').forEach(function (table) {
                if (!table.parentElement.classList.contains('table-responsive-wrapper')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive-wrapper';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });

            // Prevent broken images in post content
            document.querySelectorAll('.post-content img').forEach(function (img) {
                img.addEventListener('error', function () {
                    this.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
