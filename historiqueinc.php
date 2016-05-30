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

      <header class="page-header" href="./">
        <h1><a href="./">eticket</a></h1>
      </header>
      <h3>historique d'un incidents</h3>

      <form action="majinc.php" method="post">
        <select width="50" name="codeid">
        <?php

          /*****************************************************************/
          /*****************************************************************/
          /*
          /*NOM DU FICHIER : history.php
          /*
          /*description:regroupe l'ensemble des fonctions lier au formulaire de l'historique d'un incident.
          /*
          /*contient:
          /*-elements communs aux fonctions
          /*
          /*****************************************************************/
          /*****************************************************************/


          /*****************************************************************/
          /*
          /*elements communs aux fonctions(lier a la base de données)
          /*
          /*****************************************************************/

          $ini_array = parse_ini_file("bdd.ini",true);
          include_once 'request.php';

          /*****************************************************************/
          /*****************************************************************/


          //printdata();
          $bdd=bddacces();
          $reponse = $bdd->query($select_code);
          while ($donnees = $reponse->fetch())
          {

            echo "<option value=".$donnees['inc_code']."> ".$donnees['inc_code']."</option>";
          }
          $reponse->closeCursor();
        ?>
        </select>

        <textarea name="textaction"></textarea>
        <input type="submit" />
      </form>
    </div>
  </body>
