// Font Detection
window.onload = function() {
	WaitForWebfonts(['Nanum Gothic'], function() {
		SetFontLoaded();
	});
}



// Constants
var ADDR_GET_PHP =		"backend/get.php";
var ADDR_POST_PHP =		"backend/post.php";
var ADDR_UPLOAD_PHP =	"backend/upload.php";

var POPOVER_NONE =		"kNone";
var POPOVER_LOGIN =		"kLogin";
var POPOVER_SIGN_UP =	"kSignUp";
var POPOVER_PROFILE =	"kProfile";

var DOM_SEARCH_FIELD_ID = "BDSearchField";
var DOM_CONTENT_VIEW_ID = "BDContentWrapper";


// Global Functions
function SetFontLoaded() {
	if (angular.element(document.body).scope()) {
		angular.element(document.body).scope().$apply(function() {
			angular.element(document.body).scope().fontLoaded = true;
		});
	} else {
		setTimeout(SetFontLoaded, 500);
	}
}
function WaitForWebfonts(fonts, callback) {
	var loadedFonts = 0;
	for(var i = 0, l = fonts.length; i < l; ++i) {
		(function(font) {
			var node = document.createElement('span');
			// Characters that vary significantly among different fonts
			node.innerHTML = 'giItT1WQy@!-/#';
			// Visible - so we can measure it - but not on the screen
			node.style.position	  	= 'absolute';
			node.style.left		 	= '-10000px';
			node.style.top		  	= '-10000px';
			// Large font size makes even subtle changes obvious
			node.style.fontSize	 	= '300px';
			// Reset any font properties
			node.style.fontFamily	= 'sans-serif';
			node.style.fontVariant	= 'normal';
			node.style.fontStyle	= 'normal';
			node.style.fontWeight	= 'normal';
			node.style.letterSpacing= '0';
			document.body.appendChild(node);

			// Remember width with no applied web font
			var width = node.offsetWidth;

			node.style.fontFamily = font;

			var interval;
			function checkFont() {
				// Compare current width with original width
				if(node && node.offsetWidth != width) {
					++loadedFonts;
					node.parentNode.removeChild(node);
					node = null;
				}

				// If all fonts have been loaded
				if(loadedFonts >= fonts.length) {
					if(interval) {
						clearInterval(interval);
					}
					if(loadedFonts == fonts.length) {
						callback();
						return true;
					}
				}
			};

			if(!checkFont()) {
				interval = setInterval(checkFont, 50);
			}
		})(fonts[i]);
	}
}
function ICantBelieveYouAreUsingInternetExplorer() {
	var rv = -1; // Return value assumes failure.
	if (navigator.appName == 'Microsoft Internet Explorer') {
		var ua = navigator.userAgent;
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat( RegExp.$1 );
	}
	return rv;
}

function OnWysCmd(cmd, value) {
	document.execCommand(cmd, false, value);
}



// Angular App
var app = angular.module('uwksa', [/*'ngSanitize'*/]);

app.directive('contenteditable', function() {
	return {
		restrict: 'A',
		require: '?ngModel',
		link: function(scope, element, attrs, ngModel) {
			if(!ngModel) return;
			ngModel.$render = function() { element.html(ngModel.$viewValue || ''); };
			element.bind('blur keyup change', function() { scope.$apply(read); });
			read();
			function read() { ngModel.$setViewValue(element.html()); }
		}
	};
});

app.directive('ngFocus', function ($timeout) {
	return function (scope, element, attrs) {
		attrs.$observe('ngFocus', function (newValue) {
			if (newValue === 'true') {
				$timeout(function() { element[0].focus(); }, 50);
			}
		});
	}
});

app.directive('ngOnFocus', ['$parse', function($parse) {
	return function(scope, element, attr) {
		var fn = $parse(attr['ngOnFocus']);
		element.bind('focus', function(event) {
			scope.$apply(function() {
				fn(scope, {$event:event});
			});
		});
	}
}]);

app.directive('ngOnBlur', ['$parse', function($parse) {
	return function(scope, element, attr) {
		var fn = $parse(attr['ngOnBlur']);
		element.bind('blur', function(event) {
			scope.$apply(function() {
				fn(scope, {$event:event});
			});
		});
	}
}]);

app.directive('ngKeyup', function() {
	return function(scope, elm, attrs) {
		var ngKeyup = scope.$eval(attrs.ngKeyup);
		elm.bind('keyup', function(evt) {
			scope.$apply(function() {
				ngKeyup.call(scope, evt.which);
			});
		});
	};
});

app.directive('ctree', function() {
	return {
		restrict: 'A',
		template: '<ul class="commentUl"><li class="commentLi" ng-class="{even:depth%2==0, odd:depth%2==1}" ng-repeat="node in tree"><div cnode ng-model="node"></div></li></ul>',
		replace: true,
		transclude: true,
		scope: {
			tree: '=ngModel', depth: '=',  canComment: '=',
			onEdit: '&', onDelete: '&', onReply: '&', doLinkify: '&', generateUserLink: '&', generateNamecard: '&',
			ignoreNextEscape: '&', uploadFile: '&', buildAddedFilesList: '&', buildDeletedFilesList: '&',
			addFields: '&'
		}
	};
});

app.directive('cnode', function($compile) {
	return { 
		restrict: 'A',
		templateUrl: 'tp-cnode',
		link: function(scope, elm, attrs) {
			//if (scope.node.children.length > 0) {
				scope.node.IgnoreNextEscape = function() { scope.ignoreNextEscape(); }
				scope.node.UploadFile = function(file) { scope.uploadFile({file: file}); }
				scope.node.BuildAddedFilesList = function(files) { return scope.buildAddedFilesList({files: files}); }
				scope.node.BuildDeletedFilesList = function(files) { return scope.buildDeletedFilesList({files: files}); }
				scope.node.AddFields = function(files) { return scope.addFields({files: files}); }
				var child = $compile('<div ctree depth="'+(scope.depth+1)+'" can-comment="'+scope.canComment+'"'
						+ 'ng-model="node.children"'
						+ 'on-edit="onEdit({cId: cId})"'
						+ 'on-delete="onDelete({cId: cId})"'
						+ 'on-reply="onReply({cId: cId})"'
						+ 'do-linkify="doLinkify({str: str})"'
						+ 'generate-user-link="generateUserLink({a:a, b:b, c:c})"'
						+ 'generate-namecard="generateNamecard({a:a, b:b, c:c})"'
						+ 'ignore-next-escape="ignoreNextEscape()"'
						+ 'upload-file="uploadFile({file: file})"'
						+ 'build-added-files-list="buildAddedFilesList({files: files})"'
						+ 'build-deleted-files-list="buildDeletedFilesList({files: files})"'
						+ 'add-fields="addFields({files: files})"></div>'
					)(scope);
				elm.append(child);
			//}
		}
	};
});

app.config(function($routeProvider, $locationProvider) {
	$locationProvider.html5Mode(false).hashPrefix('!');
	var prefix = '';
	$routeProvider.
		when(prefix+'/', {}).
		when(prefix+'/:moduleAddress', {}).
		when(prefix+'/:moduleAddress/:postId', {}).
		otherwise({redirectTo: prefix+'/'});
});

app.controller('ComposeController', function($http, $scope, $location, $timeout) {
	$scope.supportsFileUpload = ICantBelieveYouAreUsingInternetExplorer()==-1 || ICantBelieveYouAreUsingInternetExplorer()>=10;

	$scope.fields = {
		title:"", content:"", pinned:false, selectedTags:new Array(), files:new Array(),
		submitting:false, incomplete:false
	};
	function ResetEditor() {
		$scope.fields.title = "";
		$scope.fields.content = "";
		$scope.fields.pinned = false;
		$scope.fields.submitting = false;
		$scope.fields.files.length = 0;
		$scope.$parent.BOARD.show.composePost = false;
		$scope.$parent.BOARD.hasDraft = false;
	}
	$scope.$watch('$parent.BOARD.show.composePost', function() {
		if ($scope.$parent.BOARD.show.composePost) {
			$scope.fields.incomplete = false;
			$scope.fields.selectedTags = new Array();
		}
	});


	$scope.OnEditorCancel = function() {
		ResetEditor();
	}
	$scope.OnEditorSubmit = function(fields) {
		var titleExists = fields.title && fields.title!="";
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (titleExists && contentExists) {
			fields.submitting = true;

			var addedTagNames = new Array();
			for (var i=0; i<fields.selectedTags.length; i++) {
				addedTagNames.push(fields.selectedTags[i].tagName);
			}

			$http.post(ADDR_POST_PHP, {
				r:'newPost', pId:-1, bName:$scope.$parent.selectedModule.address,
				title:fields.title, content:fields.content, pinned:fields.pinned, addedTags:addedTagNames,
				addedFiles:$scope.$parent.BOARD.FILE.BuildAddedFilesList(fields.files)
			}).success(function(response) {
				ResetEditor();
				$location.path('/'+$scope.$parent.selectedModule.address+'/'+response);
			});
		} else {
			fields.incomplete = true;
			$timeout(function() { fields.incomplete = false; }, 300);
		}
	}


	$scope.SetShowAvailableTags = function(val) { $scope.fields.showAvailableTags = val; }
	$scope.OnAddRemoveTag = function(tag) {
		var tagIndex = GetSelectedTagIndex(tag);
		if (tagIndex == -1) {
			$scope.fields.selectedTags.push(tag);
		} else {
			$scope.fields.selectedTags.splice(tagIndex, 1); // remove tag at tagIndex
		}

		function GetSelectedTagIndex(tag) {
			if ($scope.fields.selectedTags.length) {
				for (var i=0; i<$scope.fields.selectedTags.length; i++) {
					if (tag == $scope.fields.selectedTags[i]) return i;
				}
			}
			return -1;
		}
	}


	$scope.OnFilePromptClick = function() {
		$scope.$parent.IgnoreNextEscape();
	}
	$scope.OnFileSelect = function(uploader) {
		for (var i=0; i<uploader.files.length; i++) {
			$scope.fields.files.push(uploader.files[i]);
			$scope.$parent.BOARD.FILE.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileInsertClick = function(file) {
		if (file.isImage) {
			$scope.fields.content += "<img src='"+file.address+"' alt='' />";
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

});

app.controller('EditController', function($http, $scope, $location, $window, $timeout) {
	$scope.supportsFileUpload = ICantBelieveYouAreUsingInternetExplorer()==-1 || ICantBelieveYouAreUsingInternetExplorer()>=10;

	$scope.fields = {
		title:"", content:"", pinned:false, selectedTags:new Array(), files:new Array(),
		submitting:false, incomplete:false
	};
	$scope.$watch('$parent.BOARD.show.editPost', function() {
		if ($scope.$parent.BOARD.show.editPost) {
			var o = $scope.$parent.BOARD;
			$scope.fields.title = o.selectedPost.title;
			$scope.fields.content = o.selectedPost.content;
			$scope.fields.pinned = o.selectedPost.pinned;
			$scope.fields.submitting = false;
			$scope.fields.incomplete = false;
			$scope.fields.selectedTags = o.selectedPost.selectedTags ? o.selectedPost.selectedTags.slice() : new Array();

			$scope.fields.files = o.selectedPost.files ? o.selectedPost.files.slice() : new Array();
			$scope.BOARD.FILE.AddFields($scope.fields.files);

		} else {
			$scope.fields.title = "";
			$scope.fields.content = "";
			$scope.fields.pinned = false;
		}
	});


	$scope.OnEditorCancel = function() {
		$scope.$parent.BOARD.show.editPost = false;
	}
	$scope.OnEditorSubmit = function(fields) {
		var titleExists = fields.title && fields.title!="";
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (titleExists && contentExists && $scope.$parent.BOARD.postId) {
			fields.submitting = true;

			var addedTagNames = new Array();
			var deletedTagNames = new Array();
			for (var i=0; i<$scope.$parent.BOARD.TAG.availableTags.length; i++) {
				var currentTagName = $scope.$parent.BOARD.TAG.availableTags[i].tagName;

				var existsInOldCopy = false;
				for (var j=0; j<$scope.$parent.BOARD.selectedPost.selectedTags.length; j++) {
					if ($scope.$parent.BOARD.selectedPost.selectedTags[j].tagName == currentTagName) {
						existsInOldCopy = true;
						break;
					}
				}

				var existsInNewCopy = false;
				for (var j=0; j<fields.selectedTags.length; j++) {
					if (fields.selectedTags[j].tagName == currentTagName) {
						existsInNewCopy = true;
						break;
					}
				}

				if (existsInOldCopy && !existsInNewCopy) {
					deletedTagNames.push(currentTagName);
				} else if (existsInNewCopy && !existsInOldCopy) {
					addedTagNames.push(currentTagName);
				}
			}

			$http.post(ADDR_POST_PHP, {
				r:'editPost', pId:$scope.$parent.BOARD.postId, bName:$scope.$parent.selectedModule.address,
				title:fields.title, content:fields.content, pinned:fields.pinned,
				addedTags:addedTagNames, deletedTags:deletedTagNames,
				addedFiles:$scope.$parent.BOARD.FILE.BuildAddedFilesList(fields.files),
				deletedFiles:$scope.$parent.BOARD.FILE.BuildDeletedFilesList(fields.files)
			}).success(function(response) {
				$window.location.reload();
			});
		} else {
			fields.incomplete = true;
			$timeout(function() { fields.incomplete = false; }, 300);
		}
	}


	$scope.SetShowAvailableTags = function(val) { $scope.fields.showAvailableTags = val; }
	$scope.OnAddRemoveTag = function(tag) {
		var tagIndex = GetSelectedTagIndex(tag);
		if (tagIndex == -1) {
			$scope.fields.selectedTags.push(tag);
		} else {
			$scope.fields.selectedTags.splice(tagIndex, 1); // remove tag at tagIndex
		}

		function GetSelectedTagIndex(tag) {
			if ($scope.fields.selectedTags.length) {
				for (var i=0; i<$scope.fields.selectedTags.length; i++) {
					if (tag == $scope.fields.selectedTags[i]) return i;
				}
			}
			return -1;
		}
	}


	$scope.OnFilePromptClick = function() {
		$scope.$parent.IgnoreNextEscape();
	}
	$scope.OnFileSelect = function(uploader) {
		for (var i=0; i<uploader.files.length; i++) {
			$scope.fields.files.push(uploader.files[i]);
			$scope.$parent.BOARD.FILE.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileInsertClick = function(file) {
		if (file.isImage) {
			$scope.fields.content += "<img src='"+file.address+"' alt='' />";
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

});

app.controller('MainCommenterController', function($http, $scope, $timeout) {
	$scope.supportsFileUpload = ICantBelieveYouAreUsingInternetExplorer()==-1 || ICantBelieveYouAreUsingInternetExplorer()>=10;

	$scope.fields = { content:"", files:new Array(), submitting:false, incomplete:false };
	$scope.showCancelButton = false;
	$scope.$watch('$parent.BOARD.selectedPost.postId', function() {
		$scope.fields.content = "<br>"; // FIX_FF for commenter height
		$scope.fields.files.length = 0;
		$scope.fields.submitting = false;
		$scope.fields.incomplete = false;

		if (ICantBelieveYouAreUsingInternetExplorer() > 0) {
			$scope.fields.content = ""; // FIX_IE for the fix above
		}
	});


	$scope.OnCommenterCancel = function() { } // not happening	
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (contentExists && $scope.$parent.BOARD.postId) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'newComment', parentPost:$scope.$parent.BOARD.postId, parentComment:-1,
				content:fields.content,
				addedFiles:$scope.$parent.BOARD.FILE.BuildAddedFilesList(fields.files)
			}).success(function(response) {
				$scope.$parent.BOARD.selectedPost.comments.push(response);
				$scope.$parent.BOARD.selectedPost.commentCount ++;
				$scope.fields.content = "<br>"; // FIX_FF for commenter height
				$scope.fields.files.length = 0;
				$scope.fields.submitting = false;
			});
		} else {
			fields.incomplete = true;
			$timeout(function() { fields.incomplete = false; }, 300);
		}
	}


	$scope.OnFilePromptClick = function() {
		$scope.$parent.IgnoreNextEscape();
	}
	$scope.OnFileSelect = function(uploader) {
		for (var i=0; i<uploader.files.length; i++) {
			$scope.fields.files.push(uploader.files[i]);
			$scope.$parent.BOARD.FILE.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileInsertClick = function(file) {
		if (file.isImage) {
			$scope.fields.content += "<img src='"+file.address+"' alt='' />";
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

});

app.controller('ReplyCommenterController', function($http, $scope, $timeout) {
	$scope.supportsFileUpload = ICantBelieveYouAreUsingInternetExplorer()==-1 || ICantBelieveYouAreUsingInternetExplorer()>=10;

	$scope.fields = { content:"", files:new Array(), submitting:false, incomplete:false };
	$scope.showCancelButton = true;


	$scope.OnCommenterCancel = function() {
		$scope.param.Vaporize();
	}	
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (contentExists && $scope.param) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'newComment', parentPost:$scope.param.postId, parentComment:$scope.param.parentCommentId,
				content:fields.content,
				addedFiles:$scope.param.BuildAddedFilesList(fields.files)
			}).success(function(response) {
				if (response.commentId > 0) {
					$scope.param.UpdateScope();
					$scope.param.showNewCommenter = false;
					$scope.param.commentId = response.commentId;
					$scope.param.canEdit = response.canEdit;
					$scope.param.canDelete = response.canDelete;
					$scope.param.canComment = response.canComment;
					$scope.param.authorUserId = response.authorUserId;
					$scope.param.author = response.author;
					$scope.param.content = response.content;
					$scope.param.files = response.files;
					$scope.param.date = response.date;
					$scope.param.children = response.children;
				}
			});
		} else {
			fields.incomplete = true;
			$timeout(function() { fields.incomplete = false; }, 300);
		}
	}


	$scope.OnFilePromptClick = function() {
		$scope.param.IgnoreNextEscape();
	}
	$scope.OnFileSelect = function(uploader) {
		for (var i=0; i<uploader.files.length; i++) {
			$scope.fields.files.push(uploader.files[i]);
			$scope.param.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileInsertClick = function(file) {
		if (file.isImage) {
			$scope.fields.content += "<img src='"+file.address+"' alt='' />";
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

});

app.controller('EditCommenterController', function($http, $scope, $timeout) {
	$scope.supportsFileUpload = ICantBelieveYouAreUsingInternetExplorer()==-1 || ICantBelieveYouAreUsingInternetExplorer()>=10;
	
	$scope.fields = { content:"", files:new Array(), submitting:false, incomplete:false };
	$scope.showCancelButton = true;
	$scope.$watch('param.showEditCommenter', function() {
		if ($scope.param.showEditCommenter) {
			$scope.fields.content = $scope.param.content;
			$scope.fields.submitting = false;
			$scope.fields.incomplete = false;

			$scope.fields.files = $scope.param.files ? $scope.param.files.slice() : new Array();
			$scope.param.AddFields($scope.fields.files);
			
		} else {
			$scope.fields.content = "";
		}
	});


	$scope.OnCommenterCancel = function() {
		$scope.param.showEditCommenter = false;
	}
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (contentExists && $scope.param) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'editComment', cId:$scope.param.commentId,
				content:fields.content,
				addedFiles:$scope.param.BuildAddedFilesList(fields.files),
				deletedFiles:$scope.param.BuildDeletedFilesList(fields.files)
			}).success(function(response) {
				$scope.param.content = fields.content;
				$scope.param.files = response.files ? response.files : new Array();
				$scope.param.showEditCommenter = false;
			});
		} else {
			fields.incomplete = true;
			$timeout(function() { fields.incomplete = false; }, 300);
		}
	}


	$scope.OnFilePromptClick = function() {
		$scope.param.IgnoreNextEscape();
	}
	$scope.OnFileSelect = function(uploader) {
		for (var i=0; i<uploader.files.length; i++) {
			$scope.fields.files.push(uploader.files[i]);
			$scope.param.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileInsertClick = function(file) {
		if (file.isImage) {
			$scope.fields.content += "<img src='"+file.address+"' alt='' />";
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

});

app.controller('LoginController', function($http, $scope, $window, $timeout) {
	$scope.option = 0;
	$scope.fields = { email:"", password:"", submitting:false, incomplete:false, failed:false, noSuchEmail:false };
	$scope.$watch('$parent.popover', function() {
		if ($scope.$parent.popover == POPOVER_NONE) {
			$scope.fields.incomplete = false;
			$scope.fields.failed = false;
			$scope.fields.noSuchEmail = false;
			$scope.option = 0;
		}
	});
	

	$scope.OnLoginCancelClick = function() {
		$scope.$parent.popover = POPOVER_NONE;
		$scope.fields.incomplete = false;
		$scope.fields.failed = false;
		$scope.fields.noSuchEmail = false;
		$scope.option = 0;
	}
	$scope.OnForgotPasswordClick = function() {
		$scope.option = 1;
	}
	$scope.OnSendInstructionClick = function(fields) {
		if (fields.email != "") {
			$http.get(ADDR_GET_PHP+'?t=4&EmailAddress='+fields.email).success(function(data) {
				if (data.ResultCode == 0) {
					$timeout(function() {
						fields.noSuchEmail = true;
						$timeout(function() { fields.noSuchEmail = false; }, 1000)
					}, 10);
				} else {
					$http.post(ADDR_POST_PHP, {
						r:'forgotPassword', email:fields.email
					}).success(function(response) {
						fields.instructionSent = true;
					});
				}
			});
		}
	}
	$scope.OnLoginSubmitClick = function(fields) {
		var emailExists = fields.email && fields.email!="";
		var passwordExists = fields.password && fields.password!="";
		fields.incomplete = false;
		fields.failed = false;

		if (emailExists && passwordExists) {
			fields.submitting = true;

			$http.post(ADDR_POST_PHP, {
				r:'login', email:fields.email, password:fields.password
			}).success(function(response) {
				if (response.ResultCode == 0) {
					$window.location.reload();
				} else if (response.ResultCode == 2) {
					fields.failed = true;
					fields.failedMessage = "Retry after confirming your email address.";
				} else {
					$timeout(function() {
						fields.failed = true;
						fields.failedMessage = "The email or password you entered is incorrect.";
						$timeout(function() { fields.failed = false; }, 1000)
					}, 10);
				}
			});
		} else {
			$timeout(function() {
				fields.incomplete = true;
				$timeout(function() { fields.incomplete = false; }, 300)
			}, 10);
		}
	}

});

app.controller('SignUpController', function($http, $scope, $window, $timeout) {
	$scope.ResetVars = function() {
		$scope.fields = {
			name:"", nameChecking:false, namePassed:false, nameFailed:false,
			email:"", emailChecking:false, emailPassed:false, emailFailed:false, emailInvalid:false,
			password:"", passwordPassed:false, passwordFailed:false,
			confirm:"", confirmPassed:false, confirmFailed:false,
			submitting:false, incomplete:false, signUpCompleted:false
		};
	}
	$scope.ResetVars();


	$scope.$watch('$parent.popover', function() {
		if ($scope.$parent.popover == POPOVER_NONE) {
			$scope.fields.incomplete = false;
		}
	});
	$scope.OnSignUpCancelClick = function() {
		$scope.$parent.popover = POPOVER_NONE
		$scope.fields.incomplete = false;
	}
	$scope.OnSignUpCloseClick = function() {
		$scope.$parent.popover = POPOVER_NONE
		$scope.ResetVars();
	}


	$scope.OnSignUpSubmitClick = function(fields) {
		var nameExists = fields.name && fields.name!="";
		var emailExists = fields.email && fields.email!="";
		var passwordExists = fields.password && fields.password!="";
		var confirmExists = fields.confirm && fields.confirm!="";
		var allExist = nameExists && emailExists && passwordExists && confirmExists;
		var allPassed = fields.namePassed && fields.emailPassed && fields.passwordPassed && fields.confirmPassed;

		fields.incomplete = false;

		if (allExist && allPassed) {
			fields.submitting = true;

			$http.post(ADDR_POST_PHP, {
				r:'signUp', userName:fields.name, emailAddress:fields.email, password:fields.password
			}).success(function(response) {
				fields.submitting = false;
				fields.signUpCompleted = true;
			});
		} else {
			$timeout(function() {
				fields.incomplete = true;
				$timeout(function() { fields.incomplete = false; }, 300)
			}, 10);
		}
	}


	$scope.OnNameFieldBlur = function(fields) {
		fields.namePassed = false;
		fields.nameFailed = false;

		if (fields.name.length >= 2) {
			fields.nameChecking = true;

			$http.get(ADDR_GET_PHP+'?t=3&UserName='+fields.name).success(function(data) {
				if (data.ResultCode == 0) {
					fields.namePassed = true;
				} else {
					fields.nameFailed = true;
				}
				fields.nameChecking = false;
			});
		}
	}
	$scope.OnEmailFieldBlur = function(fields) {
		fields.emailPassed = false;
		fields.emailFailed = false;
		fields.emailInvalid = false;

		if (fields.email == "") return;

		if (/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(fields.email)) {
			fields.emailChecking = true;
			
			$http.get(ADDR_GET_PHP+'?t=4&EmailAddress='+fields.email).success(function(data) {
				if (data.ResultCode == 0) {
					fields.emailPassed = true;
				} else {
					fields.emailFailed = true;
				}
				fields.emailChecking = false;
			});
		} else {
			fields.emailInvalid = true;
		}
	}


	$scope.$watch('fields.name', function() {
		$scope.fields.nameChecking = false;
		$scope.fields.namePassed = false;
		$scope.fields.nameFailed = false;
	});
	$scope.$watch('fields.email', function() {
		$scope.fields.emailChecking = false;
		$scope.fields.emailPassed = false;
		$scope.fields.emailFailed = false;
		$scope.fields.emailInvalid = false;
	});
	$scope.$watch('fields.password', function() {
		if ($scope.fields.password == "") {
			$scope.fields.passwordPassed = false;
			$scope.fields.passwordFailed = false;
		} else if ($scope.fields.password.length >= 8) {
			$scope.fields.passwordPassed = true;
			$scope.fields.passwordFailed = false;
		} else {
			$scope.fields.passwordPassed = false;
			$scope.fields.passwordFailed = true;
		}
	});
	$scope.$watch('fields.confirm', function() {
		if ($scope.fields.confirm == "") {
			$scope.fields.confirmPassed = false;
			$scope.fields.confirmFailed = false;
		} else if ($scope.fields.confirm == $scope.fields.password) {
			$scope.fields.confirmPassed = true;
			$scope.fields.confirmFailed = false;
		} else {
			$scope.fields.confirmPassed = false;
			$scope.fields.confirmFailed = true;
		}
	});

});

app.controller('ProfileController', function($http, $scope, $window, $timeout) {
	$scope.ResetVars = function() {
		$scope.option = 0;
		$scope.fields = {
			name:"", nameChecking:false, namePassed:false, nameFailed:false,
			password:"", passwordPassed:false, passwordFailed:false,
			confirm:"", confirmPassed:false, confirmFailed:false,
			oldPassword:"", submitting:false, incomplete:false, failed:false, changeSuccessful:false
		};
	}
	$scope.ResetVars();


	$scope.OnDoneClick = function() {
		if ($scope.option == 1) {
			$window.location.reload(); //name change requires refresh
		} else {
			$scope.$parent.popover = POPOVER_NONE
			$scope.ResetVars();
		}
	}
	$scope.OnCancelClick = function() {
		$scope.$parent.popover = POPOVER_NONE
		$scope.ResetVars();
	}
	$scope.$watch('$parent.popover', function() {
		if ($scope.$parent.popover == POPOVER_NONE) {
			$scope.ResetVars();
		}
	});


	$scope.OnChangeNameClick = function() {
		$scope.option = 1;
	}
	$scope.OnChangePasswordClick = function() {
		$scope.option = 2;
	}


	$scope.OnNameSubmitClick = function(fields) {
		fields.incomplete = false;

		if (fields.name && fields.name!="") {
			fields.submitting = true;
			if (fields.namePassed) {
				PassedAction();
			} else {
				fields.namePassed = false;
				fields.nameFailed = false;

				if (fields.name.length >= 2) {
					fields.nameChecking = true;

					$http.get(ADDR_GET_PHP+'?t=3&UserName='+fields.name).success(function(data) {
						if (data.ResultCode == 0) {
							fields.namePassed = true;
							PassedAction();
						} else {
							fields.nameFailed = true;
							FailedAction();
						}
						fields.nameChecking = false;
					});
				}
			}
		} else {
			FailedAction();
		}
		function PassedAction() {
			$http.post(ADDR_POST_PHP, {
				r:'updateName', userName:fields.name
			}).success(function(response) {
				fields.submitting = false;
				fields.changeSuccessful = true;
			});
		}
		function FailedAction() {
			$timeout(function() {
				fields.incomplete = true;
				$timeout(function() { fields.incomplete = false; }, 300)
			}, 10);
		}
	}
	$scope.OnPasswordSubmitClick = function(fields) {
		var passwordExists = fields.password && fields.password!="";
		var confirmExists = fields.confirm && fields.confirm!="";
		var oldPasswordExists = fields.oldPassword && fields.oldPassword!="";
		var allPassed = fields.passwordPassed && fields.confirmPassed;

		fields.incomplete = false;
		fields.failed = false;

		if (passwordExists && confirmExists && oldPasswordExists && allPassed) {
			fields.submitting = true;

			$http.post(ADDR_POST_PHP, {
				r:'updatePassword', newPassword:fields.password, oldPassword:fields.oldPassword
			}).success(function(response) {
				if (response.ResultCode == 0) {
					fields.submitting = false;
					fields.changeSuccessful = true;
				} else {
					fields.failed = true;
				}
			});
		} else {
			$timeout(function() {
				fields.incomplete = true;
				$timeout(function() { fields.incomplete = false; }, 300)
			}, 10);
		}
	}


	$scope.OnNameFieldBlur = function(fields) {
		fields.namePassed = false;
		fields.nameFailed = false;

		if (fields.name.length >= 2) {
			fields.nameChecking = true;

			$http.get(ADDR_GET_PHP+'?t=3&UserName='+fields.name).success(function(data) {
				if (data.ResultCode == 0) {
					fields.namePassed = true;
				} else {
					fields.nameFailed = true;
				}
				fields.nameChecking = false;
			});
		}
	}
	$scope.$watch('fields.name', function() {
		$scope.fields.nameChecking = false;
		$scope.fields.namePassed = false;
		$scope.fields.nameFailed = false;
	});
	$scope.$watch('fields.password', function() {
		if ($scope.fields.password == "") {
			$scope.fields.passwordPassed = false;
			$scope.fields.passwordFailed = false;
		} else if ($scope.fields.password.length >= 8) {
			$scope.fields.passwordPassed = true;
			$scope.fields.passwordFailed = false;
		} else {
			$scope.fields.passwordPassed = false;
			$scope.fields.passwordFailed = true;
		}
	});
	$scope.$watch('fields.confirm', function() {
		if ($scope.fields.confirm == "") {
			$scope.fields.confirmPassed = false;
			$scope.fields.confirmFailed = false;
		} else if ($scope.fields.confirm == $scope.fields.password) {
			$scope.fields.confirmPassed = true;
			$scope.fields.confirmFailed = false;
		} else {
			$scope.fields.confirmPassed = false;
			$scope.fields.confirmFailed = true;
		}
	});

});

app.controller('RootController', function($http, $scope, $location, $route, $routeParams, $window, $timeout) {
	
	// ///////////////////////////////////////////////////////////////////////////
	// 									UTIL									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.UTIL = {

		StripTags: function(pHTMLString) {
			return pHTMLString.replace(/<img(?:.|\n)*?>/gm, ' [ attached image ] ').replace(/<(?:.|\n)*?>/gm, '');
		},

		Linkify: function(pHTMLString) {
			if (pHTMLString) {
				return pHTMLString.replace(/(^(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]))|(>(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]))|((.[^>"]|[^=]")(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]))/gi,
					function(match, p1, p2, offset, string) {
						var index = match.indexOf("http");
						return match.substring(0, index)+'<a href="'+match.substring(index)+'" target="_blank">'+match.substring(index)+'</a>'
					});
			}
		},

		GenerateUserLink: function(pAuthor, pAuthorEmail, pFacebookId) {
			if (pFacebookId) {
				return 'http://facebook.com/'+pFacebookId;
			} else if (pAuthorEmail && /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(pAuthorEmail)) {
				return 'mailto:'+pAuthorEmail;
			} else {
				return 'mailto:none';
			}
		},
		
		GenerateNamecard: function(pAuthor, pAuthorEmail, pFacebookId) {
			if (pFacebookId) {
				return 'Visit '+pAuthor+'\'s Facebook';
			} else if (pAuthorEmail && /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(pAuthorEmail)) {
				return 'Email to '+pAuthor + ' (' + pAuthorEmail  + ')';
			} else {
				return 'none';
			}
		},

		AmpersandFix: function(pString) {
			return pString ? pString.split("&amp;").join("&") : null;
		},

		AjaxCachingBypassToken: function() { // for IE9 and below
			var token = new Date().getTime();
			return '&token=' + token;
		},

		Realloc: function(pArray) {
			if (pArray) { // clear memory
				pArray.length = 0;
				pArray = null;
			}
			return new Array();
		}

	}



	// ///////////////////////////////////////////////////////////////////////////
	// 									USER									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.USER = {
		
		OnLoginClick: function() {
			$scope.popover = $scope.popover == POPOVER_LOGIN ? POPOVER_NONE : POPOVER_LOGIN;
		},
		OnSignUpClick: function() {
			$scope.popover = POPOVER_SIGN_UP;
		},
		OnLogoutClick: function() {
			$http.post(ADDR_POST_PHP, {
				r:'logout'
			}).success(function(response) {
				$window.location.reload();
			});
		},
		OnProfileClick: function() {
			$scope.popover = $scope.popover == POPOVER_PROFILE ? POPOVER_NONE : POPOVER_PROFILE;
		}

	};

	

	// ///////////////////////////////////////////////////////////////////////////
	// 									HOME									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.HOME = {

		// HOME variables: news, qna, column, sublet, tutors, bns, exams, community
		// each in form of { author, commentCount, date, month, postId, pureContent, selectedTags, title }

		OnExpandClick: function(pIndexToOpen) {
			this.news[pIndexToOpen].open = true;
		},

		GetLatestFeed: function() {
			var paramObj = [
				{ boardName:"news",		postLimit:3, requirePreview:true  },
				{ boardName:"qna",		postLimit:8, requirePreview:false },
				{ boardName:"column",	postLimit:5, requirePreview:false },
				{ boardName:"sublet",	postLimit:5, requirePreview:false },
				{ boardName:"tutors",	postLimit:5, requirePreview:false },
				{ boardName:"bns",		postLimit:5, requirePreview:false },
				{ boardName:"exams",	postLimit:5, requirePreview:false },
				{ boardName:"community",postLimit:5, requirePreview:false }
			];
			
			var url = ADDR_GET_PHP+'?t=5&FeedBoard='+JSON.stringify(paramObj);
			url += $scope.UTIL.AjaxCachingBypassToken();

			$http.get(url).success(function(data) {
				if (data) {

					for (var i=0; i<data.length; i++) {
						for (var j=0; j<data[i].length; j++) {
							data[i][j].title = $scope.UTIL.AmpersandFix(data[i][j].title);
							data[i][j].postContent = $scope.UTIL.AmpersandFix(data[i][j].postContent);
						}
					}
					var o = $scope.HOME; // can't use "this" - callback scope

					o.news = data[0];	// index based on paramObj
					o.qna = data[1];
					o.column = data[2];
					o.sublet = data[3];
					o.tutors = data[4];
					o.bns = data[5];
					o.exams = data[6];
					o.community = data[7];

					if (o.news[0]) { o.news[0].open = true; }
				}
				$scope.moduleLoaded = true;
			});
		}

	};



	// ///////////////////////////////////////////////////////////////////////////
	// 									BOARD									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.BOARD = {

		Initialize: function() {
			this.ResetVars();
			this.supportsFileUpload = ICantBelieveYouAreUsingInternetExplorer()==-1 || ICantBelieveYouAreUsingInternetExplorer()>=10;
			this.hasDraft = false;
		},
		ResetVars: function() {
			this.SEARCH.Initialize();
			this.TAG.Initialize();
			
			// Completely discard posts array. JS is garbage collected,
			// but Angular View having a reference to posts array blocks garbage collection.
			this.posts = $scope.UTIL.Realloc(this.posts);
			this.selectedPost = null;
			this.currentPage = 1;
			this.canPost = false;

			this.show = {
				composePost: false, editPost: false
			};
		},


		OnPageChange: function(pPageNo) {
			if (pPageNo != this.currentPage) this.GetList(pPageNo);
		},
		OnComposeClick: function() {
			this.show.composePost = true;
		},
		OnMinimizeClick: function() {
			this.show.composePost = false;
			this.hasDraft = true;
		},
		OnContinueClick: function() {
			this.show.composePost = true;
		},


		UpdateList: function() {
			this.GetList(this.currentPage);
		},
		GetList: function(pPageNo) {
			pPageNo = typeof pPageNo !== 'undefined' ? pPageNo : -1;
			//console.log("Talking to server for PostList...");

			if (this.SEARCH.timer) {
				this.SEARCH.searching = true;
			} else {
				this.buildingPostList = true;
				if (this.posts) this.posts.length = 0;
			}
			
			var url = ADDR_GET_PHP+'?t=1&postLimit=15';
			url += '&boardName='+$scope.selectedModule.address;
			url += $scope.UTIL.AjaxCachingBypassToken();
			
			// page no
			if (pPageNo > 0) url += '&pageNo='+pPageNo;
			else if (this.postId) url += '&postId='+this.postId;
			
			// search
			url += '&searchParam='+this.SEARCH.inputString;
			if (this.SEARCH.inputString && this.SEARCH.inputString!="") {
				url += '&searchOptions=';
				url += this.SEARCH.options.author ? '1' : '0';
				url += this.SEARCH.options.title ? '1' : '0';
				url += this.SEARCH.options.content ? '1' : '0';
				url += this.SEARCH.options.comments ? '1' : '0';
			}
			
			// tags
			if (this.TAG.availableTags) {
				var selectedTags = new Array();
				for (var i=0; i<this.TAG.availableTags.length; i++) {
					if (this.TAG.availableTags[i].selected) {
						selectedTags.push(this.TAG.availableTags[i].tagName);
					}
				}
				url += '&tags='+JSON.stringify(selectedTags);
			}

			
			$http.get(url).success(function(data) {
				if (data) {
					var o = $scope.BOARD;

					o.listSize = data.listSize;
					o.currentPage = o.SEARCH.searching ? 1 : data.currentPage;
					o.postCount = data.postCount;
					o.canPost = data.canPost;
					o.canPin = data.canPin;
					o.posts = data.postPreviews;
					if (o.posts) {
						for(var i=0; i<o.posts.length; i++) {
							o.posts[i].title = $scope.UTIL.AmpersandFix(o.posts[i].title);
							o.posts[i].pureContent = $scope.UTIL.AmpersandFix(o.posts[i].pureContent);
						}
					}
					if (o.TAG.availableTags.length == 0) {
						o.TAG.availableTags = data.availableTags;
						
						// data.availableTags do not have selected field, so create it with default value false
						for (var i=0; i<data.availableTags.length; i++) {
							data.availableTags.selected = false;
						}
					}
					
					if (pPageNo <= 0) o.UpdateContentView();
					o.UpdatePager();
					
					$scope.moduleLoaded = true;
				}

				o.buildingPostList = false;
				o.SEARCH.searching = false;
			});
		},
		GetContent: function(pPostId) {
			//console.log("Talking to server for PostContent...");
			this.POST.At(pPostId).loading = true;

			var url = ADDR_GET_PHP+'?t=2&postId='+pPostId;
			url += $scope.UTIL.AjaxCachingBypassToken();

			$http.get(url).success(function(data) {
				var o = $scope.BOARD;

				o.POST.At(pPostId).isContentComplete = true;
				o.POST.At(pPostId).content = data.content;
				o.POST.At(pPostId).comments = data.comments;
				o.UpdateContentView();

				o.POST.At(pPostId).loading = false;
			});
		},


		UpdateContentView: function() {
			if (this.postId && this.POST.At(this.postId)) {	// postId exists in url
				this.selectedPost = this.POST.At(this.postId);
			} else if(this.posts && this.posts[0]) {	// no postId at all - show first post by default
				if (this.posts[0].isContentComplete) {
					this.postId = this.posts[0].postId;
					this.selectedPost = this.posts[0];
				} else {
					this.postId = this.posts[0].postId;
					this.GetContent(this.posts[0].postId);
				}
			} else {									// should not happen
				this.selectedPost = null; console.log("3");
			}

			this.show.composePost = false;
			this.show.editPost = false;

			document.getElementById(DOM_CONTENT_VIEW_ID).scrollTop = 0;
			$scope.moduleLoaded = true;
		},
		UpdatePager: function() {
			var PAGER_SIZE = 7;
			var localFirst = ((Math.ceil(this.currentPage/PAGER_SIZE)-1) * PAGER_SIZE) + 1;
			var globalLast = Math.ceil(this.postCount/this.listSize);
			var pages = new Array();
			var index = 0;
			
			if (this.currentPage > PAGER_SIZE) {
				//pages[index++] = {name:"First", no:1};
				pages[index++] = {name:"<", no:(localFirst-1)};
			}
			for (var i=0; i<PAGER_SIZE; i++) {
				if (localFirst+i > globalLast) break;
				pages[index++] = {name:(localFirst+i), no:(localFirst+i)};
			}
			if (localFirst+PAGER_SIZE-1 < globalLast) {
				pages[index++] = {name:">", no:(localFirst+PAGER_SIZE)};
				//pages[index++] = {name:"Last", no:globalLast};
			}
			
			this.pages = pages;
		}

	};



	// ///////////////////////////////////////////////////////////////////////////
	// 								BOARD - POST								//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.BOARD.POST = {
		parent: $scope.BOARD,

		OnEditClick: function() {
			this.parent.show.editPost = true;
		},
		OnDeleteClick: function(event) {
			this.Delete();
		},

		Delete: function() {
			if (confirm("Are you sure you want to delete this post?")) {
				var filesToDelete = new Array();
				if (this.parent.selectedPost.files) {
					for (var i=0; i<this.parent.selectedPost.files.length; i++) {
						filesToDelete.push(this.parent.selectedPost.files[i].name);
					}
				}
				$http.post(ADDR_POST_PHP, {
						r:'deletePost', pId:this.parent.postId, files:filesToDelete
					}).success(function(response) {
						$window.location.reload();
					}
				);
			}
		},

		At: function(pPostId) {
			if (this.parent.posts) {
				for (var i=0; i<this.parent.posts.length; i++) {
					if (this.parent.posts[i].postId == pPostId) {
						return this.parent.posts[i];
					}
				}
			}
			return null;
		}

	};



	// ///////////////////////////////////////////////////////////////////////////
	// 								BOARD - COMMENT								//
	// BE CAREFUL: COMMENTS HAVE NESTED SCOPE. MUST USE ABSOLUTE REFERENCE.		//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.BOARD.COMMENT = {
		
		OnEditClick: function(pCommentId) {
			$scope.BOARD.COMMENT.At(pCommentId).showEditCommenter = true;
		},
		OnDeleteClick: function(pCommentId) {
			$scope.BOARD.COMMENT.Delete(pCommentId);
		},
		OnReplyClick: function(pCommentId) {
			$scope.BOARD.COMMENT.PopulateReply(pCommentId);
		},

		PopulateReply: function(pCommentId) {
			var VIRTUAL_NODE_ID = 'VIRTUAL_NODE_ID';
			
			function RemoveVirtualNode(pCommentList) {
				for (var i=0; i<pCommentList.length; i++) {
					if (pCommentList[i].commentId == VIRTUAL_NODE_ID) {
						pCommentList.splice(i, 1);
						return true;
					} else {
						var result = RemoveVirtualNode(pCommentList[i].children);
						if (result) return result;
					}
				}
			}

			RemoveVirtualNode($scope.BOARD.selectedPost.comments);

			var virtualNode = {
				showNewCommenter: true,
				postId: $scope.BOARD.selectedPost.postId,
				parentCommentId: pCommentId,
				commentId: VIRTUAL_NODE_ID,
				children: [],
				Vaporize: function() {
					RemoveVirtualNode($scope.BOARD.selectedPost.comments);
				},
				UpdateScope: function() {
					$scope.BOARD.selectedPost.commentCount ++;
				}
			};

			$scope.BOARD.COMMENT.At(pCommentId).children.push(virtualNode);
		},

		Delete: function(pCommentId) {
			if (confirm("Are you sure you want to delete this comment?")) {
				var filesToDelete = new Array();
				if ($scope.BOARD.COMMENT.At(pCommentId).files) {
					for (var i=0; i<$scope.BOARD.COMMENT.At(pCommentId).files.length; i++) {
						filesToDelete.push($scope.BOARD.COMMENT.At(pCommentId).files[i].name);
					}
				}
				$http.post(ADDR_POST_PHP, {
						r:'deleteComment', cId:pCommentId, files:filesToDelete
					}).success(function() {
						$scope.BOARD.COMMENT.Get($scope.BOARD.selectedPost.comments, pCommentId).deleted = true;
						$scope.BOARD.selectedPost.commentCount --;
					}
				);
			}
		},

		At: function(pCommentId) {
			return $scope.BOARD.COMMENT.Get($scope.BOARD.selectedPost.comments, pCommentId);
		},
		Get: function(pCommentList, pCommentId) {
			for (var i=0; i<pCommentList.length; i++) {
				if (pCommentList[i].commentId == pCommentId) {
					return pCommentList[i];
				} else {
					var c = $scope.BOARD.COMMENT.Get(pCommentList[i].children, pCommentId);
					if (c) return c;
				}
			}
		}

	};
	


	// ///////////////////////////////////////////////////////////////////////////
	// 								BOARD - SEARCH								//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.BOARD.SEARCH = {
		parent: $scope.BOARD,

		Initialize: function() {
			this.timer = null;
			this.pendingString = null;
			this.inputString = "";
			this.options = {
				author: true, title: true, content: true, comments: true
			};
			this.show = false;
		},

		OnSearchButtonClick: function() {
			this.show = !this.show;
			if (this.show) {
				$timeout(function() { document.getElementById(DOM_SEARCH_FIELD_ID).focus(); }, 50);
			}
		},

		OnFieldChange: function() {
			this.TriggerSearch();
		},
		OnOptionChange: function() {
			$timeout(function() { document.getElementById(DOM_SEARCH_FIELD_ID).focus(); }, 50);
			this.TriggerSearch();	
		},

		TriggerSearch: function() {
			if (this.timer) { // search already in progress; just save the string
				this.pendingString = this.inputString;
			} else { // otherwise; start searching 
				this.SearchFor(this.inputString);
			}
		},
		
		// SearchFor MUST USE ABSOLUTE REFERENCE - $timeout is nested
		SearchFor: function() {
			// clear saved string and start a timer
			$scope.BOARD.SEARCH.pendingString = null;
			$scope.BOARD.SEARCH.timer = $timeout(function() {
				if ($scope.BOARD.SEARCH.pendingString || $scope.BOARD.SEARCH.pendingString === "") {
					$scope.BOARD.SEARCH.SearchFor();
				} else {
					$scope.BOARD.SEARCH.timer = null;
				}
			}, 500);

			// talk to the backend!
			//console.log("Searching for: " + this.inputString);
			$scope.BOARD.UpdateList();
		}

	};



	// ///////////////////////////////////////////////////////////////////////////
	// 								BOARD - TAG									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.BOARD.TAG = {
		parent: $scope.BOARD,

		Initialize: function() {
			this.show = false;
			this.availableTags = new Array();
		},

		OnTagButtonClick: function() {
			this.show = !this.show;
		},

		OnTagClick: function(pTag) {
			this.show = true;

			if (this.ToggleTag(pTag)) {
				this.parent.UpdateList();
			}
		},

		ToggleTag: function(pTag) {
			if (this.availableTags) {
				for (var i=0; i<this.availableTags.length; i++) {
					if (pTag.tagName == this.availableTags[i].tagName) {
						this.availableTags[i].selected = !this.availableTags[i].selected;
						return true;
					}
				}
			}
			return false;
		}

	};



	// ///////////////////////////////////////////////////////////////////////////
	// 								BOARD - FILE								//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.BOARD.FILE = {
		parent: $scope.BOARD,

		UploadFile: function(pFile) {
			if (!$scope.BOARD.FILE.IsValidType(pFile.name)) {
				$timeout(function() { pFile.isInvalidType = true; }, 50);
				return;
			} else if (pFile.size > 52428800) {
				$timeout(function() { pFile.isInvalidSize = true; }, 50);
				return;
			}

			var form = new FormData();
			var xhr = new XMLHttpRequest();
			var alias = new Date().getTime() + "-" + pFile.name;

			pFile.failed = false;
			pFile.uploaded = false;
			pFile.progress = 0;
			pFile.alias = alias;
			pFile.address = "tmp/"+$scope.session.userId+"/"+alias;
			pFile.isImage = $scope.BOARD.FILE.IsImage(pFile.name);
			
			form.append('alias', alias);
			form.append('file', pFile);

			xhr.upload.onprogress = (function(pFile) {
				return function(e) {
					$scope.$apply(function() { pFile.progress = Math.ceil(e.loaded / e.total * 100); });
				}	
			})(pFile);

			xhr.upload.onload = (function(pFile) {
				return function(e) {
					$scope.$apply(function() { pFile.uploaded = true; });
				}
			})(pFile);

			xhr.onreadystatechange = (function(pFile) {
				return function() {
					if (xhr.readyState==4 && xhr.status==200) {
						if (xhr.responseText.indexOf("success")>=0) {
							pFile.failed = false;
						} else {
							pFile.failed = true;
						}
					} else {
						//$scope.$apply(function() { pFile.failed = true; });
					}
				}
			})(pFile);

			xhr.open('POST', ADDR_UPLOAD_PHP, true);
			xhr.send(form);
		},

		BuildAddedFilesList: function(pFiles) {
			var addedFiles = new Array();
			if (pFiles) {
				for (var i=0; i<pFiles.length; i++) {
					if (!pFiles[i].failed && !pFiles[i].deleted && pFiles[i].uploaded && pFiles[i].alias) {
						addedFiles.push({ file:pFiles[i], alias:pFiles[i].alias });
					}
				}
			}
			return addedFiles;
		},

		BuildDeletedFilesList: function(pFiles) {
			var deletedFiles = new Array();
			if (pFiles) {
				for (var i=0; i<pFiles.length; i++) {
					if (!pFiles[i].uploaded && pFiles[i].deleted) {
						deletedFiles.push(pFiles[i].name);
					}
				}
			}
			return deletedFiles;
		},

		IsValidType: function(pFileName) {
			var ext = pFileName.split('.');
			ext = ext[ext.length - 1];
			ext = ext.toLowerCase();
			
			var whitelist = [ 'jpg', 'jpeg', 'gif', 'bmp', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'zip' ];
			for (var i=0; i<whitelist.length; i++) {
				if (whitelist[i] == ext) return true;
			}
			return false;
		},

		IsImage: function(pFileName) {
			var ext = pFileName.split('.');
			ext = ext[ext.length - 1];
			ext = ext.toLowerCase();
			return (ext=='jpg' || ext=='jpeg' || ext=='gif' || ext=='bmp' || ext=='png');
		},

		AddFields: function(pFileList) {
			if (!pFileList) return
			for (var i=0; i<pFileList.length; i++) {
				pFileList[i].isImage = $scope.BOARD.FILE.IsImage(pFileList[i].name);
				pFileList[i].onServer = true;
			}
		}

	};



	// ///////////////////////////////////////////////////////////////////////////
	// 									ROOT									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.Initialize = function() {
		$scope.show = {
			splash: true, home: false, about: false, board: false
		};
		$scope.session = {
			userId: USER_ID, loggedIn: LOGGED_IN, userName: USER_NAME, userEmail: USER_EMAIL, usingFB: USING_FB=="0"?false:true
		};
		$scope.title = "UWKSA";
		$scope.message = "";
		$scope.modules = [
			{ address: "",			name: "HOME",		fullName:"HOME",				type:1 },
			{ address: "about",		name: "ABOUT",		fullName:"ABOUT",				type:2 },
			{ address: "news",		name: "NEWS",		fullName:"NEWS & EVENTS",		type:3 },
			{ address: "qna",		name: "Q&A",		fullName:"QUESTIONS & ANSWERS",	type:3 },
			{ address: "column",	name: "COLUMN",		fullName:"COLUMN",				type:3 },
			{ address: "sublet",	name: "SUBLET",		fullName:"SUBLET",				type:3 },
			{ address: "tutors",	name: "TUTORS",		fullName:"TUTORS",				type:3 },
			{ address: "bns",		name: "BUY&SELL",	fullName:"BUY & SELL",			type:3 },
			{ address: "exams",		name: "EXAMS",		fullName:"OLD EXAMS",			type:3 },
			{ address: "community",	name: "COMMUNITY",	fullName:"COMMUNITY",			type:3 }
		];
		$scope.popover = POPOVER_NONE;

		$scope.selectedModule = null;
		$scope.moduleLoaded = false;

		$scope.BOARD.Initialize();
	}
	$scope.Initialize();

	$scope.GetModule = function(pAddress) {
		if (typeof pAddress === 'undefined') return $scope.modules[0];
		for (var i=0; i<$scope.modules.length; i++) {
			if ($scope.modules[i].address == pAddress) {
				return $scope.modules[i];
			}
		}
	}

	// Only one popover at a time
	$scope.OnKeyUp = function(pKey) {
		if ($scope.ignoreNextEscape) {
			$scope.ignoreNextEscape = false;
		} else if (pKey == 27) {
			$scope.popover = POPOVER_NONE;
		}
	}

	$scope.IgnoreNextEscape = function() {
		$scope.ignoreNextEscape = true;
	}

	$scope.$watch('moduleLoaded', function() {
		if ($scope.moduleLoaded && $scope.fontLoaded) {
			$scope.show.splash = false;
		}
	});

	$scope.$watch('fontLoaded', function() {
		if ($scope.moduleLoaded && $scope.fontLoaded) {
			$scope.show.splash = false;
		}
	});


	// ///////////////////////////////////////////////////////////////////////////
	// 									ROUTE									//
	// ///////////////////////////////////////////////////////////////////////////
	$scope.$on('$routeChangeSuccess', function() {
		$scope.encodedUrl = encodeURIComponent($location.absUrl());

		if ($routeParams.moduleAddress) {
			if ($routeParams.moduleAddress == "confirmed") {
				$scope.message = "Your email has been confirmed.";
			} else if ($routeParams.moduleAddress == "activated") {
				$scope.message = "You can now use your Facebook account to login to UWKSA.";
			} else if ($routeParams.moduleAddress == "changed") {
				$scope.message = "Your password has been successfully changed.";
			} else if ($routeParams.moduleAddress == "notRegistered") {
				$scope.message = "Your account is not registered. (To register, Sign Up - Connect with Facebook)";
			}
		}

		// invalid module: manually redirect
		var moduleFromUrl = $scope.GetModule($routeParams.moduleAddress);
		if (typeof moduleFromUrl === 'undefined') {
			$location.path('/');
			return;
		}

		if ($scope.selectedModule != moduleFromUrl) {
			$scope.moduleLoaded = false;
			$scope.selectedModule = moduleFromUrl;
			
			$scope.title = 'UWKSA :: ' + moduleFromUrl.name;
			document.body.scrollTop = 0;
			document.getElementById(DOM_CONTENT_VIEW_ID).scrollTop = 0;

			$scope.BOARD.ResetVars();
			$scope.show.home = false;
			$scope.show.about = false;
			$scope.show.board = false;
		}
 
		$scope.popover = POPOVER_NONE;

		if ($scope.selectedModule.type == 1) { // home
			$scope.show.home = true;
			
			if (!$scope.moduleLoaded) {
				$scope.HOME.GetLatestFeed();
			}

		} else if ($scope.selectedModule.type == 2) { // about
			$scope.show.about = true;
			$scope.moduleLoaded = true;

		} else if ($scope.selectedModule.type == 3) { // board
			$scope.show.board = true;

			var o = $scope.BOARD; // save bytes!

			// check if postId in url is digit
			o.postId = null;
			if (/^\d+$/.test($routeParams.postId)) {
				o.postId = $routeParams.postId
			} else {
				$location.path('/'+$scope.selectedModule.address);
			}

			if (!$scope.moduleLoaded || (o.postId && !o.POST.At(o.postId))) {
				o.GetList(/*none*/);
			} else if (o.postId) {
				if (!o.POST.At(o.postId).isContentComplete) {
					o.GetContent(o.postId);
				} else {
					o.UpdateContentView();
				}
			} else {
				o.UpdateContentView();
			}
		}
	});

});
