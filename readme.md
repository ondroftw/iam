# Laravel 6+ wrapper for IAM by m7 s.r.o.
This package provides simple wrapper for comunicating with IAM created by m7 s.r.o. 
inside Laravel 6+ applications.

## Installation
1. Install via composer: `composer require m7/iam`
1. Run migrations: `php artisan migrate` - this creates (or updates) some fields to `users` table that are
used to pair your users with IAM:
    1. `iam_uid`
    1. `name`
    1. `surname`
    1. `email`
1. Fields generated by migration from this package should be added to fillable property on your `User` model, 
  like so:
      ```php
      use Illuminate\Foundation\Auth\User as Authenticatable;
      
      class User extends Authenticatable
      {
          use Authenticatable;
      
          protected $fillable = ['iam_uid', 'name', 'surname', 'email', 'password'];
      }
      ```
1. Next up, you should use `Iam` trait in your user model
    ```php
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use m7\Iam\Traits\Iam;
    
    class User extends Authenticatable
    {
        use Authenticatable, Iam;
    
        protected $fillable = ['iam_uid', 'name', 'surname', 'email', 'password'];
    }
    ```
## Configuration
### .env values
```dotenv
IAM_MANAGER_SERVER=https://server.url
IAM_MANAGER_CLIENT_ID=xxx
IAM_MANAGER_CLIENT_SECRET=xxx
IAM_MANAGER_REDIRECT_URL=/ # redirect after login
IAM_MANAGER_REDIRECT_CALLBACK=/ # redirect after logout
IAM_MANAGER_PUBLIC_KEY=auth.pub # relative path from root of the project to public key file
```
`Note` public key file should also be included in your `.gitignore` file for security reasons 
### Middleware
Middleware setup is not required, but very useful. You can also create your own middleware using methods from this package

#### IAM scopes
You can register middleware shipped with this package to protect
certain routes or route groups based on scopes assigned to users in IAM.

To do so, register `IamScopes` middleware in your `app/Http/Kernel.php`:

```php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use m7\Iam\Http\Middleware\IamScopes;

class Kernel extends HttpKernel 
{
    ...
    protected $routeMiddleware = [
        ...
        'iam.scopes' => IamScopes::class,
        ...
    ];
    ...
}
```

#### IAM auth
IAM auth middleware is used to protect routes that you wish to restrict access to. This restriction is based on
having valid access token from IAM.

To register IAM auth middleware, register `IamAuth` middleware class in your `app/Http/Kernel.php`:

```php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use m7\Iam\Http\Middleware\IamAuth;

class Kernel extends HttpKernel 
{
    ...
    protected $routeMiddleware = [
        ...
        'iam.auth' => IamAuth::class,
        ...
    ];
    ...
}
```

## Usage

### Basic
You can always get instance of iam manager class via helper function `iam_manager()` if you need to do so.

There are two routes created for login and logout. Feel free to use your own routes and functionality, but these
are recommended for logging your users in and out.
```php
Route::post('iam/login', 'm7\Iam\Http\Controllers\LoginController@login')->name('iam.manager.login');
Route::post('iam/logout', 'm7\Iam\Http\Controllers\LoginController@logout')->name('iam.manager.logout');
```
Request body for `iam.manager.login` route should contain `username` and `password` keys with corresponding values.

Alternatively, if you do not want to use this particular routes for some reason, you can log in and out your users via
manager helper instance methods `iam_manager()->login($username, $password)` and `iam_manager()->logout()`

`iam_manager()->login` method returns instance of `User` model if successfully logged in, `false` otherwise.

### Trait methods
If you successfully added `Iam` trait to user model, you can now access several methods:
```php
Auth::user()->getScopes() // get all scopes assigned to this user
Auth::user()->hasScope($scope) // check if user has certain scope ($scope can also be array, that way you can check if user has multiple scopes)
```

### Middleware
If you registered middleware during configuration, you can protect routes or route groups based on scopes
provided by IAM or based on having valid access token set

#### Single scope
Example usage of `iam.scopes` middleware for single scope could look like this
```php
Route::middleware('iam.scopes:auth.users.manage')->group(function () {
    Route::get('users/manage', function() {
        echo "This route is scope protected";
    });
})
```
#### Multiple scopes
You can also use multiple scopes. Just separate them with pipe (`|`), and you can use as many scopes as you want:
```php
Route::middleware('iam.scopes:auth.users.manage|auth.groups.view')->group(function () {
    Route::get('users/manage', function() {
        echo "This route is scope protected";
    });
})
```

#### IAM Auth
Protecting routes with `IamAuth` middleware could look like this
```php
Route::middleware('iam.auth')->group(function () {
    Route::get('orders', function() {
        echo "This route requires valid access token to be set";
    });
})
```

### Manager methods
```php
iam_manager()->login($username, $password)
```

```php
iam_manager()->logout()
```

```php
iam_manager()->getAccessToken()
```

```php
iam_manager()->getAccessTokenDecoded()
```

```php
iam_manager()->getRefreshToken()
```

```php
iam_manager()->issetValidAccessToken()
```

```php
iam_manager()->refreshToken()
```
