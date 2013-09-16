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
			tree: '=ngModel', depth: '=',
			onEdit: '&', onDelete: '&', onReply: '&', doLinkify: '&',
			ignoreNextEscape: '&', uploadFile: '&', buildAddedFilesList: '&', buildDeletedFilesList: '&'
		}
	};
});

app.directive('cnode', function($compile) {
	return { 
		restrict: 'A',
		template:'<div ng-show="!node.deleted&&!node.showNewCommenterView">'
				+ '<div class="commentHeader">'
					+ '<div class="commentDetail">'
						+ '<span class="commentAuthor linklet">{{node.author}}</span>'
						+ ' at {{node.date}}'
						+ ' <span ng-show="node.canEdit">'
							+ '| <span class="linklet" ng-click="onEdit({cId: node.commentId})">Edit</span>'
							+ ' | <span class="linklet" ng-click="onDelete({cId: node.commentId})">Delete</span>'
						+ '</span>'
					+ '</div>'
					+ '<div class="commentReply">'
						+ '<span class="linklet" ng-show="node.canComment&&depth<777" ng-click="onReply({cId: node.commentId})">Reply</span>'
					+ '</div>'
				+ '</div>'
				+ '<div class="commentBody" ng-show="!node.showEditCommenterView" ng-bind-html-unsafe="doLinkify({str: node.content})"></div>'
				+ '<div class="commentFile" ng-show="!node.showEditCommenterView&&node.files">FILE incomplete UI<div ng-repeat="file in node.files">{{file.name}} ({{file.alias}}, {{file.path}})</div></div>'
				+ '<div class="commentEdit" ng-show="node.showEditCommenterView" ng-init="param=node">'
					+ '<div ng-include src="\'tp-commenter\'" ng-controller="EditCommenterController"></div>'
				+ '</div>'
			+ '</div>'
			+ '<div class="commentNew" ng-show="!node.deleted&&node.showNewCommenterView" ng-init="param=node">'
				+ '<div ng-include src="\'tp-commenter\'" ng-controller="ReplyCommenterController"></div>'
			+ '</div>'
			+ '<div class="commentDeleted" ng-show="node.deleted">'
				+ 'This comment is deleted.'
			+ '</div>',
		link: function(scope, elm, attrs) {
			//if (scope.node.children.length > 0) {
				scope.node.IgnoreNextEscape = function() { scope.ignoreNextEscape(); }
				scope.node.UploadFile = function(file) { scope.uploadFile({file: file}); }
				scope.node.BuildAddedFilesList = function(files) { return scope.buildAddedFilesList({files: files}); }
				scope.node.BuildDeletedFilesList = function(files) { return scope.buildDeletedFilesList({files: files}); }
				var child = $compile('<div ctree depth="'+(scope.depth+1)+'" ng-model="node.children"'
						+ 'on-edit="onEdit({cId: cId})"'
						+ 'on-delete="onDelete({cId: cId})"'
						+ 'on-reply="onReply({cId: cId})"'
						+ 'do-linkify="doLinkify({str: str})"'
						+ 'ignore-next-escape="ignoreNextEscape()"'
						+ 'upload-file="uploadFile({file: file})"'
						+ 'build-added-files-list="buildAddedFilesList({files: files})"'
						+ 'build-deleted-files-list="buildDeletedFilesList({files: files})"></div>'
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
	$scope.fields = {
		title:"", content:"", pinned:false, selectedTags:new Array(), files:new Array(),
		submitting:false, incomplete:false
	};

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

			$http.post('backend/post.php', {
				r:'newPost', pId:-1, bName:$scope.$parent.selectedModule.address,
				title:fields.title, content:fields.content, pinned:fields.pinned, addedTags:addedTagNames,
				addedFiles:$scope.$parent.board.BuildAddedFilesList(fields.files)
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
			$scope.$parent.board.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

	function ResetEditor() {
		$scope.fields.title = "";
		$scope.fields.content = "";
		$scope.fields.pinned = false;
		$scope.fields.submitting = false;
		$scope.$parent.showPopover = 0;
		$scope.$parent.board.hasDraft = false;
	}

	$scope.$watch('$parent.showPopover', function() {
		if ($scope.$parent.showPopover == 'pComposePost') {
			$scope.fields.incomplete = false;
			$scope.fields.selectedTags = new Array();
		}
	});
});

app.controller('EditController', function($http, $scope, $location, $window, $timeout) {
	$scope.fields = {
		title:"", content:"", pinned:false, selectedTags:new Array(), files:new Array(),
		submitting:false, incomplete:false
	};

	$scope.OnEditorCancel = function() {
		$scope.$parent.showPopover = 0;
	}
	
	$scope.OnEditorSubmit = function(fields) {
		var titleExists = fields.title && fields.title!="";
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (titleExists && contentExists && $scope.$parent.board.postId) {
			fields.submitting = true;

			var addedTagNames = new Array();
			var deletedTagNames = new Array();
			for (var i=0; i<$scope.$parent.board.availableTags.length; i++) {
				var currentTagName = $scope.$parent.board.availableTags[i].tagName;

				var existsInOldCopy = false;
				for (var j=0; j<$scope.$parent.board.selectedPost.selectedTags.length; j++) {
					if ($scope.$parent.board.selectedPost.selectedTags[j].tagName == currentTagName) {
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

			$http.post('backend/post.php', {
				r:'editPost', pId:$scope.$parent.board.postId, bName:$scope.$parent.selectedModule.address,
				title:fields.title, content:fields.content, pinned:fields.pinned,
				addedTags:addedTagNames, deletedTags:deletedTagNames,
				addedFiles:$scope.$parent.board.BuildAddedFilesList(fields.files),
				deletedFiles:$scope.$parent.board.BuildDeletedFilesList(fields.files)
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
			$scope.$parent.board.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

	$scope.$watch('$parent.showPopover', function() {
		if ($scope.$parent.showPopover == 'pEditPost') {
			$scope.fields.title = $scope.$parent.board.selectedPost.title;
			$scope.fields.content = $scope.$parent.board.selectedPost.content;
			$scope.fields.files = $scope.$parent.board.selectedPost.files ? $scope.$parent.board.selectedPost.files.slice() : new Array();
			$scope.fields.pinned = $scope.$parent.board.selectedPost.pinned;
			$scope.fields.submitting = false;
			$scope.fields.incomplete = false;
			$scope.fields.selectedTags = $scope.$parent.board.selectedPost.selectedTags ? $scope.$parent.board.selectedPost.selectedTags.slice() : new Array();
		} else {
			$scope.fields.title = "";
			$scope.fields.content = "";
			$scope.fields.pinned = false;
		}
	});
});

app.controller('MainCommenterController', function($http, $scope, $timeout) {
	$scope.fields = { content:"", files:new Array(), submitting:false, incomplete:false };
	$scope.showCancelButton = false;

	$scope.OnCommenterCancel = function() { }
	
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		fields.incomplete = false;

		if (contentExists && $scope.$parent.board.postId) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'newComment', parentPost:$scope.$parent.board.postId, parentComment:-1,
				content:fields.content,
				addedFiles:$scope.$parent.board.BuildAddedFilesList(fields.files)
			}).success(function(response) {
				$scope.$parent.board.selectedPost.comments.push(response);
				$scope.$parent.board.selectedPost.commentCount ++;
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
			$scope.$parent.board.UploadFile(uploader.files[i]);
		}
	}
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

	$scope.$watch('$parent.board.selectedPost.postId', function() {
		$scope.fields.content = "<br>"; // FIX_FF for commenter height
		$scope.fields.files.length = 0;
		$scope.fields.submitting = false;
		$scope.fields.incomplete = false;

		if (ICantBelieveYouAreUsingInternetExplorer() > 0) {
			$scope.fields.content = ""; // FIX_IE for the fix above
		}
	});
});

app.controller('ReplyCommenterController', function($http, $scope, $timeout) {
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
					$scope.param.showNewCommenterView = false;
					$scope.param.commentId = response.commentId;
					$scope.param.canEdit = response.canEdit;
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
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}
});

app.controller('EditCommenterController', function($http, $scope, $timeout) {
	$scope.fields = { content:"", files:new Array(), submitting:false, incomplete:false };
	$scope.showCancelButton = true;

	$scope.OnCommenterCancel = function() {
		$scope.param.showEditCommenterView = false;
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
				$scope.param.showEditCommenterView = false;
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
	$scope.OnFileDeleteClick = function(file) {
		file.deleted = true;
	}

	$scope.$watch('param.showEditCommenterView', function() {
		if ($scope.param.showEditCommenterView) {
			$scope.fields.content = $scope.param.content;
			$scope.fields.files = $scope.param.files ? $scope.param.files.slice() : new Array();
			$scope.fields.submitting = false;
			$scope.fields.incomplete = false;
		} else {
			$scope.fields.content = "";
		}
	});
});

app.controller('LoginController', function($http, $scope, $window, $timeout) {
	$scope.fields = { email:"", password:"", submitting:false, incomplete:false, failed:false };
	
	$scope.OnLoginSubmitClick = function(fields) {
		var emailExists = fields.email && fields.email!="";
		var passwordExists = fields.password && fields.password!="";
		fields.incomplete = false;
		fields.failed = false;

		if (emailExists && passwordExists) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'login', email:fields.email, password:fields.password
			}).success(function(response) {
				if (response.ResultCode == 0) {
					$window.location.reload();
				} else if (response.ResultCode == 2) {
					fields.failed = true;
					fields.failedMessage = "Retry after confirming your email address.";
				} else {
					fields.failed = true;
					fields.failedMessage = "The email or password you entered is incorrect.";
				}
			});
		} else {
			$timeout(function() {
				fields.incomplete = true;
				$timeout(function() { fields.incomplete = false; }, 300)
			}, 10);
		}
	}
	$scope.OnLoginCancelClick = function() {
		$scope.$parent.showPopover = 0;
		$scope.fields.incomplete = false;
		$scope.fields.failed = false;
	}
	$scope.$watch('$parent.showPopover', function() {
		if ($scope.$parent.showPopover == 0) {
			$scope.fields.incomplete = false;
			$scope.fields.failed = false;
		}
	});
});

app.controller('SignUpController', function($http, $scope, $window, $timeout) {
	$scope.InitController = function() {
		$scope.fields = {
			name:"", nameChecking:false, namePassed:false, nameFailed:false,
			email:"", emailChecking:false, emailPassed:false, emailFailed:false, emailInvalid:false,
			password:"", passwordPassed:false, passwordFailed:false,
			confirm:"", confirmPassed:false, confirmFailed:false,
			submitting:false, incomplete:false, signUpCompleted:false
		};
	}
	$scope.InitController();

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

			$http.post('backend/post.php', {
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
	$scope.OnSignUpCancelClick = function() {
		$scope.$parent.showPopover = 0;
		$scope.fields.incomplete = false;
	}
	$scope.OnSignUpCloseClick = function() {
		$scope.$parent.showPopover = 0;
		$scope.InitController();
	}
	$scope.$watch('$parent.showPopover', function() {
		if ($scope.$parent.showPopover == 0) {
			$scope.fields.incomplete = false;
		}
	});

	$scope.OnNameFieldBlur = function(fields) {
		fields.namePassed = false;
		fields.nameFailed = false;

		if (fields.name.length >= 2) {
			fields.nameChecking = true;

			$http.get('backend/get.php?t=3&UserName='+fields.name).success(function(data) {
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
			
			$http.get('backend/get.php?t=4&EmailAddress='+fields.email).success(function(data) {
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
	$scope.InitController = function() {
		$scope.option = 0;
		$scope.fields = {
			name:"", nameChecking:false, namePassed:false, nameFailed:false,
			password:"", passwordPassed:false, passwordFailed:false,
			confirm:"", confirmPassed:false, confirmFailed:false,
			oldPassword:"", submitting:false, incomplete:false, failed:false, changeSuccessful:false
		};
	}
	$scope.InitController();

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

					$http.get('backend/get.php?t=3&UserName='+fields.name).success(function(data) {
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
			$http.post('backend/post.php', {
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

			$http.post('backend/post.php', {
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

			$http.get('backend/get.php?t=3&UserName='+fields.name).success(function(data) {
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
	$scope.OnChangeNameClick = function() {
		$scope.option = 1;
	}
	$scope.OnChangePasswordClick = function() {
		$scope.option = 2;
	}
	$scope.OnDoneClick = function() {
		if ($scope.option == 1) {
			$window.location.reload(); //name change requires refresh
		} else {
			$scope.$parent.showPopover = 0;
			$scope.InitController();
		}
	}
	$scope.OnCancelClick = function() {
		$scope.$parent.showPopover = 0;
		$scope.InitController();
	}
	$scope.$watch('$parent.showPopover', function() {
		if ($scope.$parent.showPopover == 0) {
			$scope.InitController();
		}
	});
});

app.controller('RootController', function($http, $scope, $location, $route, $routeParams, $window, $timeout) {
	//console.log("***** INITIALIZE RootController *****");
	$scope.loggedIn = LOGGED_IN;
	$scope.userName = USER_NAME;
	$scope.userEmail = USER_EMAIL;
	$scope.browserTitle = 'UWKSA';
	
	$scope.modules = [
		{ address: "",			name: "HOME",		type:1 },
		{ address: "about",		name: "ABOUT",		type:2 },
		{ address: "news",		name: "NEWS",		type:3 },
		{ address: "qna",		name: "Q&A",		type:3 },
		{ address: "column",	name: "COLUMN",		type:3 },
		{ address: "sublet",	name: "SUBLET",		type:3 },
		{ address: "tutors",	name: "TUTORS",		type:3 },
		{ address: "bns",		name: "BUY&SELL",	type:3 },
		{ address: "exams",		name: "EXAMS",		type:3 },
		{ address: "community",	name: "COMMUNITY",	type:3 }
	];
	$scope.GetModule = function(address) {
		if (typeof address === 'undefined') return $scope.modules[0];
		for (var i=0; i<$scope.modules.length; i++) {
			if ($scope.modules[i].address == address) {
				return $scope.modules[i];
			}
		}
	}

	$scope.util = {
		StripHTML: function(htmlString) {
			return htmlString.replace(/<img(?:.|\n)*?>/gm, ' [ attached image ] ').replace(/<(?:.|\n)*?>/gm, '');
		},
		Linkify: function(htmlString) {
			if (htmlString) {
				return htmlString.replace(/(^(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]))|(>(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]))|((.[^>"]|[^=]")(\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]))/gi,
					function(match, p1, p2, offset, string) {
						var index = match.indexOf("http");
						return match.substring(0, index)+'<a href="'+match.substring(index)+'" target="_blank">'+match.substring(index)+'</a>'
					});
			}
		}
	};

	$scope.user = {
		OnLoginClick: function() {
			$scope.user.TogglePopover('pLogin');
		},
		OnSignUpClick: function() {
			$scope.showPopover = 'pSignUp';
		},
		OnLogoutClick: function() {
			$http.post('backend/post.php', {
				r:'logout'
			}).success(function(response) {
				$window.location.reload();
			});
		},
		OnProfileClick: function() {
			$scope.user.TogglePopover('pProfile');
		},
		TogglePopover: function(popoverId) {
			if ($scope.showPopover == popoverId) {
				$scope.showPopover = 0;
			} else {
				$scope.showPopover = popoverId;
			}
		}
	};

	$scope.home = {
		OnExpandClick: function(indexToOpen) {
			$scope.home.news[indexToOpen].open = true;
		},

		GetLatestFeed: function() {
			var dateToken = new Date().getTime();
			var params = [
				{ boardName:"news", postLimit:3, requirePreview:true },
				{ boardName:"qna", postLimit:8, requirePreview:false },
				{ boardName:"column", postLimit:5, requirePreview:false },
				{ boardName:"sublet", postLimit:5, requirePreview:false },
				{ boardName:"tutors", postLimit:5, requirePreview:false },
				{ boardName:"bns", postLimit:5, requirePreview:false },
				{ boardName:"exams", postLimit:5, requirePreview:false },
				{ boardName:"community", postLimit:5, requirePreview:false }
			];
			
			var url = 'backend/get.php?t=5&FeedBoard='+JSON.stringify(params)+'&token='+dateToken;

			$http.get(url).success(function(data) {
				if (data) {
					$scope.home.news = data[0];
					$scope.home.qna = data[1];
					$scope.home.column = data[2];
					$scope.home.sublet = data[3];
					$scope.home.tutors = data[4];
					$scope.home.bns = data[5];
					$scope.home.exams = data[6];
					$scope.home.community = data[7];

					if ($scope.home.news[0]) {
						$scope.home.news[0].open = true;
					}
				}
				$scope.initialized = true;
			});
		}
	};

	$scope.board = {
		Clear: function() {
			$scope.board.searchInputString = "";
			
			if ($scope.board.posts) {
				$scope.board.posts.length = 0;
			}
			if ($scope.board.availableTags) {
				$scope.board.availableTags.length = 0;
				$scope.board.availableTags = null;
			}

			$scope.board.selectedPost = null;
			$scope.board.showEmptyView = true;
			$scope.board.showContentView = false;

			$scope.board.searchOptions = {
				author:true, title:true, content:true, comments:true
			};
		},


		OnPageChange: function(pageNo) {
			if (pageNo != $scope.board.currentPage) $scope.board.GetPostList(pageNo);
		},
		OnTagClick: function(tag) {
			if ($scope.board.ToggleTag(tag)) {
				$scope.board.GetPostList($scope.board.currentPage);
			}
		},


		OnSearchFieldChange: function() {
			$scope.board.TriggerSearch();
		},
		OnSearchOptionChange: function() {
			$timeout(function() { document.getElementById('searchField').focus(); }, 50);
			$scope.board.TriggerSearch();	
		},
		SetSearchHasFocus: function(val) {
			$scope.board.searchHasFocus = val;
		},
		SetSearchHasMouse: function(val) {
			$scope.board.searchHasMouse = val;
		},
		TriggerSearch: function() {
			if ($scope.board.searchTimer) {
				// search already in progress; just save the string
				$scope.board.searchPendingString = $scope.board.searchInputString;
			} else {
				// otherwise; start searching 
				$scope.board.SearchFor($scope.board.searchInputString);
			}
		},


		OnComposeClick: function() {
			$scope.showPopover = 'pComposePost';
		},
		OnMinimizeClick: function() {
			$scope.showPopover = 0;
			$scope.board.hasDraft = true;
		},
		OnContinueClick: function() {
			$scope.showPopover = 'pComposePost';
		},


		OnPostEditClick: function() {
			$scope.showPopover = 'pEditPost';
		},
		OnPostDeleteClick: function(event) {
			$scope.board.OpenDeleteDialog(-999);
		},


		OnCommentEditClick: function(commentId) {
			$scope.board.CommentAt(commentId).showEditCommenterView = true;
		},
		OnCommentDeleteClick: function(commentId) {
			$scope.board.OpenDeleteDialog(commentId)
		},
		OnCommentReplyClick: function(commentId) {
			var VIRTUAL_NODE_ID = 'VIRTUAL_NODE_ID';
			
			function RemoveVirtualNode(cList) {
				for (var i=0; i<cList.length; i++) {
					if (cList[i].commentId == VIRTUAL_NODE_ID) {
						cList.splice(i, 1);
						return true;
					} else {
						var result = RemoveVirtualNode(cList[i].children);
						if (result) return result;
					}
				}
			}

			RemoveVirtualNode($scope.board.selectedPost.comments);

			var virtualNode = {
				showNewCommenterView: true,
				postId: $scope.board.selectedPost.postId,
				parentCommentId: commentId,
				commentId: VIRTUAL_NODE_ID,
				children: [],
				Vaporize: function() {
					RemoveVirtualNode($scope.board.selectedPost.comments);
				},
				UpdateScope: function() {
					$scope.board.selectedPost.commentCount ++;
				}
			};

			$scope.board.CommentAt(commentId).children.push(virtualNode);
		},

		SearchFor: function(s) {
			// clear saved string and start a timer
			$scope.board.searchPendingString = null;
			$scope.board.searchTimer = $timeout(function() {
				if ($scope.board.searchPendingString || $scope.board.searchPendingString === "") {
					$scope.board.SearchFor($scope.board.searchPendingString);
				} else {
					$scope.board.searchTimer = null;
				}
			}, 500);

			// talk to the backend!
			//console.log("Searching for: " + $scope.board.searchInputString);
			$scope.board.GetPostList($scope.board.currentPage);
		},
		ToggleTag: function(tag) {
			if ($scope.board.availableTags) {
				for (var i=0; i<$scope.board.availableTags.length; i++) {
					if (tag.tagName == $scope.board.availableTags[i].tagName) {
						$scope.board.availableTags[i].selected = !$scope.board.availableTags[i].selected;
						return true;
					}
				}
			}
			return false;
		},


		GetPostList: function(pageNo) {
			pageNo = typeof pageNo !== 'undefined' ? pageNo : -1;
			//console.log("Talking to server for PostList...");

			if (!$scope.board.searchTimer) {
				$scope.board.buildingPostList = true;
				if ($scope.board.posts) $scope.board.posts.length = 0;
			}
			
			var url = 'backend/get.php?t=1&postLimit=15&boardName='+$scope.selectedModule.address;
			
			// page no
			if (pageNo > 0) url += '&pageNo='+pageNo;
			else if ($scope.board.postId) url += '&postId='+$scope.board.postId;
			
			// search
			url += '&searchParam='+$scope.board.searchInputString;
			if ($scope.board.searchInputString && $scope.board.searchInputString!="") {
				url += '&searchOptions=';
				url += $scope.board.searchOptions.author ? '1' : '0';
				url += $scope.board.searchOptions.title ? '1' : '0';
				url += $scope.board.searchOptions.content ? '1' : '0';
				url += $scope.board.searchOptions.comments ? '1' : '0';
			}
			

			// tags
			if ($scope.board.availableTags) {
				var selectedTags = new Array();
				for (var i=0; i<$scope.board.availableTags.length; i++) {
					if ($scope.board.availableTags[i].selected) {
						selectedTags.push($scope.board.availableTags[i].tagName);
					}
				}
				url += '&tags='+JSON.stringify(selectedTags);
			}

			// FIX_IE for ajax caching
			var dateToken = new Date().getTime();
			url += '&token='+dateToken;
			
			$http.get(url).success(function(data) {
				if (data.error == true) {
					$location.path('/'+$scope.selectedModule.address);

				} else {
					$scope.board.listSize = data.listSize;
					$scope.board.currentPage = data.currentPage;
					$scope.board.postCount = data.postCount;
					$scope.board.canPost = data.canPost;
					$scope.board.posts = data.postPreviews;
					if (!$scope.board.availableTags) {
						$scope.board.availableTags = data.availableTags;
						for (var i=0; i<data.availableTags.length; i++) {
							data.availableTags.selected = false;
						}
					}
					
					if (pageNo <= 0) $scope.board.UpdateContentView();
					$scope.board.UpdatePager();
					
					$scope.loaded = true;
				}

				$scope.board.buildingPostList = false;
			});
		},
		GetPostContent: function(postId) {
			//console.log("Talking to server for PostContent...");
			$scope.board.PostAt(postId).loading = true;

			var dateToken = new Date().getTime(); // FIX_IE for ajax caching
			$http.get('backend/get.php?t=2&postId='+postId+'&token='+dateToken).success(function(data) {
				$scope.board.PostAt(postId).isContentComplete = true;
				$scope.board.PostAt(postId).content = data.content;
				$scope.board.PostAt(postId).comments = data.comments;
				$scope.board.UpdateContentView();

				$scope.board.PostAt(postId).loading = false;
			});
		},


		OpenDeleteDialog: function(cmd) {
			$scope.board.deleteDialogFor = cmd;

			if (confirm("Are you sure you want to delete this?")) {
				if ($scope.board.deleteDialogFor == -999) {
					$scope.board.DeletePost();
				} else if ($scope.board.deleteDialogFor > 0) {
					$scope.board.DeleteComment($scope.board.deleteDialogFor);
				}
			}
		},
		DeletePost: function() {
			$http.post('backend/post.php', {
					r:'deletePost', pId:$scope.board.postId
				}).success(function(response) {
					$window.location.reload();
				});
		},
		DeleteComment: function(commentId) {
			$http.post('backend/post.php', {
					r:'deleteComment', cId:commentId
				}).success(function() {
					$scope.board.RecurseFindComment($scope.board.selectedPost.comments, commentId).deleted = true;
					$scope.board.selectedPost.commentCount --;
				});
		},


		UpdateContentView: function() {
			if ($scope.board.postId && $scope.board.PostAt($scope.board.postId)) {
				//console.log("Showing Post %s.", $scope.postId);
				$scope.board.selectedPost = $scope.board.PostAt($scope.board.postId);
				$scope.board.showEmptyView = false;
				$scope.board.showContentView = true;
			} else if($scope.board.posts && $scope.board.posts[0]) {
				//console.log("Showing first post on the list");
				$scope.board.postId = $scope.board.posts[0].postId;
				$scope.board.selectedPost = $scope.board.posts[0];
				$scope.board.showEmptyView = false;
				$scope.board.showContentView = true;
			} else {
				//console.log("Clearing Post.");
				$scope.board.selectedPost = null;
				$scope.board.showEmptyView = true;
				$scope.board.showContentView = false;
			}
			document.getElementById('w1-contentView').scrollTop = 0;
			$scope.initialized = true;
		},
		UpdatePager: function() {
			var PAGER_SIZE = 7;
			var localFirst = ((Math.ceil($scope.board.currentPage/PAGER_SIZE)-1) * PAGER_SIZE) + 1;
			var globalLast = Math.ceil($scope.board.postCount/$scope.board.listSize);
			var pages = new Array();
			var index = 0;
			
			if ($scope.board.currentPage > PAGER_SIZE) {
				//pages[index++] = {name:"First", no:1};
				pages[index++] = {name:"Prev", no:(localFirst-1)};
			}
			for (var i=0; i<PAGER_SIZE; i++) {
				if (localFirst+i > globalLast) break;
				pages[index++] = {name:(localFirst+i), no:(localFirst+i)};
			}
			if (localFirst+PAGER_SIZE-1 < globalLast) {
				pages[index++] = {name:"Next", no:(localFirst+PAGER_SIZE)};
				//pages[index++] = {name:"Last", no:globalLast};
			}
			
			$scope.board.pages = pages;
		},


		PostAt: function(postId) {
			if ($scope.board.posts) {
				for (var i=0; i<$scope.board.posts.length; i++) {
					if ($scope.board.posts[i].postId == postId) {
						return $scope.board.posts[i];
					}
				}
			}
			return null;
		},
		CommentAt: function(commentId) {
			return $scope.board.RecurseFindComment($scope.board.selectedPost.comments, commentId);
		},


		RecurseFindComment: function(cList, cId) {
			for (var i=0; i<cList.length; i++) {
				if (cList[i].commentId == cId) {
					return cList[i];
				} else {
					var c = $scope.board.RecurseFindComment(cList[i].children, cId);
					if (c) return c;
				}
			}
		},

		UploadFile: function(file) {
			var form = new FormData();
			var xhr = new XMLHttpRequest();
			var alias = new Date().getTime() + "-" + file.name;

			file.failed = false;
			file.uploaded = false;
			file.progress = 0;
			file.alias = alias;
			
			form.append('alias', alias);
			form.append('file', file);

			xhr.upload.onprogress = (function(file) {
				return function(e) {
					$scope.$apply(function() { file.progress = Math.ceil(e.loaded / e.total * 100); });
				}	
			})(file);

			xhr.upload.onload = (function(file) {
				return function(e) {
					$scope.$apply(function() { file.uploaded = true; });
				}
			})(file);

			xhr.onreadystatechange = (function(file) {
				return function() {
					if (xhr.readyState==4 && xhr.status==200 && xhr.responseText=="success") {
						file.failed = false;
					} else {
						$scope.$apply(function() { file.failed = true; });
					}
				}
			})(file);

			xhr.open('POST', 'backend/upload.php', true);
			xhr.send(form);
		},
		BuildAddedFilesList: function(files) {
			var addedFiles = new Array();
			if (files) {
				for (var i=0; i<files.length; i++) {
					if (!files[i].failed && !files[i].deleted && files[i].uploaded && files[i].alias) {
						addedFiles.push({ file:files[i], alias:files[i].alias });
					}
				}
			}
			return addedFiles;
		},
		BuildDeletedFilesList: function(files) {
			var deletedFiles = new Array();
			if (files) {
				for (var i=0; i<files.length; i++) {
					if (!files[i].uploaded && files[i].deleted) {
						deletedFiles.push(files[i].alias);
					}
				}
			}
			return deletedFiles;
		}
	};

	// Only one popover at a time
	$scope.OnKeyUp = function(key) {
		if ($scope.ignoreNextEscape) {
			$scope.ignoreNextEscape = false;
		} else if (key == 27) {
			if ($scope.showPopover == 'pComposePost') {
				$scope.board.hasDraft = true;
			}
			$scope.showPopover = 0;
		}
	}
	$scope.IgnoreNextEscape = function() {
		$scope.ignoreNextEscape = true;
	}
	
	$scope.$on('$routeChangeSuccess', function() {
		if ($routeParams.moduleAddress) {
			if ($routeParams.moduleAddress == "confirmed") {
				$scope.confirmedMessage = "Your email has been confirmed.";
			} else if ($routeParams.moduleAddress == "activated") {
				$scope.confirmedMessage = "You can now use your Facebook account to login to UWKSA.";
			}
		}

		// invalid module: manually redirect
		var moduleFromUrl = $scope.GetModule($routeParams.moduleAddress);
		if (typeof moduleFromUrl === 'undefined') {
			$location.path('/');
			return;
		}

		if ($scope.selectedModule != moduleFromUrl) {
			$scope.loaded = false;
			$scope.selectedModule = moduleFromUrl;
			$scope.browserTitle = 'UWKSA :: ' + moduleFromUrl.name;
			document.getElementById('w1-contentView').scrollTop = 0;

			$scope.board.Clear();
		}

		if ($scope.selectedModule.type == 1) { // home
			if (!$scope.loaded) {
				$scope.home.GetLatestFeed();
			}

		} else if ($scope.selectedModule.type == 2) { // about
			$scope.initialized = true;			

		} else if ($scope.selectedModule.type == 3) { // board
			$scope.board.postId = /^\d+$/.test($routeParams.postId) ? $routeParams.postId : null;
			$scope.showPopover = 0;

			if (!$scope.loaded || ($scope.board.postId && !$scope.board.PostAt($scope.board.postId))) {
				$scope.board.GetPostList();
			} else if ($scope.board.postId) {
				if (!$scope.board.PostAt($scope.board.postId).isContentComplete) {
					$scope.board.GetPostContent($scope.board.postId);
				} else {
					$scope.board.UpdateContentView();
				}
			} else {
				$scope.board.UpdateContentView();
			}
		}
	});
});
