'use strict';

RouteModule.config(['$stateProvider', function ($stateProvider) {
        $stateProvider
            .state('home', {
                url: '/',
                views: {
                    header: {
                        templateUrl: '/static/app/general/templates/nonAuthNavbar.html'
                    },
                    content: {
                        templateUrl: '/static/app/general/templates/home.html'
                    }
                },
                data: {
                    pageTitle: 'My Assistant',
                    requiresNonAuthentication: true
                }
            });
    }]
);