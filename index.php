<?php 

	require('envoi.php');
	include 'init.php';

	if ( empty(session_id()) ) 
		session_start();

	class Word {
		public $weight;
		private $t_relation;
		private $t_node;
		private $word;

		public function __construct($weight, $t_relation,$t_node,$word) 
		{
			$this->setWeight($weight);
			$this->setTRelation($t_relation);
			$this->setTNode($t_node);
			$this->setWord($word);
		}

		public function getWeight()
		{
			return $this->weight;
		}

		public function setWeight($weight)
		{
			$this->weight = $weight;
		}

		public function getTRelation()
		{
			return $this->t_relation;
		}

		public function setTRelation($t_relation)
		{
			$this->t_relation = $t_relation;
		}

		public function getTNode()
		{
			return $this->t_node;
		}

		public function setTNode($t_node)
		{
			$this->t_node = $t_node;
		}

		public function getWord()
		{
			return $this->word;
		}

		public function setWord($word)
		{
			$this->word = $word;
		}
	}

	$jdm_result = array();
	
	$data = isset($_POST['mot']) ? getData($_POST['mot']) : null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>JeuxDeMots</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="tri.js"></script>
  <script scr="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script type="text/javascript" scr="mscript.js"></script>

  <style>
    
    div { border: 1px solid black;margin:15px }
    table { solid #000;padding: 5px; }
    td {solid #000; padding: 5px;}
    th {padding: 5px;}
    #conteneur { overflow: hidden}
    #conteneur div { margin:5px;width:49.2%;float:left }
    .whiteBackground { background-color: #fff; }
	.grayBackground { background-color: #ccc; }

	/* Classe obligatoire pour les flèches */
	.flecheDesc {
	  width: 0; 
	  height: 0; 
	  float:right;
	  margin: 10px;
	  border-left: 5px solid transparent;
	  border-right: 5px solid transparent;
	  border-bottom: 5px solid black;
	}
	.flecheAsc {
	  width: 0; 
	  height: 0;
	  float:right;
	  margin: 10px;
	  border-left: 5px solid transparent;
	  border-right: 5px solid transparent;
	  border-top: 5px solid black;
	}

	/* Classe optionnelle pour le style */
	.tableau {width:100%;table-layout: fixed;border-collapse: collapse;}
	.tableau td {padding:.3rem}
	.avectri th {text-align:center;padding:5px 0 0 5px;vertical-align: middle;background-color:#999690;color:#444;cursor:pointer;
		-webkit-touch-callout: none;
	  -webkit-user-select: none;
	  -khtml-user-select: none;
	  -moz-user-select: none;
	  -ms-user-select: none;
	  -o-user-select: none;
	  user-select: none;
	}
	.avectri th.selection {background-color:#5d625c;color:#fff;}
	.avectri th.selection .flecheDesc {border-bottom-color: white;}
	.avectri th.selection .flecheAsc {border-top-color: white;}

	.avectri tr:nth-child(odd) {background-color: #d6d3ce;border-bottom:1px solid #ccc;color:#444;}
	.avectri tr:nth-child(even) {background-color: #c6c3bd;border-bottom:1px solid #ccc;color:#444;}
	.avectri tbody tr:hover:nth-child(odd) {background-color: #999690;color:#ffffff;}
	.avectri tbody tr:hover:nth-child(even) {background-color: #999690;color:#ffffff;}

}
	.zebre tbody td:nth-child(3) {text-align:center;}

  </style>
  </head>
  <body>

    <h1 align="center">Jeux De Mots</h1>
    <form method="post">

      <p align="center">

        <input type="text" id="mot" name="mot"/> 
        <input type="submit" id="formsubmit" value="Valider">

      </p>
    </form>

    <div id="conteneur">
      <div >
        <table border="1" cellpadding="10" class="avectri" cellspacing="2" width="100%" >
        	<thead>
          <tr>
            <th data-tri="1" class="selection" data-type="num" width="10%">#</th>
            <th width="30%">Mots</th>
            <th width="30%">Type de relation</th>
            <th width="30%">Type de noeud</th>
          </tr>
          </thead>
          <tbody>
          <?php
          	if ($data !== null)
          	{
          		foreach ($data as $key => $value) {
          			$jdm_result[] = new Word($key,str_replace(array("'",$_POST['mot'],">"),"",$value),"champ2","champ3");
          		}
          	}
          	if(isset($_POST['mNbAffichage'])) 
          		$selected_val = $_POST['mNbAffichage'];
          	else
          		$selected_val = $init_min_tuples;

            for($i = 0;$i < $selected_val;$i++)
            {
            	if($i <= 3){
					$testRelation="r1";
				}else if($i > 3 && $i < 5){
					$testRelation = "r2";
				}else{
					$testRelation = "r";
				}

				if($i <= 10){
					$testType="t1";
				}else if($i > 10 && $i < 13){
					$testType = "t2";
				}else{
					$testType = "t";
				}

            	if($i%2 == 1)	
            		$color_bg = '#fff';
            	else
            		$color_bg = '#ccc';

				if($jdm_result[$i] != null){
	            	if( ( in_array ($jdm_result[$i]->getTRelation(), $_POST['relationList']) && in_array ($jdm_result[$i]->getTNode(), $_POST['typeList'])) || !isset($_POST['relationList'])){

		              	echo "
								<tr bgcolor=".$color_bg.">
								<td>".$jdm_result[$i]->getWeight()."</td>
								<td>".$jdm_result[$i]->getWord()."</td>
								<td>".$jdm_result[$i]->getTRelation()."</td>
								<td>".$jdm_result[$i]->getTNode()."</td>
								</tr>
							";
					}

					if(!in_array($jdm_result[$i]->getTRelation(), $arrayRelation))
						$arrayRelation[$jdm_result[$i]->getTRelation()] = $jdm_result[$i]->getTRelation();

					if(!in_array($jdm_result[$i]->getTNode(), $arrayType))
						$arrayType[$jdm_result[$i]->getTNode()] = $jdm_result[$i]->getTNode();
				}
            }
          ?>
          </tbody>

        </table>
      </div>
      <div >
      	<form  method="post">
		<p>Nombre d'élèment à afficher
			<br>
	        <select name="mNbAffichage" id="mNbAffichage">
	          <?php
	            for($i = $init_min_tuples;$i <= 500; $i+=$init_step)
	            {
	              if($i==$_POST['mNbAffichage']){
	            		echo "<option selected=".'"'."selected".'"'." value=".$i.">".$i."</option>";
	            	}else{
	            		echo "<option value=".$i.">".$i."</option>";
	            	}
	            }
	          ?>
	        </select>
        </p>
        <p>
        	<section>
        		<section>Selection des relations </section>
        		<section>
					<select multiple name="relationList[]">
					   	<?php 
							foreach ($arrayRelation as $key => $value) {
						        if( in_array($key, $_POST['relationList']) || !isset($_POST['relationList'])) { 
						            echo '<option value="'.$key.'" selected>'.$value.'</option>';
						        } else {
						            echo '<option value="'.$key.'">'.$value.'</option>';
						        }
						    }
					   	?>
					</select>
				</section>
			</section>

			<section>
        		<section>Selection des types </section>
        		<section>
					<select multiple name="typeList[]">
					   	<?php 
							foreach ($arrayType as $key => $value) {
						        if( in_array($key, $_POST['typeList']) || !isset($_POST['typeList'])) { 
						            echo '<option value="'.$key.'" selected>'.$value.'</option>';
						        } else {
						            echo '<option value="'.$key.'">'.$value.'</option>';
						        }
						    }
					   	?>
					</select>
				</section>
			</section>
        </p>
        <input type="submit" value="Appliquer" />
    	</form>
      </div>
    </div>


  </body>
</html>
