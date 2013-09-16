<?php
include_once 'header.php';
include_once 'library.php';

$user_dir = "../tmp/" . $_SESSION['UserID'] ;
if ($_SESSION["ksa_upload_extensions"] == null) $_SESSION["ksa_upload_extensions"] = DBGetFileExtension();
$extensions = $_SESSION["ksa_upload_extensions"];
$counter = 0;
$check = false;
$fileExtension = end(explode(".", $_REQUEST["alias"]));
while ($extensions[$counter]["file_extension"] != null && $check == false){
	if ($extensions[$counter++]["file_extension"] == $fileExtension) {
		$check = true;
	}
}
if (!$check) echo "fail";
else if (filesize($_FILES["file"]["tmp_name"]) > 52428800) echo "fail";
else {
	if(!(file_exists($user_dir) && is_dir($user_dir)))
		@mkdir($user_dir, 0777, true);
	move_uploaded_file($_FILES["file"]["tmp_name"], $user_dir . "/" . $_REQUEST["alias"]); //$_FILES["alias"]
	echo "success";
}


?>