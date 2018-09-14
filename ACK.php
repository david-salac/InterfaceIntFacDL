<?php
/**
 * Class for saving of positive acknowledgment of task receiving (by application SaFaDl)
 * @author David Salac
 */
class ACK {
    private $dbh;
    private $taskId;
    /**
     * Configure database and task ID
     * @param int $id Identification of task
     */
    public function __construct(int $id) {
        $this->taskId = $id;
        
        require_once './Config.php';
        $this->dbh = new PDO(MYSQL_SERVER, /* DATABASE NAME */
        MYSQL_DATABASE_USER, /* DATABASE USER */
        MYSQL_DATABASE_PASSWORD); /* DATABASE PASSWORD */
    }
    /**
     * Save positive acknowledgment (ACK), that mean time of last activity of task
     */
    public function saveAcknowledgment() {
        try {
            $updateP = $this->dbh->prepare("UPDATE task SET task_last_active=from_unixtime(:time) WHERE task_id=:id");
            $updateP->bindParam(":id", $this->taskId, PDO::PARAM_INT);
            $updateP->bindParam(":time", time(), PDO::PARAM_INT);
            $updateP->execute();
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
