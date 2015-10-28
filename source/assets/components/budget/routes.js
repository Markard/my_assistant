'use strict';

RouteModule.config(['$stateProvider', function ($stateProvider) {
        $stateProvider
            .state('budget_dashboard', {
                url: '/budget',
                views: {
                    header: {
                        templateUrl: '/static/app/general/templates/authNavbar.html'
                    },
                    content: {
                        templateUrl: '/static/app/budget/templates/list.html',
                        controller: 'MaBudgetDashboardController'
                    }
                },
                data: {
                    pageTitle: 'Budget',
                    requiresLogin: true
                }
            });
    }]
);