'use strict';

var MaBudgetServiceModule = angular.module('MaBudgetServiceModule', []);

MaBudgetServiceModule.factory('maPurchaseService', [
    '$http',
    'maRequestHandler',
    'maApiBaseUrlV1',
    function ($http, maRequestHandler, maApiBaseUrlV1) {

        var BASE_URL = maApiBaseUrlV1 + '/purchases';

        // Return public API
        return {
            showList: showList,
            showDetails: showDetails,
            showSum: showSum,
            destroy: destroy,
            store: store,
            update: update
        };

        // -----------------------------------------------------------------------------------------------------------------
        // Public methods
        // -----------------------------------------------------------------------------------------------------------------

        function showList(limit, page, sort, direction, dimension, filters) {
            if (dimension === undefined) {
                dimension = 'purchase';
            }

            var params = {
                limit: limit,
                page: page,
                sort: sort,
                direction: direction,
                dimension: dimension
            };

            $.each(filters, function (index, element) {
                params[index] = element;
            });

            var request = $http({
                method: 'GET',
                url: BASE_URL,
                params: params
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }

        function showDetails(id) {
            var request = $http({
                method: 'GET',
                url: BASE_URL + '/' + id
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }

        function showSum(date) {
            var params = {};

            if (date !== undefined) {
                params['date'] = date;
            }

            var request = $http({
                method: 'GET',
                url: BASE_URL + '/sum',
                params: params
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }

        function store(form) {
            var request = $http({
                method: 'POST',
                url: BASE_URL,
                data: form.toArray()
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function update(id, form) {
            var request = $http({
                method: 'PUT',
                url: BASE_URL + '/' + id,
                data: form.toArray()
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function destroy(id) {
            var request = $http({
                method: 'DELETE',
                url: BASE_URL + '/' + id
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }
    }
]);

MaBudgetServiceModule.factory('maPurchaseListData', [
    '$rootScope',
    'maPurchaseService',
    function ($rootScope, maPurchaseService) {
        var result = {};
        var sort = 'bought_at';
        var direction = 'DESC';

        result.daysVisibility = {
            visibleDays: {},
            isAllDaysVisible: false
        };

        result.filters = {};
        result.items = [];
        result.info = {
            sum: 0,
            currentPage: 1,
            totalItems: 0,
            itemsPerPage: 10
        };

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        result.updateDaysVisibilityStatus = function () {
            var isAllDaysVisible = true;

            $.each(result.items, function (day, element) {
                isAllDaysVisible = isAllDaysVisible && result.daysVisibility.visibleDays[day];
            });

            result.daysVisibility.isAllDaysVisible = isAllDaysVisible;
        };

        result.showDay = function (day) {
            result.daysVisibility.visibleDays[day] = true;
        };

        result.toggleDayVisibility = function (day) {
            result.daysVisibility.visibleDays[day] = !result.daysVisibility.visibleDays[day];
            result.updateDaysVisibilityStatus();
        };

        result.toggleAllDaysVisibility = function () {
            $.each(result.items, function (index, element) {
                result.daysVisibility.visibleDays[element.day] = !result.daysVisibility.isAllDaysVisible;
            });

            result.daysVisibility.isAllDaysVisible = !result.daysVisibility.isAllDaysVisible;
        };

        /**
         * @param _filters array of objects with fields: {name: 'name', value: 'value'}
         * @param success
         */
        result.getData = function (_filters, success) {
            if (_filters !== undefined) {
                result.filters = _filters
            }

            maPurchaseService
                .showList(result.info.itemsPerPage, result.info.currentPage, sort, direction, 'day', result.filters)
                .then(function (response) {
                    var groupedItems = _groupByDays(response.items);
                    angular.copy(groupedItems, result.items);

                    result.info.currentPage = response.page;
                    result.info.totalItems = response.total_count;
                    result.info.itemsPerPage = response.num_items_per_page;

                    if ($.isFunction(success)) {
                        success(response);
                    }
                });
        };

        result.resetPagination = function () {
            result.info.currentPage = 1;
        };

        // -------------------------------------------------------------------------------------------------------------
        // Private methods
        // -------------------------------------------------------------------------------------------------------------

        function _groupByDays(days) {
            var result = [];

            $.each(days, function (index, purchases) {
                var dayInfo = {
                    rows: [],
                    sum: 0,
                    day: purchases[0].bought_at
                };

                $.each(purchases, function (purchaseIndex, purchase) {
                    dayInfo.sum += parseFloat(purchase.price);
                    dayInfo.rows.push(purchase);
                });

                result.push(dayInfo);
            });

            return result;
        }

        // Return public API
        return result;
    }
]);

MaBudgetServiceModule.factory('maIncomeService', [
    '$http',
    'maRequestHandler',
    'maApiBaseUrlV1',
    function ($http, maRequestHandler, maApiBaseUrlV1) {

        var BASE_URL = maApiBaseUrlV1 + '/incomes';

        // Return public API
        return {
            showList: showList,
            showDetails: showDetails,
            showSum: showSum,
            destroy: destroy,
            store: store,
            update: update
        };

        // -----------------------------------------------------------------------------------------------------------------
        // Public methods
        // -----------------------------------------------------------------------------------------------------------------

        function showList(limit, page, sort, direction, filters) {
            var params = {
                limit: limit,
                page: page,
                sort: sort,
                direction: direction
            };

            $.each(filters, function (index, element) {
                params[index] = element;
            });

            var request = $http({
                method: 'GET',
                url: BASE_URL,
                params: params
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }

        function showDetails(id) {
            var request = $http({
                method: 'GET',
                url: BASE_URL + '/' + id
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }

        function showSum(date) {
            var params = {};

            if (date !== undefined) {
                params['date'] = date;
            }

            var request = $http({
                method: 'GET',
                url: BASE_URL + '/sum',
                params: params
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }

        function store(form) {
            var request = $http({
                method: 'POST',
                url: BASE_URL,
                data: form.toArray()
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function update(id, form) {
            var request = $http({
                method: 'PUT',
                url: BASE_URL + '/' + id,
                data: form.toArray()
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler(form));
        }

        function destroy(id) {
            var request = $http({
                method: 'DELETE',
                url: BASE_URL + '/' + id
            });

            return request.then(maRequestHandler.handleSuccess, maRequestHandler.getErrorHandler());
        }
    }
]);

MaBudgetServiceModule.factory('maIncomeListData', [
    '$rootScope',
    'maIncomeService',
    function ($rootScope, maIncomeService) {
        var result = {};
        var sort = 'date';
        var direction = 'DESC';

        result.filters = {};
        result.items = [];
        result.info = {
            sum: 0,
            currentPage: 1,
            totalItems: 0,
            itemsPerPage: 10
        };

        // -------------------------------------------------------------------------------------------------------------
        // Public methods
        // -------------------------------------------------------------------------------------------------------------

        /**
         * @param _filters array of objects with fields: {name: 'name', value: 'value'}
         * @param success
         */
        result.getData = function (_filters, success) {
            if (_filters !== undefined) {
                result.filters = _filters
            }

            maIncomeService
                .showList(result.info.itemsPerPage, result.info.currentPage, sort, direction, result.filters)
                .then(function (response) {
                    angular.copy(response.items, result.items);

                    result.info.currentPage = response.page;
                    result.info.totalItems = response.total_count;
                    result.info.itemsPerPage = response.num_items_per_page;

                    if ($.isFunction(success)) {
                        success(response);
                    }
                });
        };

        result.resetPagination = function () {
            result.info.currentPage = 1;
        };

        // Return public API
        return result;
    }
]);