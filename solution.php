<?php
require_once './ACK.php';

/**
 * Class for saving of solution
 * @author David Salac
 */
class solution {
    private $dbh;
    /**
     * Configure database
     */
    public function __construct() {
        require_once './Config.php';
        $this->dbh = new PDO(MYSQL_SERVER, /* DATABASE NAME */
        MYSQL_DATABASE_USER, /* DATABASE USER */
        MYSQL_DATABASE_PASSWORD); /* DATABASE PASSWORD */
    }
    /**
     * Save incoming solution
     */
    public function saveSolutin() {
        try {
            $type = $_POST['type'];
            $taskId = $_POST['taskId'];
            $time = $_POST['time'];
            $stationId = $_POST['stationId'];

            //Diffie-Hellman
            $dhA = $_POST['a'];
            $dhSharedKey = $_POST['sharedKey'];

            //RSA
            $rsaM = $_POST['m'];
            $rsaD = $_POST['d'];

            //ElGamal
            $elX = $_POST['x'];
            $elM = $_POST['m'];

            $insertP = $this->dbh->prepare("UPDATE task SET task_solved=TRUE, task_priority='suspend', task_solving_time=:time, task_solving_station=:station, "
                    . "task_rsa_m=:rsam, task_rsa_d=:rsad, "
                    . "task_elgamal_x=:elx, task_elgamal_m=:elm, "
                    . "task_dh_a=:dha, task_dh_common_key=:dhck "
                    . " WHERE task_id=:id");
            
            $insertP->bindParam(":id", $taskId, PDO::PARAM_INT);
            $insertP->bindParam(":time", $time, PDO::PARAM_INT);
            $insertP->bindParam(":station", $stationId, PDO::PARAM_INT);
            
            
            $insertP->bindParam(":rsam", $rsaM, PDO::PARAM_STR);
            $insertP->bindParam(":rsad", $rsaD, PDO::PARAM_STR);
            

            $insertP->bindParam(":elx", $elX, PDO::PARAM_STR);
            $insertP->bindParam(":elm", $elM, PDO::PARAM_STR);

            $insertP->bindParam(":dha", $dhA, PDO::PARAM_STR);
            $insertP->bindParam(":dhck", $dhSharedKey, PDO::PARAM_STR);

            $insertP->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}


if(isset($_POST['ACK'])) {
    //For ACK:
    $positiveAck = new ACK((int)$_POST['taskId']);
    $positiveAck->saveAcknowledgment();
} else {
    //Script start:
    $sol = new solution();
    $sol->saveSolutin();
}
