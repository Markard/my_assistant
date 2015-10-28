'use strict';

DirectiveModule.directive('maDatePicker', [
        function () {
            return {
                restrict: 'E',
                templateUrl: 'static/shared/datePicker/view.html',
                scope: {
                    ngModel: '=',
                    dateFormat: '@dateFormat',
                    datepickerMinMode: '@datepickerMinMode',
                    datepickerMaxMode: '@datepickerMaxMode',
                    datepikerMode: '@datepikerMode',
                    inputDateFormat: '@inputDateFormat'
                },
                require: 'ngModel',
                link: function (scope, element, attr, ngModel) {
                    var initializing = true;

                    if (scope.dateFormat === undefined) {
                        scope.dateFormat = 'YYYY-MM-DD';
                    }
                    if (scope.datepickerMinMode === undefined) {
                        scope.datepickerMinMode = 'day';
                    }
                    if (scope.datepickerMaxMode === undefined) {
                        scope.datepickerMaxMode = 'year';
                    }
                    if (scope.datepikerMode === undefined) {
                        scope.datepikerMode = 'day';
                    }

                    // -------------------------------------------------------------------------------------------------
                    // Watchers
                    // -------------------------------------------------------------------------------------------------

                    scope.$watch(function () {
                        return ngModel.$modelValue;
                    }, function (currentValue) {
                        var newValue = moment(new Date(ngModel.$viewValue)).format(scope.dateFormat);

                        if (newValue != currentValue) {
                            ngModel.$setViewValue(newValue);
                        }
                    });
                },
                controller: ['$scope', function ($scope) {
                    $scope.datePickerOptions = {
                        isOpened: false,
                        startingDay: 1
                    };

                    $scope.openPopup = function ($event) {
                        $event.preventDefault();
                        $event.stopPropagation();

                        $scope.datePickerOptions.isOpened = true;
                    };
                }]
            };
        }
    ]
);