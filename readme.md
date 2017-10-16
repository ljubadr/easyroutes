# Laravel helper package to view routes in browser

![Screenshot](/screenshot.png?raw=true "Screenshot with laravel voyager")

This is a package that I created to help me speed up the laravel developement.  

## Features:
- search for the route by url
- search history
- toggle columns visibility
- table with search filter
- column to see if route exists or not
- view php method comments
- open file in editor - jump to class@method definition in editor
- saved page state - close and reopen the page to continue where you left off

## Table of Contents
* [Install](#install)
* [Provider](#provider)
* [Publish](#publish)
* [Config](#config)

## Installation
```bash
composer require ljubadr/easyroutes
```

### For Laravel 5.5 +
This package can be auto discovered by Laravel.

### For Laravel 5.4 or disabled package discovery
Add EasyRoutesServiceProvider provider to the providers array in `config/app.php`  

```php
    'providers' => [
        ...
         Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider::class,
         ...
    ],
```

## Publish
Publish config and assets  
`php artisan vendor:publish --provider="Ljubadr\EasyRoutes\Providers\EasyRoutesServiceProvider"`

## Config
Config is copied to `config/easyroutes.php`.  
Read comments for more info about the settings.

## Opening files in editor by clicking on the link in the Action column

### If you are using IntelliJ IDEA (webstorm, phpstorm, ...)  
If you are using virtual machine for your dev server, read `config/easyroutes.php` how to setup mapping.  
To open the file in editor click on the link in the `action` column.

### If you are using sublime, vscode, atom, ...  
Go to repository and follow instructions to setup opening files with url  
https://github.com/ljubadr/editor-web-open

To open the file in editor click on the link in the `action` column.
