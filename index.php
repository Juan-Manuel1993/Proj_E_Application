<?php
require('envoi.php');
include 'init.php';

if (empty(session_id())) {
    session_start();
}

class Word
{
    private $weight;
    private $word;
    private $eid=0;

    public function __construct($eid, $weight, $word)
    {
        $this->setId($eid);
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

    public function getId()
    {
        return $this->eid;
    }

    public function setId($eid)
    {
        $this->eid = $eid;
    }
}

$jdm_result = array();

$data = "";
$word = "";

if (isset($_POST['mot'])) {
    $_SESSION['mot'] = $_POST['mot'];
    $data = getdata($_POST['mot']);
    $word = $_POST['mot'];
} elseif (isset($_SESSION['mot'])) {
    $word = $_SESSION['mot'];
    $data = getdata($word);
} else {
    $word = 'chat';
    $data = getdata($word);
    $_SESSION['mot'] ='chat';
}

$news = getWordInfo($word, $data);


$info = getInformations($word, $data);

$contenu2 = getmots();

  for($i=2;$i<1000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $contenu[]= $value;
    }
  }

  $contenu = str_replace("\"", "'", $contenu);

for($i=10000;$i<11000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $content[]= $value;
    }
  }
  

  for($i=1000;$i<4000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $conten[]= $value;
    }
  }
  

 for($i=100000;$i<103000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $conte[]= $value;
    }
  }
  


 for($i=300000;$i<303000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $cont[]= $value;
    }
  }
  


 for($i=600000;$i<603000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $con[]= $value;
    }
  }
  
  

 for($i=900000;$i<903000;$i++){
    foreach ($contenu2[$i] as $key => $value) {
      if($key == 1)
      $co[]= $value;
    }
  }
  
  
  
  





?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>WordPlay</title>
  <!-- MDB icon -->
  <link rel="icon" href="img/mdb-favicon.ico" type="image/x-icon">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
  <!-- Google Fonts Roboto -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- Material Design Bootstrap -->
  <link rel="stylesheet" href="css/mdb.min.css">
  <!-- Your custom styles (optional) -->
  <link rel="stylesheet" href="css/style.css">
  <!-- MDBootstrap Datatables  -->
  <link href="css/addons/datatables.min.css" rel="stylesheet">

  <style>
  .loader {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    display:flex;
    justify-content: center;
    align-items: center;
    background-color: #ffffff;
  }
  </style>

</head>

<body>

  <!-- jQuery -->
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <!-- Bootstrap tooltips -->
  <script type="text/javascript" src="js/popper.min.js"></script>
  <!-- Bootstrap core JavaScript -->
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <!-- MDB core JavaScript -->
  <script type="text/javascript" src="js/mdb.min.js"></script>
  <!-- Your custom scripts (optional) -->
  <script type="text/javascript"></script>
  <!-- MDBootstrap Datatables  -->
  <script type="text/javascript" src="js/addons/datatables.min.js"></script>

  <script>

  $(window).on("load",function(){
    $(".loader").fadeOut("fast");
  });

  function search(row) {

    $.post("index.php",
    {
      mot: row.getElementsByTagName("td")[1].innerHTML,
    },
    function(data,status){

    });

    document.location.reload(false);
  };

  $(document).ready(function() {
    $('div table').DataTable({
      "order": [[ 0, "desc" ]]
    });
    $('.dataTables_length').addClass('bs-select');

  });


</script>

<h1 align="center">Jeux De Mots</h1>

<div class="loader">
  <img src="loading.gif" />
</div>

<form method="post">
  <p align="center">
    <input class="form-control form-control-sm mr-3 w-25" type="text" id="mot" name="mot" value=<?php echo $_SESSION['mot']; ?> />
  </p>
</form>

<div id="conteneur">
  <div>
    <h2>Mot de la recherche : <h1><?php echo $word; ?></h1> </h2>
    <h3>Définition : </h3><br>
    <?php echo $news['definition'] ?>
    <?php foreach ($news['raffinement'] as $key => $tab) {
    if ($tab['def'] != "") {
        echo $tab['def'];
    }
}
    ?>
  </div>

  <div class="container">
    <div class="row">
      <div class="col">
        <table class="table table-hover table-striped table-bordered table-sm" cellspacing="0">
          <thead>
            <tr>
              <th class="th-sm">Importance
              </th>
              <th class="w-auto">Mot sortant
              </tr>
            </thead>
            <tbody>
              <?php
              if ($data !== null) {
                  $rsortantes = $news['RSortantes'];

                  foreach ($rsortantes as $key => $tab) {
                      $word = new Word(
                          $tab['node'],
                          getEntrie($news['entries'], $tab['node'])['w'],
                          getEntrie($news['entries'], $tab['node'])['name']
                      );

                      if (!array_key_exists(getEntrie($news['entries'], $tab['node'])['eid'], $jdm_result)) {
                          echo "
                    <tr id=".$word->getId()." onclick=\"search(this)\">
                    <td>".$word->getWeight()."</td>
                    <td>".$word->getWord()."</td>
                    </tr>
                    ";
                          $jdm_result[getEntrie($news['entries'], $tab['node'])['eid']] = $word;
                      }
                  }
              }
              ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Importance
                </th>
                <th>Mot sortant
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="col">
          <table class="table table-hover table-striped table-bordered table-sm" cellspacing="0">
            <thead>
              <tr>
                <th class="th-sm">Importance
                </th>
                <th class="w-auto">Mot entrant
                </tr>
              </thead>
              <tbody>
                <?php
                if ($data !== null) {
                    $rentrantes = $news['REntrantes'];

                    foreach ($rentrantes as $key => $tab) {
                        $word = new Word(
                            $tab['node'],
                            getEntrie($news['entries'], $tab['node'])['w'],
                            getEntrie($news['entries'], $tab['node'])['name']
                        );

                        if (!array_key_exists(getEntrie($news['entries'], $tab['node'])['eid'], $jdm_result)) {
                            echo "
                      <tr id=".$word->getId()." onclick=\"search(this)\">
                      <td>".$word->getWeight()."</td>
                      <td>".$word->getWord()."</td>
                      </tr>
                      ";
                            $jdm_result[getEntrie($news['entries'], $tab['node'])['eid']] = $word;
                        }
                    }
                }
                ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Importance
                  </th>
                  <th>Mot entrant
                  </th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>


  </body>

  </html>
