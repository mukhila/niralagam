<?php

return [

    // Tenants are served at {slug}.{central_domain}, e.g. brand.niralagam.com.
    'central_domain' => env('TENANCY_CENTRAL_DOMAIN', 'niralagam.com'),

    // Used when a request's host or headers don't name a tenant (e.g. localhost).
    'default' => env('TENANCY_DEFAULT', 'main'),

    // Membership roles, highest first. Super admins bypass all role checks.
    'roles' => ['admin', 'editor', 'author', 'subscriber'],

    // Invitation links point at the React app: {frontend_url}/invite/{token}.
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    'invite_days' => (int) env('TENANCY_INVITE_DAYS', 7),

    // ── Brand theme ─────────────────────────────────────────
    // Google Fonts a brand may pick. Names go straight into a fonts.googleapis.com
    // URL and CSS, so only these exact values are accepted. 'system' = device font.
    'fonts' => [
        'system', 'Inter', 'Roboto', 'Open Sans', 'Lato', 'Poppins', 'Montserrat',
        'Merriweather', 'Playfair Display', 'Lora', 'Source Serif 4',
        'Noto Sans Tamil', 'Noto Serif Tamil', 'Mukta Malar', 'Catamaran',
    ],

    'layouts' => ['magazine', 'grid'],

    'social' => ['website', 'twitter', 'facebook', 'instagram', 'youtube', 'linkedin'],

    // Null colours keep the site's built-in look.
    'theme_defaults' => [
        'tagline'       => null,
        'about'         => null,
        'hero_title'    => null,
        'hero_subtitle' => null,
        'primary_color' => null,
        'accent_color'  => null,
        'heading_font'  => 'system',
        'body_font'     => 'system',
        'home_layout'   => 'magazine',
        'social'        => [],
    ],

];
