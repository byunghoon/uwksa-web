<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>UWKSA :: Starbucks</title>
	<script src="../script.js"></script>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<link rel="shortcut icon" href="../../../favicon.ico" />
</head>
<body>
	<?php include '../header.html'; ?>
	<div id="content">
		<div id="brand">
			<div id="logo"><img src="logo.png" alt="" /></div>
			<div id="titleBox">
				<div id="title">STARBUCKS COFFEE</div>
				<div id="subtitle">Coffeehouse</div>
			</div>
		</div>

		<div id="tabControl" class="NoSelection">
			<ul id="tabList">
				<li id="tabItem-info" onclick="OpenTab('info')">Info</li>
				<li id="tabItem-menu" onclick="OpenTab('menu')">Menu</li>
				<li id="tabItem-photos" onclick="OpenTab('photos')">Photos</li>
			</ul>
		</div>
		<div id="tabContent"></div>

		<div id="footer">
			&copy; 2013 UWKSA<br />
			<span>CMYK STUDIO</span>
		</div>
	</div>


	<div id="tp-info" class="template">
		<h1>About</h1>
		<p>
			Starbucks Corporation is an American global coffee company and coffeehouse chain based in Seattle, Washington. Starbucks is the largest coffeehouse company in the world, with 20,891 stores in 62 countries, including 13,279 in the United States, 1,324 in Canada, 989 in Japan, 851 in the People's Republic of China, 806 in the United Kingdom, 556 in South Korea, 377 in Mexico, 291 in Taiwan, 206 in the Philippines, 179 in Turkey, 171 in Thailand, and 167 in Germany.
		</p>
		<p>
			스타벅스(영어: Starbucks)는 세계에서 가장 큰 다국적 커피 전문점이다. 60개국에서 총 19,972개의 매점을 운영하고 있다. 나라별로 미국에서 12,937개, 캐나다에 1,273개, 일본에는 971개, 영국에는 790개, 중화인민공화국에 657개, 대한민국에 544개, 멕시코에 356개, 타이완에 276개를 운영하고 있다.
		</p>
		<div class="columnContainer">
			<div class="column-half">
				<div class="fixToAvoidMarginCollpase">
					<h1>Contacts</h1>
					<p>247 King Street North, Waterloo ON. N2J 2Y8</p>
					<p>(519) 886-0101</p>
				</div>
				<div>
					<h1>Hours of Operation</h1>
					<div class="columnContainer">
						<div class="column-day">Sunday</div>
						<div class="column-hours">7:00 am - 1:00 am</div>
					</div>
					<div class="columnContainer">
						<div class="column-day">Monday</div>
						<div class="column-hours">5:30 am - 1:00 am</div>
					</div>
					<div class="columnContainer">
						<div class="column-day">Tuesday</div>
						<div class="column-hours">5:30 am - 1:00 am</div>
					</div>
					<div class="columnContainer">
						<div class="column-day">Wednesday</div>
						<div class="column-hours">5:30 am - 1:00 am</div>
					</div>
					<div class="columnContainer">
						<div class="column-day">Thursday</div>
						<div class="column-hours">5:30 am - 1:00 am</div>
					</div>
					<div class="columnContainer">
						<div class="column-day">Friday</div>
						<div class="column-hours">5:30 am - 1:00 am</div>
					</div>
					<div class="columnContainer">
						<div class="column-day">Saturday</div>
						<div class="column-hours">7:00 am - 1:00 am</div>
					</div>
				</div>
			</div>

			<div class="column-half">
				<br /><br /><br />
				<iframe width="480" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.ca/maps?q=247+King+Street+North+Waterloo+ON+N2J+2Y8&amp;ie=UTF8&amp;hq=&amp;hnear=247+King+St+N,+Waterloo,+Ontario+N2J+2Y8&amp;t=m&amp;ll=43.483006,-80.524378&amp;spn=0.021797,0.041199&amp;z=14&amp;iwloc=A&amp;output=embed"></iframe><br /><small><a href="http://maps.google.ca/maps?q=247+King+Street+North+Waterloo+ON+N2J+2Y8&amp;ie=UTF8&amp;hq=&amp;hnear=247+King+St+N,+Waterloo,+Ontario+N2J+2Y8&amp;t=m&amp;ll=43.483006,-80.524378&amp;spn=0.021797,0.041199&amp;z=14&amp;iwloc=A&amp;source=embed" style="color:#0000FF;text-align:left">View Larger Map</a></small>
				<!-- origin mismatch bug by Google: https://code.google.com/p/chromium/issues/detail?id=43173 -->
			</div>
		</div>
	</div>

	<div id="tp-menu" class="template">
		<div class="photo">
			<img src="menu.jpg" alt="" />
		<div>
	</div>

	<div id="tp-photos" class="template">
		<div class="photo">
			<img src="photo1.jpg" alt="" />
			<p>Starbucks Coffeehouse Interior</p>
		</div>
		<br />
		<div class="photo">
			<img src="photo2.jpg" alt="" />
			<p>Starbucks in Brewery Blocks, Portland</p>
		</div>
		<br />
		<div class="photo">
			<img src="photo3.jpg" alt="" />
			<p>Starbucks in Yoido IFC, Seoul</p>
		</div>
		<br />
		<div class="photo">
			<img src="photo4.jpg" alt="" />
			<p>Starbucks in University Village, Seattle</p>
		</div>
	</div>
</body>
</html>
