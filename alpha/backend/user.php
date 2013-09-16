<?php
    
include_once 'header.php';
include_once 'library.php';
include_once 'crypto.php';

require 'fb_src/facebook.php';

define ('facebookAppId', '221819091304287');
define('facebookSecretKey' , '3ba4ca9678e26398671e5f5ce58b75b6');
$facebook = new Facebook(array(
        'appId'  => facebookAppId,
        'secret' => facebookSecretKey
    ));
global $facebook;

function Login($email, $password) {
    $loginInfo = DBUserLogin($email, $password);
    
    if ($loginInfo['ErrorCode'] == 0) {
        $_SESSION['UserID'] = $loginInfo['UserID'];
        $_SESSION['Email'] = $loginInfo['Email'];
        $_SESSION['UserName'] = $loginInfo['UserName'];
        $_SESSION['UserLevel'] = $loginInfo['UserLevel'];
        $_SESSION['IsFacebookUser'] = 0;
        $result['ResultCode'] = 0;
        $result['ResultMessage'] = 'Login Successful';
    }
    else {
        $result['ResultCode'] = $loginInfo['ErrorCode'];
        $result['ResultMessage'] = $loginInfo['ErrorMessage'];
    }
    header( 'Location: ../#!/') ;

}

function FacebookUrl($type, $url) {
    global $facebook;
    return $facebook->getLoginUrl(array(
        'redirect_uri'  => publicUrl . '/backend/get.php?t=8&c=' . $type . '&url=' . $url
        ));
}
//echo FacebookLogin();
function FacebookLogin() {
    global $facebook;
    try {
        // Proceed knowing you have a logged in user who's authenticated.
        $user_profile = $facebook->api('/me');
        $facebookId = $user_profile['id'];
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }

    echo "facebookId : " . $facebookId;
    $loginInfo = DBFacebookUserLogin($facebookId);
    if ($loginInfo['ErrorCode'] == 0) {
        $_SESSION['UserID'] = $loginInfo['UserID'];
        $_SESSION['Email'] = $loginInfo['Email'];
        $_SESSION['UserName'] = $loginInfo['UserName'];
        $_SESSION['UserLevel'] = $loginInfo['UserLevel'];
        $_SESSION['IsFacebookUser'] = 1;
        $result['ResultCode'] = 0;
        $result['ResultMessage'] = 'Login Successful';
    }
    else {
        $result['ResultCode'] = $loginInfo['ErrorCode'];
        $result['ResultMessage'] = $loginInfo['ErrorMessage'];
    }
    //header( 'Location: ' . $url) ;
    header('Location: ../#!/');
    
}



function Logout() {
    if (isset($_SESSION['UserID'])) {
        unset($_SESSION['UserID']);
        unset($_SESSION['Email']);
        unset($_SESSION['UserName']);
        unset($_SESSION['UserLevel']);
        unset($_SESSION['IsFacebookUser']);
    }   
}

function CheckUserName($username) {
    return json_encode_unescaped(DBCheckUserName($username));
}

function CheckEmailAddress($email) {
    return json_encode_unescaped(DBCheckEmailAddress($email));
}

function SaveUserName($userId, $userName) {
    $result = DBCheckUserName($username);
    if ($result['ResultCode'] == 0) {
        DBSaveUserName($userId, $userName);
        $_SESSION['UserName'] = $userName;
    }
}

function SaveUserPassword($userId, $new, $old) {
	$result =  DBSaveUserPassword($userId, $old, $new);
	$result['old'] = $old;
	$result['new'] = $new;
	return json_encode_unescaped($result);
}
//echo FacebookSignUp (500533200);
//echo FacebookSignUp();
function FacebookSignUp($url) {
    global $facebook;

    try {
        // Proceed knowing you have a logged in user who's authenticated.
        $user_profile = $facebook->api('/me');
        $facebookId = $user_profile['id'];
        $facebookUserName = $user_profile['first_name'] . ' ' . $user_profile['last_name'];
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }
    echo "facebookId : " . $facebookId;
    $result = DBCheckFacebookUser($facebookId);
    if ($result['ResultCode'] == 0)  {
        $result = DBSaveUser(-1, $facebookUserName, '', $facebookId, null, $facebookId,  null);
        if ($result['ResultCode'] == 0) {
            $userInfo = DBEnableUser($facebookId);
           FacebookLogin($url);
        }
    }


}
//echo SignUp('testestest', 'testestest', 'testestest');
function SignUp($username, $email, $password) {
    $result = DBCheckUserName($username);
    if ($result['ResultCode'] == 0) {
        $result = DBCheckEmailAddress($email);
        
        if ($result['ResultCode'] == 0) {
            $result = DBSaveUser(-1, $username, $password, $email, null, null, null);
            if ($result['Discontinued'] == 1) {
                SendConfirmationEmail($email);
            }
            return json_encode_unescaped($result);
        }
    }   
    return null;
}

function EnableUser($email){ 
        $userInfo = DBEnableUser($email);
        $_SESSION['UserID'] = $userInfo ['UserID'];
    $_SESSION['Email'] = $userInfo ['EmailAddress'];
    $_SESSION['UserName'] = $userInfo ['UserName'];
    $_SESSION['UserLevel'] = $userInfo ['UserLevel'];
        header( 'Location: ../#!/confirmed') ;
}

//SendConfirmationEmail('jameskim0903@hotmail.com');

function SendConfirmationEmail($email) {
        
    $to      = $email;
    $subject = 'Welcome to UWKSA Website!';
    $message = "Welcome to UWKSA.CA! Please click the link below to complete the sign-up: \r\n";
    $message = $message . '<' . publicUrl. '/backend/get.php?t=7&c=' . urlencode(fnEncrypt($email)) . ">";
    $message = $message . "\r\n\r\n We promise you that all of your personal information will be kept strictly confidential. However, please be advised that UWKSA has the right to permanently block/delete your account (without warning) should we observe any inappropriate behaviours such as spamming, trolling and poor manners. In addition, we do reserve the right to reveal your identity in public if required under extreme circumstances. \r\n \r\n";
    $message = $message . "UWKSA Web Team";
    $headers = 'From: web@uwksa.ca';
    mail($to, $subject, $message, $headers);
}
?>