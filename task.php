<?php

/**
 * Generating tasks for system
 * @author David Salac
 */
class task {
    private $id;
    private $dbh;
    
    /**
     * Configure database
     * @param string $id Identification of connected station
     */
    public function __construct(string $id) {
        $this->id = (int)$id;
        require_once './Config.php';
        $this->dbh = new PDO(MYSQL_SERVER, /* DATABASE NAME */
        MYSQL_DATABASE_USER, /* DATABASE USER */
        MYSQL_DATABASE_PASSWORD); /* DATABASE PASSWORD */
    }
    
    public function plotTaskLine(int $taskId) : string {
        $taskQ = $this->dbh->query("SELECT * FROM task WHERE task_priority != 'suspend' AND task_id=$taskId LIMIT 1");
        $taskF = $taskQ->fetch(PDO::FETCH_ASSOC);
        if($taskF['task_type'] == "rsa") {
            return '{"taskId":"'.$taskF['task_id'].'","type":"RSA","n":"'.$taskF['task_rsa_n'].'","c":"'.$taskF['task_rsa_c'].'","e":"'.$taskF['task_rsa_e'].'"}';
        }
        else if($taskF['task_type'] == "elgamal") {
            return '{"taskId":"'.$taskF['task_id'].'","type":"ElGamal","p":"'.$taskF['task_elgamal_p'].'","g":"'.$taskF['task_elgamal_g'].'","h":"'.$taskF['task_elgamal_h'].'", "c1":"'.$taskF['task_elgamal_c1'].'", "c2":"'.$taskF['task_elgamal_c2'].'"}';
        }
        else if($taskF['task_type'] == "dh") {
            return '{"taskId":"'.$taskF['task_id'].'","type":"DH","p":"'.$taskF['task_dh_p'].'","g":"'.$taskF['task_dh_g'].'","gPowA":"'.$taskF['task_dh_pow_a'].'", "gPowB":"'.$taskF['task_dh_pow_b'].'"}';
        }
        return "";
    }
    
    /**
     * Generate JSON string with task
     * @return string JSON task string
     */
    public function generateOutput() : string {
        $stationQ = $this->dbh->query("SELECT station_task FROM station JOIN task ON task_id=station_task WHERE task_solved=FALSE AND station_task IS NOT NULL AND station_id=".$this->id);
        if($stationQ->rowCount() > 0) {
            $stationTask = $stationQ->fetch(PDO::FETCH_ASSOC);
            return $this->plotTaskLine($stationTask['station_task']);
        } else {
            $taskQ = $this->dbh->query("SELECT * FROM task WHERE task_priority != 'suspend' AND task_solved=FALSE ORDER BY task_priority DESC, (task_last_active IS NOT NULL), task_last_active, task_time LIMIT 1");
            $taskF = $taskQ->fetch(PDO::FETCH_ASSOC);
            
            //NEW STATION TASK QUERY:
            $this->dbh->query("UPDATE station SET station_task=".$taskF['task_id']." WHERE station_id=".$this->id);
            //-----------------------
            
            if($taskF['task_type'] == "rsa") {
                return '{"taskId":"'.$taskF['task_id'].'","type":"RSA","n":"'.$taskF['task_rsa_n'].'","c":"'.$taskF['task_rsa_c'].'","e":"'.$taskF['task_rsa_e'].'"}';
            }
            else if($taskF['task_type'] == "elgamal") {
                return '{"taskId":"'.$taskF['task_id'].'","type":"ElGamal","p":"'.$taskF['task_elgamal_p'].'","g":"'.$taskF['task_elgamal_g'].'","h":"'.$taskF['task_elgamal_h'].'", "c1":"'.$taskF['task_elgamal_c1'].'", "c2":"'.$taskF['task_elgamal_c2'].'"}';
            }
            else if($taskF['task_type'] == "dh") {
                return '{"taskId":"'.$taskF['task_id'].'","type":"DH","p":"'.$taskF['task_dh_p'].'","g":"'.$taskF['task_dh_g'].'","gPowA":"'.$taskF['task_dh_pow_a'].'", "gPowB":"'.$taskF['task_dh_pow_b'].'"}';
            }
        }
        return "";
    }
    
    /**
     * Save informations about activity of station
     */
    public function saveStationActivity() {
        $updateP = $this->dbh->prepare("UPDATE station SET station_last_activity=from_unixtime(:time) WHERE station_id=:id");
        $updateP->bindParam(":id", $this->id, PDO::PARAM_INT);
        $updateP->bindParam(":time", time(), PDO::PARAM_INT);
        $updateP->execute();
    }
}

//Script start:
$init = new task($_GET['id']);
echo $init->generateOutput();
$init->saveStationActivity();