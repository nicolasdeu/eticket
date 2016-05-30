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
      <h3>importer des incidents</h3>
      <?php
        date_default_timezone_set('UTC');
        /*****************************************************************/
        /*****************************************************************/
        /*
        /*NOM DU FICHIER : majinc.php
        /*
        /*description:regroupe l'ensemble des fonctions lier a la mise a jour de l'incident

        /*
        /*contient:
        /*-elements communs aux fonctions
        /*-formatedate
        /*-recupoperateur
        /*
        /*****************************************************************/
        /*****************************************************************/


        /*****************************************************************/
        /*
        /*elements communs aux fonctions
        /*
        /*****************************************************************/

        $ini_array = parse_ini_file("bdd.ini",true);
        include 'request.php';


        /*****************************************************************/
        /*
        /*TITRE:formatedate
        /*
        /*description:formate les date non conforme
        /*ENTREES:donné a reformater
        /*
        /*SORTIES:
        /*-date au bon format yyyy-mm-dd hh:mm:ss
        /*
        /*****************************************************************/

        function formatedate($csvdatetime)
        {
          $regexcsvdatetime="^[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}";
          if (preg_match("#($regexcsvdatetime)#", $csvdatetime)){
            $jour=substr($csvdatetime,0,2);
            $mois=substr($csvdatetime,3,2);
            $anne=substr($csvdatetime,6,4);
            $temp=substr($csvdatetime,10);
            $datevalid=$anne."-".$mois."-".$jour.$temp;
          }
          return $datevalid;
        }


        /*****************************************************************/
        /*
        /*TITRE:recupoperateur
        /*
        /*description:récupere les opérateur dans la base de donnée
        /*
        /*
        /*SORTIES:
        /*une expression réguliere composé des opérateur valide
        /*
        /*****************************************************************/

        function recupoperateur()
        {
          $regexoperateur="";
          $reponse = $GLOBALS['bdd']->query($GLOBALS["person_as"]);
          while ($donnees = $reponse->fetch())
          {
            $regexoperateur=$regexoperateur."(".$donnees['as_mail'].")|";
          }
          $reponse->closeCursor();
          $regexoperateur=substr($regexoperateur,0,-1);
          echo "<br>".$regexoperateur."<br>";
          return $regexoperateur;
        }


        /*****************************************************************/
        /*****************************************************************/

        $datetimebase="^[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}";
        $nonvide="^[A-Za-z0-9]";
        $nbgroup=0;
        $actionbyaction=[];
        $chainetest=$_POST['textaction'];
        $codeinc=$_POST['codeid'];
        $testlist = explode("\n",$chainetest);
        foreach ($testlist as $i => $value) {
          if (!preg_match("#($nonvide)#", $value)){
            unset($testlist[$i]);
          }elseif (preg_match("#($datetimebase)#", $value)){
            if (isset($nbcollone) && $nbcollone<5) {
              $actionbyaction[$nbgroup]["description"]="no description";
            }

            $nbgroup=$nbgroup+1;
            $nbcollone=1;
            $value=formatedate($value);
            $actionbyaction[$nbgroup]["inc_code_id"]=$codeinc;
            $actionbyaction[$nbgroup]["actiondate"]=$value;
          }elseif ($nbgroup==0) {
            unset($testlist[$i]);
          }else{
            $nbcollone=$nbcollone+1;
            switch ($nbcollone) {
              case 2:
                $actionbyaction[$nbgroup]["type"]=$value;
                break;
              case 3:
                $actionbyaction[$nbgroup]["operator"]=$value;
                break;
              case 5:
                $actionbyaction[$nbgroup]["description"]=$value;
                break;
              default :
                break;
            }
          }
        }
        $bdd = bddacces();
        $req = $GLOBALS['bdd']->prepare($insert_temp);
        foreach ($actionbyaction as $key => $value) {
          $req->execute($value);
        }

        $regexassgroup="UNTESTGROUPE-(UN)|(DEUX))";
        $list_operateurs=recupoperateur();


        echo "<table>";
        $reponse = $bdd->query($GLOBALS["select_global_temp"]);
        while ($donnees = $reponse->fetch()){
          foreach ($donnees as $key => $value) {
            if (is_integer($key)) {//evite les doublons
              echo "<td>".$value."</td>";
            }
          }
          echo"</tr>";
        }

        echo "<br>";


        $assigne_to_abase=0;
        $first_action=0;
        echo "<table>";
        $reponse = $bdd->query($GLOBALS["select_global_temp"]);
        while ($donnees = $reponse->fetch()){
          if (preg_match("#($regexassgroup)#", $donnees['description'])){
            $date_dateass = $donnees['actiondate'];
            $assigne_to_abase = 1;
            $first_action = 0;
          }elseif ( $assigne_to_abase==1 && $first_action==0 && preg_match("#($list_operateurs)#", $donnees['operator'])) {
            $workinprogress =$donnees['actiondate'];
            $first_action=1;
          }elseif($assigne_to_abase==1 && $first_action==1 && preg_match("#($list_operateurs)#", $donnees['operator']) && preg_match("#(Reassignment)#", $donnees['type']) ){
            $incsolved = $donnees['actiondate'];

          }
        }
        $reponse->closeCursor();

        $req = $bdd->prepare($GLOBALS['update_dateass']);
        $req->execute(array('inc_dateass' => $date_dateass,'inc_code_im' => $codeinc));

        $req = $bdd->prepare($GLOBALS['update_solved']);
        $req->execute(array('inc_solved' => $incsolved,'inc_code_im' => $codeinc));

        $req = $bdd->prepare($GLOBALS['update_workinprogress']);
        $req->execute(array('inc_workinprogress' => $workinprogress,'inc_code_id' => $codeinc));


        echo "<br>";
        echo "<br>";

        echo "<table>";
        $reponse = $bdd->query($GLOBALS["select_waiting_temp"]);
        while ($donnees = $reponse->fetch()){
          if (preg_match("#($list_operateurs)#", $donnees['operator'])){
            foreach ($donnees as $key => $value) {
              if (is_integer($key)) {//evite les doublons
                echo "<td>".$value."</td>";
              }
            }
            echo"</tr>";
          }
        }
        echo "<br>";
        echo "<br>";
        echo "</table>";
        $reponse->closeCursor();
        $bdd->query($truncate_temp);
      ?>

    </div>
  </body>
</html>