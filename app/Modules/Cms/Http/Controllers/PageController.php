<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Redirect as CmsRedirect;
use App\Services\ThemeManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\View as ViewFacade;

class PageController extends Controller
{
    public function home(): View
    {
        $page = Page::query()
            ->published()
            ->where('is_homepage', true)
            ->with(['sections' => fn ($query) => $query->renderable()])
            ->first();

        if (! $page instanceof Page) {
            return view('welcome');
        }

        return $this->render($page);
    }

    public function show(string $slug): View|RedirectResponse
    {
        abort_if(in_array($slug, config('cms.reserved_slugs', []), true), 404);

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->with(['sections' => fn ($query) => $query->renderable()])
            ->first();

        if ($page instanceof Page) {
            return $this->render($page);
        }

        $redirect = CmsRedirect::resolvePath('/'.$slug);
        abort_unless($redirect instanceof CmsRedirect, 404);

        $redirect->recordHit();

        return redirect()->to($redirect->destination_url, $redirect->status_code);
    }

    public function preview(Page $page): View
    {
        $page->load(['sections' => fn ($query) => $query->renderable()]);

        return $this->render($page, true);
    }

    private function render(Page $page, bool $isPreview = false): View
    {
        $theme = config('cms.default_theme', 'm2026');
        app(ThemeManager::class)->setTheme(is_string($theme) ? $theme : 'm2026');

        $defaultTemplate = config('cms.default_template', 'default');
        $defaultTemplate = is_string($defaultTemplate) ? $defaultTemplate : 'default';
        $template = preg_match('/^[a-z0-9_-]+$/', $page->template) === 1
            ? $page->template
            : $defaultTemplate;
        $view = 'pages.'.$template;

        if (! ViewFacade::exists($view)) {
            $view = 'pages.default';
        }

        return view($view, [
            'page' => $page,
            'sections' => $page->sections,
            'isPreview' => $isPreview,
        ]);
    }
}
