<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Redirect as CmsRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $sourcePath = CmsRedirect::normalizeSourcePath($request->getPathInfo());
        $redirect = CmsRedirect::query()
            ->enabled()
            ->where('source_path', $sourcePath)
            ->firstOrFail();

        $redirect->recordHit();

        return redirect()->to($redirect->destination_url, $redirect->status_code);
    }
}
