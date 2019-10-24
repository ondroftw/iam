<?php

namespace m7\Iam\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class IamAuth
{
	/**
	 * @param $request
	 * @param Closure $next
	 *
	 * @return RedirectResponse|mixed
	 * @author Adam Ondrejkovic
	 */
    public function handle($request, Closure $next)
    {
		if (!iam_manager()->isUserLoggedIn()) {
			return redirect()->to(config('iammanager.redirect_callback'));
		}

		return $next($request);
    }
}
