'use strict';

DirectiveModule.directive('maMessageBox', [
        '$filter',
        function ($filter) {
            return {
                restrict: 'E',
                replace: true,
                transclude: true,
                templateUrl: 'static/shared/messageBox/view.html',
                scope: {
                    ngModel: '='
                },
                require: 'ngModel',
                link: function (scope, element, attributes, ngModel) {

                    // -------------------------------------------------------------------------------------------------
                    // Private methods
                    // -------------------------------------------------------------------------------------------------

                    function updateData(type) {
                        switch (type) {
                            case MESSAGE_TYPE_DANGER:
                                scope.titleCssClass = 'text-danger';
                                scope.title = $filter('translate')('ERROR');
                                scope.cssClass = 'alert-danger';
                                break;
                            case MESSAGE_TYPE_PRIMARY:
                                scope.titleCssClass = 'text-primary';
                                scope.title = $filter('translate')('INFORMATION');
                                scope.cssClass = 'alert-primary';
                                break;
                            case MESSAGE_TYPE_SUCCESS:
                                scope.titleCssClass = 'text-success';
                                scope.title = $filter('translate')('SUCCESS');
                                scope.cssClass = 'alert-success';
                                break;
                            case MESSAGE_TYPE_WARNING:
                                scope.titleCssClass = 'text-warning';
                                scope.title = $filter('translate')('WARNING');
                                scope.cssClass = 'alert-warning';
                                break;
                            case MESSAGE_TYPE_INFO:
                            default:
                                scope.titleCssClass = 'text-info';
                                scope.title = $filter('translate')('INFORMATION');
                                scope.cssClass = 'alert-info';
                        }

                    }

                    // -------------------------------------------------------------------------------------------------
                    // Watchers
                    // -------------------------------------------------------------------------------------------------

                    scope.$watch(function () {
                        return ngModel.$modelValue;
                    }, function (newValue, oldValue) {
                        updateData(newValue);
                    });
                }
            };
        }
    ]
);

