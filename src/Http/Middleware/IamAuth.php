<?php

namespace m7\Iam\Http\Middleware;

use App\Http\Middleware\Authenticate;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use m7\Iam\Manager;

class IamAuth
{
    /**
     * @param $request
     * @param Closure $next
     * @param string $scopes
     *
     * @return mixed
     * @author Adam Ondrejkovic
     */
    public function handle($request, Closure $next, string $scopes)
    {
        if (!iam_manager()->issetValidAccessToken()) {
            if (Auth::check()) {
                Auth::logout();
            }

            iam_manager()->removeSessionValues();
            return redirect()->to(config('iammanager.redirect_callback'));
        }

        return $next($request);

    }
}
