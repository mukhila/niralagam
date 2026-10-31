<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BrandInvitationController;
use App\Http\Controllers\Api\BrandMemberController;
use App\Http\Controllers\Api\BrandSettingsController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EngagementController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostReviewController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\SharePreviewController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ── Auth ────────────────────────────────────────────────────
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::get('/auth/brands',  [AuthController::class, 'brands']);
});

// ── Invitations (invitee side) ──────────────────────────────
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/invitations/{token}', [InvitationController::class, 'show']);
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->middleware('auth:sanctum');
});

// ── Public reads ────────────────────────────────────────────
Route::get('/brand',                 [BrandSettingsController::class, 'show']);
Route::get('/sitemap.xml',           [SeoController::class, 'sitemap']);
Route::get('/rss.xml',               [SeoController::class, 'rss']);
Route::get('/robots.txt',            [SeoController::class, 'robots']);
Route::get('/share',                 [SharePreviewController::class, 'show']);
Route::get('/authors/{handle}',       [AuthorController::class, 'show']);
Route::get('/authors/{handle}/posts', [AuthorController::class, 'posts']);
Route::get('/search',                [SearchController::class, 'search'])->middleware('throttle:60,1');

// ── Following (signed-in readers) ───────────────────────────
Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/authors/{handle}/follow', [AuthorController::class, 'toggleFollowAuthor']);
    Route::post('/brand/follow',            [AuthorController::class, 'toggleFollowBrand']);
    Route::get('/me/follows',               [AuthorController::class, 'myFollows']);
    Route::get('/me/analytics',             [AnalyticsController::class, 'mine']);
});

// ── Newsletter (reader side) ────────────────────────────────
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/newsletter/subscribe',           [NewsletterController::class, 'subscribe']);
    Route::post('/newsletter/confirm/{token}',     [NewsletterController::class, 'confirm']);
    Route::post('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe']);
});
Route::get('/posts',                 [PostController::class, 'index']);
Route::get('/posts/{slug}',          [PostController::class, 'show']);
Route::get('/categories',            [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/tags',                  [TagController::class, 'index']);
Route::get('/tags/{tag}',            [TagController::class, 'show']);

// ── Engagement public ───────────────────────────────────────
Route::post('/posts/{post}/view',    [EngagementController::class, 'recordView']);
Route::get('/posts/{post}/engage',   [EngagementController::class, 'postEngagement']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

// ── Authenticated routes ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('/profile',                  [ProfileController::class, 'show']);
    Route::put('/profile',                  [ProfileController::class, 'update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/wishlist',         [ProfileController::class, 'wishlist']);
    Route::get('/profile/bookmarks',        [ProfileController::class, 'bookmarks']);
    Route::get('/profile/posts',            [ProfileController::class, 'myPosts']);

    // Engagement toggles
    Route::post('/posts/{post}/wishlist', [EngagementController::class, 'toggleWishlist']);
    Route::post('/posts/{post}/bookmark', [EngagementController::class, 'toggleBookmark']);

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}',  [CommentController::class, 'destroy']);

    // Post write
    Route::post('/posts',          [PostController::class, 'store']);
    Route::put('/posts/{post}',    [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Editorial workflow
    Route::post('/posts/{post}/submit',  [PostReviewController::class, 'submit']);
    Route::post('/posts/{post}/approve', [PostReviewController::class, 'approve']);
    Route::post('/posts/{post}/reject',  [PostReviewController::class, 'reject']);
    Route::get('/posts/{post}/revisions', [PostReviewController::class, 'revisions']);
    Route::post('/posts/{post}/revisions/{revision}/restore', [PostReviewController::class, 'restore']);

    // Media library (brand writers). /upload/image is kept as an alias for older callers.
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/upload/image',        [MediaController::class, 'store']);
        Route::post('/media',               [MediaController::class, 'store']);
    });
    Route::get('/media',                    [MediaController::class, 'index']);
    Route::put('/media/{media}',            [MediaController::class, 'update']);
    Route::get('/media/{media}/usage',      [MediaController::class, 'usage']);
    Route::delete('/media/{media}',         [MediaController::class, 'destroy']);

    // ── Tenant admins & editors (super admins always pass) ──
    Route::middleware('tenant.role:admin,editor')->group(function () {

        // Category write
        Route::post('/categories',              [CategoryController::class, 'store']);
        Route::put('/categories/{category}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Tag write
        Route::post('/tags',        [TagController::class, 'store']);
        Route::put('/tags/{tag}',   [TagController::class, 'update']);
        Route::delete('/tags/{tag}',[TagController::class, 'destroy']);

        // Brand admin panel (scoped to the current brand)
        Route::get('/admin/analytics',                        [AnalyticsController::class, 'brand']);
        Route::get('/admin/dashboard',                        [DashboardController::class, 'index']);
        Route::get('/admin/comments',                         [CommentController::class, 'adminIndex']);
        Route::put('/admin/comments/{comment}/status',        [CommentController::class, 'updateStatus']);
    });

    // ── Brand admins: members of the current brand ──────────
    Route::middleware('tenant.role:admin')->group(function () {
        Route::put('/admin/brand',              [BrandSettingsController::class, 'update']);
        Route::get('/admin/audit',              [AuditController::class, 'brand']);

        // Newsletter
        Route::get('/admin/subscribers',                 [SubscriberController::class, 'index']);
        Route::get('/admin/subscribers/export',          [SubscriberController::class, 'export']);
        Route::delete('/admin/subscribers/{subscriber}', [SubscriberController::class, 'destroy']);
        Route::get('/admin/newsletter/digest',           [SubscriberController::class, 'digest']);
        Route::put('/admin/newsletter/digest',           [SubscriberController::class, 'updateDigest']);
        Route::post('/admin/newsletter/digest/send',     [SubscriberController::class, 'sendDigest'])->middleware('throttle:3,10');

        Route::get('/admin/members',            [BrandMemberController::class, 'index']);
        Route::put('/admin/members/{user}',     [BrandMemberController::class, 'update']);
        Route::delete('/admin/members/{user}',  [BrandMemberController::class, 'destroy']);

        // New members join by accepting an emailed invitation.
        Route::get('/admin/invitations',                     [BrandInvitationController::class, 'index']);
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('/admin/invitations',                     [BrandInvitationController::class, 'store']);
            Route::post('/admin/invitations/{invitation}/resend', [BrandInvitationController::class, 'resend']);
        });
        Route::delete('/admin/invitations/{invitation}',     [BrandInvitationController::class, 'destroy']);
    });

    // ── Super admin only (platform) ─────────────────────────
    Route::middleware('super_admin')->group(function () {
        Route::get('/admin/users',                            [UserController::class, 'index']);
        Route::get('/admin/audit/platform',                   [AuditController::class, 'platform']);
        Route::put('/admin/users/{user}',                     [UserController::class, 'update']);
        Route::get('/admin/tenants',                          [TenantController::class, 'index']);
        Route::post('/admin/tenants',                         [TenantController::class, 'store']);
        Route::get('/admin/tenants/{tenant}',                 [TenantController::class, 'show']);
        Route::put('/admin/tenants/{tenant}',                 [TenantController::class, 'update']);
        Route::delete('/admin/tenants/{tenant}',              [TenantController::class, 'destroy']);
        Route::post('/admin/tenants/{tenant}/members',        [TenantController::class, 'addMember']);
        Route::put('/admin/tenants/{tenant}/members/{user}',  [TenantController::class, 'updateMember']);
        Route::delete('/admin/tenants/{tenant}/members/{user}', [TenantController::class, 'removeMember']);
    });
});
