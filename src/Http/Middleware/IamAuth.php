<?php

namespace m7\Iam\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IamAuth
{
    /**
     * @param $request
     * @param Closure $next
     *
     * @return \Illuminate\Http\RedirectResponse|mixed
     * @author Adam Ondrejkovic
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (!iam_manager()->issetValidAccessToken()) {
                iam_manager()->logout();
                return redirect()->to(config('iammanager.redirect_callback'));
            }

            return $next($request);
        } else {
            iam_manager()->removeSessionValues();
            return redirect()->to(config('iammanager.redirect_callback'));
        }

    }
}
