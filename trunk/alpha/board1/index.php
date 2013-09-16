<!doctype html>
<html lang="en" ng-app="board" ng-controller="BoardController">
<head>
	<meta charset="utf-8">
	<title ng-bind="browserTitle"></title>
	<script src="angular.min.js"></script>
	<!--<script src="angular-sanitize.min.js"></script>-->
	<script src="script.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="shortcut icon" href="../favicon.ico?v=3" />
</head>
<body>
	<div id="shadow1" ></div>
	<div id="shadow2"></div>
	<div id="header" class="OpenSans">
		<div id="navigation">
			<ul>
				<li>HOME</li>
				<li>ABOUT</li>
				<li class="selected">Q&A</li>
				<li>COMMUNITY</li>
				<li>SUBLETS</li>
				<li>TUTORS</li>
				<li>BUY&SELL</li>
				<li>EXAMS</li>
			</ul>
		</div>

		<div id="toolbar">
			<div id="searchBar">
				<input type="text" placeholder="Search..." />
			</div>
			<div id="optionBar">

			</div>
			<div id="buttonBar">
				<input type="button" class="button-action" value="Write New"
					ng-show="!showEditView&&!showComposeView&&!hasDraft" ng-click="OnComposeClick()" />
				<input type="button" class="button-normal" value="Minimize"
					ng-show="!showEditView&&showComposeView" ng-click="OnMinimizeClick()" />
				<input type="button" class="button-action" value="Continue"
					ng-show="!showEditView&&!showComposeView&&hasDraft" ng-click="OnContinueClick()" />
			</div>
		</div>
	</div> <!-- end #header -->
	
	<div id="wrapper">
		<div id="w1-listView">
			<div id="w2-scroller">
				<ul>
					<li ng-repeat="post in posts" ng-class="{selected:postId==post.postId}">
						<a href="#/{{boardName}}/{{post.postId}}">
							<div class="listBox">
								<div class="listTitle OpenSans">{{post.title}}</div>
								<div class="listDetail">
									<span class="listComments" ng-show="post.commentCount">{{post.commentCount}} comments &middot;</span>
									<span class="listAuthor">{{post.author}}</span>
									<span class="listDate">at {{post.date}}</span>
								</div>
								<div class="listPreview" ng-bind-html-unsafe="StripTags(post.content)"></div>
							</div>
						</a>
					</li>
				</ul>
			</div> <!-- end #w2-scroller -->
			
			<div id="w2-pager">
				<ul>
					<li ng-repeat="page in pages" ng-class="{selected:currentPage==page.name}" ng-click="OnPageChange(page.no)">{{page.name}}</li>
				</ul>
			</div> <!-- end #w2-pager -->
		</div> <!-- end #w1-listView -->
		
		<div id="w1-emptyView" ng-show="showEmptyView">
			<span class="OpenSans">&lt;&lt; 눌러봐!</span>
		</div>

		<div id="w1-contentView" ng-show="showContentView" ng-scroll="OnContentViewScroll()" >
			<div id="w2-core">
				<div class="postTitle OpenSans">{{selectedPost.title}}</div>
				<div class="postAuthor OpenSans linklet">{{selectedPost.author}}</div>
				<div class="postDate OpenSans">{{selectedPost.fullDate}}</div>
				<div class="postControl OpenSans">
					<span class="linklet" ng-show="selectedPost.canEdit" ng-click="OnPostEditClick()">
						Edit
					</span>
					<span class="linklet" ng-show="selectedPost.canEdit" ng-click="OnPostDeleteClick()">
						Delete
					</span>
				</div>
				<div class="postContent" ng-bind-html-unsafe="Linkify(selectedPost.content)"></div>
				<div class="postCommentHeader OpenSans">{{selectedPost.commentCount}} Comments</div>
				<div class="postComment">
					<ctree depth="0" ng-model="selectedPost.comments"
						on-edit="OnCommentEditClick(cId)"
						on-delete="OnCommentDeleteClick(cId)"
						on-reply="OnCommentReplyClick(cId)"
						do-linkify="Linkify(str)"></ctree>
					<ul class="commentUl">
						<li class="commentLi">
							<div class="commentNew" ng-include src="'tp-commenter'" ng-controller="MainCommenterController"></div>
						</li>
					</ul>
				</div>
			</div> <!-- end #w2-core -->

			<div id="w2-deleteDialog" class="OpenSans" ng-style="deleteDialogStyle">
				<div id="dialogForeground">
					<div>Are you sure you want to delete this?</div>
					<input type="button" class="button-action" value="Yes" ng-click="OnDeleteDialogYes()" />
					<input type="button" class="button-normal" value="No" ng-click="OnDeleteDialogNo()" />
				</div>
			</div> <!-- end #w2-deleteDialog -->
			
			<div id="w2-footer">
				<div id="w3-sponsors">
				</div>
				<div id="w3-contacts">
				</div>
				<div id="w3-copyright">
					&copy; 2013 UWKSA<br />
					<span>CMYK STUDIO</span>
				</div>
			</div> <!-- end #w2-footer -->
		</div> <!-- end #w1-contentView -->

		<div id="w1-composeView" ng-show="showComposeView">
			<div id="composeBox" ng-include src="'tp-editor'" ng-controller="ComposeController"></div>
		</div> <!-- end #w1-composeView -->
		
		<div id="w1-editView" ng-show="showEditView">
			<div id="editBackground"></div>
			<div id="editForeground">
				<div id="editHeader" class="OpenSans">Edit Post</div>
				<div id="editBox" ng-include src="'tp-editor'" ng-controller="EditController"></div>
			</div>
		</div> <!-- end #w1-editView -->

	</div> <!-- end #wrapper -->

	<script type="text/ng-template" id="tp-editor">
		<div class="editorWrapper" ng-style="{opacity: 1-(fields.submitting*0.5)}">
			<div class="editorTitle">
				<input ng-model="fields.title" ng-focus="{{showComposeView||showEditView}}" type="text" placeholder="Title" />
			</div> <!-- end #w2-composeTitle -->
			<div class="editorControl" ng-include src="'tp-wysiwyg'"></div>
			<div class="editorContent" ng-model="fields.content" contentEditable></div>
			<div class="editorAction">
				<input type="button" class="button-action" value="Submit" ng-disabled="fields.submitting" ng-click="OnEditorSubmit(fields)" />
				<input type="button" class="button-normal" value="Cancel" ng-click="OnEditorCancel()" />
			</div>
		</div>
	</script>

	<script type="text/ng-template" id="tp-commenter">
		<div class="commenterWrapper" ng-style="{opacity: 1-(fields.submitting*0.5)}">
			<div class="commenterControl" ng-include src="'tp-wysiwyg'"></div>
			<div class="commenterContent" ng-model="fields.content" ng-focus="{{node.showNewCommenterView||node.showEditCommenterView}}" contentEditable></div>
			<div class="commenterAction">
				<input type="button" class="button-action" value="Submit" ng-disabled="fields.submitting" ng-click="OnCommenterSubmit(fields)" />
				<input type="button" class="button-normal" value="Cancel" ng-show="showCancelButton" ng-click="OnCommenterCancel()" />
			</div>
		</div>
	</script>

	<script type="text/ng-template" id="tp-wysiwyg">
		<div class="iconContainer">
			<input type="button" class="wys-icon wys-bold" onclick="OnWysCmd('bold')" /><input type="button" class="wys-icon wys-italic" onclick="OnWysCmd('italic')" /><input type="button" class="wys-icon wys-underline" onclick="OnWysCmd('underline')" />
		</div>
		<div class="iconDivider"></div>
		<div class="iconContainer">
			<input type="button" class="wys-icon wys-ol" onclick="OnWysCmd('insertOrderedList')" /><input type="button" class="wys-icon wys-ul" onclick="OnWysCmd('insertUnorderedList')" />
		</div>
		<div class="iconDivider"></div>
		<div class="iconContainer">
			<input type="button" class="wys-icon wys-left" onclick="OnWysCmd('justifyLeft')" /><input type="button" class="wys-icon wys-center" onclick="OnWysCmd('justifyCenter')" /><input type="button" class="wys-icon wys-right" onclick="OnWysCmd('justifyRight')" />
		</div>
		<div class="iconDivider"></div>
		<div class="iconContainer">
			<input type="button" class="wys-icon wys-indent" onclick="OnWysCmd('indent')" /><input type="button" class="wys-icon wys-outdent" onclick="OnWysCmd('outdent')" />
		</div>
	</script>
</body>
</html>
