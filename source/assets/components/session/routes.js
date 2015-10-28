'use strict';

RouteModule.config(['$stateProvider', function ($stateProvider) {
        $stateProvider
            .state('login', {
                url: '/login',
                views: {
                    header: {
                        templateUrl: '/static/app/general/templates/nonAuthNavbar.html'
                    },
                    content: {
                        templateUrl: '/static/app/session/templates/loginForm.html',
                        controller: 'maSessionCreateController'
                    }
                },
                data: {
                    pageTitle: 'Login',
                    requiresNonAuthentication: true
                }
            })
            .state('logout', {
                url: '/logout',
                views: {
                    header: {},
                    content: {
                        controller: 'maSessionDestroyController'
                    }
                }
            });
    }]
);