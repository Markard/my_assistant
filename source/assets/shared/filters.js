'use strict';

var MaFilters = angular.module('MaFilters', []);

MaFilters.filter('baseDate', function () {
    return function (input) {
        return moment(input).format('D MMMM');
    }
});

MaFilters.filter('dayOfWeek', function () {
    return function (input) {
        return moment(input).format('dddd');
    }
});

MaFilters.filter('baseYear', function () {
    return function (input) {
        return moment.unix(input).format('YYYY');
    }
});