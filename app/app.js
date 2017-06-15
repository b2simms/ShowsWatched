var app = angular.module('app', ['ui.router','ngMaterial', 'ngMessages', 'LocalStorageModule'])
.constant('API', 'http://localhost:8080')
.config(function($httpProvider) {
  $httpProvider.interceptors.push('authInterceptor');
})
.config(function($mdThemingProvider) {
  $mdThemingProvider.theme('default')
    .primaryPalette('indigo')
    .accentPalette('deep-orange')
    .warnPalette('red');
})
.config(function (localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix('thewatchlist')
    .setStorageType('localStorage');
})
.directive('compareTo', compareTo);

function compareTo() {
    return {
        require: "ngModel",
        scope: {
            otherModelValue: "=compareTo"
        },
        link: function(scope, element, attributes, ngModel) {
             
            ngModel.$validators.compareTo = function(modelValue) {
                return modelValue == scope.otherModelValue;
            };
 
            scope.$watch("otherModelValue", function() {
                ngModel.$validate();
            });
        }
    };
};