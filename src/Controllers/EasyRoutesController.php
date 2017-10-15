<?php

namespace Ljubadr\EasyRoutes\Controllers;

use Log;
use Route;
use Response;
use Exception;
use ReflectionClass;
use ReflectionException;
use NotFoundHttpException;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EasyRoutesController extends Controller
{

  /**
   * EasyRoutes index page
   *
   * @return view
   * @author ljubadr
   */
  public function index()
  {
    return view('easyroutes::index',[
      'highlightSearch' => config('easyroutes.highlight_search', false),
    ]);
  }

  /**
   * Ajax response for datatables
   * 
   * @return json
   * @author ljubadr
   */
  public function datatable(Request $request)
  {
    // all application routes
    $routeCollection = Route::getRoutes();

    // gather all routes here
    $allRoutes       = [];

    foreach ($routeCollection as $route) {
      $lineNumber  = '';
      $fileName    = '';
      $docComment  = '';
      $routeExists = false;

      $action      = $route->getActionName();

      // not a Closure, extract file and position of the method
      if ($action !== 'Closure') {
        // $action is in format className@methodName
        $params      = explode('@', $action);
        $className   = $params[0];
        $methodName  = $params[1];

        $sharedError = 'Route Class "'.$className.'" or method "'.$methodName.'" not found for action "'.$action.'"';

        try {
          /*
            ReflectionClass is used to extract data about the class
            http://php.net/manual/en/class.reflectionclass.php

            throws ReflectionException
           */
          $reflector = new ReflectionClass($className);

          // Check if class has method
          if ( ! $reflector->hasMethod($methodName) ) {
            $routeExists = false;

            Log::debug('EasyRoutesController@datatable: '. $sharedError );

            // method not defined, but provide link to file anyway
            $fileName = $reflector->getFileName();

          } else {
            /*
              Method properties
                http://php.net/manual/en/class.reflectionmethod.php
              NOTE: method can be defined in parent class
                that's why we do getFileName() separately
              throws ReflectionException
             */
            $methodReflector = $reflector->getMethod( $methodName );

            // absolute file path
            $fileName        = $methodReflector->getFileName();

            // line number in the file where method is defined
            $lineNumber      = $methodReflector->getStartLine();
            $docComment      = $methodReflector->getDocComment();

            $routeExists     = true;
          }

        } catch (ReflectionException $e) {
          // route doesn't exists
          $routeExists = false;

          Log::debug('EasyRoutesController@datatable: '. $sharedError.PHP_EOL.$e->getMessage() );
        }

      } else {
        // for the closure we can't get too much info...
        $routeExists = true;
      }

      $middlewares = $route->middleware();
      $routeParams = $route->parameterNames();

      $replace = config('easyroutes.web_open_path_replace', []);

      // map file path from vm to host
      if ( ! empty($replace) && ! empty($replace['vm']) && ! empty($replace['host']) ) {
        $fileName = str_replace($replace['vm'], $replace['host'], $fileName);
      }

      $allRoutes[] = [
        'method'     => $route->methods()[0],
        'as'         => $route->getName(),
        'route'      => $route->uri(),
        'link'       => ! empty($fileName)
                            ? 'http://localhost:63342/api/file/?file='.$fileName.'&line='.$lineNumber
                            : '',
        'action'     => $action,
        'middleware' => implode(', ', $middlewares ),
        'parameters' => implode(', ', $routeParams ),
        'exists'     => $routeExists,
        'docComment' => ! empty($fileName) && $docComment
                            ? $docComment
                            : '',
      ];
    }

    return Response::json( $allRoutes, 200, [], JSON_NUMERIC_CHECK);
  }

  /**
   * For given url, return action name
   *
   * @param  Request $request
   * @return json
   * @author ljubadr
   */
  public function routeExists(Request $request)
  {
    $routeName = $request->input('url', '');

    $routes    = Route::getRoutes();
    $request   = Request::create( $routeName );

    try {
      $action  = $routes->match( $request )
                        ->getActionName();

    } catch (Exception $e) {
      $action  = '';
    }

    if ( $action === '' ) {
      return Response::json([], 404, []);
    }

    return Response::json(['action' => $action], 200, [], JSON_NUMERIC_CHECK);
  }

}
