'use strict';

DirectiveModule.directive('maBootstrapValidation', [function () {
        return {
            restrict: 'A',
            scope: {
                ngModel: '='
            },
            require: 'ngModel',
            link: function (scope, element, attr, ngModel) {

                // -----------------------------------------------------------------------------------------------------
                // Listeners
                // -----------------------------------------------------------------------------------------------------

                scope.$on('redraw-form-errors', function (event) {
                    _removeErrors();
                    if (ngModel.$modelValue.hasErrors()) {
                        element.addClass('has-error');
                        _drawErrors(ngModel.$modelValue.errors);
                    } else {
                        element.removeClass('has-error');
                    }
                });

                // -----------------------------------------------------------------------------------------------------
                // Private methods
                // -----------------------------------------------------------------------------------------------------

                function _drawErrors(errors) {
                    $.each(errors, function (index, message) {
                        var errorMessagesContainer
                            = '<div class="tb-validation-errors-container mt-10 col-md-12">'
                            + '<div class="form-alert alert alert-danger alert-dismissible" role="alert">'
                            + '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
                            + '<span aria-hidden="true">&times;</span>'
                            + '</button>'
                            + '<strong>Error: </strong>'
                            + '<i>' + message + '</i>'
                            + '</div>'
                            + '</div>';
                        $(element).append(errorMessagesContainer);
                    });
                }

                function _removeErrors() {
                    $(element).find('.tb-validation-errors-container').remove();
                }
            }
        };
    }]
);