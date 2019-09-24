<?php
/**
 * @author Adam Ondrejkovic
 * Created by PhpStorm.
 * Date: 24/09/2019
 * Time: 14:02
 */
namespace m7\Iam\Facades;

use Illuminate\Support\Facades\Facade;

class Manager extends Facade
{

    protected static function getFacadeAccessor(){
        return 'manager';
    }
}
