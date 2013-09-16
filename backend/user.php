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
    Logout();
    $loginInfo = DBUserLogin($email, fnEncrypt($password));
    
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
    return json_encode_unescaped($result);
    //header( 'Location: ../#!/') ;

}

function FacebookUrl($type, $url) {
    global $facebook;
    return $facebook->getLoginUrl(array(
        'redirect_uri'  => publicUrl . '/backend/get.php?t=8&c=' . $type . '&url=' . urlencode($url)
        ));
}
//echo FacebookLogin();
function FacebookLogin($url) {
    Logout();
    global $facebook;
    try {
        // Proceed knowing you have a logged in user who's authenticated.
        $user_profile = $facebook->api('/me');
        $facebookId = $user_profile['id'];
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }

    $loginInfo = DBFacebookUserLogin($facebookId);
    if ($loginInfo['ErrorCode'] == 0) {
        $_SESSION['UserID'] = $loginInfo['UserID'];
        $_SESSION['Email'] = $loginInfo['Email'];
        $_SESSION['UserName'] = $loginInfo['UserName'];
        $_SESSION['UserLevel'] = $loginInfo['UserLevel'];
        $_SESSION['IsFacebookUser'] = 1;
        $result['ResultCode'] = 0;
        $result['ResultMessage'] = 'Login Successful';
        header( 'Location: ' . $url) ;
    }
    else {
        $result['ResultCode'] = $loginInfo['ErrorCode'];
        $result['ResultMessage'] = $loginInfo['ErrorMessage'];
        FacebookSignUp($url);
    }
    //header('Location: ../#!/');
    
}



function Logout() {
    if (isset($_SESSION['UserID'])) {
        unset($_SESSION['UserID']);
        unset($_SESSION['Email']);
        unset($_SESSION['UserName']);
        unset($_SESSION['UserLevel']);
        unset($_SESSION['IsFacebookUser']);
        unset($_SESSION['ForgottenEmail']);
        unset($_SESSION['ForgotPasswordToken']);
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

function UpdateForgottenPassword($password) {
    if (isset($_SESSION['ForgotPasswordToken']) && $_SESSION['ForgotPasswordToken']) {
        DBUpdateForgottenPassword($_SESSION['ForgottenEmail'], fnEncrypt($password));
        header( 'Location: ../#!/changed') ;
    }
}

function UpdatePassword($token) {
    $tokens = split("~", fnDecrypt($token));
    $email = $tokens[0];
    $timestamp = $tokens[1];
    $currentTime = getTime();
    echo $currentTime." ".$timestamp." ".($currentTime - $timestamp);
    if ($currentTime - $timestamp > 86400) {
        $result['ResultCode'] = 1;
        $result['ResultMessage'] = "This link is expired.";
        $_SESSION['ForgotPasswordToken'] = 0;
        header( 'Location: ../forgot.php?q=expired') ;
    }
    else {
        $_SESSION['ForgotPasswordToken'] = 1;
        $_SESSION['ForgottenEmail'] = $email;
        header( 'Location: ../forgot.php') ;
    }
}

function SaveUserPassword($userId, $new, $old) {
	$result =  DBSaveUserPassword($userId, fnEncrypt($old), fnEncrypt($new));
	$result['old'] = fnEncrypt($old);
	$result['new'] = fnEncrypt($new);
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
    $result = DBCheckFacebookUser($facebookId);
    if ($result['ResultCode'] == 0)  {
        $result = DBSaveUser(-1, $facebookUserName, '', $facebookId, null, $facebookId,  null);
        if ($result['ResultCode'] == 0) {
            $userInfo = DBEnableUser($facebookId);
           FacebookLogin($url);
        }
    }

    header( 'Location: ../#!/') ;
}
//echo SignUp('testestest', 'testestest', 'testestest');
function SignUp($username, $email, $password) {
    $result = DBCheckUserName($username);
    if ($result['ResultCode'] == 0) {
        $result = DBCheckEmailAddress($email);
        
        if ($result['ResultCode'] == 0) {
            $result = DBSaveUser(-1, $username, fnEncrypt($password), $email, null, null, null);
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

function SendForgotPasswordEmail($email) {
        
    $to      = $email;
    $subject = 'Forgot Password';
    $message = "To reset your password, please click the link below: \r\n";
    $message = $message . '<' . publicUrl. '/backend/get.php?t=10&c=' . urlencode(fnEncrypt($email."~".getTime())) . "> \r\n\r\n";
    $message = $message . "UWKSA Web Team";
    $headers = 'From: web@uwksa.ca';
    mail($to, $subject, $message, $headers);
}
?>