<?php
include_once 'library.php';
include_once 'header.php';
include_once 'user.php';
include_once 'crypto.php';

header("Content-type:application/json; charset=utf-8");
if ($_GET['t'] == 1) {
	// load without post no:		$bName, -1, -1
	// load with specified post no:	$bName, $postId, -1
	// page number button clicked:	$bName, -1, $pageNo
	$pageNo = $_GET['pageNo'] ;
	if ($pageNo == null) $pageNo = -1;
	$searchParam = $_GET['searchParam'];
	if ($searchParam == null) $searchParam = '';
	$postId = $_GET['postId'];
	if ($postId == null) $postId = -1;
	
	echo GetPostList($_GET['boardName'], $postId, $pageNo, $_GET['postLimit'], json_decode($_GET['tags'], true), $searchParam, $_GET['searchOptions']); //BY: added json_decode
} else if ($_GET['t'] == 2) {
	echo GetPostContent($_GET['postId']);
} else if ($_GET['t'] == 3) {
	echo CheckUserName($_GET['UserName']);
} else if ($_GET['t'] == 4) {
	echo CheckEmailAddress($_GET['EmailAddress']);
} else if ($_GET['t'] == 5) {
	echo GetLatestFeed(json_decode($_GET['FeedBoard'], true));					//BY: added json_decode
} else if ($_GET['t'] == 6) {
	for ($i = 0; $i < sizeof($addedTags); $i ++) {
		echo $addedTags[$i] . " ";
	}
	echo DBSavePost(-1, $_GET['pinned'], $_GET['bName'], $_GET['title'], $_GET['content'],$_GET['UserID'], $_GET['addedTags'], $_GET['deletedTags']);
} else if ($_GET['t'] == 7) {
	echo EnableUser(fnDecrypt($_GET['c']));
} else if ($_GET['t'] == 8) {
	if ($_GET['c'] == "signup")
		FacebookSignUp($_GET['url']);
	else if ($_GET['c'] == "login") 
		FacebookLogin($_GET['url']);
} else if ($_GET['t'] == 9) {
	echo FacebookUrl($_GET['c'], $_GET['url']);  // c = "login" or "signup"
} else if ($_GET['t'] == 10) {
	UpdatePassword($_GET['c']);
} else if ($_GET['t'] == 'special') {
	$pageNo = $_GET['pageNo'] ;
	if ($pageNo == null) $pageNo = -1;
	$searchParam = $_GET['searchParam'];
	if ($searchParam == null) $searchParam = '';
	$postId = $_GET['postId'];
	if ($postId == null) $postId = -1;
	
	echo GetPostListTest($_GET['boardName'], $postId, $pageNo, $_GET['postLimit'], json_decode($_GET['tags'], true), $searchParam, $_GET['searchOptions']); //BY: added json_decode
	/*DBNewComment(138, -1, 'main comment 1'); //1
	DBNewComment(138, -1, 'main comment 2'); //2
	DBNewComment(138, -1, 'main comment 3'); //3
	DBNewComment(138, -1, 'main comment 4'); //4
	DBNewComment(138, -1, 'main comment 5'); //5

	DBNewComment(138, 1, '1-1 sub comment'); //6
	DBNewComment(138, 1, '1-2 sub comment'); //7
	DBNewComment(138, 1, '1-3 sub comment'); //8

	DBNewComment(138, 2, '2-1 sub comment'); //9
	DBNewComment(138, 9, '2-1-1 sub sub comment'); //10
	DBewComment(138, 10, '2-1-1-1 sub sub sub comment'); //11*/
}

?>