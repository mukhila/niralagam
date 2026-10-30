<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostHtmlSanitizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsafe_html_is_removed_on_save(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $html = '<p onclick="x()">Hi <strong>there</strong></p><script>steal()</script>'
            .'<img src="x" onerror="steal()"><a href="javascript:alert(1)">bad</a>'
            .'<a href="https://ok.test" target="_blank">ok</a><iframe src="https://evil.test"></iframe>';

        $post = $this->postJson('/api/posts', ['title' => 'T', 'content' => $html, 'excerpt' => '<b>Short</b>'])
            ->assertCreated()->json();

        foreach (['<script', 'onclick', 'onerror', 'javascript:', '<iframe'] as $bad) {
            $this->assertStringNotContainsString($bad, $post['content']);
        }
        $this->assertStringContainsString('<strong>there</strong>', $post['content']);
        $this->assertStringContainsString('href="https://ok.test"', $post['content']);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $post['content']);
        $this->assertSame('Short', $post['excerpt']);

        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'featured_image' => 'javascript:alert(1)'])
            ->assertUnprocessable();
    }
}
