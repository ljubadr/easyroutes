# Laravel helper package to view routes in browser

![Screenshot](/screenshot.png?raw=true "Screenshot with laravel voyager")

## Table of Contents
* [Install](#install)
* [Publish](#publish)
* [Config](#config)
* [Provider](#provider)

## Install
`composer require ljubadr/easyroutes`

## Publish
Publish config and assets  
`php artisan vendor:publish --provider="Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider"`

## Config
Config is copied to `config/easyroutes.php`.  
Read comments for more explanation.

## Provider
For laravel up to and 5.4, add ServiceProvider  
Add EasyRoutesServiceProvider provider to the providers array in `config/app.php`  

```
    'providers' => [
        ...
         Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider::class,
         ...
    ],
```

## Opening files in editor by clicking on the link in the Action column

### If you are using IntelliJ IDEA (webstorm, phpstorm, ...)  
If you are using virtual machine for your dev server, read `config/easyroutes.php` how to setup mapping.
To open the file in editor click on the link in the `action` column.

### If you are using sublime, vscode, atom, ...  
Go to repository and follow instructions to setup opening files with url  
https://github.com/ljubadr/editor-web-open
