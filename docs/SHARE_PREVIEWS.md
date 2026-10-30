# Share previews, sitemap & feeds in production

The React app sets its own title and share tags, but **link-preview bots don't run
JavaScript** (WhatsApp, Facebook, X/Twitter, LinkedIn, Slack, Telegram, Discord…).
So the web server must:

1. send those bots' requests for `/`, `/blog`, `/blog/{slug}` and `/about` to
   `GET /api/share?path=<the path>`, which returns a small HTML page with the
   Open Graph / Twitter tags;
2. send `/sitemap.xml`, `/rss.xml` and `/robots.txt` to `/api/sitemap.xml`,
   `/api/rss.xml` and `/api/robots.txt`.

The API picks the brand from the request's host (or an `X-Tenant-Domain` header),
so keep the original host when proxying. In development, `vite.config.js` does
all of this already.

Bot list (keep in sync with `vite.config.js`):

```
facebookexternalhit|facebookcatalog|Facebot|Twitterbot|WhatsApp|LinkedInBot|Slackbot|TelegramBot|Discordbot|Pinterest|redditbot|Applebot|SkypeUriPreview|vkShare|Embedly|Iframely|Mastodon|Bluesky|Snapchat
```

## nginx

```nginx
map $http_user_agent $preview_bot {
    default 0;
    "~*(facebookexternalhit|facebookcatalog|Facebot|Twitterbot|WhatsApp|LinkedInBot|Slackbot|TelegramBot|Discordbot|Pinterest|redditbot|Applebot|SkypeUriPreview|vkShare|Embedly|Iframely|Mastodon|Bluesky|Snapchat)" 1;
}

server {
    server_name niralagam.com *.niralagam.com;   # plus each brand's custom domain
    root /var/www/niralagam-blog/dist;

    # Laravel API (php-fpm); keeps the brand's Host header
    location /api/ {
        try_files $uri /index.php?$query_string;   # or proxy_pass to the API host with proxy_set_header Host $host;
    }

    location = /sitemap.xml { rewrite ^ /api/sitemap.xml last; }
    location = /rss.xml     { rewrite ^ /api/rss.xml last; }
    location = /robots.txt  { rewrite ^ /api/robots.txt last; }

    location ~ ^/(blog(/[^/]+)?|about)?/?$ {
        if ($preview_bot) { rewrite ^ /api/share?path=$uri last; }
        try_files $uri /index.html;
    }

    location / { try_files $uri /index.html; }
}
```

## Apache (`.htaccess` in the React build's folder, e.g. on XAMPP)

Needs `mod_rewrite`, plus `mod_proxy` + `mod_proxy_http` if the API is on another host.

```apache
RewriteEngine On

# Sitemap, feed, robots → API
RewriteRule ^sitemap\.xml$ /api/sitemap.xml [L]
RewriteRule ^rss\.xml$     /api/rss.xml [L]
RewriteRule ^robots\.txt$  /api/robots.txt [L]

# Link-preview bots → share card
RewriteCond %{HTTP_USER_AGENT} (facebookexternalhit|facebookcatalog|Facebot|Twitterbot|WhatsApp|LinkedInBot|Slackbot|TelegramBot|Discordbot|Pinterest|redditbot|Applebot|SkypeUriPreview|vkShare|Embedly|Iframely|Mastodon|Bluesky|Snapchat) [NC]
RewriteRule ^((blog(/[^/]+)?|about)/?)?$ /api/share?path=/$1 [L,QSA]

# Everything else → the React app
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [L]
```

If `/api` lives on a different host, change the `/api/...` targets to the full API URL
and add `[P]` (proxy) to those rules, with `ProxyPreserveHost On`.

## Check it

```bash
curl -A "WhatsApp/2.23" https://yourbrand.com/blog/some-post
```

You should get a short HTML page whose `og:title`, `og:description` and `og:image`
match the post. Then try the URL in Facebook's Sharing Debugger
(https://developers.facebook.com/tools/debug/) or LinkedIn's Post Inspector. Those
tools also clear their cached preview after you change a post.

## Images

Use share images of about **1200×630**, under 5 MB, on a public `https://` URL.
Set one per post in the admin editor's "Search & sharing" panel; otherwise the
featured image, then the brand logo, is used.
