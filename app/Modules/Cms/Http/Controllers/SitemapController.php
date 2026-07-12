<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $pages = Page::query()
            ->published()
            ->orderByDesc('is_homepage')
            ->orderBy('slug')
            ->get(['slug', 'is_homepage', 'updated_at']);

        $urls = $pages->map(function (Page $page): string {
            $location = $page->is_homepage ? url('/') : url('/'.$page->slug);
            $lastModified = $page->updated_at?->toAtomString();

            $xml = "    <url>\n";
            $xml .= '        <loc>'.htmlspecialchars($location, ENT_XML1)."</loc>\n";

            if (is_string($lastModified)) {
                $xml .= '        <lastmod>'.htmlspecialchars($lastModified, ENT_XML1)."</lastmod>\n";
            }

            $xml .= "    </url>";

            return $xml;
        })->implode("\n");

        $document = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $document .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $document .= $urls === '' ? '' : $urls."\n";
        $document .= '</urlset>';

        return response($document, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
