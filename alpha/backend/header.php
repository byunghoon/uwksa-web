<?php
ob_start();
if (!isset($_SESSION)) {
  session_start();// 처음 실행되면 세션 생성, 그 다음부터는 생성된 세션 사용
}	
if(!$_SESSION['UserLevel']) {
	$_SESSION['UserLevel'] = '10';	// userLevel이 없으면 게스트 레벨 지정
	$_SESSION['UserID'] = -1;
}
?>