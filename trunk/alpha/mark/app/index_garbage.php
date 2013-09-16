<!doctype html>
<html lang="en" ng-app="app">
<head>
	<meta charset="utf-8">
	<title>UWKSA</title>
	<link rel="stylesheet" type="text/css" href="./css/style.css">
	<link rel="shortcut icon" href="../favicon.ico?v=3" />
</head>
<body>
<div id="header" class="OpenSans">
	<div id="navigation" ng-controller="NavController">
		<ul>
			<li ng-class="isActive('/main')"><a href="#/main">HOME</a></li>
			<li>ABOUT</li>
			<li ng-class="isActive('/qna')"><a href="#/qna">Q&A</a></li>
			<li>COMMUNITY</li>
			<li>SUBLETS</li>
			<li>TUTORS</li>
			<li>BUY&SELL</li>
			<li>EXAMS</li>
		</ul>
	</div>
	<div id="toolbar" ng-controller="BoardController">
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
	<div>
		<!-- debug -->
		<pre>$location.path() = {{$location.path()}}</pre>
		<pre>$route.current.templateUrl = {{$route.current.templateUrl}}</pre>
		<pre>$route.current.params = {{$route.current.params}}</pre>
		<pre>$route.current.scope.name = {{$route.current.scope.name}}</pre>
		<pre>$routeParams = {{$routeParams}}</pre>
	</div>
</div> 

<div ng-view>
</div>



<script type="text/ng-template" id="tp-editor" ng-controller="BoardController">
		<div ng-style="{opacity: 1-(fields.submitting*0.5)}">
			<div class="editorTitle">
				<input ng-model="fields.title" ng-focus="{{showComposeView||showEditView}}" type="text" placeholder="Title" />
			</div> <!-- end #w2-composeTitle -->
			<div class="editorControl">
				Space for editor icons (<b>B</b>, <i>I</i>, <u>U</u> etc.)
				<!--Bold Italics Underline Color OL UL Justified Left Center Right-->
			</div>
			<div class="editorContent" ng-model="fields.content" contentEditable></div>
			<div class="editorAction">
				<input type="button" class="button-action" value="Submit" ng-disabled="fields.submitting" ng-click="OnEditorSubmit(fields)" />
				<input type="button" class="button-normal" value="Cancel" ng-click="OnEditorCancel()" />
			</div>
		</div>
	</script>

	<script type="text/ng-template" id="tp-commenter" ng-controller="BoardController">
		<div ng-style="{opacity: 1-(fields.submitting*0.5)}">
			<div class="commenterControl">
				Space for editor icons (<b>B</b>, <i>I</i>, <u>U</u> etc.)
				<!--Bold Italics Underline Color OL UL Justified Left Center Right-->
			</div>
			<div class="commenterContent" ng-model="fields.content" ng-focus="{{node.showNewCommenterView||node.showEditCommenterView}}" contentEditable></div>
			<div class="commenterAction">
				<input type="button" class="button-action" value="Submit" ng-disabled="fields.submitting" ng-click="OnCommenterSubmit(fields)" />
				<input type="button" class="button-normal" value="Cancel" ng-show="showCancelButton" ng-click="OnCommenterCancel()" />
			</div>
		</div>
	</script>


<!-- Scripts -->
<script src="./lib/angular.min.js"></script>
<script src="./lib/angular-sanitize.min.js"></script>
<script src="./js/app.js"></script>
<script src="./js/controllers.js"></script>
<script src="./js/filters.js"></script>
<script src="./js/directives.js"></script>
<script src="./js/services.js"></script>
<script src="./js/controllers.js"></script>
</body>
</html>