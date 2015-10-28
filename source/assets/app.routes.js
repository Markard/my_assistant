'use strict';

var RouteModule = angular
    .module('RouteModule', [
        'ui.router',
        //------------------------------------------------------------------------------------------------------------------
        // Controllers
        //------------------------------------------------------------------------------------------------------------------
        'MaBudgetControllerModule',
        'MaSessionControllerModule',
        'MaUserControllerModule'
    ]);

RouteModule.config(['$urlRouterProvider', '$stateProvider', function ($urlRouterProvider, $stateProvider) {
    $urlRouterProvider.otherwise('/');
}
]);
