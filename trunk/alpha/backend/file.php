<?php
    
include_once 'header.php';
include_once 'library.php';
include_once 'crypto.php';
// $postId = 99;
// $encoded_postId = fnEncrypt($postId);
// $encoded_postId = substr($encoded_postId, 0, strlen($encoded_postId)-1);
// if (isset($_SESSION['UserID']) && $_SESSION['UserID'] > 0) {
	
// 	$user_dir = "../tmp/" . $_SESSION['UserID'] . "/" ;
// 	$target_dir = "../upload/" . $encoded_postId ."/";
// 	$files = directoryToArray($user_dir);
// 	echo json_encode_unescaped($files);
// 	// if there are more than 0 files in the ../tmp/[UserID] directory
// 	if (sizeof($files) > 0) {
// 		// if ../upload/[UserID] direcoty does not exists, create the directory
// 		if(!(file_exists($target_dir) && is_dir($target_dir))) {
// 			@mkdir($target_dir, 0777, true);
// 		}

// 		$index = 0;
// 		while ($file = $files[$index++]) {
// 			$filesize = filesize($file);
// 			$filealias = end(explode("/", $file));
// 			$filename = substr($filealias, 14);
// 			rename ($file, $user_dir.$filename);
// 			copy ($user_dir.$filename, $target_dir.$filename);
// 			unlink($user_dir.$filename);
// 		}

// 	}

// }
$post_id_out = 217;
$user_id = 52;
if ($post_id_out > 0) {
	echo "hi";
	$encoded_post_id = fnEncrypt("p".$post_id_out);
	$user_dir = "../tmp/".$user_id."/";
	$target_dir = "../upload/".$encoded_post_id."/";
	$files = directory_to_array($user_dir);

	echo "hi";
	// if there are more than 0 files in the ../tmp/[UserID] directory
	if (sizeof($files) > 0) {

	echo "hi";
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
			echo "(" . $fileextension . ", " . $post_id_out . ", " . -1 . ", " . $filesize . ", " . $fileDirectory . ", " . $fileAddress . ")";
			//DB Save
			//$result = DBSaveUploadFile($fileextension, $post_id_out, -1, $filesize, $fileDirectory, $fileAddress);

			//Check Image is in the DOM
			// $doc = new DOMDocument();
			// @$doc->loadHTML(mb_convert_encoding($content['content'], 'HTML-ENTITIES', 'UTF-8'));

			// $tags = $doc->getElementsByTagName('img');

			// foreach ($tags as $tag) {
			// 	$source = $tag->getAttribute('src');
			// 	$tag->removeAttribute('src');
		 //       	$tag->setAttribute('src', "");
			// }

			// $newContent = @$doc->saveHTML();



			//Move file from /tmp/ to /upload/
			// rename ($file, $user_dir.$filename);
			// copy ($user_dir.$filename, $target_dir.$filename);
			// unlink($user_dir.$filename);
		}

	}
}

?>