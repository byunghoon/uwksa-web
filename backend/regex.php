<?php
	include_once "header.php";
	include_once "library.php";

	// $content = DBGetPostContent(89);
	// echo '<?xml encoding="UTF-8">';
	// $doc = new DOMDocument();
	// @$doc->loadHTML(mb_convert_encoding($content['content'], 'HTML-ENTITIES', 'UTF-8'));

	// $tags = $doc->getElementsByTagName('img');

	// foreach ($tags as $tag) {
	// 	$source = $tag->getAttribute('src');
	// 	$tag->removeAttribute('src');
 //       	$tag->setAttribute('src', "");
	// }

	// $newContent = @$doc->saveHTML();
	// echo "new content:";
	// echo $newContent;
	// echo " end";

	echo current_page_url();


	
?>
