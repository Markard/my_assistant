'use strict';

var MaBudgetControllerModule = angular.module('MaBudgetControllerModule', [
    'MaService'
]);

MaBudgetControllerModule.controller('MaBudgetDashboardController', [
        '$scope',
        '$rootScope',
        'maPurchaseService',
        'maPurchaseListData',
        'maIncomeService',
        'maIncomeListData',
        '$modal',
        function ($scope, $rootScope, maPurchaseService, maPurchaseListData, maIncomeService, maIncomeListData, $modal) {
            $scope.loadingPhases = {
                purchasesLoading: false,
                incomeLoading: false
            };
            $scope.filters = {
                date: moment().format('YYYY-MM')
            };

            $scope.isAllItemsOpened = false;
            $scope.purchases = {
                info: maPurchaseListData.info,
                items: maPurchaseListData.items
            };
            maPurchaseListData.getData($scope.filters, function () {
                $scope.loadingPhases.purchasesLoading = true;
            });

            $scope.incomes = {
                info: maIncomeListData.info,
                items: maIncomeListData.items
            };
            maIncomeListData.getData($scope.filters, function () {
                $scope.loadingPhases.incomeLoading = true;
            });

            var initialize = true;
            $scope.$watch('filters.date', function (newValue, oldValue) {
                if (!initialize) {
                    maPurchaseListData.resetPagination();
                    maPurchaseListData.getData($scope.filters);
                    maIncomeListData.resetPagination();
                    maIncomeListData.getData($scope.filters);
                    $rootScope.$broadcast('currentMonthChanged');
                }
                initialize = false;
            });

            // ---------------------------------------------------------------------------------------------------------
            // Public methods
            // ---------------------------------------------------------------------------------------------------------

            /**
             * Purchase
             */

            $scope.openCreatePurchaseModal = function (id) {
                var modalInstance = $modal.open({
                    templateUrl: '/static/app/budget/templates/popups/purchaseEdit.html',
                    controller: 'maPurchaseController',
                    resolve: {
                        attributes: function () {
                            return {};
                        },
                        isNew: function () {
                            return true;
                        }
                    }
                });
            };

            $scope.openEditPurchaseModal = function (id) {
                maPurchaseService
                    .showDetails(id)
                    .then(function (response) {
                        $modal.open({
                            templateUrl: '/static/app/budget/templates/popups/purchaseEdit.html',
                            controller: 'maPurchaseController',
                            resolve: {
                                attributes: function () {
                                    return response;
                                },
                                isNew: function () {
                                    return false;
                                }
                            }
                        });
                    });
            };

            $scope.changePurchasePage = function () {
                maPurchaseListData.getData();
            };

            $scope.toggleDayVisibility = maPurchaseListData.toggleDayVisibility;
            $scope.toggleAllDaysVisibility = maPurchaseListData.toggleAllDaysVisibility;
            $scope.isAllDaysVisible = maPurchaseListData.daysVisibility.isAllDaysVisible;
            $scope.visibleDays = maPurchaseListData.daysVisibility.visibleDays;

            /**
             * Income
             */

            $scope.openCreateIncomeModal = function (id) {
                var modalInstance = $modal.open({
                    templateUrl: '/static/app/budget/templates/popups/incomeEdit.html',
                    controller: 'maIncomeController',
                    resolve: {
                        attributes: function () {
                            return {};
                        },
                        isNew: function () {
                            return true;
                        }
                    }
                });
            };

            $scope.openEditIncomeModal = function (id) {
                maIncomeService
                    .showDetails(id)
                    .then(function (response) {
                        $modal.open({
                            templateUrl: '/static/app/budget/templates/popups/incomeEdit.html',
                            controller: 'maIncomeController',
                            resolve: {
                                attributes: function () {
                                    return response;
                                },
                                isNew: function () {
                                    return false;
                                }
                            }
                        });
                    });
            };

            $scope.changeIncomePage = function () {
                maIncomeListData.getData();
            };
        }
    ]
);

MaBudgetControllerModule.controller('maPurchaseController', [
    '$scope',
    '$rootScope',
    'growl',
    'maPurchaseService',
    'maPurchaseListData',
    'maFormFactory',
    '$modalInstance',
    'attributes',
    'isNew',
    function ($scope, $rootScope, growl, maPurchaseService, maPurchaseListData, maFormFactory, $modalInstance, attributes, isNew) {
        $scope.form = new maFormFactory.createForm([
            {name: 'title'},
            {name: 'price'},
            {name: 'amount'},
            {name: 'bought_at', value: moment().format('YYYY-MM-DD')}
        ], isNew);
        $scope.form.setAttributes(attributes);

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.save = function () {
            var success = function (response) {
                $modalInstance.dismiss('cancel');
                growl.success(response.message);

                maPurchaseListData.showDay($scope.form.fields.bought_at.getValue());
                maPurchaseListData.getData();
                $rootScope.$broadcast('purchaseChanged');
            };

            $scope.form.clearFieldsErrors();
            if (isNew) {
                maPurchaseService
                    .store($scope.form)
                    .then(success);
            } else {
                maPurchaseService
                    .update(attributes.id, $scope.form)
                    .then(success);
            }
        };

        $scope.destroy = function () {
            $modalInstance.dismiss('cancel');
            $scope.form.clearFieldsErrors();
            maPurchaseService
                .destroy(attributes.id)
                .then(function (response) {
                    growl.success(response.message);
                    maPurchaseListData.getData();
                });
        };
    }
]);

MaBudgetControllerModule.controller('maIncomeController', [
    '$scope',
    '$rootScope',
    'growl',
    'maIncomeService',
    'maIncomeListData',
    'maFormFactory',
    '$modalInstance',
    'attributes',
    'isNew',
    function ($scope, $rootScope, growl, maIncomeService, maIncomeListData, maFormFactory, $modalInstance, attributes, isNew) {
        $scope.form = new maFormFactory.createForm([
            {name: 'title'},
            {name: 'price'},
            {name: 'date', value: moment().format('YYYY-MM-DD')}
        ], isNew);
        $scope.form.setAttributes(attributes);

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.save = function () {
            var success = function (response) {
                $modalInstance.dismiss('cancel');
                growl.success(response.message);
                maIncomeListData.getData();
                $rootScope.$broadcast('incomeChanged');
            };

            $scope.form.clearFieldsErrors();
            if (isNew) {
                maIncomeService
                    .store($scope.form)
                    .then(success);
            } else {
                maIncomeService
                    .update(attributes.id, $scope.form)
                    .then(success);
            }
        };

        $scope.destroy = function () {
            $modalInstance.dismiss('cancel');
            $scope.form.clearFieldsErrors();
            maIncomeService
                .destroy(attributes.id)
                .then(function (response) {
                    growl.success(response.message);
                    maIncomeListData.getData();
                });
        };
    }
]);