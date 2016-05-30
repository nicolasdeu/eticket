<meta charset="UTF-8">
<?php

/*********************************************************************/
/*********************************************************************/
/*
/*NOM DU FICHIER : request.php
/*
/*description:regroupe l'ensemble des requetes et element liée au tables et a la base de données
/*
/*********************************************************************/
/*********************************************************************/


/**********************************************************************/
/*
/*inc request
/*
/**********************************************************************/
    /*insert
    /******************************************************************/
    $base_insert_inc="INSERT INTO `inc_incidents`(";
    $values_insert_inc=" VALUES (";

    /******************************************************************/
    /*select
    /******************************************************************/
    $select_code = "SELECT `inc_code` FROM `inc_incidents`";
    $verif_unique_inc = "SELECT `inc_code` FROM `inc_incidents` WHERE inc_code=:inc_code";

    $select_global_inc = "SELECT `inc_code`,`inc_source`,`inc_title`,`inc_datesource`,`inc_description`,`inc_nextexp`,`inc_opentime`,`inc_solved`,`inc_sta_id`,`inc_ass_id`,`inc_clc_id`,`inc_region`,`inc_country`,`inc_email`,`inc_workinprogress`,`inc_as_id`,`inc_age`,`inc_closed`  FROM `inc_incidents`";

    $name_collonne=["Code de l'incident", "Source","Titre","Date","description","next expiration","open time","date de résolution","id statut","id du groupe","id du closurcode","region concerné","pays concerné","email","date de mise en workinprogress","id assigné","age","date de cloture"]; #même ordre que le select.
    $name_collonne_sql=["inc_code", "inc_source", "inc_title", "inc_datesource", "inc_description", "inc_nextexp", "inc_opentime", "inc_solved", "inc_sta_id", "inc_ass_id", "inc_clc_id", "inc_region", "inc_country", "inc_email", "inc_workinprogress", "inc_as_id", "inc_age", "inc_closed"];

    $select_alu_wip = "SELECT `inc_code`, `inc_workinprogress`  FROM `inc_incidents` WHERE inc_code=:inc_code" ;

    $select_open_inc = "SELECT `inc_opentime`  FROM `inc_incidents` WHERE inc_code=:inc_code" ;

    /******************************************************************/
    /*update
    /******************************************************************/
    $base_condition_inc_update="ON DUPLICATE KEY UPDATE";
    $update_dateass="UPDATE `inc_incidents` SET inc_dateass=:inc_dateass WHERE `inc_incidents`.`inc_code` = :inc_code";

    $update_solved="UPDATE `inc_incidents` SET inc_solved=:inc_solved WHERE `inc_incidents`.`inc_code` = :inc_code";

    $update_workinprogress="UPDATE `inc_incidents` SET inc_workinprogress=:inc_workinprogress WHERE `inc_incidents`.`inc_code` = :inc_code";


/**********************************************************************/
/*
/*map request
/*
/**********************************************************************/
    /*insert
    /******************************************************************/
    $insert_map = "INSERT INTO map_csvtosql(map_sql, map_csv,map_ignore) VALUES(:map_sql, :map_csv, :map_ignore)";

    /******************************************************************/
    /*select
    /******************************************************************/
    $corres_csv_sql = "SELECT map_sql,map_csv FROM map_csvtosql WHERE map_ignore=0";

    $corres_csv_sql_order = "SELECT map_sql,map_csv FROM map_csvtosql WHERE map_ignore=0 ORDER BY `map_csvtosql`.`map_sql` ASC";


/**********************************************************************/
/*
/*log request
/*
/**********************************************************************/
    /*insert
    /******************************************************************/
    $insert_log = "INSERT INTO log_logs(log_type, log_title) VALUES(:log_type, :log_title)";

    /*********************************************************************/
    /*
    /*TITRE:addlog
    /*
    /*description:ajoute une ligne dans la table de log
    /*
    /*ENTREES:le type de log $logtype, et son titre $logtitle
    /*
    /*SORTIES:
    /*-bdd log
    /*
    /*********************************************************************/

    function addlog($logtype,$logtitle)
    {
        $req = $GLOBALS['bdd']->prepare($GLOBALS['insert_log']);
        $req->execute(array('log_type' => $logtype,'log_title' => $logtitle));
    }
    
    function initlog($logtype,$logtitle,$logid)
    {
        $req = $GLOBALS['bdd']->prepare($GLOBALS['insert_log']);
        $req->execute(array('log_type' => $logtype,'log_title' => $logtitle));
    }

/**********************************************************************/
/*
/*temp request
/*
/**********************************************************************/
    /*insert
    /******************************************************************/
    $insert_temp="INSERT INTO temp(inc_code_id, actiondate, type, operator, description ) VALUES(:inc_code_id, :actiondate, :type, :operator, :description )";

    /******************************************************************/
    /*select
    /******************************************************************/
    $select_global_temp = "SELECT `inc_code_id`, `actiondate` , `type`, `operator`, `description` FROM `temp` WHERE `temp`.`type` NOT LIKE'%Notification%' ORDER BY `temp`.`actiondate` ASC ";

    $select_waiting_temp = "SELECT `inc_code_id`, `actiondate` , `type`, `operator`, `description` FROM `temp` WHERE `type` LIKE 'Waiting%' ORDER BY `temp`.`actiondate` DESC  ";

    $select_first_reasign="SELECT `inc_code_id`, `actiondate` , `type`, `operator`, `description` WHERE `temp`.`type` LIKE'%Open%'";

    /******************************************************************/
    /*truncate
    /******************************************************************/
    $truncate_temp="TRUNCATE temp";


/**********************************************************************/
/*
/*clef etrangeres
/*
/**********************************************************************/
    /******************************************************************/
    /*select
    /******************************************************************/
    $assid="SELECT `ass_id` FROM `ass_assignments` WHERE ass_name=:ass_name";
    $group_ass="SELECT `ass_name` FROM `ass_assignments`";

    $asid="SELECT `as_id` FROM `as_assignee` WHERE as_mail=:as_mail";
    $person_as="SELECT `as_mail` FROM `as_assignee`";

    $clcid="SELECT `clc_id` FROM `clc_closurecode` WHERE clc_ccr=:clc_name";


    $staid="SELECT `sta_id` FROM `sta_status` WHERE sta_name=:staname";
    $status="SELECT `sta_name` FROM `sta_status` ORDER BY `sta_status`.`sta_id`";




/*********************************************************************/
/*
/*TITRE:bddacces
/*
/*description:connection a la base de données
/*
/*ENTREES:
/*
/*SORTIES:
/*renvoi la base de données
/*si erreur retourne false
/*
/*********************************************************************/

    function bddacces()
    {
        try {
            $bdd = new PDO($GLOBALS['ini_array']["acces"]["path"],$GLOBALS['ini_array']["acces"]["user"],$GLOBALS['ini_array']["acces"]["password"]);
        return $bdd;
        } catch (Exception $e) {
            addlog('erreur : ',$e->getMessage());
            die('Erreur : ' . $e->getMessage());
            return false;
        }
    }

?>
