function RecurseFindComment(cList, cId) {
	for (var i=0; i<cList.length; i++) {
		if (cList[i].commentId == cId) {
			return cList[i];
		} else {
			var c = RecurseFindComment(cList[i].children, cId);
			if (c) return c;
		}
	}
}
function RecurseDeleteComment(cList, cId) {
	for (var i=0; i<cList.length; i++) {
		if (cList[i].commentId == cId) {
			cList.splice(i, 1);
			return true;
		} else {
			var result = RecurseDeleteComment(cList[i].children, cId);
			if (result) return result;
		}
	}
}


var app = angular.module('board', ['ngSanitize']);

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

app.directive('ngFocus', function () {
	return function (scope, element, attrs) {
		attrs.$observe('ngFocus', function (newValue) {
			newValue === 'true' && element[0].focus();
		});
	}
});

app.directive('ngScroll', function() {
	return function(scope, elm, attr) {
		var raw = elm[0];

		elm.bind('scroll', function() {
			scope.$apply(attr.ngScroll);
		});
	};
});


app.directive('ctree', function() {
	return {
		restrict: 'E',
		template: '<ul><li ng-class="{even:depth%2==0, odd:depth%2==1}" ng-repeat="node in tree"><cnode ng-model="node"></cnode></li></ul>',
		replace: true,
		transclude: true,
		scope: {
			tree: '=ngModel', depth: '=',
			onEdit: '&', onDelete: '&', onReply: '&'
		}
	};
});

app.directive('cnode', function($compile) {
	return { 
		restrict: 'E',
		template:'<div ng-show="!node.deleted&&!node.showNewCommenterView">'
				+ '<div class="commentHeader">'
					+ '<div class="commentDetail">'
						+ '<span class="commentAuthor">{{node.author}}</span>'
						+ ' at {{node.date}}'
						+ ' <span ng-show="node.canEdit">'
							+ '| <span class="linklet" ng-click="onEdit({cId: node.commentId})">Edit</span>'
							+ ' | <span class="linklet" ng-click="onDelete({cId: node.commentId})">Delete</span>'
						+ '</span>'
					+ '</div>'
					+ '<div class="commentReply">'
						+ '<span class="linklet" ng-show="node.canComment" ng-click="onReply({cId: node.commentId})">Reply</span>'
					+ '</div>'
				+ '</div>'
				+ '<div class="commentBody" ng-show="!node.showEditCommenterView" ng-bind-html-unsafe="node.content"></div>'
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
				var child = $compile('<ctree depth="'+(scope.depth+1)+'" ng-model="node.children"'
						+ 'on-edit="onEdit({cId: cId})"'
						+ 'on-delete="onDelete({cId: cId})"'
						+ 'on-reply="onReply({cId: cId})"></ctree>')(scope)
				elm.append(child);
			//}
		}
	};
});

app.config(function($routeProvider) {
	$routeProvider.
		when('/:boardName', {}).
		when('/:boardName/:postId', {}).
		otherwise({redirectTo: '/qna'});
});

app.controller('BoardController', function($http, $scope, $location, $route, $routeParams, $window) {
	console.log("***** INITIALIZE BoardController *****");
	
	$scope.sessionUserName = 'Test User';
	$scope.sessionUserId = '777';
	$scope.showEmptyView = false;
	$scope.showContentView = false;
	$scope.showComposeView = false;
	$scope.hasDraft = false;
	
	$scope.$on('$routeChangeSuccess', function() {
		if ($scope.boardName != $routeParams.boardName) {
			$scope.loaded = false;
		}
		
		$scope.fullPath = $location.path();
		$scope.boardName = $routeParams.boardName;
		$scope.postId = $routeParams.postId;
		$scope.showEditView = false;
		
		if (!$scope.loaded || ($scope.postId && !PostAt($scope.postId))) {
			GetPostList();
		} else if ($scope.postId) {
			if (!PostAt($scope.postId).isContentComplete) {
				GetPostContent();
			} else {
				UpdateContentView();
			}
		} else {
			UpdateContentView();
		}
	});

	$scope.StripTags = function(htmlString) {
		return htmlString.replace(/<img(?:.|\n)*?>/gm, 'attached image').replace(/<(?:.|\n)*?>/gm, '');
	}


	$scope.OnContentViewScroll = function() {
		if ($scope.deleteDialogFor != -1) {
			HideDeleteDialog();
		}
	}
	$scope.OnPageChange = function(pageNo) {
		if (pageNo != $scope.currentPage) GetPostList(pageNo);
	}


	$scope.OnComposeClick = function() {
		$scope.showComposeView = true;
	}
	$scope.OnMinimizeClick = function() {
		$scope.showComposeView = false;
		$scope.hasDraft = true;
	}
	$scope.OnContinueClick = function() {
		$scope.showComposeView = true;
	}


	$scope.OnDeleteDialogYes = function() {
		if ($scope.deleteDialogFor == -999) {
			DeletePost();
		} else if ($scope.deleteDialogFor > 0) {
			DeleteComment($scope.deleteDialogFor);
		}
		
		HideDeleteDialog();
	}
	$scope.OnDeleteDialogNo = function() {
		HideDeleteDialog();
	}


	$scope.OnPostEditClick = function() {
		$scope.showEditView = true;
	}
	$scope.OnPostDeleteClick = function(event) {
		OpenDeleteDialog(-999);
	}


	$scope.OnCommentEditClick = function(commentId) {
		CommentAt(commentId).showEditCommenterView = true;
	}
	$scope.OnCommentDeleteClick = function(commentId) {
		OpenDeleteDialog(commentId)
	}
	$scope.OnCommentReplyClick = function(commentId) {
		var VIRTUAL_NODE_ID = 'VIRTUAL_NODE_ID';

		RecurseDeleteComment($scope.selectedPost.comments, VIRTUAL_NODE_ID);

		var virtualNode = {
			showNewCommenterView: true,
			postId: $scope.selectedPost.postId,
			parentCommentId: commentId,
			commentId: VIRTUAL_NODE_ID,
			children: [],
			Vaporize: function() {
				RecurseDeleteComment($scope.selectedPost.comments, VIRTUAL_NODE_ID);
			},
			UpdateScope: function() {
				$scope.selectedPost.commentCount ++;
			}
		};

		CommentAt(commentId).children.push(virtualNode);
	}
	
	function OpenDeleteDialog(cmd) {
		$scope.deleteDialogFor = cmd;
		$scope.deleteDialogStyle = {
			left: (event.x-160) + 'px',
			top: (event.y+10) + 'px',
			display: 'block'
		};
	}
	function HideDeleteDialog() {
		$scope.deleteDialogFor = -1;	
		$scope.deleteDialogStyle = {
			display: 'none'
		};
	}

	function GetPostList(pageNo) {
		pageNo = typeof pageNo !== 'undefined' ? pageNo : -1;
		console.log("Talking to server for PostList...");
		
		// 3 = board ID  for QNA
		var url = 'backend/get.php?t=1&boardName='+3;
		if (pageNo > 0) url += '&pageNo='+pageNo;
		else if ($scope.postId) url += '&postId='+$scope.postId;
		
		$http.get(url).success(function(data) {
			if (data.error == true) {
				$location.path('/qna');
			} else {
				$scope.listSize = data.listSize;
				$scope.currentPage = data.currentPage;
				$scope.postCount = data.postCount;
				$scope.posts = data.postPreviews;
				
				if (pageNo <= 0) UpdateContentView();
				UpdatePager();
				
				$scope.loaded = true;
			}
		});
	}
	
	function GetPostContent() {
		console.log("Talking to server for PostContent...");
		
		$http.get('backend/get.php?t=2&postId='+$scope.postId).success(function(data) {
			PostAt($scope.postId).isContentComplete = true;
			PostAt($scope.postId).content = data.content;
			PostAt($scope.postId).comments = data.comments;
			UpdateContentView();
		});
	}

	function DeletePost() {
		$http.post('backend/post.php', {
				r:'deletePost', pId:$scope.postId
			}).success(function(response) {
				$window.location.reload();
			});
	}

	function DeleteComment(commentId) {
		$http.post('backend/post.php', {
				r:'deleteComment', cId:commentId
			}).success(function() {
				RecurseFindComment($scope.selectedPost.comments, commentId).deleted = true;
				$scope.selectedPost.commentCount --;
			});
	}
	
	function UpdateContentView() {
		if ($scope.postId) {
			console.log("Showing Post %s.", $scope.postId);
			
			$scope.selectedPost = PostAt($scope.postId);
			$scope.showEmptyView = false;
			$scope.showContentView = true;
		} else {
			console.log("Clearing Post.");
			
			$scope.selectedPost = null;
			$scope.showEmptyView = true;
			$scope.showContentView = false;
		}
	}
	
	function UpdatePager() {
		var PAGER_SIZE = 10;
		var localFirst = ((Math.ceil($scope.currentPage/PAGER_SIZE)-1) * PAGER_SIZE) + 1;
		var globalLast = Math.ceil($scope.postCount/$scope.listSize);
		var pages = new Array();
		var index = 0;
		
		if ($scope.currentPage > PAGER_SIZE) {
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
		
		$scope.pages = pages;
	}
	
	function PostAt(postId) {
		for (var i=0; i<$scope.posts.length; i++) {
			if ($scope.posts[i].postId == postId) {
				return $scope.posts[i];
			}
		}
		return null;
	}

	function CommentAt(commentId) {
		return RecurseFindComment($scope.selectedPost.comments, commentId);
	}
});


app.controller('ComposeController', function($http, $scope, $location) {
	$scope.fields = { title: "", content: "", submitting: false };

	$scope.OnEditorCancel = function() {
		ResetEditor();
	}
	
	$scope.OnEditorSubmit = function(fields) {
		var titleExists = fields.title && fields.title!="";
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";

		if (titleExists && contentExists) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'newPost', bName:$scope.$parent.boardName,
				title:fields.title, content:fields.content
			}).success(function(response) {
				ResetEditor();
				$location.path('/'+$scope.$parent.boardName+'/'+response);
			});
		}
	}

	function ResetEditor() {
		$scope.fields.title = "";
		$scope.fields.content = "";
		$scope.fields.submitting = false;
		$scope.$parent.showComposeView = false;
		$scope.$parent.hasDraft = false;
	}
});

app.controller('EditController', function($http, $scope, $location, $window) {
	$scope.fields = { title: "", content: "", submitting: false };

	$scope.OnEditorCancel = function() {
		$scope.$parent.showEditView = false;
	}
	
	$scope.OnEditorSubmit = function(fields) {
		var titleExists = fields.title && fields.title!="";
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";

		if (titleExists && contentExists && $scope.$parent.postId) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'editPost', pId:$scope.$parent.postId,
				title:fields.title, content:fields.content
			}).success(function(response) {
				$window.location.reload();
			});
		}
	}

	$scope.$watch('$parent.showEditView', function() {
		if ($scope.$parent.showEditView) {
			$scope.fields.title = $scope.$parent.selectedPost.title;
			$scope.fields.content = $scope.$parent.selectedPost.content;
			$scope.fields.submitting = false;
		} else {
			$scope.fields.title = "";
			$scope.fields.content = "";
		}
	});
});

app.controller('MainCommenterController', function($http, $scope) {
	$scope.fields = { content: "", submitting: false };
	$scope.showCancelButton = false;

	$scope.OnCommenterCancel = function() { }
	
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		if (contentExists && $scope.$parent.postId) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'newComment', parentPost:$scope.$parent.postId, parentComment:-1,
				content:fields.content
			}).success(function(response) {
				$scope.$parent.selectedPost.comments.push(response);
				$scope.$parent.selectedPost.commentCount ++;
				$scope.fields.content = "";
				$scope.fields.submitting = false;
			});
		}
	}

	$scope.$watch('$parent.selectedPost.postId', function() {
		$scope.fields.content = "";
		$scope.fields.submitting = false;
	});
});

app.controller('ReplyCommenterController', function($http, $scope) {
	$scope.fields = { content: "", submitting: false };
	$scope.showCancelButton = true;

	$scope.OnCommenterCancel = function() {
		$scope.param.Vaporize();
	}
	
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		if (contentExists && $scope.param) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'newComment', parentPost:$scope.param.postId, parentComment:$scope.param.parentCommentId,
				content:fields.content
			}).success(function(response) {
				$scope.param.UpdateScope();
				$scope.param.showNewCommenterView = false;
				$scope.param.commentId = response.commentId;
				$scope.param.canEdit = response.canEdit;
				$scope.param.canComment = response.canComment;
				$scope.param.authorUserId = response.authorUserId;
				$scope.param.author = response.author;
				$scope.param.content = response.content;
				$scope.param.date = response.date;
				$scope.param.children = response.children;
			});
		}
	}
});

app.controller('EditCommenterController', function($http, $scope) {
	$scope.fields = { content: "", submitting: false };
	$scope.showCancelButton = true;

	$scope.OnCommenterCancel = function() {
		$scope.param.showEditCommenterView = false;
	}
	
	$scope.OnCommenterSubmit = function(fields) {
		var contentExists = fields.content && fields.content!="" && fields.content!="<br>";
		if (contentExists && $scope.param) {
			fields.submitting = true;

			$http.post('backend/post.php', {
				r:'editComment', cId:$scope.param.commentId,
				content:fields.content
			}).success(function(response) {
				$scope.param.content = fields.content;
				$scope.param.showEditCommenterView = false;
			});
		}
	}

	$scope.$watch('param.showEditCommenterView', function() {
		if ($scope.param.showEditCommenterView) {
			$scope.fields.content = $scope.param.content;
			$scope.fields.submitting = false;
		} else {
			$scope.fields.content = "";
		}
	});
});