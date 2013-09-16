<?php

include_once 'header.php';

$db = db();
global $db;
define('new_user_level','8');
define('exec_level' , '6');
define('publicUrl','http://biz139.inmotionhosting.com/~uwksac5/alpha');

// $tags[0] = "cs";
// echo GetPostList('qna', -1, 1, 15, $tags, '', "1111");
//echo json encode_unescaped(DBGetPostTags(101));
//echo GetBoardTags('qna');
//$array = DBSaveUser(-1, 'frontend', 'frontend', 'frontend', null, null, null);
//echo $array['ResultCode'];
//echo GetPostContent(101);
//echo GetLatestFeed('qna', 3, '');
// $arr[0]['boardName'] = 'qna';
// $arr[0]['postLimit'] = 3;
// $arr[0]['requirePreview'] = 0;
// $arr[1]['boardName'] = 'news';
// $arr[1]['postLimit'] = 3;
// $arr[1]['requirePreview'] = 0;

// echo GetLatestFeed($arr);

function DeleteUploadFile(){

}
function GetLatestFeed($array) {
	for ($i = 0; $i < sizeof($array); $i ++) {
		$result[$i] = GetBoardLatestFeed($array[$i]['boardName'], $array[$i]['postLimit'], $array[$i]['requirePreview']);
	}
	return json_encode_unescaped($result);
}

function GetBoardLatestFeed($bName, $postLimit, $requirePreview) {
	$raw = DBGetLastestFeed($bName, $postLimit);
	if ($raw) {
		$post_ids = '*';
		$index = 0;
		while ($raw[$index] != null){
			$result[$index]['postId'] = $raw[$index]['post_id'];				//BY: post_id to postId for consistency
			$result[$index]['author'] = $raw[$index]['author'];
			$result[$index]['month'] = date('M', $raw[$index]['date']);			//BY: separated date into month and date
			$result[$index]['date'] = date('j', $raw[$index]['date']);			//BY: and added formatting
			$result[$index]['title'] = $raw[$index]['title'];
			$result[$index]['commentCount'] = $raw[$index]['commentCount'];
			if ($requirePreview) $result[$index]['pureContent'] = strcut_utf8(strip_tags($raw[$index]['content']), 200);	//BY: if statement, strip tags and strcut
			$result[$index]['selectedTags'] = DBGetPostTags($result[$index]['post_id']);	//BY: moved $index++
			$index ++;																		//BY: to another line
		}
	}
	return $result;
}

function DBGetLastestFeed($bName, $postLimit) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	$index = 0;
	if($stmt->prepare('CALL Get_Latest_Feed(?, ?)')) {
		$stmt->bind_param('si', $bName, $postLimit);
		$stmt->execute();
		$stmt->bind_result($feed['post_id'], $feed['author'], $feed['date'], $feed['title'], $feed['commentCount'], $feed['content']);
		while($stmt->fetch()){
		 $comment_list[$index++] = unserialize(serialize($feed));
		 }
		$stmt->close();
		if ($index == 0)	return null;
		else return $comment_list;
	}
}

function GenerateTagParam($tags) {
	$strQuery = '';
	for ($i = 0; $i < sizeof($tags); $i ++ ) {
		if ($strQuery != '') $strQuery .= "AND ";
		
		$strQuery .= "tag.tag = '".$tags[$i]."'";
	}

	return $strQuery;
}

function GenerateSearchParam($searchOptions, $searchParam) {
	$searchFields = array("u.UserName", "p.PostTitle", "p.PostContent", "uc.UserComment");
	$strQuery = '';
	for ($i = 0; $i < 4; $i ++) {
		if ($searchOptions[$i] == "1") {
			$query = $searchFields[$i] . " LIKE '%" . $searchParam . "%'";
			if ($strQuery == '') {
				$strQuery = $query;
			}
			else {
				$strQuery = $strQuery . " OR " . $query;
			}
		} 
	}

	return $strQuery;
}

function GetPostList($board_Id, $pId, $pNo, $PPP, $tag, $searchParam, $searchOptions) {						// Posts Per Page
	If ($pId == null || $pId == '') $pId = -1;

	$rawList = DBGetPostList($board_Id, $PPP, $pId, $pNo, $postCount, $newPage,  $tag, GenerateSearchParam($searchOptions, $searchParam), $searchOptions);	// all posts from DB
	$returnList;

	$permission = DBCheckBoardAccessibility($board_Id, $_SESSION['UserLevel']);
	
	$result['listSize'] = $PPP;
	$result['postCount'] = $postCount;
	$result['canPost'] =  $permission;
	$result['canPin'] = $_SESSION['UserLevel'] < exec_level;
	$result['availableTags'] = DBGetBoardTags($board_Id);
	
	// Fill in $returnList
		for ($i=0; $i<sizeof($rawList); $i++) {
			
			/* BEGIN BY: content cut fix */
			$htmlContent = $rawList[$i]['content_original'];
			$htmlContentShortened =strcut_utf8($htmlContent, 1000);
			$pureContent = strcut_utf8(strip_tags($rawList[$i]['content_original']), 200);
			$pureContent = str_replace('&nbsp;', ' ', $pureContent);
			//$contentComplete = $rawList[$i]['content_original'];
			//$pureContentComplete = strcut_utf8($contentComplete, 1000);
			//$contentShortened = strcut_utf8($contentComplete, 1000);
			//$pureContentShortened = strcut_utf8(strip_tags($contentComplete), 1000);
			/* END BY */

			$dateFormatted = format_date($rawList[$i]['date']);
			
			$returnList[$i]['postId'] = $rawList[$i]['post_no'];
			$returnList[$i]['canEdit'] = $rawList[$i]['user_id']==$_SESSION['UserID'];
			$returnList[$i]['canComment'] = $_SESSION['UserLevel'] <= new_user_level;
			$returnList[$i]['authorUserId'] = $rawList[$i]['user_id'];
			$returnList[$i]['author'] = $rawList[$i]['user_name'];
			$returnList[$i]['authorEmail'] = $rawList[$i]['user_email'];
			$returnList[$i]['title'] = $rawList[$i]['title'];
			$returnList[$i]['date'] = ($rawList[$i]['date']);
			$returnList[$i]['pinned'] = $rawList[$i]['pinned'];
			$returnList[$i]['date'] = $dateFormatted[0];
			$returnList[$i]['epochtime'] = $rawList[$i]['date'];
			$returnList[$i]['fullDate'] = $dateFormatted[1];

			if ($rawList[$i]['post_no'] == $pId) {
				// pId is specified and matched
				// - get content regardless of its length
				// - get all comments
				$returnList[$i]['comments'] = ListifyComments(DBGetUserComments($pId), -1);
				$returnList[$i]['content'] = $htmlContent;										// BY: was $contentComplete;
				$returnList[$i]['pureContent'] = $pureContent;									// BY: was $pureContentComplete;
				$returnList[$i]['isContentComplete'] = true;
			
			} else if ($pId == -1 && $i == 0) {
				$returnList[$i]['comments'] = ListifyComments(DBGetUserComments($returnList[$i]['postId']), -1);
				$returnList[$i]['content'] = $htmlContent;										// BY: was $contentComplete;
				$returnList[$i]['pureContent'] = $pureContent;									// BY: was $pureContentComplete;
				$returnList[$i]['isContentComplete'] = true;
			} else {
				// pId does not match
				// - do not fetch comments
				// - isContentComplete is true ONLY when comments.count==0 and content.length<100
				$returnList[$i]['comments'] = array();
				$returnList[$i]['content'] = $htmlContentShortened;								// BY: was $pureContentShortened;
				$returnList[$i]['pureContent'] = $pureContent;									// BY: strip_tags($contentComplete);
				$isContentShortEnough = (strlen($htmlContent)==strlen($htmlContentShortened));	// BY: was (strlen($contentComplete)==strlen($contentShortened));

				if ($isContentShortEnough == true && $rawList[$i]['comments'] == 0) {
					$returnList[$i]['isContentComplete'] = true;
				} else {
					$returnList[$i]['isContentComplete'] = false;
				}
			}
			$returnList[$i]['commentCount'] = $rawList[$i]['comments'];
			
			$returnList[$i]['selectedTags'] = DBGetPostTags($returnList[$i]['postId']);
		}
		
		$result['currentPage'] = $newPage;
		$result['postPreviews'] = $returnList;
		/*for ($j = 0; $j < sizeof($returnList); $j ++) {
			echo $returnList[$j]['content'];
			echo "       <p>      ";
		}*/
		return json_encode_unescaped($result);
		//return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	
}

function GetPostContent($pId) {
	$rawPost = DBGetPostContent($pId);
	$result['content'] = $rawPost['content'];
	$result['authorEmail'] = $rawPost['user_email'];
	$result['canComment'] = $_SESSION['UserLevel'] <= new_user_level;

	$result['comments'] = ListifyComments(DBGetUserComments($pId), -1);
	return json_encode_unescaped($result);
}


function GetPostTags($pId) {
	return json_encode_unescaped(DBGetPostTags($pId));
}

function GetBoardTags($bName) {
	return json_encode_unescaped(DBGetBoardTags($bName));
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
			$returnArray[$index]['canEdit'] = $raw[$i]['user_id']==$_SESSION['UserID'] && $_SESSION['UserLevel'] <= new_user_level;
			$returnArray[$index]['canComment'] = $_SESSION['UserLevel'] < new_user_level;
			$returnArray[$index]['authorUserId'] = $raw[$i]['user_id'];
			$returnArray[$index]['author'] = $raw[$i]['user_name'];
			$returnArray[$index]['authorEmail'] = $raw[$i]['user_email'];
			$returnArray[$index]['content'] = $raw[$i]['content_original'];
			$returnArray[$index]['date'] = $dateFormatted[1];
			$returnArray[$index]['epochtime'] = $raw[$i]['date'];
			$returnArray[$index]['children'] = ListifyComments($raw, $raw[$i]['comment_no']);

			$index ++;
		}
	}

	return $returnArray;
}

function DBGetPostTags($pId) {
	global $db;
	$stmt = $db->stmt_init();
	$counter = 0;

	$sql = 'Call Get_Post_Tag(' . $pId . ');';
	$db->next_result();
	if ($result = $db->query($sql)) {
		while ($row = $result->fetch_row()) {
			$tag[$counter]['tagName'] = $row[0];
			$tag[$counter++]['displayName'] = $row[1];
		}
	}

	if ($counter == 0)	return array();
	else return $tag;
}

function DBGetBoardTags($bName) {
	global $db;
	$stmt = $db->stmt_init();
	$counter = 0;

	$db->next_result();
	$sql = "Call Get_Board_Tag('" . $bName . "');";
	if ($result = $db->query($sql)) {
		while ($row = $result->fetch_row()) {
			$tag[$counter]['tagName'] = $row[0];
			$tag[$counter++]['displayName'] = $row[1];
		}
	}

	if ($counter == 0)	return array();
	else return $tag;
}

function DBGetUserInfo($user_id) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	$sql = 'CALL Get_User_Info(' . $user_id . ');';
	$result = $db->query($sql);
	if ($result){
		$resultRow = $result->fetch_row();
		$user['UserID'] = $resultRow[0];
		$user['UserName'] = $resultRow[1];
		$user['Email'] = $resultRow[2];
		$user['UserLevel'] = $resultRow[3];
		$user['ErrorCode'] = 0;
		$user['ErrorStatus'] = 'Success';
		$user['ErrorMessage'] = '';
		
		return $user;
	}
}

function DBFacebookUserLogin($facebookUserId){
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	if ($stmt -> prepare ('CALL KSA_Facebook_Login(?)')) {
		$stmt->bind_param('s', $facebookUserId);
		$stmt->execute();
		$stmt->bind_result($loginResult['ErrorCode'], $loginResult['ErrorStatus'], $loginResult['ErrorMessage'], $loginResult['pkUserID']);
		$stmt->fetch();
		$stmt->close();
		$db->next_result();
		if ($loginResult['ErrorCode'] == 0) {
			$sql = 'CALL Get_User_Info(' . $loginResult['pkUserID'] . ');';
			$result = $db->query($sql);
			if ($result){
				$resultRow = $result->fetch_row();
				$user['UserID'] = $resultRow[0];
				$user['UserName'] = $resultRow[1];
				$user['Email'] = $resultRow[2];
				$user['UserLevel'] = $resultRow[3];
				$user['ErrorCode'] = 0;
				$user['ErrorStatus'] = 'Success';
				$user['ErrorMessage'] = 'Login Successful';
				
				return $user;
			}
			else {
				$loginResult['ErrorCode'] = 1;
				$loginResult['ErrorStatus'] = 'Fail';
				$loginResult['ErrorMessage'] = 'Error on retrieving user info';
			}
		
		}
		
		return $loginResult;
	}
}

function DBUserLogin($email, $password) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	if ($stmt -> prepare ('CALL KSA_Login(?,?)')) {
		$stmt->bind_param('ss', $email, $password);
		$stmt->execute();
		$stmt->bind_result($loginResult['ErrorCode'], $loginResult['ErrorStatus'], $loginResult['ErrorMessage'], $loginResult['pkUserID']);
		$stmt->fetch();
		$stmt->close();
		$db->next_result();
		if ($loginResult['ErrorCode'] == 0) {
			$sql = 'CALL Get_User_Info(' . $loginResult['pkUserID'] . ');';
			$result = $db->query($sql);
			if ($result){
				$resultRow = $result->fetch_row();
				$user['UserID'] = $resultRow[0];
				$user['UserName'] = $resultRow[1];
				$user['Email'] = $resultRow[2];
				$user['UserLevel'] = $resultRow[3];
				$user['ErrorCode'] = 0;
				$user['ErrorStatus'] = 'Success';
				$user['ErrorMessage'] = 'Login Successful';
				
				return $user;
			}
			else {
				$loginResult['ErrorCode'] = 1;
				$loginResult['ErrorStatus'] = 'Fail';
				$loginResult['ErrorMessage'] = 'Error on retrieving user info';
			}
		
		}
		
		return $loginResult;
	}
	
}

function DBCheckUserName($userName) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	$result['ResultCode'] = -1;
	$result['ResultMessage'] = 'DB Connection Fail';
	if ($stmt -> prepare ('CALL Check_User_Name(?)')) {
		$stmt->bind_param('s', $userName);
		$stmt->execute();
		$stmt->bind_result($result['ResultCode'], $result['ResultMessage']);
		$stmt->fetch();
		$stmt->close();
	}

	return $result;
}

function DBCheckBoardAccessibility($boardName, $userLevel) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	if ($stmt -> prepare ('CALL Check_Board_Accessibility(?,?)')) {
		$stmt->bind_param('is', $userLevel, $boardName);
		$stmt->execute();
		$stmt->bind_result($result['ResultCode']);
		$stmt->fetch();
		$stmt->close();
	}


	if ($result)
		if ($result['ResultCode'] == 0)
			return true;
		else 
			return false;
	else 	return false;
}

function DBCheckFacebookUser($facebookUserId) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	$result['ResultCode'] = -1;
	$result['ResultMessage'] = 'DB Connection Fail';
	if ($stmt -> prepare ('CALL Check_Facebook_User(?)')) {
		$stmt->bind_param('i', $facebookUserId);
		$stmt->execute();
		$stmt->bind_result($result['ResultCode'], $result['ResultMessage']);
		$stmt->fetch();
		$stmt->close();
	}

	return $result;
}

function DBCheckEmailAddress($emailAddress) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	$result['ResultCode'] = -1;
	$result['ResultMessage'] = 'DB Connection Fail';
	if ($stmt -> prepare ('CALL Check_Email_Address(?)')) {
		$stmt->bind_param('s', $emailAddress);
		$stmt->execute();
		$stmt->bind_result($result['ResultCode'], $result['ResultMessage']);
		$stmt->fetch();
		$stmt->close();
	}

	return $result;
}
#region DB GET

function DBGetFileExtension() {
	global $db;
	$stmt = $db->stmt_init();
	$counter = 0;
	if($stmt->prepare('CALL Get_File_Extension()')) {
		$stmt->execute();
		$stmt->bind_result($extension['file_extension_id'], $extension["file_extension"]);
		while ($stmt->fetch()) {
			$fileextensions[$counter++] = unserialize(serialize($extension));
		}
		$stmt->close();
		return $fileextensions;
	}
}

function DBGetPostList($bName, $PPP, $PId, $pNo, &$TotalPostNum, &$CurrentPage, $Tags, $searchParam, $searchOptions) {
	global $db;
	$stmt = $db->stmt_init();
	$counter = 0;
	$strTag = '';
	if ($Tags != null && $Tags != '') {
		$index = 0;
		$strTag = "'" . $Tags[$index++] . "'";
		while ($Tags[$index]){
			$strTag .= ", '" . $Tags[$index++] . "'";
			if ($index > 20){
				break;
			}
		}
	}
	//$sql = 'CALL Get_Post_List('.$PPP.', '.$pNo.', '.$pId.', '.$bName.', '.$strTag.', '.$searchParam.')';
	echo $sql;
	if ($stmt->prepare('CALL Get_Post_List(?, ?, ?, ?, ?, ?)')) {
		$stmt->bind_param('iiisss', $PPP, $pNo, $PId, $bName, $strTag, $searchParam);
		
		$stmt->execute();
		$stmt->bind_result($post['post_no'], $post['user_id'], $post['user_name'], $post['user_email'], $post['title'], $post['content_original'], $post['comments'], $post['pinned'], $post['date']);
		while ($stmt->fetch()) {
			$postList[$counter++] = unserialize(serialize($post));
			if ($PId == null || $PId == -1) {
				$PId = $postList[$counter-1]['post_no'];
			}
		}
		$stmt->close();
		$db->next_result();
		if ($strTag == '') {
			$countQuery = "SELECT COUNT(DISTINCT pkPostID) FROM post p ";
			if ($searchParam != ""){
				$innerJoinQuery = '';
				if ($searchOptions[0] == "1") {
					$innerJoinQuery .= "INNER JOIN user u ON u.pkUserID = p.fkUserID ";
				}
				if ($searchOptions[3] == "1") {
					$innerJoinQuery .= "INNER JOIN usercomment uc ON uc.fkPostID = p.pkPostID ";
				}
				$countQuery .= $innerJoinQuery;
			}
			$countQuery .= "WHERE p.fkBoardID = (SELECT board.pkBoardID FROM board WHERE board.BoardName = '" . $bName . "')";
			if ($searchParam != "") {
				$countQuery .= " AND (" . $searchParam . ")";
			}

		}
		else
		{
			$countQuery = "SELECT COUNT(DISTINCT pkPostID) FROM post p INNER JOIN posttag ON p.pkPostID = posttag.fkPostID INNER JOIN tag ON posttag.fkTagID = tag.pkTagID ";
			if ($searchParam != ""){
				$innerJoinQuery = '';
				if ($searchOptions[0] == "1") {
					$innerJoinQuery = $innerJoinQuery . "INNER JOIN user u ON u.pkUserID = p.fkUserID ";
				}
				if ($searchOptions[3] == "1") {
					$innerJoinQuery = $innerJoinQuery . "INNER JOIN usercomment uc ON uc.fkPostID = p.pkPostID";
				}
				$countQuery .= $innerJoinQuery;
			}
			
			$countQuery .= " WHERE p.fkBoardID = (SELECT board.pkBoardID FROM board WHERE board.BoardName = '" . $bName . "') AND tag.Tag IN (" . $strTag . ")";
			if ($searchParam != "") {
				$countQuery .= " AND (" . $searchParam . ")";
			}
		}
		$result = $db->query($countQuery);
		if ($result) {
			$row = $result->fetch_row();
			$TotalPostNum = $row[0];
		}
		if ($pNo == -1) {
			$db->next_result();
			$stmt = $db->stmt_init();
			$sql = 'CALL Get_Current_Page(' . $PId . ', ' . $PPP . ', \'' . $bName . '\', \'' . $strTag . '\');';
			//echo $sql;
			if ($stmt->prepare($sql)){
				$stmt->execute();
				$stmt->bind_result($CurrentPage);
				$stmt->fetch();
				$stmt->close();
				//echo "current Page: " . $CurrentPage . "<br/>";
			}
			
			// if ($result2) {
			// 	$row2 = $result2->fetch_row();
			// 	$CurrentPage = $row2[0];
			// 	echo "current Page: " . $CurrentPage . "<br/>";
			// }
		}
		else {
			$CurrentPage = $pNo;
		}
		if ($counter == 0) 	return null;
		else				return $postList;
	}
}

function DBGetUserComments($parentPost) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	$index = 0;
	if($stmt->prepare('CALL Get_Post_Comment(?)')) {
		$stmt->bind_param('i', $parentPost);
		$stmt->execute();
		$stmt->bind_result($comment['deleted'], $comment['parentComment'], $comment['comment_no'], $comment['user_id'], $comment['user_name'], $comment['user_email'], $comment['content_original'], $comment['date']);
		while($stmt->fetch()){
		 $comment_list[$index++] = unserialize(serialize($comment));
		 }
		$stmt->close();
		if ($index == 0)	return null;
		else return $comment_list;
	}
}

function DBGetPostContent($pId) {
	global $db;
	$stmt = $db->stmt_init();
	
	if($stmt->prepare('CALL Get_Post_Content(?)')) {
		$stmt->bind_param('i', $pId);
		$stmt->execute();
		$stmt->bind_result($post['post_id'], $post['user_id'], $post['board_id'],  $post['user_name'], $post['user_email'], $post['date'], $post['title'], $post['content'], $post['comments'], $post['pinned']);
		$stmt->fetch();
		$stmt->close();
		return $post;
	}
}
#endregion

#region DB UPDATE

function DBEnableUser($email) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	if ($stmt->prepare('CALL Enable_User(?)')) {
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$stmt->close();
	}
}

function DBSaveUserName($userId, $userName) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	if ($stmt->prepare('CALL Save_User_Name(?,?)')) {
		$stmt->bind_param('is', $userId, $userName);
		$stmt->execute();
		$stmt->close();
	}
}

function DBSaveUserPassword($userId, $oldPassword, $newPassword) {
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();
	if ($stmt->prepare('CALL Save_User_Password(?,?,?)')) {
		$stmt->bind_param('iss', $userId, $oldPassword, $newPassword);
		$stmt->execute();
		$stmt->bind_result($result['ResultCode'], $result['ResultMessage']);
		$stmt->fetch();
		$stmt->close();

	}
	else {
		$result['ResultCode'] = 4;
		$result['ResultMessage'] = 'DB Connection Failed';
	}
	
	return $result;
}

// comment on post = $parentComment = -1
// comment on comment = $parentComment, $parentPost both to be populated
//DBSaveBoard('news');


function DBSaveBoard($boardName) {
	$date = getTime();
	global $db;
	$stmt = $db->stmt_init();
	if ($stmt->prepare('CALL Save_Board(?, ?, ?)')) {
		$stmt->bind_param('sss', $boardName, $date, $date);
		$stmt->execute();
		$stmt->close();
	}
}


function DBSaveComment($cid, $parentPost, $parentComment, $content, $user_id, $deleted) {
	$date = getTime();
	global $db;
	$stmt = $db->stmt_init();
	
	if ($stmt->prepare('CALL Check_Comment_Owner(?, ?)')) {
		$stmt->bind_param('ii', $cid, $user_id);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		if ($result == 0 || $cid == -1) {
			$db->next_result();
			$stmt = $db->stmt_init();	
			if($stmt->prepare('CALL Save_User_Comment(?, ?, ?, ?, ?, ?,?,?)')) {
				$stmt->bind_param('iiissiii', $cid, $parentPost, $parentComment, $user_id, $content, $deleted, $date, $date);
				$stmt->execute(); 
				$stmt->bind_result($comment['parentComment'], 
									$comment['comment_no'], 
									$comment['user_id'], 
									$comment['user_name'],
									$comment['content'], 
									$comment['date'],
									$comment['deleted']);
				$stmt->fetch();
				$stmt->close();

				$commentId = $comment['comment_no'];
				if ($commentId > 0) {
					$encoded_comment_id = str_replace("/", "SLASH", fnEncrypt("c".$commentId));
					$user_dir = "../tmp/".$user_id."/";
					$target_dir = "../upload/".$encoded_comment_id."/";
					$files = directory_to_array($user_dir);

					// if there are more than 0 files in the ../tmp/[UserID] directory
					if (sizeof($files) > 0) {
						// if ../upload/[UserID] direcoty does not exists, create the directory
						if(!(file_exists($target_dir) && is_dir($target_dir))) {
							@mkdir($target_dir, 0777, true);
						} 
						else {
							//delete all files
						}

						$index = 0;
						while ($file = $files[$index++]) {
							$filesize = filesize($file);
							$filealias = end(explode("/", $file));
							$fileextension = end(explode(".", $filealias));
							$filename = substr($filealias, 14);
							$fileDirectory = $target_dir . $filename;
							$fileAddress = publicUrl . "/upload/".$encoded_comment_id."/".$filename;

							//DB Save
							$result = DBSaveUploadFile($fileextension, -1, $commentId, $filesize, $fileDirectory, $fileAddress);

							//Check Image is in the DOM
							$doc = new DOMDocument();
							@$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

							$tags = $doc->getElementsByTagName('img');

							foreach ($tags as $tag) {
								$source = $tag->getAttribute('src');
								if ($source == ($user_dir.$filealias)) {
									$tag->removeAttribute('src');
							       	$tag->setAttribute('src', $fileDirectory);
						        }
							}

							$newContent = @$doc->saveHTML('body');
							$newContent = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $newContent);

							$db->next_result();
							$stmt = $db->stmt_init();
							//htmlspecialchars($title, ENT_HTML401, 'UTF-8', false)
							if($stmt->prepare('CALL Save_User_Comment(?, ?, ?, ?, ?, ?,?,?)')) {
								$stmt->bind_param('iiissiii', $commentId, $parentPost, $parentComment, $user_id, $content, $deleted, $date, $date);
								$stmt->execute(); 
								$stmt->bind_result($comment['parentComment'], 
													$comment['comment_no'], 
													$comment['user_id'], 
													$comment['user_name'],
													$comment['content'], 
													$comment['date'],
													$comment['deleted']);
								$stmt->fetch();
								$stmt->close();
							}


							//Move file from /tmp/ to /upload/
							rename ($file, $user_dir.$filename);
							copy ($user_dir.$filename, $target_dir.$filename);
							unlink($user_dir.$filename);
						}

					}
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
				$returnArray['authorEmail'] = $comment['author_email'];
				$returnArray['content'] = $comment['content'];
				$returnArray['date'] = $comment['date'];
				$returnArray['date'] = $dateFormatted[1];
				$returnArray['children'] = array();			
			}
		}
	}

	return json_encode_unescaped($returnArray);
}

function DBSavePostTag($post_id, $tag_id, $action) {
	global $db;
	$db->next_result();
	$sql = "CALL " . $action . "_Post_Tag(" . $post_id . ", '" . $tag_id . "');";
	$db->query($sql);

}

function DBSaveBoardTag($board_id, $tag_id) {
	global $db;
	$db->next_result();
	$sql = "CALL Save_Board_Tag(" . $board_id . ", '" . $tag_id . "');";
	$db->query($sql);

}
function DBImportComment($cid, $parentPost, $parentComment, $content, $user_id, $deleted, $date, $comment_srl) {
	global $db;
	$stmt = $db->stmt_init();
	$db->next_result();
	if($stmt->prepare('CALL Import_User_Comment(?, ?, ?, ?, ?, ?,?,?, ?)')) {
		$stmt->bind_param('iiissiiii', $cid, $parentPost, $parentComment, $user_id, $content, $deleted, $date, $date, $comment_srl);
		$stmt->execute(); 
		$stmt->bind_result($comment['resultCode'], $comment['resultMessage']);
		$stmt->fetch();
		$stmt->close();

		
	}
	return json_encode_unescaped($comment);
}
function DBImportPost($post_id, $pinned, $boardName, $title, $content, $user_id, $document_srl, $date) {
	
	global $db;
	$db->next_result();
	$stmt = $db->stmt_init();

	
	if($stmt->prepare('CALL Import_Post(?,?,?,?,?,?,?,?,?)')) {
		$stmt->bind_param('ississiii', $post_id, $boardName, $user_id, $pinned, $title , $content, $date, $date, $document_srl);
		$stmt->execute();
		
		$post_id_out = NULL;
		$stmt->bind_result($post_id_out);
		$stmt->fetch();
		$stmt->close();

		return $post_id_out;
	}
	
	
	return -1;
	
}

function DBSavePost($post_id, $pinned, $boardName, $title, $content, $user_id, $added_tags, $deleted_tags) {
	$date = getTime();
	global $db;
	$stmt = $db->stmt_init();

	if ($stmt->prepare('CALL Check_Post_Owner(?,?)')) {
		$stmt->bind_param('ii', $post_id, $user_id);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		if ($result == 0 || $post_id == -1) {
			$db->next_result();
			$stmt = $db->stmt_init();
			//htmlspecialchars($title, ENT_HTML401, 'UTF-8', false)
			if($stmt->prepare('CALL Save_Post(?,?,?,?,?,?,?,?)')) {
				$stmt->bind_param('isiissii', $post_id, $boardName, $user_id, $pinned, $title , $content, $date, $date);
				$stmt->execute();
				
				$post_id_out = NULL;
				$stmt->bind_result($post_id_out);
				$stmt->fetch();
				$stmt->close();

				if ($added_tags != null) {
					for ($t = 0; $t < sizeof($added_tags); $t ++) {
						DBSavePostTag($post_id_out, $added_tags[$t], "Save");
					}
				}

				if ($deleted_tags != null) {
					for ($t = 0; $t < sizeof($deleted_tags); $t ++) {
						DBSavePostTag($post_id_out, $deleted_tags[$t], "Delete");
					}	
				}
				if ($post_id_out > 0) {
					$encoded_post_id = str_replace("/", "SLASH", fnEncrypt("p".$post_id_out));
					$user_dir = "../tmp/".$user_id."/";
					$target_dir = "../upload/".$encoded_post_id."/";
					$files = directory_to_array($user_dir);

					// if there are more than 0 files in the ../tmp/[UserID] directory
					if (sizeof($files) > 0) {
						// if ../upload/[UserID] direcoty does not exists, create the directory
						if(!(file_exists($target_dir) && is_dir($target_dir))) {
							@mkdir($target_dir, 0777, true);
						} 
						else {
							//delete all files
						}

						$index = 0;
						while ($file = $files[$index++]) {
							$filesize = filesize($file);
							$filealias = end(explode("/", $file));
							$fileextension = end(explode(".", $filealias));
							$filename = substr($filealias, 14);
							$fileDirectory = $target_dir . $filename;
							$fileAddress = publicUrl . "/upload/".$encoded_post_id."/".$filename;

							//DB Save
							//$result = DBSaveUploadFile($fileextension, $post_id_out, -1, $filesize, $fileDirectory, $fileAddress);

							//Check Image is in the DOM
							$doc = new DOMDocument();
							@$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

							$tags = $doc->getElementsByTagName('img');

							foreach ($tags as $tag) {
								$source = $tag->getAttribute('src');
								if ($source == ($user_dir.$filealias)) {
									$tag->removeAttribute('src');
							       	$tag->setAttribute('src', $fileDirectory);
						       }
							}

							$newContent = @$doc->saveHTML('body');
							$newContent = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $newContent);

							$db->next_result();
							$stmt = $db->stmt_init();
							//htmlspecialchars($title, ENT_HTML401, 'UTF-8', false)
							if($stmt->prepare('CALL Save_Post(?,?,?,?,?,?,?,?)')) {
								$stmt->bind_param('isiissii', $post_id, $boardName, $user_id, $pinned, $title , $newContent, $date, $date);
								$stmt->execute();
								
								$post_id_out = NULL;
								$stmt->bind_result($post_id_out);
								$stmt->fetch();
								$stmt->close();
							}



							//Move file from /tmp/ to /upload/
							rename ($file, $user_dir.$filename);
							copy ($user_dir.$filename, $target_dir.$filename);
							unlink($user_dir.$filename);
						}

					}
				}
				


				return $post_id_out;
			}
		}
	}
	
	
	return -1;
	
}

function DBSaveUploadFile ($file_extension, $post_id, $comment_id, $file_size, $file_directory, $file_address) {
	global $db;
	$stmt = $db->stmt_init();
	$db->next_result();
	$sql = 'CALL Save_Upload_File(?, ?, ?, ?, ?, ?)';
	if($stmt->prepare($sql)) {
		$stmt->bind_param('siiiss', $file_extension, $post_id, $comment_id, $file_size, $file_directory, $file_address);
		$stmt->execute();
		
		// $stmt->bind_result($result['ResultCode'], $result['ResultStatus'], $result['ResultMessage'], $result['Discontinued']);
		// $stmt->fetch();
		$stmt->close();
		$result['ResultCode'] = "0";
		$result['ResultStatus'] = "Success";
		$result['ResultMessage'] = "File Uploaded";
		return $result;
	}
	
	$result['ResultCode'] = "1";
	$result['ResultStatus'] = "Fail";
	$result['ResultMessage'] = "DB connection fail";

	return $result;

}
function DBSaveArchievedUser($user_level, $username, $password, $email_address, $student_id, $facebook_user_id, $discontinued, $date) {
	if ($user_level == null) $user_level = -1;
	if ($student_id == null) $student_id = '';
	if ($facebook_user_id == null) $facebook_user_id = '';
	if ($discontinued == null) $discontinued = false;
	global $db;
	$stmt = $db->stmt_init();
	$sql = 'CALL Save_Archieved_User(?,?,?,?,?,?,?,?, ?)';
	if($stmt->prepare($sql)) {
		$stmt->bind_param('isssssbss', $user_level, $username, $password, $email_address, $student_id, $facebook_user_id, $discontinued, $date, $date);
		$stmt->execute();
		
		$stmt->bind_result($result['ResultCode'], $result['ResultStatus'], $result['ResultMessage'], $result['Discontinued']);
		$stmt->fetch();
		$stmt->close();
		return $result;
	}
	
	$result['ResultCode'] = "1";
	$result['ResultStatus'] = "Fail";
	$result['ResultMessage'] = "DB connection fail";

	return $result;

}
function DBSaveUser($user_level, $username, $password, $email_address, $student_id, $facebook_user_id, $discontinued) {
	$date = getTime();
	if ($user_level == null) $user_level = -1;
	if ($student_id == null) $student_id = '';
	if ($facebook_user_id == null) $facebook_user_id = '';
	if ($discontinued == null) $discontinued = false;
	global $db;
	$stmt = $db->stmt_init();
	$sql = 'CALL Save_User(?,?,?,?,?,?,?,?, ?)';
	if($stmt->prepare($sql)) {
		$stmt->bind_param('isssssbss', $user_level, $username, $password, $email_address, $student_id, $facebook_user_id, $discontinued, $date, $date);
		$stmt->execute();
		
		$stmt->bind_result($result['ResultCode'], $result['ResultStatus'], $result['ResultMessage'], $result['Discontinued']);
		$stmt->fetch();
		$stmt->close();
		return $result;
	}
	
	$result['ResultCode'] = "1";
	$result['ResultStatus'] = "Fail";
	$result['ResultMessage'] = "DB connection fail";

	return $result;

}
#endregion

#region DB DELETE

function DBDeletePost($pId, $user_id) {
	global $db;
	$stmt = $db->stmt_init();
	if ($stmt->prepare('CALL Check_Post_Owner(?,?)')) {
		$stmt->bind_param('ii', $pId, $user_id);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		if ($result == 0) {
			$db->next_result();
			$stmt = $db->stmt_init();
			
			if($stmt->prepare('CALL Delete_Post(?)')) {
				$stmt->bind_param('i', $pId);
				$stmt->execute();
				$stmt->close();
			}
		}
	}
}

function DBDeleteComment($cId, $user_id) {
	global $db;
	$stmt = $db->stmt_init();
	if ($stmt->prepare('CALL Check_Comment_Owner(?,?)')) {
		$stmt->bind_param('ii', $cId, $user_id);
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		if ($result == 0) {
			$db->next_result();
			$stmt = $db->stmt_init();
	
			if($stmt->prepare('CALL Delete_Comment(?)')) {
				$stmt->bind_param('i', $cId);
				$stmt->execute();
				$stmt->close();
			}
		}
	}
}
#endregion


function db() {
	$db = new mysqli('localhost', 'uwksac5_admin', 'qpfrldpqhsowntpdy', 'uwksac5_ksa_1');
	if(mysqli_connect_errno()) die('Connect failed: ' .mysqli_connect_error());
	$stmt = $db->stmt_init();
	if($stmt->prepare("SET names utf8")) {
		$stmt->execute();
		$stmt->close();
	}
	return $db;
}

function getTime() {

	//return strtotime("now America/Toronto");
	$theTime = time(); // specific date/time we're checking, in epoch seconds. 

	$tz = new DateTimeZone('America/Toronto'); 
	$transition = $tz->getTransitions($theTime, $theTime); 

	// only one array should be returned into $transition. Now get the data: 
	$offset = $transition[0]['offset']; 

	$current_time = time();
	$current_tz = new DateTimeZone('America/Los_Angeles');
	$trans = $current_tz->getTransitions($current_time, $current_time);

	//$offset_origin = $trans[0]['offset'];

	
	return $theTime - $offset - 3600;
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


function json_encode_unescaped($arr)
{
	//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
	array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
	return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');

}
function format_date($date) {
	$format[0] = date('M j', $date);
	$format[1] = date('l F j, Y \a\t h:i:s a', $date);
	return $format;
}

function directory_to_array($directory) {
    $array_items = array();
    if(file_exists($directory) && is_dir($directory)){
	    if ($handle = opendir($directory)) {
	        while (false !== ($file = readdir($handle))) {
	            if ($file != "." && $file != "..") {
	                if (is_dir($directory. "/" . $file)) {
	                    $file = $directory . "/" . $file;
	                    $array_items[] = preg_replace("/\/\//si", "/", $file);
	                } else {
	                    $file = $directory . "/" . $file;
	                    $array_items[] = preg_replace("/\/\//si", "/", $file);
	                }
	            }
	        }
	        closedir($handle);
	    }
    	return $array_items;
	}
	else {
		return array();
	}
}

function current_page_url() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 		$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
?>