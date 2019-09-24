<?php

namespace m7\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use m7\Iam\Facades\Manager;

class LoginController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @author Adam Ondrňejkovic
     */
    public function login(Request $request)
    {
        if (Manager::loginWithCredentials("ondrejkovic@m7.sk", "0000")) {
            return redirect()->to(config('iammanager.redirect_url'));
        }
    }
}
