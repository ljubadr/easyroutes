<!DOCTYPE html>
<html>
  <head>
    <title>EasyRoutes</title>

    <link rel="stylesheet" href="/vendor/easyroutes/css/easyroutes-libs.min.css">
    <script src="/vendor/easyroutes/js/easyroutes-libs.min.js"></script>

    <!-- Scripts -->
    <script>
        // send X-CSRF-TOKEN with every ajax request
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        });

        var routesUrl       = "{{ action('\Ljubadr\EasyRoutes\Controllers\EasyRoutesController@datatable') }}",
            routeExistUrl   = "{{ action('\Ljubadr\EasyRoutes\Controllers\EasyRoutesController@routeExists') }}";

        var highlightSearch = {{ $highlightSearch ? 'true' : 'false' }};
    </script>

    {{-- <script src="/vendor/easyroutes/js/easyroutes.min.js"></script> --}}
  </head>

  <body>
    <div class="easy-container">
      <div class="row">

        <div class="col-6">

          <form id="routeExistsForm" class="row">
            <div class="col-10">
              <input id="convertRouteInput" type="text" placeholder="Paste url here to find matching route (with GET params)">
            </div>

            <div class="col-2">
              <button id="routeExistsButton" class="btn post">Find</button>
            </div>
          </form>

        </div>

        <div class="col-2">
          <button id="reloadRoutesButton" class="btn get clickable">Reload table</button>
        </div>

        <div class="col-4">
          <span class="">
            <select id="previousRoutes"></select>
          </span>
        </div>

      </div>

      {{-- toggle visible columns --}}
      <div class="row">
        <div class="visible-text">Visible columns:</div>
        <a class="toggle-vis" data-column="0">Method</a>
        <a class="toggle-vis" data-column="1">Exists</a>
        <a class="toggle-vis" data-column="2">Route</a>
        <a class="toggle-vis" data-column="3">As</a>
        <a class="toggle-vis" data-column="4">Parameters</a>
        <a class="toggle-vis" data-column="5">#</a>
        <a class="toggle-vis" data-column="6">Middleware</a>
        <a class="toggle-vis" data-column="7">#</a>
        <a class="toggle-vis" data-column="8">Action</a>
        <a class="toggle-vis" data-column="9">Comment</a>
      </div>

      <div class="row padding-top-15">
        <table id="routesTable" class="table display compact row-border hover">
        </table>
      </div>
    </div>

    {{--
      When link is clicked, action is sent to this hidden frame
      So file will open without redirecting current page
      or opening and closing new tab
     --}}
    <iframe style="display:none;" name="fakeFrame"></iframe>

    <script type="text/javascript">
      var localStorageName = 'route-search-history';

      /*
        Youtube like loader for ajax calls
        @ https://github.com/rstacruz/nprogress/
       */
      NProgress.configure({ trickleSpeed: 50 });

      $( document )
        .ajaxSend(function() {
          NProgress.start();
        })
        .ajaxStart(function() {
          NProgress.set(0.1);
        })
        .ajaxComplete(function() {
          NProgress.set(0.8);
        })
        .ajaxStop(function() {
          NProgress.done();
        });

      if ( $.fn.dataTable && $.fn.dataTable.ext ) {
        /*
          dataTables error handling
          Override dataTables default error mode (annoying `alert()` )
         */
        $.fn.dataTable.ext.errMode = function( settings, helpPage, message ) { 
          console.log(message);
        };
      }

      /**
       * Toggle button disabled attr and also toggle .disabled class
       * @param  boolean enabled
       * @return $(this)
       */
      $.fn.toggleButton = function(enabled) {
        return $(this)
            .toggleClass('disabled', ! enabled)
            .prop('disabled',        ! enabled);
      }

      /*
        Message notification helper
          - quick and dirty
       */
      var easy = {
        success: function(text) { this.message(text, '#58cd89')},
        info:    function(text) { this.message(text, '#61b0e9')},
        warning: function(text) { this.message(text, '#a3a3a3')},
        danger:  function(text) { this.message(text, '#e66164')},
        error:   function(text) { this.message(text, '#e66164')},
        message: function(text, backgroundColor) {
          var newdiv = document.createElement("div");

          newdiv.appendChild( document.createTextNode(text) );

          var styleText = 'position: absolute; \
            top: 7px; \
            right: 27px; \
            padding: 20px; \
            color: white; \
            min-width: 200px; \
            background-color: '+backgroundColor+';'

          newdiv.setAttribute('style', styleText);

          document.body.appendChild(newdiv);

          // remove message after 3s
          setTimeout(function() {
            newdiv.parentElement.removeChild(newdiv)
          }, 3000);
        }
      };


      /**
       * Debounce function
       * From
       *   https://davidwalsh.name/javascript-debounce-function
       *
       * 
       */
      function debounce(func, wait, immediate) {
        var timeout;

        return function() {
          var context = this, args = arguments;

          var later = function() {
            timeout = null;

            if (!immediate) {
              func.apply(context, args);
            }
          };

          var callNow = immediate && !timeout;

          clearTimeout(timeout);

          timeout = setTimeout(later, wait);

          if (callNow) {
            func.apply(context, args);
          }
        };
      };

      // page related logic

      /*
        color each middleware with same callor
       */
      var randomColorCache = {};

      // list of "random colors" - made sure that they are readable on backgorund
      var colors = [
        '#6666ff', '#66b3ff', '#3fa9a9', '#53d273', '#0066cc', '#82a593',
        '#134d00', '#988e8e', '#eb8c83', '#dd4132', '#6e1a12', '#420f0b',
        '#638e83', '#003829', '#98988a', '#ff666a', '#0bb238', '#b2c2c2',
        '#ffa111', '#0c69b2', '#7bc5ff', '#908573', '#ffb84d', '#40a5f2',
        '#7e9a9a', '#bfa886', '#db9833', '#2a6592', '#8ec3eb', '#cc5520',
        '#997463', '#ff4441', '#244a26', '#55cc20', '#b2312f', '#ff5f5c',
        '#9e9e48', '#7aaacc', '#3c81b2', '#5eccc0', '#11b2a2', '#b200a4'
      ];

      /*
        Global datatables search history
        Hacky solution
       */
      var previousRoutes = [];

      // dataTables will be initialized into htis
      var routesTable,
          // datatables config 
          tableConfig = {
            // highlight filter search in datatable cells
            // https://github.com/julmot/datatables.mark.js/
            // highlightSearch is defined in index.blade.php
            mark: highlightSearch,
            dom: '<"top"flip>rt<"bottom"lip>',
            responsive: true,
            // regex is clunky, throws exceptions if invalid regex, will look into
            // this later
            // search: {
            //    regex: true
            // },
            // save table filters/order/visible columns between page reloads
            stateSave:  true,
            // default processing message
            oLanguage: {
              sProcessing: "Loading, please wait..."
            },
            // default order column - "Action" column
            order: [
              [8, "desc"]
            ],
            // items per page select
            lengthMenu: [
              // values
              [25, 50, 100, -1],
              // text
              [25, 50, 100, "All"]
            ],
            // default per page value
            pageLength:  25,
            searching:   true,
            // delay is quirky, doesn't look good with mark.js
            // searchDelay: 1000,
            processing:  true,
            order: [],
            // datatables data is loaded with external ajax function
            serverSide:  false,
            // column definitions
            columns: [
              {
                title:      "Method",
                data:       "method",
                name:       "method",
                className:  "dtc_method",
                render: function( data, type, row ) {
                  // data values are: POST, GET, PUT, PATCH...
                  return data.map(function(element) {
                    return '<div class="'+ element.toLowerCase()+' easy-box">'+element+'</div>';
                  }).join('');
                }
              },
              {
                title:      "Exists",
                data:       "exists",
                name:       "exists",
                className:  "dtc_valid",
                width:      "20px",
                render: function( data, type, row ) {
                  // reusing existing classes
                  var className = data ? 'post' : 'delete';
                  // text
                  var text      = data ? 'Yes'  : 'No';

                  return '<div class="'+className+' easy-box">'+text+'</div>';
                }
              },
              {
                title:      "Route",
                data:       "route",
                name:       "route",
                className:  "dtc_route",
                render: function( data, type, row ) {
                  /*
                    Find all {params} in the string and add coloring class
                   */
                  return data.split('/').map(function(element) {
                      if ( element.indexOf('?}') !== -1 ) {
                        return element.replace(/\{.*\?\}/g, "<strong class='optional-param'>$&</strong>");
                      }

                      if ( element.indexOf('}') !== -1 ) {
                        return element.replace(/\{.*?\}/g, "<strong class='required-param'>$&</strong>");
                      }

                      return element;
                    })
                    .join('/');
                }
              },
              {
                title:      "As",
                data:       "as",
                name:       "as",
                className:  "dtc_as",
              },
              {
                title:      "Parameters",
                data:       "parameters",
                name:       "parameters",
                className:  "dtc_middleware",
              },
              {
                title:      "#",
                data:       "parametersNumber",
                name:       "parametersNumber",
                className:  "dtc_parametersNumber",
                width:      "2%",
                render: function( data, type, row ) {
                  return row.parameters.split(',').length;
                }
              },
              {
                title:      "Middleware",
                data:       "middleware",
                name:       "middleware",
                className:  "dtc_middleware",
                render: function( data, type, row ) {
                  // split middleware list and add coloring for each one
                 return data.split(',').map(function(middlewareName) {
                      var currentColor = '';

                      if ( typeof randomColorCache[ middlewareName ] !== 'undefined' ) {
                        currentColor = randomColorCache[ middlewareName ];

                      } else {
                        currentColor = colors.shift();

                        randomColorCache[ middlewareName ] = currentColor;

                        colors.push( currentColor );
                      }

                      return '<span style="color: '+currentColor+';">'+middlewareName+'</span>';
                    })
                   .join(',');
                }
              },
              {
                title:      "#",
                data:       "middlewareNumber",
                name:       "middlewareNumber",
                className:  "dtc_middlewareNumber",
                width:      "1%",
                render: function( data, type, row ) {
                  // number of middlewares used on this route
                  return row.middleware.split(',').length;
                }
              },
              {
                title:      "Action",
                data:       "action",
                name:       "action",
                width:      "200px",
                className:  "dtc_action",
                render: function(data, type, row) {
                  // wrap "@" in span (different color)
                  var newData = data.split('@')
                                    .join('<span class="at">@</span>');

                  if (row.link) {
                    return '<a href="'+row.link+'" target="fakeFrame">'+newData+'</a>';
                  }

                  return newData;
                }
              },
              {
                title:     "Comment",
                className: "dtc_comment",
                orderable: false,
                data:      "docComment",
                width:     "1%",
                render: function( data, type, row) {
                  if (row.docComment === '') {
                    return '';
                  }

                  // create button to expand and show methods comment
                  // event attached on .expandComments
                  return '<span class="post easy-box pull-right clickable expandComments">View</span>';
                }
              },
            ],
            // called on every draw event of the datatables
            drawCallback: function( settings ) {
              toggleVisibleColumnsButtons(routesTable);
            }
        }

      /**
       * Toggle 'Visible columns' buttons depending on which columns are visible in
       * datatables
       * @param  object table - datatable
       * @param array columns - list of columns to toggle
       * @return undefined
       */
      function toggleVisibleColumnsButtons(table, columns) {
        if ( ! table ) {
          return;
        }

        var col = columns ? columns : table.columns()[0];

        col.forEach(function(columnNumber) {
          var column = table.column( columnNumber );

          $('.toggle-vis[data-column="'+columnNumber+'"]').toggleClass('visible', column.visible());
        });
      }

      // init datatable
      routesTable = $('#routesTable').DataTable( tableConfig );

      // load initial data
      loadTableAjax();

      /*
        toggle datatable visible columns and update button 'state'
       */
      $('.toggle-vis').on('click', function(e) {
        e.preventDefault();

        var columnNumber = $(this).attr('data-column');

        // toggle table column visibility
        toggleColumn( columnNumber, routesTable );

        // toggle 'Visible columns' button
        toggleVisibleColumnsButtons( routesTable );
      });

      /**
       * Toggle visibility for single table column
       * @param  int columnNumber - index 0 based
       * @return undefined
       */
      function toggleColumn( columnNumber, table ) {
        // Get the column API object
        var column = table.column( columnNumber );

        // Toggle the visibility
        column.visible( ! column.visible() );
      }

      /**
       * Attach 'Reload table' click event
       */
      $('#reloadRoutesButton').on('click', loadTableAjax);

      /**
       * Toggle comment visibility
       */
      $('#routesTable tbody').on('click', '.expandComments', function () {
        var tr      = $(this).closest('tr');
        var row     = routesTable.row( tr );

        var rowData = row.data();

        if ( row.child.isShown() ) {
          row.child.hide();

        } else {
          row.child( formatCommentFull(rowData) )
            .show();
        }
      });

      // prevent form submit, trigger button click (search will be done with ajax)
      $('#routeExistsForm').on('submit', function(event) {
        event.preventDefault();

        $('#routeExistsButton').trigger('click');
      });

      /*
        'Paste url here' search
       */
      $('#routeExistsButton').on('click', function() {
        var routeWithParams = $('#convertRouteInput').val();

        $("#routeExistsButton").toggleButton(false);

        $.ajax({
          type:     "GET",
          url:      routeExistUrl,
          dataType: 'json',
          data: {
            url: routeWithParams,
          }
        })
        .done(function( response ) {
          easy.info('Route found');

          // update search filter to show found route
          changeDatatableSearchFilterValue( response.action );
        })
        .fail(function() {
          easy.error('Route not found');

          changeDatatableSearchFilterValue( '' );
        })
        .always(function() {
          $("#routeExistsButton").toggleButton(true);
        });

      });

      /*
        Save previously searched routes
        Update values in 'Previous routes' search (select2)

        Save in history only 1 second (1000ms) after user stopped typing (with debounce())
       */
      $(document).on('keyup', '#routesTable_filter input', debounce(function() {
          var text = $(this).val();

          // get number of visible rows
          var numOfRowsVisible = routesTable.rows({filter: 'applied'})[0].length;

          // if no results or empty filter text
          // skip
          if ( numOfRowsVisible == 0 || ! text ) {
            return;
          }

          // if new entry is duplicate, remove it and add it to beginning of select2
          // remove previous duplicate
          previousRoutes = previousRoutes.filter(function(element) {
            return element.text !== text;
          });

          // add new value to select2
          previousRoutes.push({
            // timestamp as id
            id:   (new Date()).getTime(),
            text: text
          });

          setLocalstorage( previousRoutes );

          renderSelect2( previousRoutes );
        }, 1000)
      );

      /*
        When 'Previous search' selected, copy value to datatalbes filter
       */
      $(document).on('select2:select', '#previousRoutes', function(event) {
        var id = $(this).val();

        var previous = previousRoutes.filter(function(element) {
          return element.id == id;
        });

        if ( previous.length === 0 ) {
          // element not found
          return;
        }

        // copy value to search box - don't trigger `keyup` as we have event listener there
        $('#routesTable_filter input').val( previous[0].text );

        // manually trigger table search
        routesTable.search( previous[0].text ).draw();
      });

      /*
        We save search history into local storage and load that into select2
       */
      function renderSelect2(routes) {
        routes = routes.slice().reverse();

        $('#previousRoutes').empty().trigger('change');

        $('#previousRoutes')
          .select2({
            allowClear:  true,
            placeholder: 'Previous routes',
            data:        routes
          });

        if ( routes.length ) {
          $('#previousRoutes').select2('val', routes[ 0 ].id );
        }
      }

      previousRoutes = getLocalstorage();

      // init select 2
      renderSelect2( previousRoutes );

      /*
        Update datatalbes search filter value
       */
      function changeDatatableSearchFilterValue(text) {
        $('#routesTable_filter input')
            .val( text )
            .trigger('keyup');
      }

      /**
       * Show extended
       * from example: https://datatables.net/examples/api/row_details.html
       */
      function formatCommentFull(row) {
        return '<pre>'+row.docComment+'</pre>';
      }

      /**
       * Load routes data from the server
       * sort and search are handled by Datatable plugin on the FE
       */
      function loadTableAjax() {
        var $reloadRoutesButton = $('#reloadRoutesButton');
        $reloadRoutesButton.toggleButton(false);

        $.get(routesUrl, function(response) {
          loadTable(response);

          $reloadRoutesButton.toggleButton(true);
        });
      }

      /**
       * Insert data into datatable and re-render
       * @param array data - data loaded from the server
       */
      function loadTable(data) {
        // remove old data
        routesTable.clear();

        // add data that's loaded from the server
        routesTable.rows.add(data);

        // re-draw table
        routesTable.draw();
      }

      /*
        Save search filter into the local storage
       */
      function setLocalstorage( arr ) {
        localStorage.setItem(localStorageName, JSON.stringify(arr) );
      }

      /*
        Get all the search history from local storage
       */
      function getLocalstorage() {
        var arr = [];

        try {
          // JSON.parse - returns null if it was empty
          arr = JSON.parse( localStorage.getItem(localStorageName) );

        } catch (e) {
          console.log('EasyRoutes - Empty localStorage');
        }

        return arr ? arr : [];
      }

      // why is this here (I forgot :D)
      // var actionName = '';

      // // extend search function
      // // ?!?
      // $.fn.dataTable.ext.search.push(function( settings, data, dataIndex ) {
      //   var route = data[7];

      //   // show all
      //   if ( actionName === '' ) {
      //       return true;
      //   }

      //   return route === actionName;
      // });

    </script>
  </body>
</html>
