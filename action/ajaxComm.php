<?php

function set_community(?array $match) {
    $_SESSION["current_community"] = (int) $_POST['id'];
    
}
function join_community(?array $match) {
    $id = (int) $_POST['id'];
    $tempcomm = new Community($GLOBALS["db"], $id);
    $tempcomm->recruit(User::current());
}
	
function searchCommu(?array $match){
	$allcoms=$GLOBALS["communities"]->search_community($_POST["tosearch"]);
	foreach ($allcoms as $key => $com) {?>

	<div id="idCom-<?=$com->id()?>" class="communityPreviewSearch">
		<img class="communityImgSearch" src="<?=$com->get_cover();?>" alt="<?=$com->get_display_name();?>">

		<div class="communityLabelSearch">
			<p class="title"><?=$com->get_display_name();?></p>
			<p class="descriptionCommuSearch"><?=$com->get_description(80);?>...</p>
			<?php if($com->is_banned(User::current())): ?>
				<div class="communityBanInfo">Vous êtes bannis</div>
			<?php else: ?>
				<div class="communityButtonSearch cursor" onclick="joinOrLeave(<?=$com->id()?>);">
				<?php if($com->user_in(User::current())){?>
				Leave
				<?php }else{
				?>Join
				<?php } ?>
				</div>
			<?php endif ?>
		</div>
		
	</div>
	<?php 
	}
}

function JoinOrLeaveCommu(?array $match){
	$tempcomm = new Community($GLOBALS["db"], $_POST["idCommu"]);
	//si il est dedans, il quitte
	if ($tempcomm->user_in(User::current())) {
		$tempcomm->leave(User::current());
		
		$allcomsOfUser= User::current()->get_communities();
		$_SESSION["current_community"] = $allcomsOfUser[0]->id();
	}
	//si il n'est pas dedans, il rejoint.
	else{
		$tempcomm->recruit(User::current());
		$_SESSION["current_community"] = (int) $_POST['idCommu'];
	}
}

function get_rules(?array $match){
    $comm = $GLOBALS['communities']->get_by_id($_SESSION['current_community']);
    $rules = htmlspecialchars_decode($comm->rules());
    if(strlen($rules)>0){
    	echo "<div class='rulesContainer'>";
	    echo "<h3>Règles</h3>";
		echo "<div id='markdowContainer'>$rules</div>";
		echo "</div>";
    }else{
    	echo "no rules";
    }
    

}