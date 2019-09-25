<?php
/**
 * @author Adam Ondrejkovic
 * Created by PhpStorm.
 * Date: 24/09/2019
 * Time: 10:44
 */

Route::group(['namespace' => 'm7\Iam\Http\Controllers', 'middleware' => ['web']], function() {
    Route::post('iam/login', 'LoginController@login')->name('iam.manager.login');
});
