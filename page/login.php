<!DOCTYPE html>
<html>
<head>
	<title>OkyDoky</title>
	<link rel="shortcut icon" href="<?= Routes::url_for('/img/favicon.ico')?>" type="image/x-icon" />
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0' >
	<link rel="stylesheet" type="text/css" href="<?= Routes::url_for('/styles/style.css')?>">
</head>
<body>

	<a href="." class="top-left-name-absolute">OkyDoky</a>
	<div class="form-structor">
		<form id="signupForm" class="signup" action="<?= Routes::url_for('/signup') ?>" method="POST">
			<h2 class="form-title" id="signup"><span>or</span>Sign up</h2>
			<div class="form-holder">
				<input type="text" name="nickname" class="input" placeholder="Nickname" />
				<input type="email" name="email" class="input" placeholder="Email" />
				<input type="password" name="password" class="input" placeholder="Password" />
			</div>
			<input type="submit" value="Sign up" class="submit-btn"></input>
		</form>
		<form id="loginForm" class="login slide-up" action="<?= Routes::url_for('/signin') ?>" method="POST">
			<div class="center">
				<h2 class="form-title" id="login"><span>or</span>Login</h2>
				<div class="form-holder">
					<input type="text" name="login" class="input" placeholder="Email/Nickname" />
					<input type="password" name="password" class="input" placeholder="Password" />
				</div>
				<input type="submit" value="Login"class="submit-btn"></input>
			</div>
		</form>
	</div>


</body>
<script src="<?= Routes::url_for('/js/login.js')?>"></script>
</html>	
