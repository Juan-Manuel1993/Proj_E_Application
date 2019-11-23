<?php

	session_start();
	// afficher incrémentalement
	// sauvegarder les données en cache
	// gérer la durée de vie du cache
	// si les donnnées sont déjà en cache pas la peine de chercher sur le serveur (ce qui dimuniera la durée de récupération des données)
	// récupérer les poids les plus forts
	// faire une api qui récupère le type de requête du client
	// id triées par ordre de poids décroissants
	// tri par ordre alphabétique (plus dure) avec les poids. Prendre les n plus forts et les afficher par ordre alphabétique
	// gestion de la mémoire (côté client)
	// On peut charger tous les mots communs en avance (animal etc)
	// on doit pouvoir masquer des relations
	// garder ce qu'on a déjà demander au serveur pour ne pas l'afficher deux fois
	//Gestion des relations négatives
	//Gestion de l'affichage


	/*
	display_errors = On
	display_startup_errors = On
	error_reporting = E_ALL
	html_errors = On

	xdebug.default_enable = 1
	xdebug.max_nesting_level = 200
	*/

		function getdata($arg){

			$postfields = http_build_query(array(
			'gotermrel' => $arg
		));

			$url = "http://www.jeuxdemots.org/rezo-dump.php";

			$options = array(
				'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded",
				'method'  => 'POST',
				'content' => $postfields,
				),
			);

			$context = stream_context_create( $options );

			$result = file_get_contents( $url, false, $context );

			return $result;

		}
		$i=$_POST['mot']; // Récupère ce que l'utilisateur a entré


		$result = getdata($i);

		//$file = "noeuds$i.txt";


		$noeuds = "./noeuds$i.txt"; // fichier qui contient tous les noeuds
		$rs ="./rs$i.txt"; // fichier qui contient toutes les relations sortantes
		$re ="./re$i.txt"; // fichier qui contient toutes les relations entrantes
		$ty ="./ty$i.txt"; // fichier qui contient les types de relations

		//iconv(mb_detect_encoding($result, mb_detect_order(), true), "UTF-8", $result);

		//$html = utf8_encode($result);
		//$html = html_entity_decode($result,ENT_QUOTES,"UTF-8");

		// récupérer la définition du mot
		$definitionmot= explode('<def>',$result);

		$definitionmots = explode('</def>', $definitionmot[1]);
		//$definitionmots[0] contient toutes les définitions du mot

		//print_r($definitionmots[0]);

		// Découper le contenu de la page par <code>
		$decoupeparcode = explode('<CODE>',$result);


		$apresformatedname = explode('formated name\'',$decoupeparcode[1]); // récupérer tout ce qui est après le mot et avant  " formated name " dans le texte. Ce qui correspond à tout ce dont on a besoin à partir de noeud

		// Découper apresformatedname[1] par //" tous les noeuds sont dans $getnoeud[0]
		$getnoeud = explode('//',$apresformatedname[1]);

		//toutes les types de relations sont dans $gettyperelation[1]
		$gettyperelation = explode('rthelp\'',$getnoeud[1]);

		// recupère tout le texte apres "formated name"
		//contient toutes les relations
		$touterelation = explode('les relations sortantes : r;rid;node1;node2;type;w ',$apresformatedname[1]);

		// découpe le texte par // pour récupérer les relations
		$getrelationsortante = explode('//',$touterelation[1]);
		//$getrelationsortante[0] contient toutes les relations sortantes

		// $getrelationentrante[1] contient toutes les relations entrantes
		$getrelationentrante = explode('les relations entrantes : r;rid;node1;node2;type;w ',$getrelationsortante[1]);

		// enlever le r; du début des relations sortantes
		//$getrelationsortante2 nouvelle table de relation sortante

		$getrelationsortante2 = explode('r;', $getrelationsortante[0]);

		//récupère la taille du tableau de relation sortante
		//$taillerelationsortante = $count($getrelationsortante2);

		// enlève le e; du début des noeuds
		// getnoeuds nouveau tableau de noeuds
		$getnoeuds = explode('e;', $getnoeud[0]);

		// enleve "rt;" du debut de tous les types de relation
		// suprimert devient notre nouveau contenu de type de relation

		$supprimert = explode('rt;', $gettyperelation[1]);

		//on récupère le nom de chaque type de relations existant
		for ($i=1; $i<count($supprimert);$i++){

			$r = explode(';', $supprimert[$i]);

			$type[$r[0]] = $r[1];

		}

		for ($i=1; $i<count($getrelationsortante2);$i++){

			//	decoupe chaque ligne de la table des relations sortantes par un ";"
			$ri = explode(';', $getrelationsortante2[$i]);

			// crée une table de hachache de numero de relation sortante =====> poids
			$relationsortante[$ri[0]] = $ri[4];

			// crée une table de hachache de numero de relation sortante =====> identifiant du noeud
			$relationsortante2[$ri[0]] = $ri[2];

			// crée une table de hachache de numero de relation sortante =====> type de relation
			$typerelation[$ri[0]] = $ri[3];

		}

		for ($i=2; $i<count($getnoeuds);$i++){

			// decoupe chaque ligne de la table des noeuds par un ";"
			$b = explode(';', $getnoeuds[$i]);

			// si c'est un string je récupère le string sans les identifiants (nettoyage)
			if (is_string($b[4])) {
				$node[$b[0]] = $b[4];
			}else{
				// recupère nom du noeud
				$node[$b[0]] = $b[1];
			}

			//$node contient le nom des noeuds

		}

		#je supprime de la table des noeuds, tous les noeuds qui ne sont pas dans la table de relation sortante
		foreach ($node as $key => $value) {
			if (!in_array($key, $relationsortante2)) {
				unset($node[$key]);
			}
		}

		#création d'un tableau relation(identifiant) => nom de noeud
		foreach ($relationsortante2 as $key => $value) {
			$relationsortante2[$key] = $node[$value];
		}

		//$raff = array();

		foreach ($typerelation as $key => $value) {
			// création d'un tableau de noeuds liés par la relation r_raff_sem au noeud cherché
			if($typerelation[$key] == 1){
				$po = explode('\'', $relationsortante2[$key]);
				$raff[] = $po[1];
			}
		}

		//print_r($raff);


		foreach ($typerelation as $key => $value) {
			$typerelation[$key] = $type[$value];
		}

		//print_r($typerelation);
		ksort($typerelation);

		#tri par clé croissant
		ksort($node);

		#tri par valeur croissant
		ksort($relationsortante2);

		ksort($relationsortante);

		foreach ($relationsortante2 as $key => $value) {
			$relationsortante2[$key] =  $typerelation[$key].",".$relationsortante2[$key].",".$relationsortante[$key].",";
		}



		$toutdef = array_map('raffinement', $raff);
		print_r($toutdef);


		function raffinement($arg){


		$html = getdata($arg);


		$definitionmot= explode('<def>',$html);


		$definitionmots = explode('</def>', $definitionmot[1]);

		return $definitionmots[0]."<br>";
	}


	/*
	file_put_contents($noeuds,$p3[0]);
	file_put_contents($rs,$getrelationsortante[0]);
	file_put_contents($re,$getrelationentrante[1]);
	file_put_contents($ty,$gettyperelation[1]);

	$lies = array();

*/
?>
