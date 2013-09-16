<?php
include_once 'library.php';
header("Content-type:application/json; charset=utf-8");

if ($_GET['t'] == 1) {
	// load without post no:		$bName, -1, -1
	// load with specified post no:	$bName, $postId, -1
	// page number button clicked:	$bName, -1, $pageNo
	echo GetPostList($_GET['boardName'], $_GET['postId'], $_GET['pageNo']);
} else if ($_GET['t'] == 2) {
	echo GetPostContent($_GET['postId']);
} else if ($_GET['t'] == 'special') {
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