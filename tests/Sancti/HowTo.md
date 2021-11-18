# Sancti testing
Before tests install package see package README.md

## Create testing env
nano .env.testing
```sh
APP_ENV=testing
APP_DEBUG=true
```

## Create testing config xml
nano phpunit.xml
```xml
<env name="APP_ENV" value="testing" force="true"/>
<env name="APP_DEBUG" value="true" force="true"/>
```

## Database tables
```sh
php artisan --env=testing migrate
```

## Publish
```sh
# go to laravel app
cd app

# copy test files
php artisan vendor:publish --tag=sancti-tests --force

# test with artisan
php artisan test tests/Sancti --stop-on-failure

# or with phpunit
vendor/bin/phpunit tests/Sancti --stop-on-failure
```

## Manually
Copy files from package **tests/Sancti** directory to laravel app **tests/Sancti** directory

### Add to laravel app phpunit.xml
```xml
<testsuite name="Sancti">
	<directory suffix="Test.php">./tests/Sancti</directory>
</testsuite>
```

### Run tests
```sh
php artisan test tests/Sancti --stop-on-failure
```