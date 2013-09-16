<!doctype html>
<html lang="en" ng-app="app">
<head>
	<meta charset="utf-8">
	<title>UWKSA Main</title>
	<script src="./lib/angular.min.js"></script>
	<script src="./lib/angular-sanitize.min.js"></script>
	<script src="./js/app.js"></script>
	<script src="./js/controllers.js"></script>
	<script src="./js/filters.js"></script>
	<script src="./js/directives.js"></script>
	<script src="./js/services.js"></script>
	<script src="./js/controllers.js"></script>
	<link rel="stylesheet" type="text/css" href="./css/style.css">
	<link rel="stylesheet" type="text/css" href="./css/grid.css">
	<link rel="shortcut icon" href="../favicon.ico?v=3" />

</head>
<body>
	<div id="header" class="OpenSans">
		<div id="logo">
			<a href="http://biz139.inmotionhosting.com/~uwksac5/mark/app/">
				<img src="http://placehold.it/100x60/f1234f/043210">
			</a>
		</div>
		<div id="navigation">
			<ul>
				<li class="selected">HOME</li>
				<li>ABOUT</li>
				<li>Q&A</li>
				<li>COMMUNITY</li>
				<li>SUBLETS</li>
				<li>TUTORS</li>
				<li>BUY&SELL</li>
				<li>EXAMS</li>
				<li>LOGIN</li>
				<li>JOIN</li>
			</ul>
		</div>
	</div> <!-- end #header -->

	<div id="theMaster" class="container_12">
		<div class="slides" ng-controller="SlideController">
			<carousel interval="interval">
				<slide ng-repeat="slide in slides" active="slide.active">
					<img ng-src="{{slide.image}}" style="margin:auto;">
					<div class="carousel-caption">
						<h4>This is a friggin title</h4>
						<p>{{slide.text}}</p>
					</div>
				</slide>
			</carousel>
		</div>
		<div id="boards" class="container_12" ng-controller="MainController">
			<div id="frow">
				<div id="board-wrapper" class="news grid_6">
					<div id="title">
						<h1>NEWS & EVENTS</h1>	
					</div>
					<div id="top">
						<div class="content-wrapper">
							<div id="top-news-title">
								<h2>A Message from the New President</h2>
								<span>Posted Sept 17th, 2013</span>
							</div>
							<div id="content">
								<span>
									안녕하세요, 2013-14년도 UWKSA 회장을 맡게 된 Actuarial Science 4학년에 재학 중인 89년생 조규진이라고 합니다. 10-11년도엔 기획부장을, 11-12년도엔 부회장을, 12-13년도엔 김현태 전 회장님과 함께 고문을 맡았었는데요, 어느덧 회장자리까지 올라오게 되었습니다. 
								</span>
							</div>
							<a href="http://www.reddit.com">
								<span>READ MORE</span>
							</a>
						</div>
					</div>
					<div id="more">
						<div class="content-wrapper">
							<ul>
								<li>
									<span>UW Hackathon finalist : Team CMYK</span>
									<span>1/1/2013</span>
								</li>
								<li>
									<span>UW Hackathon finalist : Team CMYK</span>
									<span>1/1/2013</span>
								</li>
								<li>
									<span>UW Hackathon finalist : Team CMYK</span>
									<span>1/1/2013</span>
								</li>
								<li>
									<span>UW Hackathon finalist : Team CMYK</span>
									<span>1/1/2013</span>
								</li>
								<li>
									<span>UW Hackathon finalist : Team CMYK</span>
									<span>1/1/2013</span>
								</li>
							</ul>	
						</div>					
					</div>
				</div>
				<div id="board-wrapper" ng-controller="qnaController" class="grid_6">
					<div id="title">
						<h1>QNA</h1>
					</div>
					<div class="content-wrapper">
						<ul>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>What is the main cause of shoulder injury?</span>
								<span>1/1/2013</span>
							</li>						
						</ul>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<div id="others">
				<div id="board-wrapper" ng-controller="tutorController" class="grid_4">
					<div id="title">
						<h1>TUTORS</h1>
					</div>
					<div class="content-wrapper">
						<ul>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>LSAT minute LSAT cramming!</span>
								<span>1/1/2013</span>
							</li>
						</ul>
					</div>
				</div>
				<div id="board-wrapper" ng-controller="subletController" class="grid_4">
					<div id="title">
						<h1>SUBLETS</h1>
					</div>
					<div class="content-wrapper">
						<ul>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>BATCAVE is looking for new tenents</span>
								<span>1/1/2013</span>
							</li>
						</ul>
					</div>
				</div>
				<div id="board-wrapper" ng-controller="bsController" class="grid_4">
					<div id="title">
						<h1>BUY & SELL</h1>
					</div>
					<div class="content-wrapper">
						<ul>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>[SELL] 고소미 1kg</span>
								<span>1/1/2013</span>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear"></div>
				<div id="board-wrapper" ng-controller="ebController" class="grid_4">
					<div id="title">
						<h1>EXAM BANK</h1>
					</div>
					<div class="content-wrapper">
						<ul>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>10 years of CS350</span>
								<span>1/1/2013</span>
							</li>
						</ul>
					</div>
				</div>
				<div id="board-wrapper" ng-controller="communityController" class="grid_8">
					<div id="title">
						<h1>COMMUNITY</h1>
					</div>
					<div class="content-wrapper">
						<ul>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
							<li>
								<span>흔한 4A CS 스케쥴.simang</span>
								<span>1/1/2013</span>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
	<div id="footer">
		
	</div>
	
</body>
</html>
