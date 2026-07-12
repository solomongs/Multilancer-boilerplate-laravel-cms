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
        $redirect = CmsRedirect::resolvePath($request->getPathInfo());
        abort_unless($redirect instanceof CmsRedirect, 404);

        $redirect->recordHit();

        return redirect()->to($redirect->destination_url, $redirect->status_code);
    }
}
