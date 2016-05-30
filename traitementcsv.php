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
      <h3>rapport d'erreur</h3>
      <a href="./listincident.php">Retour a la liste ses incidents</a>
      <br>
      <br>
      <br>
      <?php
        date_default_timezone_set('UTC');
        /*********************************************************************/
        /*********************************************************************/
        /*
        /*NOM DU FICHIER : eabasecsv.php
        /*
        /*description:regroupe l'ensemble des fonctions lier aux stokage des donné des csv
        /*
        /*contient:
        /*-elements communs aux fonctions
        /*-extractcsvdata
        /*-verifheader
        /*-stockagecsv
        /*-formatedate
        /*
        /*********************************************************************/
        /*********************************************************************/


        /*********************************************************************/
        /*
        /*elements communs aux fonctions(lier a la base de données)
        /*
        /*
        /*********************************************************************/

        $ini_array = parse_ini_file("bdd.ini",true);
        include 'request.php';




        /*********************************************************************/
        /*
        /*TITRE:extractcsvdata
        /*
        /*description:extrait les données du csv
        /*
        /*ENTREES:chemin relatif du csv $csvfile
        /*
        /*SORTIES:
        /*-array des données du csv
        /*
        /*********************************************************************/

        function extractcsvdata($csvfile)
        {
          $file = fopen($csvfile,"r");
          while(! feof($file)){
              $data[]=fgetcsv($file,"",";");
          }
          fclose($file);
          return $data;
        }

        /*********************************************************************/
        /*
        /*TITRE:formatedate
        /*
        /*description:formate les date presente dans le csv
        /*
        /*ENTREES:donné a reformater
        /*
        /*SORTIES:
        /*-date au bon format yyyy-mm-dd hh:mm:ss
        /*
        /*********************************************************************/

        function formatedate($csvdatetime)
        {
          $regexcsvdatetime="^[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}$";
          if (preg_match("#($regexcsvdatetime)#", $csvdatetime)){
            $jour=substr($csvdatetime,0,2);
            $mois=substr($csvdatetime,3,2);
            $annee=substr($csvdatetime,6,4);
            $temp=substr($csvdatetime,10);
            $datevalid=$annee."-".$mois."-".$jour.$temp;
          }else{
            $liste_datetime = explode("/",$csvdatetime);
            if (strlen($liste_datetime[0])==1) {
              $mois="0".$liste_datetime[0];
            }else{
              $mois=$liste_datetime[0];
            }
            if (strlen($liste_datetime[1])==1) {
              $jour="0".$liste_datetime[1];
            }else{
              $jour=$liste_datetime[1];
            }
            $liste_annee_time = explode(" ",$liste_datetime[2]);
            $annee=$liste_annee_time[0];
            $liste_hms = explode(":",$liste_annee_time[1]);
            if ($liste_annee_time[2]=="PM") {
              $liste_hms[0]=$liste_hms[0]+12;
            }elseif (strlen($liste_hms[0])==1) {
              $liste_hms[0]="0".$liste_hms[0];
            }
            $datevalid=$annee."-".$mois."-".$jour." ".implode(":", $liste_hms);
          }
          return $datevalid;
        }


        /*********************************************************************/
        /*
        /*TITRE:verifheader
        /*
        /*description:veriffie les entetes a ne pas ignoré(vérifie que le header soit valide)
        /*
        /*ENTREES:les données du csvet la bdd
        /*
        /*SORTIES:un array des colonnes et la requete preparé
        /*
        /*********************************************************************/

        function verifheader($donneescsv,$bdd)
        {
          $header=$donneescsv[0];
          $nb_col_tot=count($header);
          $requete_final=$GLOBALS["base_insert_inc"];
          $requete_prepare=$GLOBALS["values_insert_inc"];
          $correct_header=[];
          $nb_col_valid=0;
          $reponse = $bdd->query($GLOBALS["corres_csv_sql"]);

          while ($donnees = $reponse->fetch()){
            foreach ($header as $key => $value){
              if ($value == $donnees["map_csv"]){
                $nb_col_valid=$nb_col_valid+1;
                $requete_final=$requete_final.$donnees["map_sql"].", ";
                $requete_prepare=$requete_prepare.":".$donnees["map_sql"].", ";
                $correct_header[$key+1]=$donnees["map_sql"];
              }
            }
          }
          $reponse->closeCursor();

          $requete_final=$requete_final.")";
          $requete_prepare=$requete_prepare.")";
          $requete_final=str_replace(", )", ")", $requete_final);
          $requete_prepare=str_replace(", )", ")", $requete_prepare);
          $requete_final=$requete_final.$requete_prepare;
          $correct_header["requete_final"]=$requete_final;
          $type = "header valide";
          $title= "sur ".$nb_col_tot." lignes ".$nb_col_valid." sont valide";
          addlog($type, $title);
          return $correct_header;
        }


        /*****************************************************************/
        /*
        /*TITRE:stockagecsv
        /*
        /*description:stoc
        /*
        /*ENTREES:Le fichier csv
        /*
        /*SORTIES:
        /*-Le deplacement du csv
        /*-un log
        /*-le nom final
        /*
        /*****************************************************************/

        function stockagecsv($csvfile)
        {
          $nom_destination = "";
          $nom_origine = $csvfile['name'];

          if (!$nom_origine=="") {
            $nom_destination = date("Ymd")."_".$nom_origine;
            if (is_uploaded_file($csvfile["tmp_name"])) {
              if(file_exists($GLOBALS["ini_array"]["destination"]["crea_dest_csv"])){
                if (move_uploaded_file($csvfile['tmp_name'], $GLOBALS["ini_array"]["destination"]["crea_dest_csv"].$nom_destination)) {
                  echo "Le fichier ".$nom_origine." a été déplacé";
                }else{
                  echo "Le déplacement du fichier temporaire a échoué.";
                }

              } else {
                echo "Le déplacement du fichier temporaire a échoué vérifiez l'existence du répertoire ";
                $erreur_title = "chemin invalide";
                addlog("erreur", $erreur_title);
                $nom_destination = "";
              }
            }
          }else {
            echo "Le fichier n'a pas été uploadé ";
            $erreur_title = "Le fichier n'a pas été uploade";
            addlog("erreur", $erreur_title);
          }
          return $nom_destination;
        }

        /*****************************************************************/
        /*
        /*TITRE:insertline
        /*
        /*description:traitement d'une ligne importé
        /*
        /*ENTREES:l'array de ligne
        /*
        /*SORTIES:
        /*
        /*-insert bdd
        /*
        /*****************************************************************/

        function insertline($keyline, $line, $correct_header)
        {
          $requete_execute = [];
          foreach ($correct_header as $c_h_key => $c_h_value){
            if ($c_h_key != "requete_final" ) {
              foreach ($line as $lkey => $lvalue) {
                if ($c_h_key == $lkey+1) {
                  if ($c_h_value=="inc_sta_id") {
                    $req = $GLOBALS["bdd"]->prepare($GLOBALS["staid"]);
                    $req->execute(array('staname' => $lvalue));
                    $lvalue=$req->fetchAll();
                    $lvalue=$lvalue[0][0];
                    $req->closeCursor();
                  }elseif ($c_h_value=="inc_prio_id") {
                    $lvalue=$lvalue[0];

                  }elseif ($c_h_value=="inc_ass_id") {
                    echo $lvalue." ";
                    $req = $GLOBALS["bdd"]->prepare($GLOBALS["assid"]);
                    $req->execute(array('ass_name' => $lvalue));
                    $lvalue=$req->fetchAll();
                    $lvalue=$lvalue[0][0];
                    $req->closeCursor();
                    $req->closeCursor();
                  }elseif ($c_h_value=="inc_clc_id") {
                    $req = $GLOBALS["bdd"]->prepare($GLOBALS["clcid"]);
                    $req->execute(array('clc_name' => $lvalue));
                    $lvalue=$req->fetchAll();
                    $lvalue=$lvalue[0][0];
                    $req->closeCursor();
                  }elseif ($c_h_value=="inc_solved" || $c_h_value=="inc_workinprogress" || $c_h_value=="inc_closed" || $c_h_value=="inc_opentime" || $c_h_value=="inc_nextexp") {
                    $lvalue=formatedate($lvalue);
                  }
                  $requete_execute[$c_h_value]=$lvalue;
                }
              }
            }else{
              $doublon=0
              $reponse = $bdd->query($verif_unique_inc);
              while ($donnees = $reponse->fetch())
              {
                if ($donnees['inc_code']==$requete_execute['inc_code']) {
                  $doublon = 1
                }
              }
              if ($doublon==0) {
              $req = $GLOBALS['bdd']->prepare($c_h_value);
              $req->execute($requete_execute);
              addlog("BDD", "La ligne ".$keyline." a bien été ajouté dans la base de donnée.");
              }
            }
          }
        }

        /****************************************************************/
        /****************************************************************/
        if (!empty($_POST)) {
          $bdd=bddacces();
          $nomcsv=stockagecsv($_FILES['fichiercsv']);
          if (!$nomcsv=="") {

            $lines=extractcsvdata($ini_array["destination"]["crea_dest_csv"].$nomcsv);
            $result = count($lines);

            $correct_header=verifheader($lines,$bdd);

            foreach ($lines as $key => $value) {//ou
              if (!($key == 0) ) {
                if (!$value==[]) {
                  insertline($key, $value, $correct_header);
                }
              }
            }
          }else{
            ?>
          <div class="alert alert-warning">
            <strong>Warning!</strong> Aucun fichier n'a était soumis.
          </div>

        <?php
          }
          }else{
        ?>
        <div class="alert alert-warning">
          <strong>Warning!</strong> Vous avez accedé a cette page sans passer par le formulaire d'envoi de csv.
        </div>

      <?php
        }
      ?>

      </div>

  </body>
</html>
