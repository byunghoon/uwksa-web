

angular.module('app.controllers', ['carousel', 'carousel.templates', 'accordion','accordion.templates'])
	.controller('SlideController', function($scope) {
	$scope.interval = 5000;
	$scope.slides = [
		{image: 'http://placehold.it/960x400/ffffff/000000', text: 'this is a friggin placeholder'},
		{image: 'http://placehold.it/960x400/000000/ffffff', text: 'another flippin placeholder'},
		{image: 'http://placehold.it/960x400/fefefe/efefef', text: 'huehuehue'},
		{image: 'http://placehold.it/960x400/f7f7f3/e9e7e5', text: 'lorem ipsum shit'}
	];
}).controller('NewsController', function($scope) {
  	
}).controller('qnaController', function($scope) {
  $scope.qnassss = [
    {title: 'dummy post1', date: '2999/12/1'},
    {title: 'dummy post2', date: '2999/12/1'},
    {title: 'dummy post3', date: '2999/12/1'},
    {title: 'dummies everywhere', date: '2999/12/1'},
    {title: 'dumbasses', date: '2999/12/1'}
  ]
}).controller('tutorController', function($scope) {
  $scope.tutorssss = [
    {title: 'dummy post1', date: '2999/12/1'},
    {title: 'dummy post2', date: '2999/12/1'},
    {title: 'dummy post3', date: '2999/12/1'},
    {title: 'dummies everywhere', date: '2999/12/1'},
    {title: 'dumbasses', date: '2999/12/1'}
  ]
}).controller('subletController', function($scope) {
  $scope.subletssss = [
    {title: 'dummy post1', date: '2999/12/1'},
    {title: 'dummy post2', date: '2999/12/1'},
    {title: 'dummy post3', date: '2999/12/1'},
    {title: 'dummies everywhere', date: '2999/12/1'},
    {title: 'dumbasses', date: '2999/12/1'}
  ]
}).controller('bsController', function($scope) {
  $scope.bsessss = [
    {title: 'dummy post1', date: '2999/12/1'},
    {title: 'dummy post2', date: '2999/12/1'},
    {title: 'dummy post3', date: '2999/12/1'},
    {title: 'dummies everywhere', date: '2999/12/1'},
    {title: 'dumbasses', date: '2999/12/1'}
  ]
}).controller('ebController', function($scope) {
  $scope.ebssss = [
    {title: 'dummy post1', date: '2999/12/1'},
    {title: 'dummy post2', date: '2999/12/1'},
    {title: 'dummy post3', date: '2999/12/1'},
    {title: 'dummies everywhere', date: '2999/12/1'},
    {title: 'dumbasses', date: '2999/12/1'}
  ]
}).controller('communityController', function($scope) {
  $scope.ctyssss = [
    {title: 'dummy post1', date: '2999/12/1'},
    {title: 'dummy post2', date: '2999/12/1'},
    {title: 'dummy post3', date: '2999/12/1'},
    {title: 'dummies everywhere', date: '2999/12/1'},
    {title: 'dumbasses', date: '2999/12/1'}
  ]
}).controller('MainController', function($http, $scope, $location, $route, $routeParams, $window, $timeout) {
	




});


angular.module("carousel", ['app.services'])
.controller('CarouselController', ['$scope', '$timeout', '$transition', '$q', function ($scope, $timeout, $transition, $q) {
  var self = this,
    slides = self.slides = [],
    currentIndex = -1,
    currentTimeout, isPlaying;
  self.currentSlide = null;

  /* direction: "prev" or "next" */
  self.select = function(nextSlide, direction) {
    var nextIndex = slides.indexOf(nextSlide);
    //Decide direction if it's not given
    if (direction === undefined) {
      direction = nextIndex > currentIndex ? "next" : "prev";
    }
    if (nextSlide && nextSlide !== self.currentSlide) {
      if ($scope.$currentTransition) {
        $scope.$currentTransition.cancel();
        //Timeout so ng-class in template has time to fix classes for finished slide
        $timeout(goNext);
      } else {
        goNext();
      }
    }
    function goNext() {
      //If we have a slide to transition from and we have a transition type and we're allowed, go
      if (self.currentSlide && angular.isString(direction) && !$scope.noTransition && nextSlide.$element) { 
        //We shouldn't do class manip in here, but it's the same weird thing bootstrap does. need to fix sometime
        nextSlide.$element.addClass(direction);
        nextSlide.$element[0].offsetWidth = nextSlide.$element[0].offsetWidth; //force reflow

        //Set all other slides to stop doing their stuff for the new transition
        angular.forEach(slides, function(slide) {
          angular.extend(slide, {direction: '', entering: false, leaving: false, active: false});
        });
        angular.extend(nextSlide, {direction: direction, active: true, entering: true});
        angular.extend(self.currentSlide||{}, {direction: direction, leaving: true});

        $scope.$currentTransition = $transition(nextSlide.$element, {});
        //We have to create new pointers inside a closure since next & current will change
        (function(next,current) {
          $scope.$currentTransition.then(
            function(){ transitionDone(next, current); },
            function(){ transitionDone(next, current); }
          );
        }(nextSlide, self.currentSlide));
      } else {
        transitionDone(nextSlide, self.currentSlide);
      }
      self.currentSlide = nextSlide;
      currentIndex = nextIndex;
      //every time you change slides, reset the timer
      restartTimer();
    }
    function transitionDone(next, current) {
      angular.extend(next, {direction: '', active: true, leaving: false, entering: false});
      angular.extend(current||{}, {direction: '', active: false, leaving: false, entering: false});
      $scope.$currentTransition = null;
    }
  };

  /* Allow outside people to call indexOf on slides array */
  self.indexOfSlide = function(slide) {
    return slides.indexOf(slide);
  };

  $scope.next = function() {
    var newIndex = (currentIndex + 1) % slides.length;
    
    //Prevent this user-triggered transition from occurring if there is already one in progress
    if (!$scope.$currentTransition) {
      return self.select(slides[newIndex], 'next');
    }
  };

  $scope.prev = function() {
    var newIndex = currentIndex - 1 < 0 ? slides.length - 1 : currentIndex - 1;
    
    //Prevent this user-triggered transition from occurring if there is already one in progress
    if (!$scope.$currentTransition) {
      return self.select(slides[newIndex], 'prev');
    }
  };

  $scope.select = function(slide) {
    self.select(slide);
  };

  $scope.isActive = function(slide) {
     return self.currentSlide === slide;
  };

  $scope.slides = function() {
    return slides;
  };

  $scope.$watch('interval', restartTimer);
  function restartTimer() {
    if (currentTimeout) {
      $timeout.cancel(currentTimeout);
    }
    function go() {
      if (isPlaying) {
        $scope.next();
        restartTimer();
      } else {
        $scope.pause();
      }
    }
    var interval = +$scope.interval;
    if (!isNaN(interval) && interval>=0) {
      currentTimeout = $timeout(go, interval);
    }
  }
  $scope.play = function() {
    if (!isPlaying) {
      isPlaying = true;
      restartTimer();
    }
  };
  $scope.pause = function() {
    if (!$scope.noPause) {
      isPlaying = false;
      if (currentTimeout) {
        $timeout.cancel(currentTimeout);
      }
    }
  };

  self.addSlide = function(slide, element) {
    slide.$element = element;
    slides.push(slide);
    //if this is the first slide or the slide is set to active, select it
    if(slides.length === 1 || slide.active) {
      self.select(slides[slides.length-1]);
      if (slides.length == 1) {
        $scope.play();
      }
    } else {
      slide.active = false;
    }
  };

  self.removeSlide = function(slide) {
    //get the index of the slide inside the carousel
    var index = slides.indexOf(slide);
    slides.splice(index, 1);
    if (slides.length > 0 && slide.active) {
      if (index >= slides.length) {
        self.select(slides[index-1]);
      } else {
        self.select(slides[index]);
      }
    } else if (currentIndex > index) {
      currentIndex--;
    }
  };
}]);

angular.module("accordion", ['app.services', 'app.directives'])
.constant('accordionConfig', {
	closeOthers: true
})
.controller('AccordionController', ['$scope', '$attrs', 'accordionConfig', function ($scope, $attrs, accordionConfig) {
  
  // This array keeps track of the accordion groups
  this.groups = [];

  // Ensure that all the groups in this accordion are closed, unless close-others explicitly says not to
  this.closeOthers = function(openGroup) {
    var closeOthers = angular.isDefined($attrs.closeOthers) ? $scope.$eval($attrs.closeOthers) : accordionConfig.closeOthers;
    if ( closeOthers ) {
      angular.forEach(this.groups, function (group) {
      	
        if ( group !== openGroup ) {
          group.isOpen = false;
        } else if ( group === openGroup) {
        	group.isOpen = true;
        }
      });
    }
  };

  // This is called from the accordion-group directive to add itself to the accordion
  this.addGroup = function(groupScope) {
    var that = this;
    this.groups.push(groupScope);

    groupScope.$on('$destroy', function (event) {
      that.removeGroup(groupScope);
    });
  };

  // This is called from the accordion-group directive when to remove itself
  this.removeGroup = function(group) {
    var index = this.groups.indexOf(group);
    if ( index !== -1 ) {
      this.groups.splice(this.groups.indexOf(group), 1);
    }
  };

}]);


































