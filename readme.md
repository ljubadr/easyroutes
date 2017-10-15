# Laravel helper package to view routes in browser

# Install
`composer require ljubadr/easyroutes`

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

# Opening files in editor by clicking on the class@method name
## If you are using IntelliJ IDEA (webstorm, phpstorm, ...)
Just click on the link in the action column

## If you are using sublime, vscode, atom, ...
Go to repository and follow instructions to setup opening files with url
https://github.com/ljubadr/editor-web-open
