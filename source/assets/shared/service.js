'use strict';

var DEFAULT_DATE_FORMAT = 'YYYY-MM-DD';

var FIELD_TYPE_STRING = 'string';
var FIELD_TYPE_TIMESTAMP = 'timestamp';
var FIELD_TYPE_REPEATED = 'repeated';

var MaService = angular.module('MaService', []);

var COMMON_FIELD = '_common_field';

MaService.factory('maRequestHandler', [
    '$rootScope',
    '$q',
    'growl',
    function ($rootScope, $q, growl) {

        // Return public API
        return {
            handleSuccess: handleSuccess,
            getErrorHandler: getErrorHandler
        };

        // -------------------------------------------------------------------------------------------------------------
        // Private methods
        // -------------------------------------------------------------------------------------------------------------

        function handleSuccess(response) {
            return response.data;
        }

        /**
         * If method receive reason = formValidationFailed then it use the form for handling this error.
         * Else if method receive reason  = error then it will use growl to show message.
         *
         * @param form optional
         * @param customHandler optional
         * @return {errorHandler}
         */
        function getErrorHandler(form, customHandler) {
            return errorHandler;

            function errorHandler(response) {
                if (!angular.isObject(response.data)) {
                    growl.error('An unknown error occurred.');

                    return $q.reject();
                }

                var REASON_ERROR = 'error';
                var REASON_FORM_VALIDATION_FAILED = 'formValidationFailed';

                switch (response.data.reason) {
                    case REASON_ERROR:
                        growl.error(response.data.message);

                        return $q.reject();
                    case REASON_FORM_VALIDATION_FAILED:
                        if (form) {
                            handleFormErrors(response.data.data, form);
                            $rootScope.$broadcast('redraw-form-errors');
                        } else {
                            growl.error('Form validation fails.');
                        }

                        return $q.reject();
                    default:
                        return $q.reject(response.data);
                }
            }
        }

        function handleFormErrors(data, form) {
            if (data.global !== undefined) {
                $.each(data.global, function (i, message) {
                    form.addError(COMMON_FIELD, message);
                });
            }

            if (data.fields !== undefined) {
                $.each(data.fields, function (fieldName, message) {
                    var match = fieldName.match("^([a-zA-Z0-9_]+)(\.|)");
                    if (match[1] !== undefined) {
                        form.addError(match[1], message);
                    }
                })
            }
        }
    }
]);

MaService.factory('maFormFactory', [
    'maFieldFactory',
    function (maFieldFactory) {

        return {
            createForm: createForm
        };

        // -------------------------------------------------------------------------------------------------------------
        // Private methods
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Create Form object from list of properties names.
         *
         * @param _fields
         * @param _isNew
         */
        function createForm(_fields, _isNew) {

            var self = this;

            // ---------------------------------------------------------------------------------------------------------
            // Construct
            // ---------------------------------------------------------------------------------------------------------

            this.isNew = _isNew || _isNew === undefined;
            this.fields = {};

            $.each(_fields, function (index, field) {
                self.fields[field.name] = new maFieldFactory.createField(field.name, field.type, field.value, field.options);
            });
            self.fields[COMMON_FIELD] = new maFieldFactory.createField(
                COMMON_FIELD, FIELD_TYPE_STRING
            );

            // Return public API
            this.addError = addError;
            this.clearFieldsErrors = clearFieldsErrors;
            this.toArray = toArray;
            this.setAttributes = setAttributes;
            this.reset = reset;

            // ---------------------------------------------------------------------------------------------------------
            // Private methods
            // ---------------------------------------------------------------------------------------------------------

            /**
             * @param fieldName
             * @param message
             *
             * @return bool
             */
            function addError(fieldName, message) {
                if (self.fields[fieldName] !== undefined) {
                    self.fields[fieldName].addError(message);
                    return true;
                }
                return false;
            }

            /**
             * @param name
             * @private
             * @returns null|object
             */
            function getFieldObjectByName(name) {
                var result = null;

                $.each(self.fields, function (index, field) {
                    if (field.name === name) {
                        result = field;
                    }
                });

                return result;
            }

            function clearFieldsErrors() {
                $.each(self.fields, function (index, field) {
                    field.resetErrors();
                });
            }

            function toArray() {
                var result = {};

                $.each(self.fields, function (fieldName, field) {
                    if (fieldName !== COMMON_FIELD) {
                        result[fieldName] = field.getValue();
                    }
                });

                return result;
            }

            function setAttributes(attributes) {
                $.each(attributes, function (fieldName, value) {
                    if (self.fields[fieldName] !== undefined && self.fields[fieldName] !== COMMON_FIELD) {
                        self.fields[fieldName].setValue(value);
                    }
                });
            }

            function reset() {
                $.each(self.fields, function (index, field) {
                    field.setValue(null);
                    field.resetErrors();
                });
            }

            return this;
        }
    }
]);

MaService.factory('maFieldFactory', [
    function () {

        return {
            createField: createField
        };

        // -------------------------------------------------------------------------------------------------------------
        // Private methods
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Create Form object from list of properties names.
         *
         * @param _name string
         * @param _type string (optional)
         * @param _value mixed (optional)
         * @param _options object (optional)
         */
        function createField(_name, _type, _value, _options) {

            var self = this;

            // ---------------------------------------------------------------------------------------------------------
            // Construct
            // ---------------------------------------------------------------------------------------------------------
            this.name = _name;
            this.errors = [];

            if (_value !== undefined) {
                setValue(_value);
            } else {
                this.value = null;
            }

            switch (_type) {
                case FIELD_TYPE_STRING:
                default:
                    this.type = FIELD_TYPE_STRING;
                    break;
                case FIELD_TYPE_TIMESTAMP:
                    this.type = FIELD_TYPE_TIMESTAMP;
                    this.format = null;
                    if (_options.format !== undefined) {
                        this.format = _options.format;
                    } else if (this.type === FIELD_TYPE_TIMESTAMP) {
                        this.format = DEFAULT_DATE_FORMAT;
                    }
                    break;
                case FIELD_TYPE_REPEATED:
                    this.type = FIELD_TYPE_REPEATED;
                    this.value = {};
                    if (_options !== undefined && _options.firstName !== undefined) {
                        this.value[_options.firstName] = null;
                    } else {
                        this.value['first'] = null;
                    }

                    if (_options !== undefined && _options.secondName !== undefined) {
                        this.value[_options.secondName] = null;
                    } else {
                        this.value['second'] = null;
                    }

            }

            if (_type === undefined) {
                this.type = FIELD_TYPE_STRING;
            } else {
                this.type = _type;
            }

            // Return public API
            this.resetErrors = resetErrors;
            this.addError = addError;
            this.hasErrors = hasErrors;
            this.setValue = setValue;
            this.getValue = getValue;

            // ---------------------------------------------------------------------------------------------------------
            // Private methods
            // ---------------------------------------------------------------------------------------------------------

            function resetErrors() {
                self.errors = [];
            }

            function addError(message) {
                self.errors.push(message);
            }

            function hasErrors() {
                return self.errors.length > 0
            }

            function setValue(_value) {
                switch (self.type) {
                    case FIELD_TYPE_TIMESTAMP:
                        self.value = moment.unix(_value).format(self.format);
                        break;
                    case FIELD_TYPE_STRING:
                    default:
                        self.value = _value

                }
            }

            function getValue() {
                switch (self.type) {
                    case FIELD_TYPE_REPEATED:
                        var result = {};
                        $.each(self.value, function (index, value) {
                            result[index] = value;
                        });
                        return result;
                    case FIELD_TYPE_TIMESTAMP:
                        var localUtcOffset = moment('2015-08-06').utcOffset();
                        return moment(self.value).add(localUtcOffset, 'm').unix();
                    case FIELD_TYPE_STRING:
                    default:
                        return self.value;
                }
            }

            return this;
        }
    }
]);

MaService.factory('maSecurityFirewall', [
    '$state',
    'maCurrentUser',
    function ($state, maCurrentUser) {

        // Return public API
        return {
            protect: protect
        };

        // ---------------------------------------------------------------------------------------------------------
        // Public methods
        // ---------------------------------------------------------------------------------------------------------

        function protect(stateData, event) {
            var status = maCurrentUser.getStatus();

            if (stateData.requiresConfirmationStatus
                && status !== USER_STATUS_REQUIRE_CONFIRMATION) {
                preventEvent(event);
                $state.go('home');
            } else if (stateData.requiresLogin
                && status === USER_STATUS_NON_AUTHENTICATED) {
                preventEvent(event);
                $state.go('login');
            } else if ((stateData.requiresNonAuthentication || stateData.requiresConfirmationStatus)
                && status === USER_STATUS_AUTHENTICATED) {
                preventEvent(event);
                $state.go('budget_dashboard');
            }
        }

        // ---------------------------------------------------------------------------------------------------------
        // Private methods
        // ---------------------------------------------------------------------------------------------------------

        function preventEvent(event) {
            if (event) {
                event.preventDefault();
            }
        }
    }
]);

MaService.factory('maApiBaseUrlV1', ['$location', function ($location) {
    return $location.protocol() + '://' + $location.host() + '/api/v1';
}]);