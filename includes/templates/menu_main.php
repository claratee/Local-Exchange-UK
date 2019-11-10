<!--START include navigation -->
<div class="navigation">
		<input type="checkbox" id="nav" class="hidden"/>
		<label for="nav" class="nav-open"><i></i><i></i><i></i></label>
		<div class="nav-container">
			<nav>
				<ul class="menu">
					<li><a href="{{HTTP_BASE}}/index.php">Home</a></li>
					<li><a href="{{HTTP_BASE}}/pages.php?page_id=7">Information</a></li>
					<li><a href="{{HTTP_BASE}}/pages.php?page_id=84">Events</a></li>
					<li><a href="{{HTTP_BASE}}/listings.php?type=O&timeframe=14">Offered</a></li>
					<li><a href="{{HTTP_BASE}}/listings.php?type=W&timeframe=14">Wanted</a></li>
					<li><a href="{{HTTP_BASE}}/member_directory.php">Member directory</a></li>
					<li><a href="{{HTTP_BASE}}/member_profile_menu.php">My profile </a></li>
					<li><a href="{{HTTP_BASE}}/member_trade_menu.php">My trades and transactions</a></li>
					<!-- <li><a href="{{SERVER_PATH_URL}}/trade_history.php">My trades</a></li> -->
					<li><a href="{{HTTP_BASE}}/contact.php">Contact us</a></li>	
					<li><a href="{{HTTP_BASE}}/{{login_toggle_link}}">{{login_toggle_text}}</a></li>
				</ul>
			</nav>
		</div>
	</div>
<!--END include navigation -->