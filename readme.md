# Laravel helper package to view routes in browser

![Screenshot](/screenshot.png?raw=true "Screenshot with laravel voyager")

# Install
`composer require ljubadr/easyroutes`

# Edit config file  
After EasyRoutes install, config is copied to `config/easyroutes.php`.  
Readd read comments for more explanation.

## Publish config and assets  
`php artisan vendor:publish --provider="Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider"`

## For laravel up to and 5.4, add ServiceProvider  
Add EasyRoutesServiceProvider provider to the providers array in `config/app.php`  

```
    'providers' => [
        ...
         Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider::class,
         ...
    ],
```

# Opening files in editor by clicking on the link in the Action column

## If you are using IntelliJ IDEA (webstorm, phpstorm, ...)  
Just click on the link in the action column

## If you are using sublime, vscode, atom, ...  
Go to repository and follow instructions to setup opening files with url  
https://github.com/ljubadr/editor-web-open
