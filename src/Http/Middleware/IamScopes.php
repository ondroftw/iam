<?php

namespace m7\Iam\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IamScopes
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
        $scopesArray = explode("|", $scopes);

        if (Auth::user()->hasScope($scopesArray)) {
            return $next($request);
        }

        return redirect()->route('scope.cannot');
    }
}
