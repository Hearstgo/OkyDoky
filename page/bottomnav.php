<nav class="bottomNav">

	<a class="bottomNavElement" href="./feed">
		<svg class="home" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16"><path d="M7.313 1.262a1 1 0 0 1 1.374 0l4.844 4.579c.3.283.469.678.469 1.09v5.57a1.5 1.5 0 0 1-1.5 1.5h-2A1.5 1.5 0 0 1 9 12.5V10a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v2.5A1.5 1.5 0 0 1 5.5 14h-2A1.5 1.5 0 0 1 2 12.5V6.93c0-.412.17-.807.47-1.09l4.843-4.58zM8 1.988l-4.844 4.58A.5.5 0 0 0 3 6.93v5.57a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5V10a1.5 1.5 0 0 1 1.5-1.5h1A1.5 1.5 0 0 1 10 10v2.5a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5V6.93a.5.5 0 0 0-.156-.363L8 1.988z" fill="#2F3542"/></svg>
	</a>
	<a class="bottomNavElement" href="./top">
		<svg class="top" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="16" height="16"preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path d="M25.7 9.3l-7-7c-.2-.2-.4-.3-.7-.3H8c-1.1 0-2 .9-2 2v24c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V10c0-.3-.1-.5-.3-.7zM18 4.4l5.6 5.6H18V4.4zM24 28H8V4h8v6c0 1.1.9 2 2 2h6v16z" fill="#2F3542"/><path d="M10 22h12v2H10z" fill="#2F3542"/><path d="M10 16h12v2H10z" fill="#2F3542"/></svg>
		<?php 
	if(empty(User::current()->get_communities())){
		?>
		<img id="previewCommunityBottomNav2" class="commTopRightFeed noselect" src="./img/default_community.png" alt="communauté">
		<?php
	}
	else{
		?>
		<img id="previewCommunityBottomNav2" class="commTopRightFeed noselect" src="<?= $GLOBALS["communities"]->get_by_id($_SESSION["current_community"])->get_cover();?>" alt="communauté">
		<?php
	}
	?>
	</a>
	<a class="bottomNavElement" href="./search">
		<svg class="search" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" preserveAspectRatio="xMidYMid meet" viewBox="0 0 1024 1024"><path d="M909.6 854.5L649.9 594.8C690.2 542.7 712 479 712 412c0-80.2-31.3-155.4-87.9-212.1c-56.6-56.7-132-87.9-212.1-87.9s-155.5 31.3-212.1 87.9C143.2 256.5 112 331.8 112 412c0 80.1 31.3 155.5 87.9 212.1C256.5 680.8 331.8 712 412 712c67 0 130.6-21.8 182.7-62l259.7 259.6a8.2 8.2 0 0 0 11.6 0l43.6-43.5a8.2 8.2 0 0 0 0-11.6zM570.4 570.4C528 612.7 471.8 636 412 636s-116-23.3-158.4-65.6C211.3 528 188 471.8 188 412s23.3-116.1 65.6-158.4C296 211.3 352.2 188 412 188s116.1 23.2 158.4 65.6S636 352.2 636 412s-23.3 116.1-65.6 158.4z" fill="#2F3542"/></svg>
	</a>
	<div class="bottomNavElement cursor" onclick="switchCreate();" >
		<div id="overlayCreate" class="hidden">
			<?php 
			if(!empty(User::current()->get_communities())){
			?>
			<a href="./post">Créez un post !</a>
			<?php } ?>
			<a href="./createCommunity">Créez une communauté !</a>
		</div>
		<svg class="post" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16"><g fill="#2F3542"><path fill-rule="evenodd" d="M8 3.5a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5H4a.5.5 0 0 1 0-1h3.5V4a.5.5 0 0 1 .5-.5z"/><path fill-rule="evenodd" d="M7.5 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H8.5V12a.5.5 0 0 1-1 0V8z"/><path fill-rule="evenodd" d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/></g></svg>
		
	</div>

	<a class="bottomNavElement" href="./tchat" style="transform: translateX(20%);">
		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M87.49 380c1.19-4.38-1.44-10.47-3.95-14.86a44.86 44.86 0 0 0-2.54-3.8a199.81 199.81 0 0 1-33-110C47.65 139.09 140.73 48 255.83 48C356.21 48 440 117.54 459.58 209.85a199 199 0 0 1 4.42 41.64c0 112.41-89.49 204.93-204.59 204.93c-18.3 0-43-4.6-56.47-8.37s-26.92-8.77-30.39-10.11a31.09 31.09 0 0 0-11.12-2.07a30.71 30.71 0 0 0-12.09 2.43l-67.83 24.48a16 16 0 0 1-4.67 1.22a9.6 9.6 0 0 1-9.57-9.74a15.85 15.85 0 0 1 .6-3.29z" fill="none" stroke="#2F3542" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32"/><rect x="0" y="0" width="512" height="512" fill="rgba(0, 0, 0, 0)" /></svg>
	</a>

	
	<a class="bottomNavElement" href="./community">
	<?php 
	if(empty(User::current()->get_communities())){
		?>
		<img id="previewCommunityBottomNav" class="small-bubble noselect" src="./img/default_community.png" alt="communauté">
		<?php
	}
	else{
		?>
		<img id="previewCommunityBottomNav" class="small-bubble noselect" src="<?= $GLOBALS["communities"]->get_by_id($_SESSION["current_community"])->get_cover();?>" alt="communauté">
		<?php
	}
	?>
	</a>

</nav>

<script type="text/javascript">
	function switchCreate(){
	//on toogle la visibilité
	var container = document.getElementById("overlayCreate");
	container.classList.toggle("hidden");
}

</script>