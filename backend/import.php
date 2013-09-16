<?php
include_once 'library.php';
include_once 'crypto.php';
ini_set('max_execution_time', 3000);

// member import
/*
global $db;
$stmt = $db->stmt_init();
$counter = 0;
if ($stmt->prepare('SELECT member_srl, nick_name, regdate FROM xe_member')) {
		
		$stmt->execute();
		$stmt->bind_result($member['email_serial'], $member['user_name'], $member['reg_date']); // store member_serial to email address(unique field);
		while ($stmt->fetch()) {
			$memberList[$counter] = unserialize(serialize($member));
			$memberList[$counter]['user_name'] = $member['user_name'] . ' (Archieved)';
			$dateString = substr($member['reg_date'], 0, 4)."-".substr($member['reg_date'], 4, 2)."-".substr($member['reg_date'], 6,2);
			$date = new Datetime($dateString);

			$memberList[$counter]['date'] = $date->getTimestamp();
			$counter ++;
		}
		echo json_encode_unescaped($memberList);

		foreach ($memberList as $member) {
			echo json_encode_unescaped(DBSaveArchievedUser(8, $member['user_name'], '', $member['email_serial'], '', '', 0, $member['date']) );
			echo "\r\n";
		}
}
*/


// import user comments
// global $db;
// $stmt = $db->stmt_init();
// $counter = 0;
// if ($stmt->prepare('SELECT xcl.comment_srl, xcl.document_srl, xcl.regdate, xc.content, xc.member_srl, xcl.head from xe_comments_list xcl INNER JOIN xe_comments xc ON xcl.comment_srl = xc.comment_srl WHERE xcl.depth = 1')) {
		
// 		$stmt->execute();
// 		$stmt->bind_result($comment['comment_srl'], $comment['document_srl'], $comment['reg_date'], $comment['content'], $comment['member_srl'], $comment['head']); // store member_serial to email address(unique field);
// 		while ($stmt->fetch()) {
// 			$commentList[$counter] = unserialize(serialize($comment));
// 			echo substr($comment['reg_date'], 0, 4)."-".substr($comment['reg_date'], 4, 2)."-".substr($comment['reg_date'], 6,2) . " " . substr($comment['reg_date'], 8,2) . ":" . substr($comment['reg_date'], 10,2) . ":" . substr($comment['reg_date'], 12,2);
			
// 			$dateString = substr($comment['reg_date'], 0, 4)."-".substr($comment['reg_date'], 4, 2)."-".substr($comment['reg_date'], 6,2) . " " . substr($comment['reg_date'], 8,2) . ":" . substr($comment['reg_date'], 10,2) . ":" . substr($comment['reg_date'], 12,2);
// 			$date = new Datetime($dateString);
// 			$commentList[$counter]['date'] = $date->getTimestamp();
// 			$counter ++;
// 		}

// 		foreach ($commentList as $comment) {
// 			//echo "CALL Import_User_Comment(-1, ".$comment['document_srl'].", " . $comment['head'] .", ".$comment['content'].", ".$comment['member_srl'].", ".false.", ".$comment['date'].", ". $comment['comment_srl'].");\r\n";
// 			DBImportComment(-1, $comment['document_srl'], $comment['head'], $comment['content'], $comment['member_srl'], false, $comment['date'], $comment['comment_srl']);
// 			// if ($result['resultCode'] != 0) {
// 			// 	echo $result['resultMessage'];
// 			// 	echo "<br/>";
// 			// }
			
// 		}
// 		echo "success";
// }

 

// global $db;
// $stmt = $db->stmt_init();
// $counter = 0;
// if ($stmt->prepare('SELECT post.pkPostID FROM post')) {
		
// 		$stmt->execute();
// 		$stmt->bind_result($post['post_id']); // store member_serial to email address(unique field);
// 		while ($stmt->fetch()) {
// 			$postList[$counter] = unserialize(serialize($post));
			
// 			$counter ++;
// 		}

// 		foreach ($postList as $post) {
// 			//echo "CALL Import_User_Comment(-1, ".$comment['document_srl'].", " . $comment['head'] .", ".$comment['content'].", ".$comment['member_srl'].", ".false.", ".$comment['date'].", ". $comment['comment_srl'].");\r\n";
// 			$stmt = $db->stmt_init();
// 			$db->next_result();
// 			if ($stmt->prepare('CALL Update_Comment_Count(?)')) {
// 				$stmt->bind_param('i', $post['post_id']);
// 				$stmt->execute();
// 				$stmt->close();
// 				$db->next_result();
// 				$stmt = $db->stmt_init();
// 			}
// 			// if ($result['resultCode'] != 0) {
// 			// 	echo $result['resultMessage'];
// 			// 	echo "<br/>";
// 			// }
			
// 		}
// 		echo "success";
// }



// global $db;
// $stmt = $db->stmt_init();
// $counter = 0;
// if ($stmt->prepare("SELECT user.pkUserID, user.EmailAddress, user.Password FROM user WHERE user.pkUserID > 20 ")) {
		
// 		$stmt->execute();
// 		$stmt->bind_result($user['user_id'], $user['email_address'], $user['password']); // store member_serial to email address(unique field);
// 		while ($stmt->fetch()) {
// 			$userList[$counter] = unserialize(serialize($user));
			
// 			$counter ++;
// 		}

// 		foreach ($userList as $user) {
// 			if ($user['password'] != '') {
// 				DBSaveUserPassword($user['user_id'], $user['password'], fnEncrypt($user['password']));
// 			}
// 		}
// 		echo "success";
// }

?>
