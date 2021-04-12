<!DOCTYPE html>
<html>
<head>
	<title>Profil</title>
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0' >
	<!-- <link rel="stylesheet" href="<?= Routes::url_for('/styles/styleProfil.css')?>"> -->
  <link rel="stylesheet" href="<?= Routes::url_for('/styles/styleApp.css')?>">
</head>


  



<body>
<!-- DANS LES PARAMETRES ...  -->
<div id="pageparametres" class="cadre">
		<img onclick="closeparametre()" src="<?= Routes::url_for('/img/svg/cross.svg')?>"/>
		<h4>Settings</h4>
		<div class="options">
			<a href=""> Changer mon mot de passe </a>
			<a href="<?= Routes::url_for('/disconnect')?>"> Déconnexion  </a>
			<a href=""> Supprimer mon compte </a>
		</div>
</div>
<!-- RESTE DE LA PAGE -->
<div id="page">
	<div class="topBar">
	<img onclick="document.location.href='<?= Routes::url_for('/feed')?>'" class="backArrow cursor" src="./img/svg/arrow-back-fill.svg">
	<div class="right-container">
		<!-- AFFICHAGE DES FAVORIS -->
		<a href=""><img src="https://img.icons8.com/ios/50/000000/bookmark-ribbon--v2.png" name="favorilogo" class="logofavori"/></a>
		<!-- AFFICHAGE DES PARAMETRES -->
		<a href=javascript:void(0); onclick="afficheparameter()">
		<img src="https://img.icons8.com/ios/50/000000/settings--v1.png" class="logoparametre"/> </a>
	</div>
	
	</div>

<!-- LA PAGE DE PROFIL -->

<section id="verticalScrollContainer">
	<div class="profilContainer">
		<div class="generalInfo">
			<div class="generalInfo-top">
				<div>
					<!-- IMG PROFIL -->
					<img class="pictprofil" src="<?= User::is_connected() ? User::current()->profile_pic() : "anonyme" ?>" alt="profil">
					<!-- MODIF  -->
					<a href="<?= Routes::url_for("/profil-edit")?>"class="modiferprofil cursor"> Modifier <img src="https://img.icons8.com/fluent/48/000000/pencil-tip.png" /></a>
				</div>
				
				<div class="profil-infos-container">
					<h2 class="nameprofil"><?= User::is_connected() ? User::current()->display_name() : "anonyme" ?> <img class="logocertifier" src="https://img.icons8.com/nolan/64/approval.png"/></h2>
					<ul class="roleprofil">
						<li style="background-color: red;">Original</li>
						<li style="background-color: green;">drole</li>
						<li style="background-color: orange;">maitre du css</li>
					</ul>
				</div>
				
			</div>
			<div class="generalInfo-bottom">
				<!-- POSTS -->
				<div class="nbPostContainer">
					<img src="./img/svg/document-outline.svg" class="logocptpost"/>	
					XX
				</div>
				<!-- FOLLOWERS -->
				<div class="followersContainer">
					<img src="./img/svg/user-outlined.svg" class="logocptperso"/>
					XX
				</div>					
			</div>

			
		</div>
		<p class="profilDescription"><?= User::is_connected() ? User::current()->description() : "anonyme" ?> </p>
	</div>

<!-- RECUPERER LES COMMUNAUTES  -->
<?php 
$communities = User::current()->get_communities();
if (sizeof($communities)>0){ ?>
<div class="communitySelectorProfil">
    <div id="boxesContainer" class="horizontal-scroll">
    	<?php 
			foreach($communities as $comm){
				$idCom = $comm->id();
				?>
			   	<div onclick="switchFilter(<?=$idCom?>);">
					<img id="community-<?=$idCom?>" class="communityPreview-profil" src="<?=$comm->get_cover()?>"alt ="<?=$comm->get_display_name()?>">
					<p id="label-<?=$idCom?>" class="communityPreviewLabel-profil"><?=$comm->get_display_name()?></p>
					<img id="check-<?=$idCom?>" class="checkfilter hidden" src="./img/svg/checkwhite.svg">
				</div>
			  <?php
			}
			?>

	</div>
<?php
}
else{
	?>
<p>Aucune communauté rejointe</p>
<?php 
}
?>
	
	<div class="postprofil">
	<!-- POUR UNE PUBLICATION -->
	</div>
		
	</section>

</div>

</body>

<!-- FONCTIONS POUR AFFICHAGE DES PARAMETRES -->
<script type="text/javascript">
			function afficheparameter(){
				document.getElementById('pageparametres').style.display = 'block';
				document.getElementById('page').style.opacity = '0.2';

				//document.getElementById(pageparametres).style.displat = none;
			}

			function closeparametre(){
				document.getElementById('pageparametres').style.display = 'none';
				document.getElementById('page').style.opacity = '1';

			}
</script>
<script type="text/javascript">
	function switchFilter(n){
		var  boxes = document.getElementById("boxesContainer").childNodes;
		for (var i = 0; i < boxes.length; i++) {
			
			if (i%2!=0){
				boxe = boxes[i]
				var c= boxe.childNodes;
				var toBlurry = c[1];
				var label = c[3];
				var check = c[5];
				if (check.id=="check-"+n){
					//on toogle la visibilité du nom et du check
				    label.classList.toggle("hide");
				    check.classList.toggle("hidden");
				    //l'opacité et le scroll du fond
				    toBlurry.classList.toggle("blurryOverlayProfilFilter"); 
				}else{
					//on toogle la visibilité du nom et du check
				    label.classList.remove("hide");
				    check.classList.add("hidden");
				    //l'opacité et le scroll du fond
				    toBlurry.classList.remove("blurryOverlayProfilFilter"); 
				}
			}
			

		}
	    

	   
	}
</script>
<!-- <script type="text/javascript">
	var page = "profil";
</script>
<script src="<?= Routes::url_for('/js/feedAjax.js')?>"></script>
<script src="<?= Routes::url_for('/js/votesAjax.js')?>"></script> -->
</html>

