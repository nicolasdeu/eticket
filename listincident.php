<html>
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script type="text/javascript" src="assets/js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="assets/js/affichage.js"></script>
    <style type="text/css">
      body { background-color:#DDD; }
      [class*="col"] { margin-bottom: 20px; }
      img { width: 100%; }
      .well {
        background-color:#CCC;
        padding: 20px;
      }
    </style>
  </head>
  <body>

    <div class="container">
      <header class="page-header">
        <h1><a href="./">eticket</a></h1>
      </header>
      <h3>liste des incidents</h3>
      <?php

        /*****************************************************************/
        /*****************************************************************/
        /*
        /*NOM DU FICHIER : listincident.php
        /*
        /*description:regroupe l'ensemble des fonctions lier aux a l'affichage des incidents.
        /*
        /*contient:
        /*-elements communs aux fonctions
        /*-afficherheader
        /*-afficherincidents
        /*
        /****************************************************************/
        /****************************************************************/


        /****************************************************************/
        /*
        /*elements communs aux fonctions
        /*
        /****************************************************************/

        $ini_array = parse_ini_file("bdd.ini",true);
        include_once 'request.php';


        /****************************************************************/
        /*
        /*TITRE:printheader
        /*
        /*description:affiche le nom des collone en entete du tableaux
        /*
        /*ENTREES:
        /*
        /*SORTIES:
        /*affiche le header en html
        /*
        /****************************************************************/

        function printheader()
        {
          $listheader = $GLOBALS['name_collonne'];
          foreach ($listheader as $key => $nom_collone) {
            echo"<th>".$nom_collone."</th>";
          }
        }


        /****************************************************************/
        /****************************************************************/


        echo "<div class='table-responsive'>";
        echo "<table border='1' class='table'>";
        echo "<tr>";
        printheader();
        echo "</tr>";
        //printdata();
        $bdd=bddacces();
        $reponse = $bdd->query($GLOBALS["select_global_inc"]);
        while ($donnees = $reponse->fetch()){
          print_r($donnees);
          echo"<tr>";
          foreach ($donnees as $key => $value) {
            if (is_integer($key)) {//evite les doublons
              echo "<td>".$value."</td>";
            }
          }
          echo"</tr>";
        }
        echo "</table>";
        echo "</div>";
        $reponse->closeCursor();
      ?>

