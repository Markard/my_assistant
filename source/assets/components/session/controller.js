'use strict';

var MaSessionControllerModule = angular.module('MaSessionControllerModule', [
    'MaSessionServiceModule',
    'MaService',
    'angular-jwt'
]);

MaSessionControllerModule.controller('maSessionCreateController', [
        '$scope',
        '$rootScope',
        'maFormFactory',
        'maSessionService',
        '$state',
        'store',
        'maCurrentUser',
        function ($scope, $rootScope, maFormFactory, maSessionService, $state, store, maCurrentUser) {

            $scope.form = new maFormFactory.createForm([
                {name: 'username'},
                {name: 'password'}
            ], false);

            // ---------------------------------------------------------------------------------------------------------
            // Public methods
            // ---------------------------------------------------------------------------------------------------------

            $scope.login = function () {
                $scope.form.clearFieldsErrors();
                maSessionService
                    .login($scope.form)
                    .then(function (response) {
                        store.set('jwt', response.token);
                        maCurrentUser.fillUserFormWithToken(response.token);
                        maCurrentUser.setStatus(USER_STATUS_AUTHENTICATED);
                        $state.go('budget_dashboard');
                    }, function (responseData) {
                        if (responseData && responseData.reason === 'emailNotConfirmed') {
                            $rootScope.$broadcast('redraw-form-errors');
                            maCurrentUser.setStatus(USER_STATUS_REQUIRE_CONFIRMATION);
                            maCurrentUser.setEmail(responseData.data.email);
                            $state.go('confirm_email', {message: responseData.message, type: MESSAGE_TYPE_WARNING});
                        }
                    });
            };
        }
    ]
);

MaSessionControllerModule.controller('maSessionDestroyController', [
        'maSessionService',
        function (maSessionService) {
            maSessionService.logout();
        }
    ]
);