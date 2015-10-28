'use strict';

var MaUserService = angular.module('MaUserService', [
    'MaService'
]);

MaUserService.factory('maUserService', [
    '$http',
    'maRequestHandler',
    'maApiBaseUrlV1',
    function ($http, maRequestHandler, maApiBaseUrlV1) {

        var BASE_URL = '/app_dev.php/api/v1/users';

        // Return public API
        return {
            store: store,
            registration: registration,
            confirm: confirm,
            resendConfirmationCode: resendConfirmationCode
        };

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        function store(form) {
            return $http({
                method: 'POST',
                url: maApiBaseUrlV1 + '/users',
                data: form.toArray()
            }).then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function registration(form) {
            return $http({
                method: 'POST',
                url: maApiBaseUrlV1 + '/users/registration',
                data: form.toArray()
            }).then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function confirm(form) {
            return $http({
                method: 'DELETE',
                url: maApiBaseUrlV1 + '/email/' + form.fields.email.value + '/confirm/' + form.fields.confirmation_code.value
            }).then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function resendConfirmationCode(email) {
            return $http({
                method: 'PUT',
                url: maApiBaseUrlV1 + '/email/' + email + '/resend_confirmation_code'
            }).then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }
    }
]);

MaUserService.factory('maCurrentUser', [
    'store',
    'maFormFactory',
    'jwtHelper',
    function (store, maFormFactory, jwtHelper) {

        var status;
        var form;

        initialize();

        // Return public API
        return {
            getStatus: getStatus,
            setStatus: setStatus,
            fillUserFormFromForm: fillUserFormFromForm,
            fillUserFormWithToken: fillUserFormWithToken,
            resetForm: resetForm,
            setEmail: setEmail,
            getEmail: getEmail
        };

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        function getStatus() {
            return status;
        }

        function setStatus(_status) {
            if ([
                    USER_STATUS_AUTHENTICATED,
                    USER_STATUS_NON_AUTHENTICATED,
                    USER_STATUS_REQUIRE_CONFIRMATION
                ].indexOf(_status) === -1) {
                throw new Error('Invalid user status was set. You tried to set status: ' + _status);
            }

            status = _status;
        }

        function fillUserFormFromForm(_form) {
            if (_form.fields.username) {
                form.fields.username.value = _form.fields.username.value;
            }

            if (_form.fields.email) {
                form.fields.email.value = _form.fields.email.value;
            }
        }

        function fillUserFormWithToken(token) {
            var payload = jwtHelper.decodeToken(token);

            form.fields.username.value = payload.username;
            form.fields.email.value = payload.email;
        }

        function resetForm() {
            form.reset();
        }

        function setEmail(email) {
            form.fields.email.value = email;
        }

        function getEmail() {
            return form.fields.email.value;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Private methods
        // -------------------------------------------------------------------------------------------------------------

        function initialize() {
            var token = store.get('jwt');

            form = new maFormFactory.createForm([
                {name: 'username'},
                {name: 'email'},
                {name: 'password', type: 'repeated'}
            ], false);

            if (token && !jwtHelper.isTokenExpired(token)) {
                setStatus(USER_STATUS_AUTHENTICATED);
                fillUserFormWithToken(token);
            } else {
                setStatus(USER_STATUS_NON_AUTHENTICATED);
            }
        }
    }
]);