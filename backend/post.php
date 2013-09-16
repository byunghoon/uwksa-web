<?php
include_once 'library.php';
include_once 'header.php';
include_once 'user.php';

// Note we are not using $_POST.
// http://victorblog.com/2012/12/20/make-angularjs-http-service-behave-like-jquery-ajax/
$params = json_decode(file_get_contents('php://input'), true);
$request = $params['r'];

// should do safety check (null values, user permissions, etc.)
if ($request == 'newPost') {
//$post_id, $pinned, $board_id, $title, $content, $user_id
	echo DBSavePost(-1, $params['pinned'], $params['bName'], $params['title'], $params['content'],$_SESSION['UserID'], $params['addedTags'], $params['deletedTags'], $params['addedFiles'], null); //BY: $_SESSION['added/deletedTags'] to $params['added/deletedTags']
} else if($request == 'editPost') {
	if ($params['pId']) {
		DBSavePost ($params['pId'],$params['pinned'], $params['bName'], $params['title'], $params['content'], $_SESSION['UserID'], $params['addedTags'], $params['deletedTags'], $params['addedFiles'], $params['deletedFiles']); //BY: $_SESSION['added/deletedTags'] to $params['added/deletedTags']
	}
} else if ($request == 'deletePost') {
	DBDeletePost($params['pId'], $_SESSION['UserID'], $params['files']);
} else if ($request == 'newComment') {
	$parentComment = $params['parentComment'];
	if ($parentComment == null) $parentComment = -1;
	$parentPost= $params['parentPost'];
	if ($parentPost == null) $parentPost = -1;
	
	echo DBSaveComment(-1, $parentPost, $parentComment, $params['content'], $_SESSION['UserID'], 0, $params['addedFiles'], null);
} else if ($request == 'editComment') {
	echo DBSaveComment($params['cId'], $params['parentPost'], $params['parentComment'], $params['content'], $_SESSION['UserID'], 0, $params['addedFiles'], $params['deletedFiles']);
} else if ($request == 'deleteComment') {
	DBDeleteComment($params['cId'], $_SESSION['UserID'], $params['files']);
} else if ($request == 'login') {
	echo Login($params['email'], $params['password']);
} else if ($request == 'logout') {
	Logout();
} else if ($request == 'signUp') {
	echo SignUp($params['userName'], $params['emailAddress'], $params['password']);
} else if ($request == 'updateName') {
	SaveUserName($_SESSION['UserID'], $params['userName']);
} else if ($request == 'updatePassword') {
	echo SaveUserPassword($_SESSION['UserID'], $params['newPassword'], $params['oldPassword']);
} else if ($request == 'forgotPassword') { 
	SendForgotPasswordEmail($params['email']);
} else if ($_POST['r'] == 'updateForgottenPassword' ) {
	echo UpdateForgottenPassword($_POST['newPassword']);
}



/*
$request = $_POST['r'];
if($_POST['b']) $board_id = $_POST['b'];
if($_POST['n']) $post_no = $_POST['n'];
if($_POST['t']) $title = $purifier->purify($_POST['t']);
if($_POST['p']) $pinned = $_POST['p'];
if($_POST['co']) $content_original = $_POST['co'];
if($_POST['cs']) $content_sanitized = $purifier->purify($_POST['cs']);
if($_POST['pr']) $parent = $_POST['pr'];
if($_POST['d']) $depth = $_POST['d'];
if($_POST['m']) $comment_no = $_POST['m'];


if($request=='newPost')
{
	if($pinned && $board_id && $title && $content_sanitized && $content_original
		&& is_postable($board_id, $_SESSION['class']))
	{
		new_post(($pinned=="yes"?1:0), $board_id, $_SESSION['id'],
			$_SESSION['name'], $title, $content_sanitized, $content_original);
	}
}
else if($request=='editPost')
{
	if($pinned && $board_id && $post_no && $title && $content_sanitized
		&& $content_original && is_postable($board_id, $_SESSION['class']))
	{
		set_pinned($post_no, ($pinned=="yes"?1:0));
		set_title($post_no, $title);
		set_post_content($post_no, $content_sanitized, $content_original);
	}
		
}
else if($request=='deletePost')
{
	if($board_id && $post_no && is_postable($board_id, $_SESSION['class']))
	{
		delete_post($post_no);
	}
}
else if($request=='newComment')
{
	if($board_id && $parent && $content_sanitized && $content_original &&
		is_commentable($board_id, $_SESSION['class']))
	{
		$comment['comment_no'] = new_conmment($parent, $_SESSION['id'],
			$_SESSION['name'], $content_sanitized, $content_original);
		$comment['user_id'] = $_SESSION['id'];
		$comment['user_name'] = $_SESSION['name'];
		$comment['content_sanitized'] = $content_sanitized;
		$comment['content_original'] = $content_original;
		$comment['date'] = time();
		echo draw_comment_view($board_id, $comment, ($depth?$depth:0));
	}
}
else if($request=='editComment')
{
	if($board_id && $comment_no && $content_sanitized && $content_original &&
		is_commentable($board_id, $_SESSION['class']))
	{
		set_comment_content($comment_no, $content_sanitized, $content_original);
		echo $content_sanitized;
	}
}
else if($request=='deleteComment')
{
	if($board_id && $comment_no && is_commentable($board_id, $_SESSION['class']))
	{
		delete_comment($comment_no);
	}
}
*/
?>