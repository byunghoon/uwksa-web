<?php ob_start(); include_once 'backend/header.php'; ?>
<!doctype html>
<html lang="en" xmlns:ng="http://angularjs.org" id="ng-app" ng-app="uwksa" ng-controller="RootController">
<head>
	<!--[if lte IE 8]>
		<script src="http://bestiejs.github.io/json3/lib/json3.min.js"></script>
    <![endif]-->
    <!--<meta name="viewport" content="width=device-width, target-densityDpi=device-dpi, initial-scale=1" />--> <!-- Tempoarily disable this to fix Mac Chrome bug -->
    <meta charset="utf-8">
	<meta name="fragment" content="!" />
	<meta name="keywords" content="UWKSA, University of Waterloo Korean Students Association, Waterloo, University of Waterloo, Korean Students, Student Association, UW, KSA, 워털루, 워터루, 워털루대학교, 워터루대학교, 한인학생회, 한인회, 학생회" />
	<meta name="description" content="University of Waterloo of Korean Students Association (UWKSA) is the largest Korean student organization in K-W Area. Its main mission is to create a harmonious and active Korean community within UW, and promote unity amongst Koreans at UW. It encourages interaction and sharing of knowledge within the Korean student society by hosting various social and academic events." />
	<title ng-bind="title"></title>
	<?php echo '<script>var USING_FB = "'.$_SESSION['IsFacebookUser'].'"; var USER_ID = "'.$_SESSION['UserID'].'"; var USER_NAME = "'.htmlentities($_SESSION['UserName'], ENT_QUOTES, "UTF-8").'"; var USER_EMAIL = "'.$_SESSION['Email'].'"; var LOGGED_IN = ' . ($_SESSION['UserID']>0 ? 'true' : 'false') . ';</script>'; ?>
	<?php include_once 'templates.html'; ?>
	<script src="angular.min.js"></script>
	<!--<script src="angular-sanitize.min.js"></script>-->
	<script src="script.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css">
	<!--[if lte IE 8]>
		<script src="respond.min.js"></script>
    <![endif]-->
    <link rel="shortcut icon" href="../favicon.ico?v=3" />
</head>
<body ng-keyup="OnKeyUp">
	<div id="splash" class="ProgressBar" ng-show="show.splash">
		<span><img src="data:image/png;base64,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" alt="" /></span>
	</div>

	<div id="header" class="NoSelection">
		<div id="navigation">
			<ul>
				<li ng-repeat="module in modules" ng-class="{selected:module==selectedModule}">
					<a href="#!/{{module.address}}">{{module.name}}</a>
				</li>
			</ul>
		</div>
		<div id="login">
			<span ng-show="!session.loggedIn" ng-click="USER.OnLoginClick()">LOGIN</span>
			<span ng-show="!session.loggedIn" ng-click="USER.OnSignUpClick()">SIGN UP</span>
			<span class="userName" ng-show="session.loggedIn" ng-bind-html-unsafe="session.userName" ng-click="USER.OnProfileClick()"></span>
		</div>
	</div>

	<span ng-show="popover!='kSignUp'">
		<?php include_once 'home.html'; ?>
		<?php include_once 'about.html'; ?>
		<?php include_once 'board.html'; ?>
	</span>


	<div id="loginView" ng-show="popover=='kLogin'" ng-controller="LoginController" ng-class="{shake:fields.failed||fields.incomplete}">
		<div id="loginBox" ng-show="option==0">
			<div id="login1"><a href="facebook.php?q=login&url={{encodedUrl}}"><img src="assets/user_fb_login.png" alt="" /></a></div>
			<div id="loginOr">
				<div id="leftHr"></div>
				<div id="or">OR</div>
				<div id="rightHr"></div>
			</div>
			<form>
				<div id="login2">
					<div class="row">
						<div class="label">Email</div>
						<div class="field"><input type="text" ng-model="fields.email" ng-focus="{{$parent.popover=='kLogin'}}"></div>
					</div>
					<div class="row">
						<div class="label">Password</div>
						<div class="field"><input type="password" ng-model="fields.password" /></div>
					</div>
				</div>
				<input type="submit" class="button-action" value="Login" ng-click="OnLoginSubmitClick(fields)" />
				<input type="button" class="button-normal" value="Cancel" ng-click="OnLoginCancelClick()" />
				<div class="linklet" ng-show="!fields.failed" ng-click="OnForgotPasswordClick()">
					Forgot Password?
				</div>
				<div class="failMessage" ng-show="fields.failed">
					{{fields.failedMessage}}
				</div>
			</form>
		</div>
		<div id="forgotPasswordBox" ng-show="option==1">
			<div class="instruction">
				Please enter your email address. We will send instructions on how to reset your password.
			</div>
			<div class="row">
				<input type="text" ng-model="fields.email" ng-focus="{{option==1}}" />
				<input type="button" class="button-action" value="Send" ng-click="OnSendInstructionClick(fields)" />
			</div>
			<div class="failMessage" ng-show="!fields.instructionSent&&!fields.noSuchEmail">&nbsp;</div>
			<div class="failMessage" ng-show="fields.noSuchEmail">Your email does not exist.</div>
			<div class="successMessage" ng-show="fields.instructionSent">An instruction is sent to your email.</div>
			<input type="button" class="button-normal" value="Close" ng-click="OnLoginCancelClick()" />
		</div>
	</div>

	<div id="signUpView" ng-show="popover=='kSignUp'">
		<div id="signUpForeground" ng-controller="SignUpController" ng-class="{shake:fields.failed||fields.incomplete}" ng-style="{opacity: 1-(fields.submitting*0.5)}">
			<div id="signUpCompletedMessage" ng-show="fields.signUpCompleted">
				<div class="green">Sign up complete!</div>
				<div>A confirmation email is sent to your email address. If you don't see one, please check your spam folder.</div>
				<input type="button" class="button-normal" value="Close" ng-click="OnSignUpCloseClick()" />
			</div>
			<div id="signUpBox" ng-show="!fields.signUpCompleted">
				<div id="signUp1Header">Sign up with Facebook</div>
				<div id="signUp1"><a href="facebook.php?q=signup"><img src="assets/user_fb_connect.png" alt="" /></a></div>
				<div id="signUp2Header">Sign up with email</div>
				<form>
					<div id="signUp2">
						<div class="row" ng-class="{passed:fields.namePassed, failed:fields.nameFailed}">
							<div class="label">Nickname</div>
							<div class="field"><input type="text" maxlength="32" ng-model="fields.name" ng-focus="{{$parent.popover=='kSignUp'}}" ng-on-blur="OnNameFieldBlur(fields)" /></div>
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

	<div class="OpenSans" ng-show="popover=='kProfile'" id="profileView" ng-controller="ProfileController" ng-class="{shake:fields.failed||fields.incomplete}">
		<div id="profileOptionBox" ng-class="{usingFacebook:$parent.session.usingFB}" ng-show="option==0&&!fields.changeSuccessful">
			<input type="button" class="button-action" value="Logout" ng-click="$parent.USER.OnLogoutClick()" />
			<input type="button" class="button-action" ng-show="!$parent.session.usingFB" value="Change Nickname" ng-click="OnChangeNameClick()" />
			<input type="button" class="button-action" ng-show="!$parent.session.usingFB" value="Change Password" ng-click="OnChangePasswordClick()" />
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
						<div class="plaintext" ng-bind-html-unsafe="$parent.session.userName"></div>
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
						<div class="field"><input type="password" ng-model="fields.password" ng-focus="{{option==2}}"></div>
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
						<div class="field"><input type="password" ng-model="fields.confirm"></div>
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

</body>
</html>
