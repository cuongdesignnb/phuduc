<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('products.index'), 'lastmod' => null],
            ['loc' => route('news.index'), 'lastmod' => null],
            ['loc' => route('about'), 'lastmod' => null],
        ];

        Product::query()
            ->where('status', 'active')
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->eachById(function (Product $product) use (&$urls): void {
                $urls[] = [
                    'loc' => route('products.show', $product->slug),
                    'lastmod' => $product->updated_at,
                ];
            });

        Post::query()
            ->where('status', 'published')
            ->select(['id', 'slug', 'published_at', 'updated_at'])
            ->orderBy('id')
            ->eachById(function (Post $post) use (&$urls): void {
                $urls[] = [
                    'loc' => route('news.show', $post->slug),
                    'lastmod' => $post->published_at?->greaterThan($post->updated_at)
                        ? $post->published_at
                        : $post->updated_at,
                ];
            });

        $body = collect($urls)
            ->unique('loc')
            ->map(function (array $url): string {
                $lastmod = $url['lastmod'] instanceof CarbonInterface
                    ? '<lastmod>'.$url['lastmod']->toAtomString().'</lastmod>'
                    : '';

                return '<url><loc>'.htmlspecialchars($url['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8').'</loc>'.$lastmod.'</url>';
            })
            ->implode('');

        return response(
            '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$body
            .'</urlset>',
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }
}
