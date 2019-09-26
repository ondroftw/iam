<?php

namespace m7\Iam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use m7\Iam\Manager;

class AuthController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @author Adam Ondrňejkovic
     */
    public function login(Request $request)
    {
        if (iam_manager()->login($request->get("email"), $request->get("password"))) {
            return redirect()->to(config('iammanager.redirect_url'));
        } else {
            return redirect()->to(config('iammanager.redirect_callback'));
        }
    }

    /**
     * @return RedirectResponse
     * @author Adam Ondrejkovic
     */
    public function logout()
    {
        iam_manager()->logout();
        return redirect()->to(config('iammanager.redirect_callback'));
    }


}
