'use strict';


angular.module('app', ['ngSanitize', 'app.controllers', 'app.directives', 'app.services']).
	config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/:boardName', {});
	$routeProvider.when('/:boardName/:postId', {});
	$routeProvider.otherwise({redirectTo: '/home'});
}]);
