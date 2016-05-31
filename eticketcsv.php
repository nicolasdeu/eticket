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
      <h3>importer des incidents</h3>

      <p>nom des collones dont les données seront stockées:</p>
      <button class="hide_liste_collones">afficher/cacher nom des collones</button>
      <div id="liste_collones">
      <ul>
        <?php

          $ini_array = parse_ini_file("bdd.ini",true);
          include 'request.php';

          $bdd=bddacces();
          $groupactuel = "";
          $reponse = $bdd->query($corres_csv_sql_order);
          while ($donnees = $reponse->fetch())
          {
            if ($groupactuel=="") {
              $groupactuel = $donnees['map_sql'];
              echo "<li>".$donnees['map_csv'];
            }elseif ($groupactuel==$donnees['map_sql']) {
              echo " ou ".$donnees['map_csv'];
            }else{
              $groupactuel = $donnees['map_sql'];
              echo "</li> <li>".$donnees['map_csv'];
            }
          }
          $reponse->closeCursor();
          echo "</li>";
        ?>
      </ul>
      </div>

        <p>contraintes pour stocké un incident</p>
        <button class="hide_contrainte_csv">afficher/cacher les contrainte</button>
        <div id="contrainte_csv" >
          <ul>
            <li>Le code de l'incident doit etre au format "IncodeXXXXXXXXX"ou   chaque X est un chiffre</li>
            <li>Les dates doivent etres soit aux format "jj/mm/aaaa hh:mm:ss" ou  "(m)m/(j)j/aaaa (h)h:mm:ss (AM ou PM)"</li>
            <li>Pour les collones conssernant la "priorité" seul le chiffre (de 1 a 4) en premiere position compte</li>
            <li>les noms des status valides sont:
              <ul>
                <?php
                  $reponse = $bdd->query($status);
                  while ($donnees = $reponse->fetch())
                  {
                    echo "<li>".$donnees['sta_name']."</li>";
                  }
                  $reponse->closeCursor();
                ?>
              </ul>
            </li>
            <li>les groupes valides sont:
              <ul>
                <?php
                  $reponse = $bdd->query($group_ass);
                  while ($donnees = $reponse->fetch())
                  {
                    echo "<li>".$donnees['ass_name']."</li>";
                  }
                  $reponse->closeCursor();
                ?>
              </ul>
            </li>
            <li>les personnes valides sont:
              <ul>
                <?php
                  $reponse = $bdd->query($person_as);
                  while ($donnees = $reponse->fetch())
                  {
                    echo "<li>".$donnees['as_mail']."</li>";
                  }
                  $reponse->closeCursor();
                ?>
              </ul>
            </li>
          </ul>
        </div>

        <form enctype="multipart/form-data" action="traitementcsv.php" method="post" class="well">
          <legend>Transfère de fichier csv</legend>
          <input type="hidden" name="MAX_FILE_SIZE" accept=".csv" value="100000"  />
          <input type="file" accept=".csv" name="fichiercsv" />
          <input type="submit" />
        </form>

        <br>
        <p>Exemple de csv valide</p>
        <div id="exemple_csv">
          Incident ID;Autre colone;Title;Source of Call;Resolution Code;Status;Priority;Service Line;Caller Region;Caller Country;Caller email Address;Incident Resolving Workgroup;First Touch;Creation/Open time;Owner assigned time;Resolved time;Closed time

          <br>
          INC-0001;donnée inutile;Titre de l'incident;E-mail;Solved;Closed;3 Average;global service desk;Region;Country;un@email;UNGROUPUN;01/12/2015 03:43:00;01/12/2015 05:30:29;01/12/2015 05:30:48;30/12/2015 16:51:56;04/01/2016 16:52:51
        </div>

      </div><!-- container -->
  </body>
</html>
