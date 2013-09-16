<?php
include_once 'header.php';
include_once 'library.php';

$user_dir = "../tmp/" . $_SESSION['UserID'] ;
if ($_SESSION["ksa_upload_extensions"] == null) $_SESSION["ksa_upload_extensions"] = DBGetFileExtension();
$extensions = $_SESSION["ksa_upload_extensions"];
echo json_encode_unescaped($extensions);
$counter = 0;
$check = false;
$fileExtension = end(explode(".", $_REQUEST["alias"]));
echo $fileExtension;
echo $extensions[0]["file_extension"];
echo "\n\r";
while ($extensions[$counter]["file_extension"] != null && $check == false){
	echo $counter . ", " . $check . ", " . $extensions[$counter]["file_extension"] . ", " . $fileExtension . "\n\r";
	if ($extensions[$counter++]["file_extension"] == $fileExtension) {
		echo $extensions[$counter-1]["file_extension"];
		$check = true;
	}
}
echo "    file size    :    " . filesize($_FILES["file"]["tmp_name"]);
if (!$check) echo "fail 2";
else if (filesize($_FILES["file"]["tmp_name"]) > 50000000) echo "fail 1";
else {
	if(!(file_exists($user_dir) && is_dir($user_dir)))
		@mkdir($user_dir, 0777, true);
	move_uploaded_file($_FILES["file"]["tmp_name"], $user_dir . "/" . $_REQUEST["alias"]); //$_FILES["alias"]
	echo "success";
}


?>