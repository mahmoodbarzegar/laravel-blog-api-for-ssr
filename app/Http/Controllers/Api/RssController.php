<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;

class RssController extends Controller
{
    public function __invoke()
    {
        $posts = Post::with('author')->published()->latest('published_at')->limit(30)->get();
        $siteUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');

        $items = $posts->map(function (Post $post) use ($siteUrl) {
            $link = "{$siteUrl}/posts/{$post->slug}";

            return <<<XML
                <item>
                    <title>{$this->escape($post->title)}</title>
                    <link>{$link}</link>
                    <guid isPermaLink="true">{$link}</guid>
                    <pubDate>{$post->published_at->toRfc2822String()}</pubDate>
                    <author>{$this->escape($post->author->name)}</author>
                    <description>{$this->escape($post->excerpt)}</description>
                </item>
                XML;
        })->implode("\n");

        $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
                <channel>
                    <title>{$this->escape(config('app.name'))}</title>
                    <link>{$siteUrl}</link>
                    <description>آخرین مقالات وبلاگ</description>
                    <language>fa</language>
                    {$items}
                </channel>
            </rss>
            XML;

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    private function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
