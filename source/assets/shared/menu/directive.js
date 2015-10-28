'use strict';

DirectiveModule.directive('maMenuAuth', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: 'static/shared/menu/view.html',
                link: function (scope, element, attributes) {
                }
            };
        }
    ]
);

DirectiveModule.directive('maMenuNonAuth', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: 'static/shared/menu/view-non-auth.html',
                link: function (scope, element, attributes) {
                }
            };
        }
    ]
);
