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
                <div class="loader-icon"><img src="{{ asset('theme/imgs/template/logo/logo-gradient.svg') }}" alt="Preloader"></div>
            </div>
        </div>
    </div>

    {{-- Navbar style-2 --}}
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
        {{-- Author Profile Card --}}
        <div class="container pt-100">
            <div class="row">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <div class="card text-center border-0">
                        <div class="card-img mb-4 text-center">
                            @if($author->avatar)
                            <img class="rounded-circle avatar-154" src="{{ Storage::url($author->avatar) }}" alt="{{ $author->name }}">
                            @else
                            <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center text-white fw-bold mx-auto" style="width:154px;height:154px;font-size:3.5rem;">{{ strtoupper(substr($author->name, 0, 1)) }}</div>
                            @endif
                        </div>
                        <div class="card-body text-center">
                            <h3 class="mb-3">{{ $author->name }}</h3>
                            <p class="mb-0 fs-7">{{ $author->bio ?? 'You should write because you love the shape of stories and sentences and the creation of different words on a page.' }}</p>
                            @if($author->website)
                            <a href="{{ $author->website }}" target="_blank" rel="noopener" class="d-block mt-2 fs-8 text-600 hover-dark">{{ $author->website }}</a>
                            @endif
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
                                <a href="#" class="icon-shape icon-46">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-behance" viewBox="0 0 16 16"><path d="M4.654 3c.461 0 .887.035 1.278.14.39.07.711.216.996.391s.497.426.641.747c.14.32.216.711.216 1.137 0 .496-.106.922-.356 1.242-.215.32-.566.606-.997.817.606.176 1.067.496 1.348.922s.461.957.461 1.563c0 .496-.105.922-.285 1.278a2.3 2.3 0 0 1-.782.887c-.32.215-.711.39-1.137.496a5.3 5.3 0 0 1-1.278.176L0 12.803V3zm-.285 3.978c.39 0 .71-.105.957-.285.246-.18.355-.497.355-.887 0-.216-.035-.426-.105-.567a1 1 0 0 0-.32-.355 1.8 1.8 0 0 0-.461-.176c-.176-.035-.356-.035-.567-.035H2.17v2.31c0-.005 2.2-.005 2.2-.005zm.105 4.193c.215 0 .426-.035.606-.07.176-.035.356-.106.496-.216s.25-.215.356-.39c.07-.176.14-.391.14-.641 0-.496-.14-.852-.426-1.102-.285-.215-.676-.32-1.137-.32H2.17v2.734h2.305zm6.858-.035q.428.427 1.278.426c.39 0 .746-.106 1.032-.286q.426-.32.53-.64h1.74c-.286.851-.712 1.457-1.278 1.848-.566.355-1.243.566-2.06.566a4.1 4.1 0 0 1-1.527-.285 2.8 2.8 0 0 1-1.137-.782 2.85 2.85 0 0 1-.712-1.172c-.175-.461-.25-.957-.25-1.528 0-.531.07-1.032.25-1.493.18-.46.426-.852.747-1.207.32-.32.711-.606 1.137-.782a4 4 0 0 1 1.493-.285c.606 0 1.137.105 1.598.355.46.25.817.532 1.102.958.285.39.496.851.641 1.348.07.496.105.996.07 1.563h-5.15c0 .58.21 1.11.496 1.396m2.24-3.732c-.25-.25-.642-.391-1.103-.391-.32 0-.566.07-.781.176s-.356.25-.496.39a.96.96 0 0 0-.25.497c-.036.175-.07.32-.07.46h3.196c-.07-.526-.25-.882-.497-1.132zm-3.127-3.728h3.978v.957h-3.978z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Author Posts --}}
        <section class="sec-1-author overflow-hidden">
            <div class="container">
                @if($posts->count())
                <div class="row mt-4 g-4">
                    @foreach($posts as $post)
                    <div class="col-12">
                        <div class="article card-12 d-flex flex-md-row align-items-stretch flex-column">
                            <a href="{{ route('post.show', $post->slug) }}" class="card-img-top hover-effect-30 rounded-16 overflow-hidden">
                                @if($post->featured_image)
                                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="cover-image">
                                @else
                                <div class="cover-image bg-200"></div>
                                @endif
                            </a>
                            <div class="card-body">
                                <div class="card-corner">
                                    <a href="{{ route('post.show', $post->slug) }}" class="arrow-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M13.75 6.75L19.25 12L13.75 17.25" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M19 12H4.75" stroke="#0E0E0F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <div class="curve-one"></div>
                                    <div class="curve-two"></div>
                                </div>
                                <div class="left">
                                    @if($post->category)
                                    <div class="mb-2">
                                        <a href="#" class="badge bg-1 fs-8">{{ $post->category->name }}</a>
                                    </div>
                                    @endif
                                    <a href="{{ route('post.show', $post->slug) }}" class="hover-underline">
                                        <h4 class="card-title mb-0 mt-3">{{ $post->title }}</h4>
                                    </a>
                                    @if($post->excerpt)
                                    <p class="card-text text-600 fs-7 mb-0 mt-3 text-truncate-3">{{ $post->excerpt }}</p>
                                    @endif
                                    <div class="bottom mt-auto d-flex flex-wrap align-items-center gap-2 pt-5">
                                        <a href="{{ route('author.show', $author->handle()) }}" class="author d-flex align-items-center gap-2">
                                            @if($author->avatar)
                                            <img class="avatar avatar-md rounded-circle" src="{{ Storage::url($author->avatar) }}" alt="{{ $author->name }}">
                                            @else
                                            <span class="avatar avatar-md rounded-circle bg-dark d-flex align-items-center justify-content-center text-white fw-bold fs-7">{{ strtoupper(substr($author->name, 0, 1)) }}</span>
                                            @endif
                                            <span class="fs-7 text-dark fw-regular">{{ $author->name }}</span>
                                        </a>
                                        <ul class="d-flex align-items-center gap-4 text-600 m-0 ps-3">
                                            <li><p class="fs-8 m-0">{{ optional($post->published_at)->format('M d, Y') }}</p></li>
                                        </ul>
                                        <div class="ms-lg-auto ms-5 d-flex align-items-center gap-3 me-5">
                                            <span class="fs-8 text-600">{{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 200)) }} min read</span>
                                        </div>
                                    </div>
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
                                {{-- Previous --}}
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

                                {{-- Next --}}
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
