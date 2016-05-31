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
        <h1> <a href="./"> eticket</a></h1>
      </header>

      <h3>ajouter le nom d'une collone au mapping</h3>

      <ul>
        <?php
          $ini_array = parse_ini_file("bdd.ini",true);
          include 'request.php';
          /**************************************************************/
          /*
          /*TITRE:addmap
          /*
          /*description:ajoute une ligne dans la table de log
          /*
          /*ENTREES:le nom de la collone csv $nomcsv, le nom de la collone sql $nomsql, et si elle est a ignorer
          /*
          /*SORTIES:
          /*-bdd log
          /*
          /***************************************************************/
          function addmap($nomcsv,$nomsql,$ignore)
            {
              $req = $GLOBALS['bdd']->prepare($GLOBALS['insert_map']);
              $req->execute(array('map_sql' => $nomsql, 'map_csv' => $nomcsv, 'map_ignore' => $ignore));
            }
/**********************************************************************/
/**********************************************************************/
          if (!empty($_POST)) {
            $bdd=bddacces();
            $nomcsv=$_POST['namecsv'];
            $nomsql=$_POST['namesql'];
            $ignore=$_POST['ignorer'];
            if (!$nomcsv=="") {
              addmap($nomcsv,$nomsql,$ignore);

            }else{?>
              <div class="alert alert-warning">
                <strong>Warning!</strong> Aucun nom de collone csv n'a était mis.
              </div>
            <?php
            }
          }
/**********************************************************************/
/**********************************************************************/
          if (!isset($bdd)) {
            $bdd=bddacces();
          }
          $groupactuel = "";
          $tableaurecap = "";
          $reponse = $bdd->query($corres_csv_sql_order);
          while ($donnees = $reponse->fetch())
          {
            if ($groupactuel=="") {
              $groupactuel = $donnees['map_sql'];
              echo "<li>".$groupactuel. " = ".$donnees['map_csv'];
            }elseif ($groupactuel==$donnees['map_sql']) {
              echo " ; ".$donnees['map_csv'];
            }else{
              $groupactuel = $donnees['map_sql'];
              echo "</li> <li>".$groupactuel. " = ".$donnees['map_csv'];
            }
          }
          $reponse->closeCursor();
          echo "</li>";
        ?>
      </ul>

      <form action="addmapcolonne.php" method="post">
        <input type="text" name="namecsv">
        <select width="50" name="namesql">
          <?php
            foreach ($name_collonne_sql as $key => $name) {
          ?>
          <option value=<?php echo $name; ?>> <?php echo $name; ?></option>
          <?php
            }
            $reponse->closeCursor();
          ?>
        </select>
        <select width="50" name="ignorer">
          <option value=0> ne pas ignorer</option>
          <option value=1> ignorer</option>
        </select>
        <input type="submit" onclick="return confirm('Attention tout ajout est définitif.');"/>
      </form>
    </div>
  </body>
</html>
