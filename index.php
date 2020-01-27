<?php
require('envoi.php');
include 'init.php';

if (empty(session_id())) {
    session_start();
}

$data = "";
$word = "";

//$time_start = microtime(true);

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
  * {
  box-sizing: border-box;
}

body {
  font: 16px Arial;
}

/*the container must be positioned relative:*/
.autocomplete {
  position: relative;
  display: inline-block;
}

input {
  border: 1px solid transparent;
  background-color: #f1f1f1;
  padding: 10px;
  font-size: 16px;
}

input[type=text] {
  background-color: #f1f1f1;
  width: 100%;
}

input[type=submit] {
  background-color: DodgerBlue;
  color: #fff;
  cursor: pointer;
}

.autocomplete-items {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 0;
  right: 0;
}

.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color: #fff;
  border-bottom: 1px solid #d4d4d4;
}

/*when hovering an item:*/
.autocomplete-items div:hover {
  background-color: #e9e9e9;
}

/*when navigating through the items using the arrow keys:*/
.autocomplete-active {
  background-color: DodgerBlue !important;
  color: #ffffff;
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

  <link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link>
<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script>

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

  function getEntrie(entries, eid)
  {
    $entrie = null;
    entries.forEach((item, index) => {
      if (item.eid == eid) {
        $entrie = item;
      }
    })
    return $entrie;

  }

  function getRelationType(relationstypes,rtid){
    $entrie = null;
    relationstypes.forEach((item, index) => {
      if (item.rtid == rtid) {
        $entrie = item;
      }
    })
    return $entrie;
	}

  $(document).ready(function() {

    $.get("autoc.txt",
    function(data, status){
      const regex = /[^\n;^\d]+/g;
      var tags = data.match(regex);

      autocomplete(document.getElementById("mot"), tags.splice(0,54334).sort());

    });

    $('div table').DataTable({
      "order": [[ 0, "desc" ]]
    });
    $('.dataTables_length').addClass('bs-select');

    var json = <?php echo json_encode($news); ?>;
    var dtableSortant = $('#TSortant').DataTable();
    var dtableEntrante = $('#TEntrant').DataTable();

    $.each(json.RSortantes, function(key, val) {

      var entrie = getEntrie(json.entries, val.node);
      if(entrie != null){
        dtableSortant.row.add( [ val.w ,entrie.name, getRelationType(json.RTypes,val.type).trname ] ).node().id = val.node;
      }
    });

    $.each(json.REntrantes, function(key, val) {
      var entrie = getEntrie(json.entries, val.node);
      if(entrie != null){
        dtableEntrante.row.add( [ entrie.w ,entrie.name ] ).node().id = val.node;
      }
    });

    dtableSortant.draw(false);
    dtableEntrante.draw(false);

    var table = $('#TSortant').DataTable();

        $('#TSortant tbody').on('click', 'tr', function(row) {
          $.post("index.php",
          {
            mot: this.getElementsByTagName("td")[1].innerHTML,
          },
          function(data,status){

          });

          document.location.reload(false);
        } );

  });

  function autocomplete(inp, arr) {
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) { return false;}
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        /*for each item in the array...*/
        for (i = 0; i < arr.length; i++) {
          /*check if the item starts with the same letters as the text field value:*/
          if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
            /*create a DIV element for each matching element:*/
            b = document.createElement("DIV");
            /*make the matching letters bold:*/
            b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
            b.innerHTML += arr[i].substr(val.length);
            /*insert a input field that will hold the current array item's value:*/
            b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
            /*execute a function when someone clicks on the item value (DIV element):*/
            b.addEventListener("click", function(e) {
                /*insert the value for the autocomplete text field:*/
                inp.value = this.getElementsByTagName("input")[0].value;
                /*close the list of autocompleted values,
                (or any other open lists of autocompleted values:*/
                closeAllLists();
            });
            a.appendChild(b);
          }
        }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
          /*If the arrow DOWN key is pressed,
          increase the currentFocus variable:*/
          currentFocus++;
          /*and and make the current item more visible:*/
          addActive(x);
        } else if (e.keyCode == 38) { //up
          /*If the arrow UP key is pressed,
          decrease the currentFocus variable:*/
          currentFocus--;
          /*and and make the current item more visible:*/
          addActive(x);
        } else if (e.keyCode == 13) {
          /*If the ENTER key is pressed, prevent the form from being submitted,*/
          e.preventDefault();
          if (currentFocus > -1) {
            /*and simulate a click on the "active" item:*/
            if (x) x[currentFocus].click();
          }
        }
    });
    function addActive(x) {
      /*a function to classify an item as "active":*/
      if (!x) return false;
      /*start by removing the "active" class on all items:*/
      removeActive(x);
      if (currentFocus >= x.length) currentFocus = 0;
      if (currentFocus < 0) currentFocus = (x.length - 1);
      /*add class "autocomplete-active":*/
      x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
      /*a function to remove the "active" class from all autocomplete items:*/
      for (var i = 0; i < x.length; i++) {
        x[i].classList.remove("autocomplete-active");
      }
    }
    function closeAllLists(elmnt) {
      /*close all autocomplete lists in the document,
      except the one passed as an argument:*/
      var x = document.getElementsByClassName("autocomplete-items");
      for (var i = 0; i < x.length; i++) {
        if (elmnt != x[i] && elmnt != inp) {
          x[i].parentNode.removeChild(x[i]);
        }
      }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
  }

</script>

<h1 align="center">Jeux De Mots</h1>

<form align="center" autocomplete="off" method="post">

  <div class="autocomplete" style="width:300px;">
    <input id="mot" type="text" name="mot" placeholder="Word">
  </div>
  <input type="submit">
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
        <table id="TSortant" class="table table-hover table-striped table-bordered table-sm" cellspacing="0">
          <thead>
            <tr>
              <th class="th-sm">Importance
              </th>
              <th class="w-auto">Mot sortant
              </th>
              <th class="w-auto">Type
                </th>
              </tr>
            </thead>
            <tbody  >

            </tbody>
            <tfoot>
              <tr>
                <th>Importance
                </th>
                <th>Mot sortant
                </th>
                <th>Type
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="col">
          <table id="TEntrant" class="table table-hover table-striped table-bordered table-sm" cellspacing="0">
            <thead>
              <tr>
                <th class="th-sm">Importance
                </th>
                <th class="w-auto">Mot entrant
                </tr>
              </thead>
              <tbody>

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
