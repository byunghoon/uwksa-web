<?php ob_start(); include_once 'backend/header.php'; ?>
<!doctype html>
<html lang="en" xmlns:ng="http://angularjs.org" id="ng-app" ng-app="uwksa" ng-controller="RootController">
<head>
	<!--[if lte IE 8]>
		<script src="http://bestiejs.github.io/json3/lib/json3.min.js"></script>
    <![endif]-->
    <!--[if lte IE 9]>
    	<style type="text/css">
			#searchBar input[type="text"], .editorTitle input[type="text"] { padding-top:20px !important; }
			#login2 input[type="text"], #login2 input[type="password"] { padding-top:5px !important; }
			#signUp2 input[type="text"], #signUp2 input[type="password"] { padding-top:10px !important; }
		</style>
    <![endif]-->
	<meta charset="utf-8">
	<meta name="fragment" content="!" />
	<title ng-bind="browserTitle"></title>
	<?php echo '<script>var USER_ID = "'.$_SESSION['UserID'].'"; var USER_NAME = "'.$_SESSION['UserName'].'"; var USER_EMAIL = "'.$_SESSION['Email'].'"; var LOGGED_IN = ' . ($_SESSION['UserID']>0 ? 'true' : 'false') . ';</script>'; ?>
	<script src="angular.min.js"></script>
	<!--<script src="angular-sanitize.min.js"></script>-->
	<script src="script.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="shortcut icon" href="../favicon.ico?v=3" />
</head>
<body ng-keyup="OnKeyUp">
	<div id="initView" ng-hide="initialized">
		<span>Initializing...</span>
	</div>
	<div id="header" class="OpenSans NoSelection">
		<div id="logo">
			{{msg}}
		</div>
		<div id="navigation">
			<ul>
				<li ng-repeat="module in modules" ng-class="{selected:module==selectedModule}">
					<a href="#!/{{module.address}}">{{module.name}}</a>
				</li>
			</ul>
		</div>
		<div id="login">
			<span ng-show="!loggedIn" ng-click="user.OnLoginClick()">LOGIN</span>
			<span ng-show="!loggedIn" ng-click="user.OnSignUpClick()">SIGN UP</span>
			<span ng-show="loggedIn" ng-click="user.OnProfileClick()">{{userName}}</span>
		</div>
	</div> <!-- end #header -->

	<div id="home" ng-show="selectedModule.type==1">
		<div id="q1-slideshow">
			<div class="EmailConfirmed OpenSans" ng-show="confirmedMessage">{{confirmedMessage}}</div>
		</div> <!-- #q1-slideshow -->
		<div id="q1-level1">
			<div id="q2-news">
				<div class="titleBox OpenSans">
					<div class="title">NEWS &amp; EVENTS</div>
				</div>
				<div class="contentBox">
					<div class="content">
						<ul>
							<li ng-repeat="post in home.news" ng-class="{selected:post.open}">
								<div class="dateBox OpenSans">
									<div class="month">{{post.month}}</div>
									<div class="date">{{post.date}}</div>
								</div>
								<div class="newsBoxTip"></div>
								<div class="newsBox">
									<div class="newsTitle"><a href="#!/news/{{post.postId}}">{{post.title}}</a></div>
									<!--<div class="newsPreview" ng-show="post.open" ng-bind-html-unsafe="util.StripHTML(post.content)"></div>-->
									<div class="newsPreview" ng-show="post.open">{{post.pureContent}}</div>
									<div class="newsClickable OpenSans">
										<div ng-show="post.open" class="continue linklet"><a href="#!/news/{{post.postId}}">Continue reading...</a></div>
										<div ng-show="!post.open" class="expand" ng-click="home.OnExpandClick($index)"></div>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div id="q2-qna">
				<div class="titleBox OpenSans">
					<div class="title">QUESTIONS &amp; ANSWERS</div>
				</div>
				<div class="contentBox">
					<div class="content">
						<ul>
							<li ng-repeat="post in home.qna">
								<div class="bullet"></div>
								<div class="listItemBox">
									<span class="qnaTitle"><a href="#!/qna/{{post.postId}}">{{post.title}}</a></span>
									<span class="qnaAnswers OpenSans" ng-show="post.commentCount>1">{{post.commentCount}} answers</span>
									<span class="qnaAnswers OpenSans" ng-show="post.commentCount==1">1 answer</span>
									<span class="qnaAnswers OpenSans" ng-show="post.commentCount==0">no answer</span>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div> <!-- #q2-group1 -->
		
		<div id="q1-level2">
			<div class="titleBox OpenSans">
				<div class="title">LATEST POSTS</div>
			</div>
			<div class="contentBox">
				<div class="content">
					<div class="contentRow">
						<div class="contentColumn">
							<div class="boardName OpenSans">COLUMN</div>
							<ul>
								<li ng-repeat="post in home.column">
									<span class="listItemDate OpenSans">{{post.month}} {{post.date}}</span>
									<span class="listItemTitle"><a href="#!/column/{{post.postId}}">{{post.title}}</a></span>
								</li>
							</ul>
						</div>
						<div class="contentColumn middle">
							<div class="boardName OpenSans">SUBLET</div>
							<ul>
								<li ng-repeat="post in home.sublet">
									<span class="listItemDate OpenSans">{{post.month}} {{post.date}}</span>
									<span class="listItemTitle"><a href="#!/sublet/{{post.postId}}">{{post.title}}</a></span>
								</li>
							</ul>
						</div>
						<div class="contentColumn">
							<div class="boardName">TUTORS</div>
							<ul>
								<li ng-repeat="post in home.tutors">
									<span class="listItemDate OpenSans">{{post.month}} {{post.date}}</span>
									<span class="listItemTitle"><a href="#!/tutors/{{post.postId}}">{{post.title}}</a></span>
								</li>
							</ul>
						</div>
					</div>
					<div class="contentRow">
						<div class="contentColumn">
							<div class="boardName OpenSans">BUY &amp; SELL</div>
							<ul>
								<li ng-repeat="post in home.bns">
									<span class="listItemDate OpenSans">{{post.month}} {{post.date}}</span>
									<span class="listItemTitle"><a href="#!/bns/{{post.postId}}">{{post.title}}</a></span>
								</li>
							</ul>
						</div>
						<div class="contentColumn middle">
							<div class="boardName OpenSans">EXAMS</div>
							<ul>
								<li ng-repeat="post in home.exams">
									<span class="listItemDate OpenSans">{{post.month}} {{post.date}}</span>
									<span class="listItemTitle"><a href="#!/exams/{{post.postId}}">{{post.title}}</a></span>
								</li>
							</ul>
						</div>
						<div class="contentColumn">
							<div class="boardName OpenSans">COMMUNITY</div>
							<ul>
								<li ng-repeat="post in home.community">
									<span class="listItemDate OpenSans">{{post.month}} {{post.date}}</span>
									<span class="listItemTitle"><a href="#!/community/{{post.postId}}">{{post.title}}</a></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- #q1-group2 -->

		<div id="q1-level3">
			<div class="titleBox OpenSans">
				<div class="title">PHOTO GALLERY</div>
			</div>
			<div class="contentBox">
				<div class="content">
					<div class="underConstruction">Under Construction</div>
					<!--<div class="placeholder"></div>-->
				</div>
			</div>
		</div> <!-- #q1-group3 -->

		<div id="q1-footer" class="OpenSans">
			<div class="footerBox" ng-include src="'tp-footer'"></div>
		</div> <!-- #q1-footer -->
	</div> <!-- end #home -->

	<div id="about" ng-show="selectedModule.type==2">
		About page coming soon.
	</div> <!-- end #about -->

	<div id="board" ng-show="selectedModule.type==3">
		<div id="shadow1"></div>
		<div id="shadow2"></div>

		<div id="w1-toolbar" class="OpenSans">
			<div id="searchBar">
				<input type="text" id="searchField" placeholder="Search..." ng-model="board.searchInputString" ng-on-focus="board.SetSearchHasFocus(true)" ng-on-blur="board.SetSearchHasFocus(false)" ng-change="board.OnSearchFieldChange()" />
			</div>
			<div id="searchOptions" ng-show="board.searchHasFocus||board.searchHasMouse" class="OpenSans" ng-mouseover="board.SetSearchHasMouse(true)" ng-mouseout="board.SetSearchHasMouse(false)">
				<input type="checkbox" id="searchOptionsAuthor" ng-model="board.searchOptions.author" ng-click="board.OnSearchOptionChange()" /><label for="searchOptionsAuthor" ng-click="board.OnSearchOptionChange()"> author</label>
				<input type="checkbox" id="searchOptionsTitle" ng-model="board.searchOptions.title" ng-click="board.OnSearchOptionChange()" /><label for="searchOptionsTitle" ng-click="board.OnSearchOptionChange()"> title</label>
				<input type="checkbox" id="searchOptionsContent" ng-model="board.searchOptions.content" ng-click="board.OnSearchOptionChange()" /><label for="searchOptionsContent" ng-click="board.OnSearchOptionChange()"> content</label>
				<input type="checkbox" id="searchOptionsComments" ng-model="board.searchOptions.comments" ng-click="board.OnSearchOptionChange()" /><label for="searchOptionsComments" ng-click="board.OnSearchOptionChange()"> comments</label>
			</div>
			<div id="tagBar">
				<ul>
					<li ng-repeat="tag in board.availableTags" ng-show="$index%2==0" ng-click="board.OnTagClick(tag)" ng-class="{selected:tag.selected}">
						{{tag.displayName}}
					</li>
				</ul>
				<ul>
					<li ng-repeat="tag in board.availableTags" ng-show="$index%2==1" ng-click="board.OnTagClick(tag)" ng-class="{selected:tag.selected}">
						{{tag.displayName}}
					</li>
				</ul>
			</div>
			<div id="buttonBar" ng-show="board.canPost">
				<input type="button" class="button-action" value="Write New"
					ng-show="showPopover!='pEditPost'&&showPopover!='pComposePost'&&!board.hasDraft" ng-click="board.OnComposeClick()" />
				<input type="button" class="button-normal" value="Minimize"
					ng-show="showPopover!='pEditPost'&&showPopover=='pComposePost'" ng-click="board.OnMinimizeClick()" />
				<input type="button" class="button-action" value="Continue"
					ng-show="showPopover!='pEditPost'&&showPopover!='pComposePost'&&board.hasDraft" ng-click="board.OnContinueClick()" />
			</div>
		</div> <!-- end #w1-toolbar -->

		<div id="w1-listView" ng-class="{showSearchOptions:board.searchHasFocus||board.searchHasMouse}">
			<div id="w2-scroller" ng-class="{loading:board.buildingPostList&&!board.posts.length}">
				<ul>
					<li class="message OpenSans" ng-show="!board.buildingPostList&&!board.posts.length">No results.</li>
					<li ng-repeat="post in board.posts" ng-class="{selected:board.postId==post.postId, loading:post.loading}">
						<a href="#!/{{selectedModule.address}}/{{post.postId}}">
							<div class="listBox">
								<div class="listTitle OpenSans">
									<span class="listPinnedSign" ng-show="post.pinned">Pinned</span>{{post.title}}
								</div>
								<div class="listDetail">
									<span class="listComments" ng-show="post.commentCount">{{post.commentCount}} comments &middot;</span>
									<span class="listAuthor">{{post.author}}</span>
									<span class="listDate">on {{post.date}}</span>
								</div>
								<!--<div class="listPreview" ng-bind-html-unsafe="util.StripHTML(post.content)"></div>-->
								<div class="listPreview">{{post.pureContent}}</div>
							</div>
						</a>
					</li>
				</ul>
			</div> <!-- end #w2-scroller -->
			
			<div id="w2-pager">
				<ul>
					<li ng-repeat="page in board.pages" ng-class="{selected:board.currentPage==page.name}" ng-click="board.OnPageChange(page.no)">{{page.name}}</li>
				</ul>
			</div> <!-- end #w2-pager -->
		</div> <!-- end #w1-listView -->

		<div id="w1-emptyView" ng-show="board.showEmptyView"></div>

		<div id="w1-contentView" ng-show="board.showContentView">
			<div id="w2-core">
				<div class="postTitle OpenSans">{{board.selectedPost.title}}</div>
				<div class="postDetail OpenSans">
					<div class="postInfo">
						<div class="postAuthor"><span class="linklet">{{board.selectedPost.author}}</span></div>
						<div class="postDate">{{board.selectedPost.fullDate}}</div>
					</div>
					<div class="postControl">
						<span class="linklet" ng-show="board.selectedPost.canEdit" ng-click="board.OnPostEditClick()">
							Edit
						</span>
						<span class="linklet" ng-show="board.selectedPost.canEdit" ng-click="board.OnPostDeleteClick()">
							Delete
						</span>
					</div>
				</div>
				<div class="postContent" ng-bind-html-unsafe="util.Linkify(board.selectedPost.content)"></div>
				<div class="postFile" ng-show="board.selectedPost.files">
					(FILE incomplete UI)
					<div ng-repeat="file in board.selectedPost.files">
						{{file.name}} ({{file.alias}}, {{file.path}})
					</div>
				</div>
				<div class="postTags OpenSans" ng-show="board.selectedPost.selectedTags.length>0">
					<span class="linklet" ng-repeat="tag in board.selectedPost.selectedTags" ng-click="board.OnTagClick(tag)">
						{{tag.displayName}}<span ng-hide="$index==board.selectedPost.selectedTags.length-1">,</span>
					</span>
				</div>
				<div class="postCommentHeader OpenSans">{{board.selectedPost.commentCount}} Comments</div>
				<div class="postComment">
					<div ctree depth="0" ng-model="board.selectedPost.comments"
						on-edit="board.OnCommentEditClick(cId)"
						on-delete="board.OnCommentDeleteClick(cId)"
						on-reply="board.OnCommentReplyClick(cId)"
						do-linkify="util.Linkify(str)"
						ignore-next-escape="IgnoreNextEscape()"
						upload-file="board.UploadFile(file)"
						build-added-files-list="board.BuildAddedFilesList(files)"
						build-deleted-files-list="board.BuildDeletedFilesList(files)"></div>
					<ul class="commentUl">
						<li class="commentLi" ng-show="board.canPost">
							<div class="commentNew" ng-include src="'tp-commenter'" ng-controller="MainCommenterController"></div>
						</li>
					</ul>
				</div>
			</div> <!-- end #w2-core -->
			
			<div id="w2-footer" class="OpenSans">
				<div class="footerBox" ng-include src="'tp-footer'"></div>
			</div> <!-- end #w2-footer -->
		</div> <!-- end #w1-contentView -->

		<div id="w1-composeView" ng-show="$parent.showPopover=='pComposePost'" ng-controller="ComposeController" ng-class="{shake:fields.incomplete}">
			<div id="composeBox" ng-include src="'tp-editor'"></div>
		</div> <!-- end #w1-composeView -->

		<div id="w1-editView" ng-show="$parent.showPopover=='pEditPost'" ng-controller="EditController">
			<div class="modalBackground"></div>
			<div id="editForeground" ng-class="{shake:fields.incomplete}">
				<div id="editHeader" class="OpenSans">Edit Post</div>
				<div id="editBox" ng-include src="'tp-editor'"></div>
			</div>
		</div> <!-- end #w1-editView -->



		<script type="text/ng-template" id="tp-editor">
			<div class="editorWrapper NoSelection" ng-style="{opacity: 1-(fields.submitting*0.5)}">
				<div class="editorTitle">
					<input ng-model="fields.title" ng-focus="{{$parent.showPopover=='pComposePost'||$parent.showPopover=='pEditPost'}}" type="text" placeholder="Title" />
				</div> <!-- end #w2-composeTitle -->
				<div class="editorControl" ng-include src="'tp-wysiwyg'"></div>
				<div class="editorContent TextSelection" ng-model="fields.content" contentEditable ng-mouseover="SetShowAvailableTags(false)"></div>
				<div class="editorFile" style="position:absolute; bottom:0; left:-300px; width:240px; padding:30px; background:#EEEEEE;" ng-include src="'tp-file'"></div>
				<div class="editorSelectedTags OpenSans" ng-click="SetShowAvailableTags(true)">
					<div class="selectedTags">
						<div class="selectedTagsNone" ng-show="fields.selectedTags.length==0">click to add tags...</div>
						<span class="selectedTagItem" ng-repeat="tag in fields.selectedTags" ng-click="OnAddRemoveTag(tag)">
							{{tag.displayName}}
						</span>
					</div>
				</div>
				<div class="editorAvailableTags OpenSans" ng-show="fields.showAvailableTags" ng-mouseover="SetShowAvailableTags(true)" ng-mouseout="SetShowAvailableTags(false)">
					<span class="availableTagItem" ng-repeat="tag in $parent.board.availableTags" ng-click="OnAddRemoveTag(tag)" ng-class="{selected:tag.selected}" >
						{{tag.displayName}}
					</span>
				</div>
				<div class="editorAction">
					<input type="checkbox" ng-model="fields.pinned" ng-checked="fields.pinned" />
					<input type="button" class="button-action" value="Submit" ng-disabled="fields.submitting" ng-click="OnEditorSubmit(fields)" />
					<input type="button" class="button-normal" value="Cancel" ng-click="OnEditorCancel()" />
				</div>
			</div>
		</script>

		<script type="text/ng-template" id="tp-commenter">
			<div class="commenterWrapper NoSelection" ng-style="{opacity: 1-(fields.submitting*0.5)}" ng-class="{shake:fields.incomplete}">
				<div class="commenterControl" ng-include src="'tp-wysiwyg'"></div>
				<div class="commenterContent TextSelection" ng-model="fields.content" ng-focus="{{node.showNewCommenterView||node.showEditCommenterView}}" contentEditable></div>
				<div class="editorFile" ng-include src="'tp-file'"></div>
				<div class="commenterAction">
					<input type="button" class="button-action" value="Submit" ng-disabled="fields.submitting" ng-click="OnCommenterSubmit(fields)" />
					<input type="button" class="button-normal" value="Cancel" ng-show="showCancelButton" ng-click="OnCommenterCancel()" />
				</div>
			</div>
		</script>

		<script type="text/ng-template" id="tp-file">
			(incomplete UI)
			<span ng-repeat="file in fields.files">
				<span ng-hide="file.deleted">
					<span class="fileName">{{file.name}}</span>
					<span class="fileProgress" ng-show="!file.uploaded">{{file.progress}}</span>
					<span class="fileDelete" ng-show="file.uploaded" ng-click="OnFileDeleteClick(file)">x<span>
				</span>
			</span>
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
			<div class="iconDivider"></div>
			<div class="iconContainer OpenSans">
				<input type="button" class="wys-icon wys-attach" onclick="angular.element(this).next().children()[0].click();angular.element(this).scope().OnFilePromptClick()" value="attach" />
				<form enctype="multipart/form-data">
					<input type="file" class="uploader" multiple="multiple" onchange="angular.element(this).scope().OnFileSelect(this)" />
				</form>
			</div>
		</script>
	</div> <!-- end #board -->

	<div ng-show="showPopover=='pLogin'">
		<div class="OpenSans" id="loginView" ng-controller="LoginController" ng-class="{shake:fields.failed||fields.incomplete}">
			<div id="loginBox">
				<div id="login1"><a href="facebook.php?q=login"><img src="assets/user_fb_login.png" alt="" /></a></div>
				<div id="loginOr">
					<div class="popoverHr" id="leftHr"></div>
					<div id="or">OR</div>
					<div class="popoverHr" id="rightHr"></div>
				</div>
				<form>
					<div id="login2">
						<div class="row">
							<div class="label">Email</div>
							<div class="field"><input type="text" ng-model="fields.email" ng-focus="{{$parent.showPopover=='pLogin'}}"></div>
						</div>
						<div class="row">
							<div class="label">Password</div>
							<div class="field"><input type="password" ng-model="fields.password" /></div>
						</div>
					</div>
					<input type="submit" class="button-action" value="Login" ng-click="OnLoginSubmitClick(fields)" />
					<input type="button" class="button-normal" value="Cancel" ng-click="OnLoginCancelClick()" />
					<div id="failMessage" ng-show="fields.failed">
						{{fields.failedMessage}}
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="signUpView" ng-show="showPopover=='pSignUp'">
		<div class="modalBackground"></div>
		<div class="OpenSans" id="signUpForeground" ng-controller="SignUpController" ng-class="{shake:fields.failed||fields.incomplete}" ng-style="{opacity: 1-(fields.submitting*0.5)}">
			<div id="signUpCompletedMessage" ng-show="fields.signUpCompleted">
				<div class="green">Sign up complete!</div>
				<div>A confirmation email is sent to your email address. If you don't see one, please check your spam folder.</div>
				<input type="button" class="button-normal" value="Close" ng-click="OnSignUpCloseClick()" />
			</div>
			<div id="signUpBox" ng-show="!fields.signUpCompleted">
				<div id="signUpHeader">SIGN UP</div>
				<div id="signUpOption1">
					<div class="optionLabel">Option 1.</div>
					<div class="optionHr popoverHr"></div>
				</div>
				<div id="signUp1"><a href="facebook.php?q=signup"><img src="assets/user_fb_connect.png" alt="" /></a></div>
				<div id="signUpOption2">
					<div class="optionLabel">Option 2.</div>
					<div class="optionHr popoverHr"></div>
				</div>
				<form>
					<div id="signUp2">
						<div class="row" ng-class="{passed:fields.namePassed, failed:fields.nameFailed}">
							<div class="label">Nickname</div>
							<div class="field"><input type="text" maxlength="32" ng-model="fields.name" ng-focus="{{$parent.showPopover=='pSignUp'}}" ng-on-blur="OnNameFieldBlur(fields)" /></div>
							<div class="message" ng-class="{checking:fields.nameChecking}">
								<span ng-show="!fields.nameChecking&&!fields.nameFailed&&!fields.namePassed">at least 2 characters</span>
								<span ng-show="fields.nameFailed">Name already exists!</span>
								<span ng-show="fields.namePassed">You can use this name.</span>
							</div>
						</div>
						<div class="row" ng-class="{passed:fields.emailPassed, failed:fields.emailFailed||fields.emailInvalid}">
							<div class="label">Email</div>
							<div class="field"><input type="text" ng-model="fields.email" ng-on-blur="OnEmailFieldBlur(fields)" /></div>
							<div class="message" ng-class="{checking:fields.emailChecking}">
								<span ng-show="fields.emailInvalid">Invalid email format.</span>
								<span ng-show="fields.emailFailed">Email already exists!</span>
								<span ng-show="fields.emailPassed">You can use this email.</span>
							</div>
						</div>
						<div id="noFakeEmailsPlease">
							A confirmation email will be sent to this address.
						</div>
						<div class="row" ng-class="{passed:fields.passwordPassed, failed:fields.passwordFailed}">
							<div class="label">Password</div>
							<div class="field"><input type="password" ng-model="fields.password" /></div>
							<div class="message">
								<span ng-show="!fields.passwordPassed&&!fields.passwordFailed">at least 8 characters</span>
								<span ng-show="fields.passwordFailed">Password too short!</span>
								<span ng-show="fields.passwordPassed">You can use this password.</span>
							</div>
						</div>
						<div class="row" ng-class="{passed:fields.confirmPassed, failed:fields.confirmFailed}">
							<div class="label2">Confirm<br />Password</div>
							<div class="field"><input type="password" ng-model="fields.confirm" /></div>
							<div class="message">
								<span ng-show="fields.confirmFailed">Does not match!</span>
								<span ng-show="fields.confirmPassed">Password matches.</span>
							</div>
						</div>
					</div>
					<input type="submit" class="button-action" value="Sign Up" ng-click="OnSignUpSubmitClick(fields)" />
					<input type="button" class="button-normal" value="Cancel" ng-click="OnSignUpCancelClick()" />
				</form>
			</div>
		</div>
	</div>

	<div ng-show="showPopover=='pProfile'">
		<div class="OpenSans" id="profileView" ng-controller="ProfileController" ng-class="{shake:fields.failed||fields.incomplete}">
			<div id="profileOptionBox" ng-show="option==0&&!fields.changeSuccessful">
				<input type="button" class="button-action" value="Logout" ng-click="$parent.user.OnLogoutClick()" />
				<input type="button" class="button-action" value="Change Nickname" ng-click="OnChangeNameClick()" />
				<input type="button" class="button-action" value="Change Password" ng-click="OnChangePasswordClick()" />
				<input type="button" class="button-normal" value="Cancel" ng-click="OnCancelClick()" />
			</div>
			<div id="profileChangeSuccessMessage" ng-show="fields.changeSuccessful">
				<div>Your profile has been updated.</div>
				<input type="button" class="button-action" value="Done" ng-click="OnDoneClick()" />
			</div>
			<div id="profileChangeNameBox" ng-show="option==1&&!fields.changeSuccessful">
				<form>
					<div ng-class="{passed:fields.namePassed, failed:fields.nameFailed}">
						<div class="row">
							<div class="label">Old Name</div>
							<div class="plaintext">{{userName}}</div>
						</div>
						<div class="row">
							<div class="label">New name</div>
							<div class="field"><input type="text" ng-model="fields.name" ng-focus="{{option==1}}" ng-on-blur="OnNameFieldBlur(fields)"></div>
						</div>
						<div class="message" ng-class="{checking:fields.nameChecking}">
							<span ng-show="!fields.nameChecking&&!fields.nameFailed&&!fields.namePassed">at least 2 characters</span>
							<span ng-show="fields.nameFailed">Name already exists!</span>
							<span ng-show="fields.namePassed">You can use this name.</span>
						</div>
					</div>
					<input type="submit" class="button-action" value="Update" ng-click="OnNameSubmitClick(fields)" />
					<input type="button" class="button-normal" value="Cancel" ng-click="OnCancelClick()" />
				</form>
			</div>
			<div id="profileChangePasswordBox" ng-show="option==2&&!fields.changeSuccessful">
				<form>
					<div ng-class="{passed:fields.passwordPassed, failed:fields.passwordFailed}">
						<div class="row">
							<div class="label">New<br />Password</div>
							<div class="field"><input type="text" ng-model="fields.password" ng-focus="{{option==2}}"></div>
						</div>
						<div class="message">
							<span ng-show="!fields.passwordPassed&&!fields.passwordFailed">at least 8 characters</span>
							<span ng-show="fields.passwordFailed">Password too short!</span>
							<span ng-show="fields.passwordPassed">You can use this password.</span>
						</div>
					</div>
					<div ng-class="{passed:fields.confirmPassed, failed:fields.confirmFailed}">
						<div class="row">
							<div class="label">Confirm New<br />Password</div>
							<div class="field"><input type="text" ng-model="fields.confirm" ng-focus="{{$parent.showPopover=='pLogin'}}"></div>
						</div>
						<div class="message">
							<span ng-show="fields.confirmFailed">Does not match!</span>
							<span ng-show="fields.confirmPassed">Password matches.</span>
						</div>
					</div>
					
					<div class="row">
						<div class="label">Old<br />Password</div>
						<div class="field"><input type="password" ng-model="fields.oldPassword" /></div>
					</div>
					<input type="submit" class="button-action" value="Update" ng-click="OnPasswordSubmitClick(fields)" />
					<input type="button" class="button-normal" value="Cancel" ng-click="OnCancelClick()" />
					<div id="failMessage" ng-show="fields.failed">
						Your old password is incorrect.
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="text/ng-template" id="tp-footer">
		<div class="sponsors">
			<div class="footerTitle">SPONSORS</div>
			<div class="placeholder"></div>
			<div class="placeholder"></div>
			<div class="placeholder"></div>
			<div class="placeholder"></div>
			<div class="placeholder"></div>
		</div>
		<div class="contacts">
			<div class="footerTitle">CONTACTS</div>
			<div class="contactItem">
				<b>pres@uwksa.ca</b><br />
				Kyoo Jin Cho<br />
				President, 2013-2014 UWKSA
			</div>
			 <div class="contactItem">
				<b>web@uwksa.ca</b><br />
				UWKSA Web Team
			</div>
		</div>
		<div class="copyright">
			&copy; 2013 UWKSA<br />
			<span>CMYK STUDIO</span>
		</div>
	</script>
</body>
</html>
