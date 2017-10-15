<?php

return [
  /*
    Change default route for EasyRoutes
   */
  'route_prefix' => 'easyroutes',

  /*
    Default middleware added to EasyRoutes
   */
  'middleware' => ['web', 'auth'],

  /*
    Use this to disable routes on production
   */
  'enabled' => env('APP_DEBUG', false),

  /*
    Highlight search results in the table
    This is done by mark.js plugin, and it can be slow in some cases
    that's why it's off by default
   */
  'highlight_search' => false,

  /*
    Leave empty if server is running on host machine

    If project is run in vm (homestead / vagrant /...)
    map path from the vm to the path on host machine

    With IntelliJ IDEA (PhpStorm, WebStrom, ...) you can open file with
      http://localhost:63342/api/file/?file=<absolute-path-to-file>&line=20
    But for this to work, file needs to point to host machine file location
    EasyRoutes table will create links in this format, but make sure that mapping
      is right
   */
  'web_open_path_replace' => [
    'vm'   => '',   // example: '/home/vagrant/Code/easyroutes',
    'host' => '',   // example: '/home/user/Code/easyroutes',
  ],

];
