<?php
/**
 * @author Adam Ondrejkovic
 * Created by PhpStorm.
 * Date: 24/09/2019
 * Time: 10:40
 */

namespace m7\Iam;

use Illuminate\Support\ServiceProvider;

class IamManagerServiceProvider extends ServiceProvider
{
    /**
     * @author Adam Ondrejkovic
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    /**
     * @author Adam Ondrejkovic
     */
    public function register()
    {
        $this->app->bind('manager', Manager::class);
        $this->mergeConfigFrom(__DIR__.'/config/iammanager.php', 'iammanager');
    }
}
