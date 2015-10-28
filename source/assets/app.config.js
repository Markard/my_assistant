'use strict';

var Config = angular.module('Config', [
    'ui.router',
    'angular-jwt',
    'angular-storage',
    'angular-growl'
]);

Config
    .config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }]);

Config
    .config(['growlProvider', function (growlProvider) {
        growlProvider.globalTimeToLive(5000);
        growlProvider.globalDisableCountDown(true);
        growlProvider.globalPosition('top-right');
    }]);

Config
    .config([
        '$httpProvider',
        'jwtInterceptorProvider',
        function ($httpProvider, jwtInterceptorProvider) {
            jwtInterceptorProvider.tokenGetter = ['store', function (store) {
                return store.get('jwt');
            }];

            $httpProvider.interceptors.push('jwtInterceptor');
            $httpProvider.interceptors.push([
                '$q',
                '$injector',
                function ($q, $injector) {
                    return {
                        'responseError': function (response) {
                            if (response.status === 401 || response.status === 403) {
                                $injector.get('$state').transitionTo('logout');
                            }
                            return $q.reject(response);
                        }
                    };
                }
            ]);
        }
    ]);

Config
    .run([
        '$rootScope',
        'maSecurityFirewall',
        function ($rootScope, maSecurityFirewall) {
            $rootScope.$on('$stateChangeStart', function (e, to) {
                if (to.data) {
                    maSecurityFirewall.protect(to.data, e)
                }
            });
        }
    ]);
