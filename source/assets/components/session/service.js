'use strict';

var MaSessionServiceModule = angular.module('MaSessionServiceModule', [
    'MaService',
    'ui.router',
    'angular-storage'
]);

MaSessionServiceModule.factory('maSessionService', [
    '$http',
    'maRequestHandler',
    'store',
    '$state',
    'maCurrentUser',
    'maApiBaseUrlV1',
    function ($http, maRequestHandler, store, $state, maCurrentUser, maApiBaseUrlV1) {

        // Return public API
        return {
            login: login,
            logout: logout
        };

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        function login(form) {
            return $http({
                method: 'POST',
                url: maApiBaseUrlV1 + '/get_token',
                data: form.toArray()
            }).then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function logout() {
            store.remove('jwt');
            maCurrentUser.setStatus(USER_STATUS_NON_AUTHENTICATED);
            maCurrentUser.resetForm();
            $state.go('login');
        }
    }
]);