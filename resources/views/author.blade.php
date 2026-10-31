<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $author->name }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $author->bio ?? 'Posts by ' . $author->name }}">
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
                        @php $navCats = \App\Models\Category::orderBy('name')->limit(4)->get(); @endphp
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('home') }}"><span>Home</span></a>
                            </li>
                            @if($navCats->isNotEmpty())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle link-effect-1" href="#"><span>Categories</span></a>
                                <ul class="dropdown-menu">
                                    @foreach($navCats as $cat)
                                    <li><a class="dropdown-item" href="#">{{ $cat->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                            @auth
                            <li class="nav-item">
                                <a class="nav-link link-effect-1" href="{{ route('admin.dashboard') }}"><span>Admin</span></a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-4">
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
        @php $mobileCats = \App\Models\Category::orderBy('name')->limit(6)->get(); @endphp
        <ul class="sidebar-nav list-unstyled ps-0">
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('home') }}">Home</a>
            </li>
            @if($mobileCats->isNotEmpty())
            <li class="nav-item collapse">
                <a class="nav-link mb-2 collapse-toggle" href="#">Categories</a>
                <ul class="collapse-menu d-flex flex-column gap-1 list-unstyled">
                    @foreach($mobileCats as $cat)
                    <li><a class="collapse-item" href="#">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </li>
            @endif
            @auth
            <li class="nav-item">
                <a class="nav-link mb-2" href="{{ route('admin.dashboard') }}">Admin Panel</a>
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
        {{-- Author Profile Hero --}}
        <section class="sec-1-single-3 pb-40 overflow-hidden">
            <div class="container pt-80">
                <div class="row">
                    <div class="col-lg-6 col-md-8 mx-auto text-center">
                        <div class="mb-4">
                            @php
                                $nameParts = array_values(array_filter(explode(' ', $author->name)));
                                $initials  = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                            @endphp
                            @if($author->avatar)
                            <img class="rounded-circle" style="width:120px;height:120px;object-fit:cover;" src="{{ str_starts_with($author->avatar, 'http') ? $author->avatar : Storage::url($author->avatar) }}" alt="{{ $author->name }}">
                            @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto" style="width:120px;height:120px;font-size:2.5rem;background:#e8f0fe;color:#3b5bdb;">{{ $initials }}</div>
                            @endif
                        </div>
                        <h3 class="mb-2">{{ $author->name }}</h3>
                        <p class="fs-7 text-600 mb-3">{{ $author->bio ?? 'You should write because you love the shape of stories and sentences and the creation of different words on a page.' }}</p>
                        @if($author->website)
                        <a href="{{ $author->website }}" target="_blank" rel="noopener" class="fs-8 text-600 hover-dark d-block mb-3">{{ $author->website }}</a>
                        @endif
                        <div class="d-inline-flex group-social-icons bg-transparent">
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
                    </div>
                </div>
            </div>
        </section>

        {{-- Author Posts --}}
        <section class="sec-author-posts pb-70 overflow-hidden">
            <div class="container">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <svg class="dark-mode-invert" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M0.582044 11.7285C8.79451 13.4712 10.252 14.8614 12.125 22.7372C13.8067 14.8768 15.2308 13.4992 23.4018 11.8279C15.1894 10.0852 13.7319 8.69503 11.8589 0.81924C10.1769 8.67956 8.75306 10.0571 0.582044 11.7285Z" fill="#0E0E0F"/></svg>
                    <h5 class="mb-0">Posts by {{ $author->name }}</h5>
                </div>

                @if($posts->count())
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        <div class="article card-5">
                            <div class="post-link">
                                <div class="hover-effect-1">
                                    <div class="position-relative card-img-top thumbnail">
                                        <a href="{{ route('post.show', $post->slug) }}">
                                            @if($post->featured_image)
                                            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="cover-image">
                                            @else
                                            <div class="cover-image bg-200"></div>
                                            @endif
                                        </a>
                                        @if($post->category)
                                        <a href="#" class="badge bg-1 fs-8">{{ $post->category->name }}</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-corner white no-border">
                                    <a href="{{ route('post.show', $post->slug) }}" class="arrow-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <div class="curve-one"></div>
                                    <div class="curve-two"></div>
                                </div>
                            </div>
                            <div class="card-body mt-3">
                                <a href="{{ route('post.show', $post->slug) }}" class="hover-underline">
                                    <h6 class="card-title mb-2">{{ $post->title }}</h6>
                                </a>
                                @if($post->excerpt)
                                <p class="fs-8 text-600 text-truncate-2 mb-2">{{ $post->excerpt }}</p>
                                @endif
                                <div class="d-flex align-items-center gap-3 text-600">
                                    <span class="fs-8">{{ optional($post->published_at)->format('M d, Y') }}</span>
                                    <span class="fs-8">{{ max(1, (int) ceil(str_word_count(strip_tags($post->content)) / 200)) }} min read</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($posts->hasPages())
                <div class="row mt-5">
                    <div class="col-12 d-flex justify-content-center">
                        <nav>
                            <ul class="pagination gap-2">
                                @if($posts->onFirstPage())
                                <li class="page-item disabled"><span class="page-link rounded-8">←</span></li>
                                @else
                                <li class="page-item"><a class="page-link rounded-8" href="{{ $posts->previousPageUrl() }}">←</a></li>
                                @endif

                                @foreach($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                                <li class="page-item {{ $posts->currentPage() === $page ? 'active' : '' }}">
                                    <a class="page-link rounded-8" href="{{ $url }}">{{ $page }}</a>
                                </li>
                                @endforeach

                                @if($posts->hasMorePages())
                                <li class="page-item"><a class="page-link rounded-8" href="{{ $posts->nextPageUrl() }}">→</a></li>
                                @else
                                <li class="page-item disabled"><span class="page-link rounded-8">→</span></li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
                @endif

                @else
                <div class="row mt-5">
                    <div class="col-12 text-center py-5">
                        <p class="text-600 fs-6">No posts published yet.</p>
                        <a href="{{ route('home') }}" class="btn btn-dark rounded-8 button-effect-1">Back to Home</a>
                    </div>
                </div>
                @endif
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer>
        <div class="section-footer-4 overflow-hidden">
            <div class="container border-top-300">
                <div class="row g-5 sec-padding">
                    <div class="col-lg-4 col-md-8 pe-lg-5">
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('home') }}"><img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:36px;border-radius:8px;"></a>
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
                                <h6 class="mb-3">Tags</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(\App\Models\Tag::orderBy('name')->limit(8)->get() as $tag)
                                    <a href="#" class="tag-item"><span>{{ $tag->name }}</span></a>
                                    @endforeach
                                </div>
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
    <script src="{{ asset('theme/js/main.js') }}"></script>
</body>
</html>
