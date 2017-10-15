# Laravel helper package to view routes in browser

# Install
`composer require ljubadr/easyroutes:dev-master`

Publish config and assets

`php artisan vendor:publish --provider="Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider"`

# Edit config file
Edit `config/easyroutes.php`

Add new provider in `config/app.php`

```
    'providers' => [
        ...
         Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider::class,
         ...
    ],
```
