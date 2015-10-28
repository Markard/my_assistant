'use strict';

RouteModule.config(['$stateProvider', function ($stateProvider) {
        $stateProvider
            .state('registration', {
                url: '/registration',
                views: {
                    header: {
                        templateUrl: '/static/app/general/templates/nonAuthNavbar.html'
                    },
                    content: {
                        templateUrl: '/static/app/user/templates/userRegistration.html',
                        controller: 'maUserRegistrationController'
                    }
                },
                data: {
                    pageTitle: 'Registration',
                    requiresNonAuthentication: true
                }
            })
            .state('confirm_email', {
                url: '/confirm_email',
                params: {
                    type: MESSAGE_TYPE_INFO,
                    message: ''
                },
                views: {
                    header: {
                        templateUrl: '/static/app/general/templates/nonAuthNavbar.html'
                    },
                    content: {
                        templateUrl: '/static/app/user/templates/confirmEmail.html',
                        controller: 'maUserConfirmEmailController'
                    }
                },
                data: {
                    pageTitle: 'Confirm Email',
                    requiresConfirmationStatus: true
                }
            });
    }]
);