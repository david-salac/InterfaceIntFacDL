<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script is useful for handling stations in system
 */

/**
 * Handling of stations in system
 * @author David Salac
 */
class StationsPage extends Page {
    
    /**
     * Insert or update station in system
     * @param string $id ID of station for update
     * @return string Error message
     */
    private function saveStation(string $id = null) : string {
        $station_id = (string) $_POST['station_id'];
        $task = (string) $_POST['task'];
        if($id == null) {
            try {
                $insertS = $this->getDBH()->prepare("INSERT INTO station VALUES(:id, FROM_UNIXTIME(:created), NULL, :task)");
                $insertS->bindParam(":id", $station_id, PDO::PARAM_STR);
                $insertS->bindParam(":created", time(), PDO::PARAM_INT);
                if($task == "null") {
                    $taskNul = null;
                    $insertS->bindParam(":task", $taskNul, PDO::PARAM_NULL);
                } else {
                    $insertS->bindParam(":task",$task, PDO::PARAM_INT);
                }
                $insertS->execute();
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        } else {
            try {
                $insertS = $this->getDBH()->prepare("UPDATE station SET station_id=:id, station_task=:task WHERE station_id=:old LIMIT 1");
                $insertS->bindParam(":id", $station_id, PDO::PARAM_STR);
                $insertS->bindParam(":old", $id, PDO::PARAM_STR);
                if($task == "null") {
                    $taskNul = null;
                    $insertS->bindParam(":task", $taskNul, PDO::PARAM_NULL);
                } else {
                    $insertS->bindParam(":task",$task, PDO::PARAM_INT);
                }
                $insertS->execute();
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        }
        return "";
    }
    
    /**
     * Delete station in system
     * @param string $id Identification of station
     * @return string Error log
     */
    private function deleteStation(string $id) : string {
        $delete = $this->getDBH()->prepare("DELETE FROM station WHERE station_id=:stationid");
        
        try {
            $delete->bindParam(":stationid", $id, PDO::PARAM_STR);
            $delete->execute();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }

    /**
     * Create instance of class and commit required action
     */
    public function __construct() {
        parent::__construct();
        
        $this->setPageTitle("Stations");
        if(isset($_POST['action'])) {
            if($_POST['action'] == "insert-station") {
                $this->addErrorMessage($this->saveStation());
            } else if($_POST['action'] == "edit-station") {
                $this->addErrorMessage($this->saveStation($_POST['old_id']));
            } else if($_POST['action'] == "delete-station") {
                $this->addErrorMessage($this->deleteStation($_POST['stationid']));
            }
        }
        
    }
    
    /**
     * Plot the form for inserting or editing of station
     * @param string $id Edited station ID
     */
    private function plotStationsForm(string $id = null) {
        $this->plotBoxStart();
        if($id == null) {
        echo '<h3 id="insert-station">Insert station (node) to system</h3>';
            echo '<form action="stations.html" method="post">';
                echo '<input type="hidden" name="action" value="insert-station">';
                echo '<label for="station_id">Station ID (unique):</label>';
                echo '<input type="text" pattern="[\d]+" id="station_id" name="station_id" maxlength="9" required placeholder="Station ID (number less than 1 000 000 000)">';
                echo '<label for="task">Assigned task:</label>';
                echo '<select id="task" name="task">';
                echo '<option value="null">No assigned task</option>';
                $tasks = $this->getDBH()->query("SELECT DATE_FORMAT(task_time,'%Y/%m/%d %H:%i:%S') AS time, task_time, task_type, task_id FROM task ORDER BY task_id DESC, task_type, task_time");
                while($task = $tasks->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="'.$task['task_id'].'">ID: '.$task['task_id'].', type: '.$task['task_type'].' inserted on '.$task['time'].'</option>';
                }
                echo '</select>';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="INSERT" id="submitF">';
            echo '</form>';
        }
        else {
            $stations = $this->getDBH()->prepare("SELECT * FROM station WHERE station_id=:id");
            $stations->bindParam(":id", $id, PDO::PARAM_STR);
            $stations->execute();
            $station = $stations->fetch(PDO::FETCH_ASSOC);
            
            echo '<h3 id="edit-station">Edit station (node) in system</h3>';
            echo '<form action="stations.html" method="post">';
                echo '<input type="hidden" name="action" value="edit-station">';
                echo '<input type="hidden" name="old_id" value="'.$station['station_id'].'">';
                echo '<label for="station_id">Station ID (unique):</label>';
                echo '<input type="text" pattern="[\d]+" id="station_id" name="station_id"  value="'.$station['station_id'].'" maxlength="9" required placeholder="Station ID (number less than 1 000 000 000)">';
                echo '<label for="task">Assigned task:</label>';
                echo '<select id="task" name="task">';
                echo '<option value="null">No assigned task</option>';
                $tasks = $this->getDBH()->query("SELECT DATE_FORMAT(task_time,'%Y/%m/%d %H:%i:%S') AS time, task_time, task_type, task_id FROM task ORDER BY task_id DESC, task_type, task_time");
                while($task = $tasks->fetch(PDO::FETCH_ASSOC)) {
                    if($task['task_id'] == $station['station_task']) {
                        echo '<option value="'.$task['task_id'].'" selected>ID: '.$task['task_id'].', type: '.$task['task_type'].' inserted on '.$task['time'].'</option>';
                    } else {
                        echo '<option value="'.$task['task_id'].'">ID: '.$task['task_id'].', type: '.$task['task_type'].' inserted on '.$task['time'].'</option>';
                    }
                }
                echo '</select>';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="EDIT" id="submitF">';
            echo '</form>';
        }
        $this->plotBoxEnd();
        
    }
    
    /**
     * Plot the list of all stations in system
     */
    private function plotStationsList() {
        $this->plotBoxStart();
        echo '<h3 id="list-stations">List of stations (nods) in system</h3>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<td>Station ID</td>';
        echo '<td>Assigned task</td>';
        echo '<td>Created</td>';
        echo '<td>Last activity</td>';
        echo '<td>Delete</td>';
        echo '</tr>';
        echo '<tbody>';
            $stations = $this->getDBH()->query("SELECT * FROM station ORDER BY station_id");
            while($station = $stations->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                    echo '<td>';
                    echo '<a href="index.php?id=stations&action=edit-station&stationid=' . $station['station_id'].'#edit-station" title="Click to edit">' . $station['station_id']."</a>";
                    echo '</td>';
                    echo '<td>';
                    echo $station['station_task'] == null ? "N / A" : $station['station_task'];
                    echo '</td>';
                    echo '<td>';
                    echo $station['station_created'];
                    echo '</td>';
                    echo '<td>';
                    echo ($station['station_last_activity'] == null ? "N / A" : $station['station_last_activity']);
                    echo '</td>';
                    echo '<td>';
                    echo '<a href="index.php?id=stations&action=delete-station&stationid=' . $station['station_id'].'#delete-station" title="Click to delete" > <span class="fa fa-times"></span> </a>';
                    echo '</td>';
                echo '</tr>';
            }
        echo '</tbody>';
        echo '</thead>';
        echo '</table>';
        $this->plotBoxEnd();
    }
    
    /**
     * Plot the content of page
     */
    protected function plotContent() {
        if($_GET['action'] == "delete-station") {
            $this->plotBoxStart('warning');
            echo '<p>';
            echo 'Do you really want to remove item <strong>';
            echo $_GET['stationid'];
            echo "</strong>?";
            echo '</p>';
            echo '<form action="stations.html" method="post">';
            echo '<a href="stations.html" class="button left">CANCEL</a>';
            echo '<input type="hidden" name="action" value="delete-station">';
            echo '<input type="hidden" name="stationid" value="'.$_GET['stationid'].'">';
            echo '<input type="submit" class="left" value="YES" >';
            echo '<div class="clear"></div>';
            echo '</form>';
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();
        }
        
        $this->plotBoxStart();
        echo '<p>';
        echo '<a href="/stations.html#insert-station" class="navbutton">INSERT STATION</a>'; 
        echo '<a href="/stations.html#list-stations" class="navbutton">LIST OF STATIONS</a>';
        echo '</p>';
        echo '<div class="clear"></div>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h2>Stations (nodes) of system</h2>';
        $this->plotStationsList();
        $this->plotStationsForm($_GET['action'] == 'edit-station' ? $_GET['stationid'] : null );
        $this->plotBoxEnd();
    }
}

