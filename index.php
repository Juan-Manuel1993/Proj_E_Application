<?php
    require('envoi.php');
    include 'init.php';

    if (empty(session_id())) {
        session_start();
    }

/*
    class Word
    {
        public $weight;
        private $word;

        public function __construct($weight, $word)
        {
            $this->setWeight($weight);
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

        public function getWord()
        {
            return $this->word;
        }

        public function setWord($word)
        {
            $this->word = $word;
        }
    }
*/
/*
    $jdm_result = array();

    $data = "";
    $word = "";

   // getinfo('chat');
   
    //si le mot existe
    if (isset($_POST['mot'])) {
        // on enregistre la session
        $_SESSION['mot'] = $_POST['mot'];

        // on récupère les données du mot
        $data = getdata($_POST['mot']);

        // on récupère le mot entré par l'utilisateur
        $word = $_POST['mot'];

        // si l aseesion existe déjà
    } else if (isset($_SESSION['mot'])) {
        $word = $_SESSION['mot'];
        $data = getdata($word);

        // affichage par défaut
    } 
*/

    // récupère définitions et raffinements
    $info = null;
    //$info = getInformations($word, $data);


   // $tout = file_get_contents("http://www.jeuxdemots.org/JDM-LEXICALNET-FR/01012020-LEXICALNET-JEUXDEMOTS-ENTRIES.txt");

    $tout = getmots();

   
    $var= explode('id;terme;', $tout);

    //$var = str__replace("\"","\"",$var);
    
    $varr =explode("\n", $var[1]);


    
    $contenu = array();
    $r = array();
    // pas 4->5, 7->8,1->11
    for ($i=1200000; $i <1300000; $i++) { 
      $t = explode(';', $varr[$i]);
      $contenu2[]=$t[1];
    }
        
    //  unset($contenu2[128]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Jeux Mots</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel=’stylesheet’ type=’text/css’ href=’style.css’ />
  <script type="text/javascript" src="tri.js"></script>
  <!-- <script type="text/javascript" src="auto.js"></script> -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
 <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->
  <!-- <script type="text/javascript" src="js/bootstrap.min.js"></script> -->
  
 
  </head>
  <body>


<div class="ui-widget">

    <h1 align="center">Jeux De Mots</h1>
    <form action = "envoi.php" method="post">
      <p align="center">
      <label for="tags">Mot: </label>
       <input id="tags">
        <input type="submit" id="formsubmit" value="Rechercher">

      </p>

    </form>

  </div>


     <script type="text/javascript"  charset="utf-8">

      //var disp = <?php echo '["' . implode('", "', $contenu) . '"]' ?>;


      var disp2 = <?php echo '["' . implode('", "', $contenu2) . '"]' ?>;


    
      
    
    
/*
  $( function() {
    
    $( "#tags" ).autocomplete({
      source: disp
    });
  } );
 
*/

  $( function() {
    
    $( "#tags" ).autocomplete({
      source: disp2,
      minLength:3
    });
  } );

 
  </script>


  </body>
</html>
