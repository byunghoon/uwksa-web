<?php
$db = db();
global $db;

function GetPostList($bName, $pId, $pNo) {
	$PPP = 15;							// Posts Per Page
	$rawList = DBGetPostList($bName);	// all posts from DB
	$returnList;
	$postCount = sizeof($rawList);
	
	$result['listSize'] = $PPP;
	$result['postCount'] = $postCount;
	$result['canPost'] = true;
	$result['availableTags'] = array();
	
	// Compute start index
	$startIndex = 0;
	$startIndexExists = false;
	if ($pNo > 0) {						// if $pNo specified
		$startIndex = ($pNo-1)*$PPP;
		$startIndexExists = ($startIndex < $postCount);
		
	} else if ($pId > 0) {				// if $pId specified
		for ($i=0; $i<$postCount; $i++) {
			if ($i%$PPP == 0) {
				$startIndex = $i;
			}
			if ($rawList[$i]['post_no'] == $pId) {
				$startIndexExists = true;
				break;
			}
		}
	} else {
		$startIndexExists = true;
	}
	
	// Fill in $returnList
	if ($startIndexExists) {
		$endIndex = ($startIndex+$PPP > $postCount) ? $postCount : $startIndex+$PPP;
		for ($i=$startIndex; $i<$endIndex; $i++) {
			$n = $i - $startIndex;
			
			$contentComplete = $rawList[$i]['content_original'];
			$contentShortened = strcut_utf8($contentComplete, 1000);
			$dateFormatted = format_date($rawList[$i]['date']);
			
			$returnList[$n]['postId'] = $rawList[$i]['post_no'];
			$returnList[$n]['canEdit'] = $rawList[$i]['user_id']==777;
			$returnList[$n]['canComment'] = true;
			$returnList[$n]['authorUserId'] = $rawList[$i]['user_id'];
			$returnList[$n]['author'] = $rawList[$i]['user_name'];
			$returnList[$n]['title'] = $rawList[$i]['title'];
			$returnList[$n]['date'] = $dateFormatted[0];
			$returnList[$n]['fullDate'] = $dateFormatted[1];

			if ($rawList[$i]['post_no'] == $pId) {
				// pId is specified and matched
				// - get content regardless of its length
				// - get all comments
				$returnList[$n]['comments'] = ListifyComments(DBGetComments($pId), -1);
				$returnList[$n]['content'] = $contentComplete;
				$returnList[$n]['isContentComplete'] = true;
			
			} else {
				// pId does not match
				// - do not fetch comments
				// - isContentComplete is true ONLY when comments.count==0 and content.length<100
				$returnList[$n]['comments'] = array();
				$returnList[$n]['content'] = $contentShortened;
				$isContentShortEnough = (strlen($contentComplete)==strlen($contentShortened));

				if ($isContentShortEnough == true && $rawList[$i]['comments'] == 0) {
					$returnList[$n]['isContentComplete'] = true;
				} else {
					$returnList[$n]['isContentComplete'] = false;
				}
			}
			$returnList[$n]['commentCount'] = $rawList[$i]['comments'];
			$returnList[$n]['tagIds'] = array();
		}
		
		$result['currentPage'] = ($startIndex/$PPP) + 1;
		$result['postPreviews'] = $returnList;
		return json_encode_unescaped($result);
		//return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	} else {
		$result['error'] = true;
		return json_encode_unescaped($result);
		//return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	}	
}

function GetPostContent($pId) {
	$rawPost = DBGetPostContent($pId);
	$result['content'] = $rawPost['content_original'];
	$result['comments'] = ListifyComments(DBGetComments($pId), -1);

	return json_encode_unescaped($result);
}

function ListifyComments($raw, $parentComment) {
	$returnArray = array();
	$index = 0;

	for ($i=0; $i<sizeof($raw); $i++) {
		if ($raw[$i]['parentComment'] == $parentComment) {
			$dateFormatted = format_date($raw[$i]['date']);

			$returnArray[$index]['commentId'] = $raw[$i]['comment_no'];
			$returnArray[$index]['deleted'] = $raw[$i]['deleted'];
			$returnArray[$index]['parentCommentId'] = $raw[$i]['parentComment'];
			$returnArray[$index]['canEdit'] = true;
			$returnArray[$index]['canComment'] = true;
			$returnArray[$index]['authorUserId'] = $raw[$i]['user_id'];
			$returnArray[$index]['author'] = $raw[$i]['user_name'];
			$returnArray[$index]['content'] = $raw[$i]['content_original'];
			$returnArray[$index]['date'] = $dateFormatted[1];
			$returnArray[$index]['children'] = ListifyComments($raw, $raw[$i]['comment_no']);

			$index ++;
		}
	}

	return $returnArray;
}


function DBGetPostList($bName) {
	global $db;
	$stmt = $db->stmt_init();
	$counter = 0;
	if ($stmt->prepare('SELECT post_no, user_id, user_name, title, content_original, comments, date FROM post ORDER BY post_no DESC')) {
		$stmt->execute();
		$stmt->bind_result($post['post_no'], $post['user_id'], $post['user_name'], $post['title'], $post['content_original'], $post['comments'], $post['date']);
		while ($stmt->fetch()) $postList[$counter++] = unserialize(serialize($post));
		$stmt->close();
		return $postList;
	}

}

function DBGetPostContent($pId) {
	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('SELECT pinned, board_id, user_id, user_name, title, content_sanitized, content_original, comments, date FROM post WHERE post_no=?')) {
		$stmt->bind_param('i', $pId);
		$stmt->execute();
		$stmt->bind_result($post['pinned'], $post['board_id'], $post['user_id'], $post['user_name'], $post['title'], $post['content_sanitized'], $post['content_original'], $post['comments'], $post['date']);
		$stmt->fetch();
		$stmt->close();
		return $post;
	}
}


function DBNewPost($bName, $title, $content) {
	$pinned = 0;
	$board_id = $bName;
	$user_id = '777';
	$user_name = 'Test User';
	$content_sanitized = $content;
	$content_original = $content;
	$date = time();

	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('INSERT INTO post (pinned, board_id, user_id, user_name, title, content_sanitized, content_original, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')) {
		$stmt->bind_param('issssssi', $pinned, $board_id, $user_id, $user_name, $title, $content_sanitized, $content_original, $date);
		$stmt->execute();
		$stmt->close();

		$stmt2 = $db->stmt_init();
		if($stmt2->prepare('SELECT post_no FROM post ORDER BY post_no DESC LIMIT 1')) {
			$stmt2->execute();
			$stmt2->bind_result($postNo);
			$stmt2->fetch();
			$stmt2->close();
			return $postNo;
		}
	}
}

function DBEditPost($pId, $title, $content) {
	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('UPDATE post SET title=?, content_sanitized=?, content_original=? WHERE post_no=?')) {
		$stmt->bind_param('sssi', $title, $content, $content, $pId);
		$stmt->execute();
		$stmt->close();
	}
}

function DBDeletePost($pId) {
	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('DELETE FROM post WHERE post_no=?')) {
		$stmt->bind_param('i', $pId);
		$stmt->execute();
		$stmt->close();
	}
}

function DBGetComments($parentPost) {
	global $db;
	$stmt = $db->stmt_init();
	$index = 0;
	if($stmt->prepare('SELECT deleted, parentComment, comment_no, user_id, user_name, content_sanitized, content_original, date FROM comment WHERE parentPost=? ORDER BY date')) {
		$stmt->bind_param('s', $parentPost);
		$stmt->execute();
		$stmt->bind_result($comment['deleted'], $comment['parentComment'], $comment['comment_no'], $comment['user_id'], $comment['user_name'], $comment['content_sanitized'], $comment['content_original'], $comment['date']);
		while($stmt->fetch()) $comment_list[$index++] = unserialize(serialize($comment));
		$stmt->close();
		return $comment_list;
	}
}

function DBNewComment($parentPost, $parentComment, $content) {
	$user_id = '777';
	$user_name = 'Test User';
	$content_sanitized = $content;
	$content_original = $content;
	$date = time();

	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('INSERT INTO comment (parentPost, parentComment, user_id, user_name, content_sanitized, content_original, date) VALUES (?, ?, ?, ?, ?, ?, ?)')) {
		$stmt->bind_param('iissssi', $parentPost, $parentComment, $user_id, $user_name, $content_sanitized, $content_original, $date);
		$stmt->execute();
		$stmt->close();
	}
	$stmt = $db->stmt_init();
	if($stmt->prepare('UPDATE post SET comments=comments+1 WHERE post_no=?')) {
		$stmt->bind_param('i', $parentPost);
		$stmt->execute();
		$stmt->close();
	}


	$stmt = $db->stmt_init();
	if($stmt->prepare('SELECT parentComment, comment_no, user_id, user_name, content_sanitized, content_original, date FROM comment ORDER BY comment_no DESC LIMIT 1')) {
		$stmt->execute();
		$stmt->bind_result($comment['parentComment'], $comment['comment_no'], $comment['user_id'], $comment['user_name'], $comment['content_sanitized'], $comment['content_original'], $comment['date']);
		$stmt->fetch();
		$stmt->close();
	}
	$returnArray = array();
	$dateFormatted = format_date($comment['date']);
	$returnArray['commentId'] = $comment['comment_no'];
	$returnArray['deleted'] = false;
	$returnArray['parentCommentId'] = $comment['parentComment'];
	$returnArray['canEdit'] = true;
	$returnArray['canComment'] = true;
	$returnArray['authorUserId'] = $comment['user_id'];
	$returnArray['author'] = $comment['user_name'];
	$returnArray['content'] = $comment['content_original'];
	$returnArray['date'] = $dateFormatted[1];
	$returnArray['children'] = array();
	return json_encode_unescaped($returnArray);
}

function DBEditComment($cId, $content) {
	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('UPDATE comment SET content_sanitized=?, content_original=? WHERE comment_no=?')) {
		$stmt->bind_param('ssi', $content, $content, $cId);
		$stmt->execute();
		$stmt->close();
	}
}

function DBDeleteComment($cId) {
	global $db;
	$stmt = $db->stmt_init();
	if($stmt->prepare('SELECT parentPost FROM comment WHERE comment_no=?')) {
		$stmt->bind_param('i', $cId);
		$stmt->execute();
		$stmt->bind_result($parentPost);
		$stmt->fetch();
		$stmt->close();
	}

	$stmt = $db->stmt_init();
	if($stmt->prepare('SELECT comment_no FROM comment WHERE parentComment=?')) {
		$stmt->bind_param('i', $cId);
		$stmt->execute();
		$stmt->store_result();
		$safeToDelete = ($stmt->num_rows == 0 ? true : false);
		$stmt->close();
	}
	if ($safeToDelete) {
		$query = 'DELETE FROM comment WHERE comment_no=?';
	} else {
		$query = 'UPDATE comment SET deleted=1 WHERE comment_no=?';
	}
	
	$stmt = $db->stmt_init();
	if($stmt->prepare($query)) {
		$stmt->bind_param('i', $cId);
		$stmt->execute();
		$stmt->close();
	}
	$stmt = $db->stmt_init();
	if($stmt->prepare('UPDATE post SET comments=comments-1 WHERE post_no=?')) {
		$stmt->bind_param('i', $parentPost);
		$stmt->execute();
		$stmt->close();
	}
}




function db() {
	$db = new mysqli('localhost', 'uwksac5', 'qpfrldpqhsowntpdy', 'uwksac5_test');
	if(mysqli_connect_errno()) die('Connect failed: ' .mysqli_connect_error());
	$stmt = $db->stmt_init();
	if($stmt->prepare("SET names utf8")) {
		$stmt->execute();
		$stmt->close();
	}
	return $db;
}

function strcut_utf8($str, $len, $checkmb=false, $tail='') {
	/**
	* UTF-8 Format
	* 0xxxxxxx = ASCII, 110xxxxx 10xxxxxx or 1110xxxx 10xxxxxx 10xxxxxx
	* latin, greek, cyrillic, coptic, armenian, hebrew, arab characters consist of 2bytes
	* BMP(Basic Mulitilingual Plane) including Hangul, Japanese consist of 3bytes
	**/
	preg_match_all('/[\xE0-\xFF][\x80-\xFF]{2}|./', $str, $match); // target for BMP
	$m = $match[0];
	$slen = strlen($str); // length of source string
	$tlen = strlen($tail); // length of tail string
	$mlen = count($m); // length of matched characters
	if ($slen <= $len) return $str;
	if (!$checkmb && $mlen <= $len) return $str;
	$ret = array();
	$count = 0;
	for ($i=0; $i<$len; $i++) {
		$count += ($checkmb && strlen($m[$i]) > 1)?2:1;
		if ($count + $tlen > $len) break;
		$ret[] = $m[$i];
	}
	$result =  join('', $ret).$tail;
	return ($result == $str) ? $result : $result.'..';
}

function format_date($date) {
	$format[0] = date('M j', $date);
	$format[1] = date('l F j, Y \a\t h:i:s a', $date);
	return $format;
}

function json_encode_unescaped($arr)
{
	//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
	array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
	return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');

}
?>