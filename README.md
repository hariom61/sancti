# Sancti rest api authentication for laravel
Rest api register / login authentication with user account email verification based on Laravel sanctum.

### Default routes for rest api
- Register user
- Login user
- Activate email
- Reset password
- Change password
- Logout user
- Delete tokens

## Create laravel project
```sh
composer create-project laravel/laravel sancti
cd sancti
```

### Install sancti with composer
composer require breakermind/sancti
```json
{
	"require": {
		"breakermind/sancti": "^1.0"
	}
}
```

### Create database and configure mysql, smtp in .env file
nano sancti/.env
```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=toor

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hi@localhost
MAIL_FROM_NAME="${APP_NAME}"
```

## Sanctum setup
Configure sanctum authentication for laravel https://laravel.com/docs/8.x/sanctum
```sh
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Migrations
```sh
php artisan migrate
```

### Sanctum middleware for SPA (optional)
nano app/Http/Kernel.php
```php
'api' => [
	// Unhash
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
]
```

## Sancti setup
```sh
php artisan vendor:publish --provider="Sancti\SanctiServiceProvider.php"
php artisan vendor:publish --tag=sancti-config --force
```

### Migrations
```sh
php artisan migrate
```

### Update composer autoload
```sh
composer update
composer dump-autoload -o
```

### Run local server
```sh
php artisan serv
```

### Default routes tests
Allowed request content types: application/json or x-www-form-urlencoded
```sh
# login
curl -X POST http://app.xx/api/login -F "password=password123" -F "email=bo@woo.xx"
curl -X POST http://app.xx/api/login -d '{"email": "bo@woo.xx", "password": "password123"}'

# register
curl -X POST http://app.xx/api/register -F "name=Jony" -F "password=password123" -F "password_confirmation=password123" -F "email=bo@woo.xx"

# activate
curl http://app.xx/api/activate/30/61928cad3f2d0

# reset
curl -X POST http://app.xx/api/reset -F "email=bo@woo.xx"

# token example
{"token":"1|Sfp1JQPzY0AoeETTkR9A8QkrAMDZ5ITQxrrwLpZK"}

# get logged user data
curl http://app.xx/api/user -H 'Authorization: Bearer 1|Sfp1JQPzY0AoeETTkR9A8QkrAMDZ5ITQxrrwLpZK'

# change pass logged users
curl -X POST http://app.xx/api/change-password -F "password_current=password123" -F "password=password1231" -F "password_confirmation=password1231" -H 'Authorization: Bearer 1|Sfp1JQPzY0AoeETTkR9A8QkrAMDZ5ITQxrrwLpZK'

# logout logged users
curl http://app.xx/api/logout -H 'Authorization: Bearer 1|Sfp1JQPzY0AoeETTkR9A8QkrAMDZ5ITQxrrwLpZK'

# delete tokens logged users
curl http://app.xx/api/delete -H 'Authorization: Bearer 1|Sfp1JQPzY0AoeETTkR9A8QkrAMDZ5ITQxrrwLpZK'

```

### Routes example
routes/api.php
```php
// Public routes
Route::get('/post/{id}', [PostController::class, 'details'])->name('post.details');

// Private routes
Route::middleware(['auth:sanctum'])->group(function () {
	// Only authorized users
	Route::get('/user/{id}', [UserController::class, 'details'])->name('user.details');
});
```

# Package

### Add service provider to config/app.php
Add if installed not from composer or if local package or errors
```php
'providers' => [
	Sancti\SanctiServiceProvider::class,
],
'aliases' => [
	'Sancti' => Sancti\Http\Facades\SanctiFacade::class,
]
```

### Local package development
Add import repo paths **dev-main**
```json
{
	"repositories": [{
		"type": "vcs",
		"url": "https://github.com/breakermind/sancti"
	}],
	"require": {
		"breakermind/sancti": "dev-main"
	}
}
```