'use strict';

var MaUsersControllers = angular.module('MaUserControllerModule', [
    'MaService',
    'MaUserService'
]);

MaUsersControllers.controller('maUserRegistrationController', [
        '$scope',
        'maFormFactory',
        'maUserService',
        '$state',
        'maCurrentUser',
        function ($scope, maFormFactory, maUserService, $state, maCurrentUser) {

            $scope.form = new maFormFactory.createForm([
                {name: 'username'},
                {name: 'email'},
                {name: 'password', type: 'repeated'}
            ], false);

            // ---------------------------------------------------------------------------------------------------------
            // Public methods
            // ---------------------------------------------------------------------------------------------------------

            $scope.register = function () {
                $scope.form.clearFieldsErrors();
                maUserService
                    .registration($scope.form)
                    .then(function (response) {
                        maCurrentUser.fillUserFormFromForm($scope.form);
                        maCurrentUser.setStatus(USER_STATUS_REQUIRE_CONFIRMATION);
                        $state.go('confirm_email', {
                            'message': response.message,
                            'type': MESSAGE_TYPE_INFO
                        });
                    });
            };
        }
    ]
);
MaUsersControllers.controller('maUserConfirmEmailController', [
        '$scope',
        '$stateParams',
        'maFormFactory',
        'maUserService',
        '$state',
        'growl',
        'store',
        'maCurrentUser',
        function ($scope, $stateParams, maFormFactory, maUserService, $state, growl, store, maCurrentUser) {

            var currentUserEmail = maCurrentUser.getEmail();

            if (!currentUserEmail) {
                $state.go('home');
            }

            $scope.form = new maFormFactory.createForm([
                {name: 'email', value: currentUserEmail},
                {name: 'confirmation_code'}
            ], false);

            $scope.message = {
                type: $stateParams.type,
                value: $stateParams.message
            };

            // ---------------------------------------------------------------------------------------------------------
            // Public methods
            // ---------------------------------------------------------------------------------------------------------

            $scope.confirm = function () {
                $scope.form.clearFieldsErrors();
                maUserService
                    .confirm($scope.form)
                    .then(function (response) {
                        var token = response.data.token;

                        store.set('jwt', token);
                        maCurrentUser.fillUserFormWithToken(token);
                        maCurrentUser.setStatus(USER_STATUS_AUTHENTICATED);
                        $state.go('budget_dashboard');
                        growl.success(response.message);
                    });
            };

            $scope.resendConfirmationCode = function () {
                maUserService
                    .resendConfirmationCode($scope.form.fields.email.value)
                    .then(function (response) {
                        growl.success(response.message);
                    }, function (responseData) {
                        if (responseData && responseData.reason === 'resendTimeout') {
                            growl.error(responseData.message);
                        }
                    })
            }
        }
    ]
);