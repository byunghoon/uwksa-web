
angular.module("app.directives", [])
.directive('carousel', [function() {
  return {
    restrict: 'EA',
    transclude: true,
    replace: true,
    controller: 'CarouselController',
    require: 'carousel',
    templateUrl: 'template/carousel/carousel.html',
    scope: {
      interval: '=',
      noTransition: '=',
      noPause: '='
    }
  };
}]).directive('slide', ['$parse', function($parse) {
  return {
    require: '^carousel',
    restrict: 'EA',
    transclude: true,
    replace: true,
    templateUrl: 'template/carousel/slide.html',
    scope: {
    },
    link: function (scope, element, attrs, carouselCtrl) {
      //Set up optional 'active' = binding
      if (attrs.active) {
        var getActive = $parse(attrs.active);
        var setActive = getActive.assign;
        var lastValue = scope.active = getActive(scope.$parent);
        scope.$watch(function parentActiveWatch() {
          var parentActive = getActive(scope.$parent);
          
          if (parentActive !== scope.active) {
            // we are out of sync and need to copy
            if (parentActive !== lastValue) {
              // parent changed and it has precedence
              lastValue = scope.active = parentActive;
            } else {
              // if the parent can be assigned then do so
              setActive(scope.$parent, parentActive = lastValue = scope.active);
            }
          }
          return parentActive;
        });
      }

      carouselCtrl.addSlide(scope, element);
      //when the scope is destroyed then remove the slide from the current slides array
      scope.$on('$destroy', function() {
        carouselCtrl.removeSlide(scope);
      });

      scope.$watch('active', function(active) {
        if (active) {
          carouselCtrl.select(scope);
        }
      });
    }
  };
}]).directive('collapse', ['$transition', function($transition) {
	var fixUpHeight = function(scope, element, height) {
		element.removeClass('collapse');
		element.css({height : height});
		var x = element[0].offsetWidth;
		element.addClass('collapse');
	};

	return {
		link: function(scope, element, attrs) {
			var isCollapsed;
			var initialAnimSkip = true;
			scope.$watch(function() {return element[0].scrollHeight; }, function(value) {
				if(element[0].scrollHeight !== 0) {
					if(!isCollapsed) {
						if(initialAnimSkip) {
							fixUpHeight(scope, element, element[0].scrollHeight + 'px');
						} else {
							fixUpHeight(scope, element, 'auto');
						}
					}
				}
			});

			scope.$watch(attrs.collapse, function(value) {
				if(value) {
					collapse();
				} else {
					expand();
				}
			});

			var currentTransition;
			var doTransition = function(change) {
				if(currentTransition) {
					currentTransition.cancel();
				}
				currentTransition = $transition(element, change);
				currentTransition.then(
					function() {currentTransition = undefined;},
					function() {currentTransition = undefined;}
				);
				return currentTransition
			};
			var expand = function() {
				if(initialAnimSkip) {
					initialAnimSkip = false;
					if(!isCollapsed) {
						fixUpHeight(scope, element, 'auto');
					}
				} else {
					doTransition({height:element[0].scrollHeight + 'px'})
					.then(function() {
						if(!isCollapsed) {
							fixUpHeight(scope, element, 'auto');
						}
					});
				}
				isCollapsed = false;
			};

			var collapse = function() {
				isCollapsed = true;
				if(initialAnimSkip) {
					initialAnimSkip = false;
					fixUpHeight(scope, element, 0);
				} else {
					fixUpHeight(scope, element, element[0].scrollHeight + 'px');
					doTransition({'height':'0'});
				}
			};
		}
	};
}])
.directive('accordion', function () {
  return {
    restrict:'EA',
    controller:'AccordionController',
    transclude: true,
    replace: false,
    templateUrl: 'template/accordion/accordion.html'
  };
})

// The accordion-group directive indicates a block of html that will expand and collapse in an accordion
.directive('accordionGroup', ['$parse', '$transition', '$timeout', function($parse, $transition, $timeout) {
  return {
    require:'^accordion',         // We need this directive to be inside an accordion
    restrict:'EA',
    transclude:true,              // It transcludes the contents of the directive into the template
    replace: true,                // The element containing the directive will be replaced with the template
    templateUrl:'template/accordion/accordion-group.html',
    scope:{ heading:'@' },        // Create an isolated scope and interpolate the heading attribute onto this scope
    controller: ['$scope', function($scope) {
      this.setHeading = function(element) {
        this.heading = element;
      };
    }],
    link: function(scope, element, attrs, accordionCtrl) {
      var getIsOpen, setIsOpen;

      accordionCtrl.addGroup(scope);

      scope.isOpen = false;
      
      if ( attrs.isOpen ) {
        getIsOpen = $parse(attrs.isOpen);
        setIsOpen = getIsOpen.assign;

        scope.$watch(
          function watchIsOpen() { return getIsOpen(scope.$parent); },
          function updateOpen(value) { scope.isOpen = value; }
        );
        
        scope.isOpen = getIsOpen ? getIsOpen(scope.$parent) : false;
      }

      scope.$watch('isOpen', function(value) {
        console.log(scope.isOpen);
        if ( value ) {
          accordionCtrl.closeOthers(scope);
        }
        if ( setIsOpen ) {
          setIsOpen(scope.$parent, value);
        }
      });
    }
  };
}])

// Use accordion-heading below an accordion-group to provide a heading containing HTML
// <accordion-group>
//   <accordion-heading>Heading containing HTML - <img src="..."></accordion-heading>
// </accordion-group>
.directive('accordionHeading', function() {
  return {
    restrict: 'EA',
    transclude: true,   // Grab the contents to be used as the heading
    template: '',       // In effect remove this element!
    replace: true,
    require: '^accordionGroup',
    compile: function(element, attr, transclude) {
      return function link(scope, element, attr, accordionGroupCtrl) {
        // Pass the heading to the accordion-group controller
        // so that it can be transcluded into the right place in the template
        // [The second parameter to transclude causes the elements to be cloned so that they work in ng-repeat]
        accordionGroupCtrl.setHeading(transclude(scope, function() {}));
      };
    }
  };
})

// Use in the accordion-group template to indicate where you want the heading to be transcluded
// You must provide the property on the accordion-group controller that will hold the transcluded element
// <div class="accordion-group">
//   <div class="accordion-heading" ><a ... accordion-transclude="heading">...</a></div>
//   ...
// </div>
.directive('accordionTransclude', function() {
  return {
    require: '^accordionGroup',
    link: function(scope, element, attr, controller) {
      scope.$watch(function() { return controller[attr.accordionTransclude]; }, function(heading) {
        if ( heading ) {
          element.html('');
          element.append(heading);
        }
      });
    }
  };
});
;

angular.module("carousel.templates", ["template/carousel/carousel.html", "template/carousel/slide.html"]);
angular.module("accordion.templates", ["template/accordion/accordion-group.html","template/accordion/accordion.html"]);

angular.module("template/carousel/carousel.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("template/carousel/carousel.html",
    "<div ng-mouseenter=\"pause()\" ng-mouseleave=\"play()\" class=\"carousel\">\n" +
    "    <ol class=\"carousel-indicators\" ng-show=\"slides().length > 1\">\n" +
    "        <li ng-repeat=\"slide in slides()\" ng-class=\"{active: isActive(slide)}\" ng-click=\"select(slide)\"></li>\n" +
    "    </ol>\n" +
    "    <div class=\"carousel-inner\" ng-transclude></div>\n" +
    "    <a ng-click=\"prev()\" class=\"carousel-control left\" ng-show=\"slides().length > 1\">&lsaquo;</a>\n" +
    "    <a ng-click=\"next()\" class=\"carousel-control right\" ng-show=\"slides().length > 1\">&rsaquo;</a>\n" +
    "</div>\n" +
    "");
}]);

angular.module("template/carousel/slide.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("template/carousel/slide.html",
    "<div ng-class=\"{\n" +
    "    'active': leaving || (active && !entering),\n" +
    "    'prev': (next || active) && direction=='prev',\n" +
    "    'next': (next || active) && direction=='next',\n" +
    "    'right': direction=='prev',\n" +
    "    'left': direction=='next'\n" +
    "  }\" class=\"item\" ng-transclude></div>\n" +
    "");
}]);


angular.module("template/accordion/accordion-group.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("template/accordion/accordion-group.html",
    "<div class=\"accordion-group\" ng-controller=\"NewsController\">\n" +
    "  <div class=\"accordion-heading\" ><a class=\"accordion-toggle\" ng-click=\"isOpen=!isOpen\" accordion-transclude=\"heading\">{{heading}}</a></div>\n" +
    "  <div class=\"accordion-body\" collapse=\"!isOpen\">\n" +
    "    <div class=\"accordion-inner\" ng-transclude></div>  </div>\n" +
    "</div>");
}]);

angular.module("template/accordion/accordion.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("template/accordion/accordion.html",
    "<div class=\"accordion\" ng-transclude></div>");
}]);













