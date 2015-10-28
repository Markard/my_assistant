'use strict';

DirectiveModule.directive('maBudgetSummary', [
        '$rootScope',
        'maPurchaseListData',
        'maPurchaseService',
        'maIncomeListData',
        'maIncomeService',
        function ($rootScope, maPurchaseListData, maPurchaseService, maIncomeListData, maIncomeService) {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: 'static/shared/budgetSummary/view.html',
                controller: ['$scope', function ($scope) {
                    $scope.totalPurchase =
                        $scope.totalPurchaseForMonth =
                            $scope.totalIncome =
                                $scope.totalIncomeForMonth = 0;

                    updateTotalPurchaseSummary();
                    updateMonthPurchaseSummary();
                    updateTotalIncomeSummary();
                    updateMonthIncomeSummary();
                    /**
                     * -------------------------------------------------------------------------------------------------
                     * Private methods
                     * -------------------------------------------------------------------------------------------------
                     */
                    function updateTotalPurchaseSummary() {
                        maPurchaseService
                            .showSum()
                            .then(function (response) {
                                $scope.totalPurchase = response.data.sum;
                            });
                    }

                    function updateMonthPurchaseSummary() {
                        maPurchaseService
                            .showSum(maPurchaseListData.filters.date)
                            .then(function (response) {
                                $scope.totalPurchaseForMonth = response.data.sum;
                            });
                    }

                    function updateTotalIncomeSummary() {
                        maIncomeService
                            .showSum()
                            .then(function (response) {
                                $scope.totalIncome = response.data.sum;
                            });
                    }

                    function updateMonthIncomeSummary() {
                        maIncomeService
                            .showSum(maIncomeListData.filters.date)
                            .then(function (response) {
                                $scope.totalIncomeForMonth = response.data.sum;
                            });
                    }

                    /**
                     * -------------------------------------------------------------------------------------------------
                     * Events
                     * -------------------------------------------------------------------------------------------------
                     */
                    $rootScope.$on('currentMonthChanged', function () {
                        updateMonthPurchaseSummary();
                        updateMonthIncomeSummary();
                    });

                    $rootScope.$on('purchaseChanged', function () {
                        updateTotalPurchaseSummary();
                        updateMonthPurchaseSummary();
                    });

                    $rootScope.$on('incomeChanged', function () {
                        updateTotalIncomeSummary();
                        updateMonthIncomeSummary();
                    });
                }],
                link: function (scope, element, attributes) {
                }
            };
        }
    ]
);
