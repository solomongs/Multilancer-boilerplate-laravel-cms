<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /soloadmin',
            'Disallow: /cms-preview',
            'Disallow: /livewire',
            'Sitemap: '.route('cms.sitemap'),
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
