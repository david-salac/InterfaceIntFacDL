<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script handling submitting of tasks in systems
 */
class TaskPage extends Page {
    
    private function deleteTask(string $id) : string {
        $update = $this->getDBH()->prepare("UPDATE station SET station_task = NULL WHERE station_task=:id");
        $update->bindParam(":id", $id, PDO::PARAM_STR);
        $update->execute();
        
        $delete = $this->getDBH()->prepare("DELETE FROM task WHERE task_time=:time");
        try {
            $delete->bindParam(":time", $id, PDO::PARAM_STR);
            $delete->execute();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        print_r($delete->errorInfo());
        return "";
    }
    
    private function saveIntegerFact(string $id = null) : string {
        $integer_n = (string) $_POST['integer_n'];
        $priority = (string) $_POST['priority'];
        if($id == null) {
            $time = time();
            $check = $this->getDBH()->prepare("SELECT task_time FROM task WHERE task_n=:integer_n AND task_type='factorization' LIMIT 1");
            $check->bindParam(":integer_n", $integer_n, PDO::PARAM_STR);
            $check->execute();
            if($check->rowCount() == 0) {
                try {
                    $insert = $this->getDBH()->prepare("INSERT INTO task VALUES(FROM_UNIXTIME(:time), :priority, 'factorization', :integer_n, NULL, NULL)");
                    $insert->bindParam(':time', $time, PDO::PARAM_INT);
                    $insert->bindParam(':priority', $priority, PDO::PARAM_STR);
                    $insert->bindParam(':integer_n', $integer_n, PDO::PARAM_STR);
                    $insert->execute();
                } catch (PDOException $e) {
                    return $e->getMessage();
                }
                return "";
            }
            else {
                return "Duplicite value of integer n!";
            }
        } else {
            $time = $id;
            $check = $this->getDBH()->prepare("SELECT task_time FROM task WHERE task_n=:integer_n AND task_time!=:time AND task_type='factorization' LIMIT 1");
            $check->bindParam(":integer_n", $integer_n, PDO::PARAM_STR);
            $check->bindParam(":time", $time, PDO::PARAM_STR);
            $check->execute();
            if($check->rowCount() == 0) {
                try {
                    $update = $this->getDBH()->prepare("UPDATE task SET task_priority=:priority, task_n=:integer_n WHERE task_time=:time");
                    $update->bindParam(':priority', $priority, PDO::PARAM_STR);
                    $update->bindParam(':integer_n', $integer_n, PDO::PARAM_STR);
                    $update->bindParam(':time', $time, PDO::PARAM_STR);
                    $update->execute();
                } catch (PDOException $e) {
                    return $e->getMessage();
                }
                return "";
            }
            else {
                return "Duplicite value of integer n!";
            }
        }
    }
    
    private function saveDiscreteLogarithm(string $id = null) : string {
        $integer_n = (string) $_POST['integer_n'];
        $integer_g = (string) $_POST['integer_g'];
        $integer_a = (string) $_POST['integer_a'];
        $priority = (string) $_POST['priority'];
        
        if($id == null) {
            $time = time();
            try {
                $insert = $this->getDBH()->prepare("INSERT INTO task VALUES(FROM_UNIXTIME(:time), :priority, 'logarithm', :integer_n, :integer_g, :integer_a)");
                $insert->bindParam(':time', $time, PDO::PARAM_INT);
                $insert->bindParam(':priority', $priority, PDO::PARAM_STR);
                $insert->bindParam(':integer_n', $integer_n, PDO::PARAM_STR);
                $insert->bindParam(':integer_g', $integer_g, PDO::PARAM_STR);
                $insert->bindParam(':integer_a', $integer_a, PDO::PARAM_STR);
                $insert->execute();
            } catch (PDOException $e) {
                return $e->getMessage();
            }            
        } else {
            $time = $id;
            try {
                $update = $this->getDBH()->prepare("UPDATE task SET task_priority=:priority, task_n=:integer_n, task_g=:integer_g, task_a=:integer_a WHERE task_time=:time");
                $update->bindParam(':priority', $priority, PDO::PARAM_STR);
                $update->bindParam(':integer_n', $integer_n, PDO::PARAM_STR);
                $update->bindParam(':integer_g', $integer_g, PDO::PARAM_STR);
                $update->bindParam(':integer_a', $integer_a, PDO::PARAM_STR);
                $update->bindParam(':time', $time, PDO::PARAM_STR);
                $update->execute();
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        }
        return "";
    }
    
    public function __construct() {
        parent::__construct();
        
        $this->setPageTitle("Tasks");
        if(isset($_POST['action'])) {
            if($_POST['action'] == "insert-factorization") {
                $this->addErrorMessage($this->saveIntegerFact());
            }
            else if($_POST['action'] == "edit-factorization") {
                $this->addErrorMessage($this->saveIntegerFact($_POST['time']));
            } else if($_POST['action'] == "insert-discrete-logarithm") {
                $this->addErrorMessage($this->saveDiscreteLogarithm());
            }
            else if($_POST['action'] == "edit-discrete-logarithm") {
                $this->addErrorMessage($this->saveDiscreteLogarithm($_POST['time']));
            }
            else if($_POST['action'] == "delete-factorization") {
                $this->addErrorMessage($this->deleteTask($_POST['time']));
            }
            else if($_POST['action'] == "delete-discrete-logarithm") {
                $this->addErrorMessage($this->deleteTask($_POST['time']));
            }
        }
        
    }
    
    private function formatLargeNumber(string $number) : string {
        $retStr = "";
        $j = 0;
        for($i = strlen($number) - 1; $i >= 0; $i--) {
            $j++;
            $retStr = $number[$i].$retStr;
            if( $j % 3 == 0) {
                $retStr = " ".$retStr;
            }
            
        }
        return $retStr;
    }
    
    private function plotIntegerFactForm(string $id = null) {
        $this->plotBoxStart();
        if($id == null) {
            echo '<h3 id="insert-factorization">Insert integer to be factorized</h3>';
            echo '<form action="tasks.html" method="post">';
                echo '<input type="hidden" name="action" value="insert-factorization">';
                echo '<label for="integer_n">Integer <em>n</em> in hexadecimal form:</label>';
                echo '<input type="text" pattern="[a-fA-F\d]+" id="integer_n" name="integer_n" required placeholder="Factorized integer">';
                echo '<label for="priority">Priority of task:</label>';
                echo '<select id="priority" name="priority">';
                echo '<option value="suspend">Suspend</option>';
                echo '<option value="low">Low</option>';
                echo '<option value="medium">Medium</option>';
                echo '<option value="high">High</option>';
                echo '</select>';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="INSERT" id="submitF">';
            echo '</form>';
        }
        else {
            $time = base64_decode(strtr($id, '-_,', '+/='));
            $edits = $this->getDBH()->prepare("SELECT * FROM task WHERE task_time=:time");
            $edits->bindParam(":time", $time);
            $edits->execute();
            $edit = $edits->fetch(PDO::FETCH_ASSOC);
            echo '<h3 id="edit-factorization">Edit integer to be factorized</h3>';
            echo '<form action="tasks.html" method="post">';
                echo '<input type="hidden" name="time" value="'.$edit['task_time'].'">';
                echo '<input type="hidden" name="action" value="edit-factorization">';
                echo '<label for="integer_n">Integer <em>n</em> in hexadecimal form:</label>';
                echo '<input type="text" readonly pattern="[a-fA-F\d]+" id="integer_n" name="integer_n" required placeholder="Factorized integer" value="'.$edit['task_n'].'">';
                echo '<label for="priority">Priority of task:</label>';
                echo '<select id="priority" name="priority">';
                echo '<option value="suspend" '.($edit['task_priority'] == 'suspend' ? "selected" : "").'>Suspend</option>';
                echo '<option value="low" '.($edit['task_priority'] == 'low' ? "selected" : "").'>Low</option>';
                echo '<option value="medium" '.($edit['task_priority'] == 'medium' ? "selected" : "").'>Medium</option>';
                echo '<option value="high" '.($edit['task_priority'] == 'high' ? "selected" : "").'>High</option>';
                echo '</select>';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="EDIT" id="submitF">';
            echo '</form>';
        }
        $this->plotBoxEnd();
    }
    
    private function plotIntegerFactList() {
        $this->plotBoxStart();
        echo '<h3 id="list-factorization">List of processing integers</h3>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<td>Date</td>';
        echo '<td>Integer <em>n</em> (hex)</td>';
        echo '<td>Priority</td>';
        echo '<td>Solution (hex)</td>';
        echo '<td>Delete</td>';
        echo '</tr>';
        echo '<tbody>';
            $integers = $this->getDBH()->query("SELECT DATE_FORMAT(task_time,'%Y/%m/%e %H:%i:%S') AS time, task_time, task_priority, task_n FROM task WHERE task_type='factorization' ORDER BY task_time");            
            while($integer = $integers->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                    echo '<td>';
                    echo $integer['time'];
                    echo '</td>';
                    echo '<td>';
                        echo "<a href='/?id=tasks&action=edit-factorization&integer_n=".strtr(base64_encode($integer['task_time']), '+/=', '-_,')."#edit-factorization' title='Click to edit'>";
                        echo $this->formatLargeNumber($integer['task_n']);
                        echo '</a>';
                    echo '</td>';
                    echo '<td>';
                    echo $integer['task_priority'];
                    echo '</td>';
                    $solutions = $this->getDBH()->query("SELECT GROUP_CONCAT(solution_factor) AS factors FROM solution WHERE task_time='".$integer['task_time']."' GROUP BY task_time");
                    $solution = $solutions->fetch(PDO::FETCH_ASSOC);
                    if($solution['factors'] != null) {
                        echo '<td>'.str_replace(",", " &middot; ",$solution['factors']).'</td>';
                    } else {
                        echo '<td>N / A</td>';
                    }
                    echo '<td>';
                    echo '<a href="index.php?id=tasks&action=delete-factorization&time=' .strtr(base64_encode($integer['task_time']), '+/=', '-_,').'#delete-factorization" title="Click to delete" > <span class="fa fa-times"></span> </a>';
                    echo '</td>';
                echo '</tr>';
            }
            
        echo '</tbody>';
        echo '</thead>';
        echo '</table>';
        $this->plotBoxEnd();
    }
    
    private function plotDiscreteLogForm(string $id = null) {
        $this->plotBoxStart();
        if($id == null) {
            echo '<h3 id="insert-logarithm">Insert new discrete logarithm problem</h3>';
            echo '<form action="tasks.html" method="post">';
                echo '<input type="hidden" name="action" value="insert-discrete-logarithm">';
                
                echo '<label for="integer_n">Integer <em>n</em> in hexadecimal form:</label>';
                echo '<input type="text" pattern="[a-fA-F\d]+" id="integer_n" name="integer_n" required placeholder="Integer n">';
                
                echo '<label for="integer_g">Integer <em>g</em> in hexadecimal form:</label>';
                echo '<input type="text" pattern="[a-fA-F\d]+" id="integer_g" name="integer_g" required placeholder="Integer g">';
                
                echo '<label for="integer_a">Integer <em>a</em> in hexadecimal form:</label>';
                echo '<input type="text" pattern="[a-fA-F\d]+" id="integer_a" name="integer_a" required placeholder="Integer a">';
                                
                echo '<label for="priority">Priority of task:</label>';
                echo '<select id="priority" name="priority">';
                echo '<option value="suspend">Suspend</option>';
                echo '<option value="low">Low</option>';
                echo '<option value="medium">Medium</option>';
                echo '<option value="high">High</option>';
                echo '</select>';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="INSERT" id="submitD">';
            echo '</form>';
        }
        else {
            $time = base64_decode(strtr($id, '-_,', '+/='));
            $edits = $this->getDBH()->prepare("SELECT * FROM task WHERE task_time=:time");
            $edits->bindParam(":time", $time);
            $edits->execute();
            $edit = $edits->fetch(PDO::FETCH_ASSOC);
            echo '<h3 id="edit-logarithm">Edit discrete logarithm problem</h3>';
            echo '<form action="tasks.html" method="post">';
                echo '<input type="hidden" name="time" value="'.$edit['task_time'].'">';
                echo '<input type="hidden" name="action" value="edit-discrete-logarithm">';
                
                echo '<label for="integer_n">Integer <em>n</em> in hexadecimal form:</label>';
                echo '<input type="text" readonly pattern="[a-fA-F\d]+" id="integer_n" value="'.$edit['task_n'].'" name="integer_n" required placeholder="Integer n">';
                
                echo '<label for="integer_g">Integer <em>g</em> in hexadecimal form:</label>';
                echo '<input type="text" readonly pattern="[a-fA-F\d]+" id="integer_g" value="'.$edit['task_g'].'" name="integer_g" required placeholder="Integer g">';
                
                echo '<label for="integer_a">Integer <em>a</em> in hexadecimal form:</label>';
                echo '<input type="text" readonly pattern="[a-fA-F\d]+" id="integer_a" value="'.$edit['task_a'].'" name="integer_a" required placeholder="Integer a">';
                
                echo '<label for="priority">Priority of task:</label>';
                echo '<select id="priority" name="priority">';
                echo '<option value="suspend" '.($edit['task_priority'] == 'suspend' ? "selected" : "").'>Suspend</option>';
                echo '<option value="low" '.($edit['task_priority'] == 'low' ? "selected" : "").'>Low</option>';
                echo '<option value="medium" '.($edit['task_priority'] == 'medium' ? "selected" : "").'>Medium</option>';
                echo '<option value="high" '.($edit['task_priority'] == 'high' ? "selected" : "").'>High</option>';
                echo '</select>';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="EDIT" id="submitD">';
            echo '</form>';
        }
        $this->plotBoxEnd();
    }
    
    private function plotDiscreteLogarithmList() {
        $this->plotBoxStart();
        echo '<h3 id="list-logarithm">List of processing logarithms</h3>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<td>Date</td>';
        echo '<td>Integer <em>n</em> (hex)</td>';
        echo '<td>Integer <em>g</em> (hex)</td>';
        echo '<td>Integer <em>a</em> (hex)</td>';
        echo '<td>Priority</td>';
        echo '<td>Solution <em>k</em> (hex)</td>';
        echo '<td>Delete</td>';
        echo '</tr>';
        echo '<tbody>';
            $integers = $this->getDBH()->query("SELECT DATE_FORMAT(task_time,'%Y/%m/%e %H:%i:%S') AS time, task_time, task_priority, task_n, task_g, task_a FROM task WHERE task_type='logarithm' ORDER BY task_time");            
            while($integer = $integers->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                    echo '<td>';
                    echo $integer['time'];
                    echo '</td>';
                    echo '<td>';
                        echo "<a href='/?id=tasks&action=edit-logarithm&integer_n=".strtr(base64_encode($integer['task_time']), '+/=', '-_,')."#edit-logarithm' title='Click to edit'>";
                        echo $this->formatLargeNumber($integer['task_n']);
                        echo '</a>';
                    echo '</td>';
                    
                    echo '<td>';
                        echo $this->formatLargeNumber($integer['task_g']);
                    echo '</td>';
                    
                    echo '<td>';
                        echo $this->formatLargeNumber($integer['task_a']);
                    echo '</td>';
                    
                    echo '<td>';
                    echo $integer['task_priority'];
                    echo '</td>';
                    $solutions = $this->getDBH()->query("SELECT GROUP_CONCAT(solution_factor) AS factors FROM solution WHERE task_time='".$integer['task_time']."' GROUP BY task_time");
                    $solution = $solutions->fetch(PDO::FETCH_ASSOC);
                    if($solution['factors'] != null) {
                        echo '<td>'.$solution['factors'].'</td>';
                    } else {
                        echo '<td>N / A</td>';
                    }
                    echo '<td>';
                    echo '<a href="index.php?id=tasks&action=delete-discrete-logarithm&time=' .strtr(base64_encode($integer['task_time']), '+/=', '-_,').'#delete-logarithm" title="Click to delete" > <span class="fa fa-times"></span> </a>';
                    echo '</td>';
                echo '</tr>';
            }
            
        echo '</tbody>';
        echo '</thead>';
        echo '</table>';
        $this->plotBoxEnd();
    }

    private function plotDeleteFactorization(string $id) {
        $this->plotBoxStart('warning');
        $taskQ = $this->getDBH()->prepare("SELECT task_n FROM task WHERE task_time=:time");
        $taskQ->bindParam(":time", $id, PDO::PARAM_STR);
        $taskQ->execute();
        $task = $taskQ->fetch(PDO::FETCH_ASSOC);
        echo '<p>';
        echo 'Do you really want to remove item inserted on <strong>';
        echo $id;
        echo "</strong> where <strong><em>n</em> = ".$this->formatLargeNumber($task['task_n'])."</strong>?";
        echo '</p>';
        echo '<form action="tasks.html" method="post">';
        echo '<a href="tasks.html" class="button left">CANCEL</a>';
        echo '<input type="hidden" name="action" value="delete-factorization">';
        echo '<input type="hidden" name="time" value="'.$id.'">';
        echo '<input type="submit" class="left" value="YES" >';
        echo '<div class="clear"></div>';
        echo '</form>';
        echo '<div class="clear"></div>';
        $this->plotBoxEnd();
    }
    private function plotDeleteLogarithm(string $id) {
        $this->plotBoxStart('warning');
        $taskQ = $this->getDBH()->prepare("SELECT task_n, task_g, task_a FROM task WHERE task_time=:time");
        $taskQ->bindParam(":time", $id, PDO::PARAM_STR);
        $taskQ->execute();
        $task = $taskQ->fetch(PDO::FETCH_ASSOC);
        echo '<p>';
        echo 'Do you really want to remove item inserted on <strong>';
        echo $id;
        echo "</strong> where <strong><em>n</em> = ".$this->formatLargeNumber($task['task_n'])."</strong>, <strong><em>g</em> = ".$this->formatLargeNumber($task['task_g'])."</strong>, <strong><em>a</em> = ".$this->formatLargeNumber($task['task_a'])."</strong>?";
        echo '</p>';
        echo '<form action="tasks.html" method="post">';
        echo '<a href="tasks.html" class="button left">CANCEL</a>';
        echo '<input type="hidden" name="action" value="delete-discrete-logarithm">';
        echo '<input type="hidden" name="time" value="'.$id.'">';
        echo '<input type="submit" class="left" value="YES" >';
        echo '<div class="clear"></div>';
        echo '</form>';
        echo '<div class="clear"></div>';
        $this->plotBoxEnd();
    }
    protected function plotContent() {
        if($_GET['action'] == "delete-factorization") {
            $this->plotDeleteFactorization(base64_decode(strtr($_GET['time'], '-_,', '+/=')));
        } else if($_GET['action'] == "delete-discrete-logarithm") {
            $this->plotDeleteLogarithm(base64_decode(strtr($_GET['time'], '-_,', '+/=')));
        }
        
        $this->plotBoxStart();
        echo '<p>';
        echo '<a href="/tasks.html#insert-factorization" class="navbutton">INSERT FACTORIZATION</a>'; 
        echo '<a href="/tasks.html#insert-logarithm" class="navbutton">INSERT LOGARITHM</a>';
        echo '<a href="/tasks.html#list-factorization" class="navbutton">LIST OF FACTORIZATION</a>'; 
        echo '<a href="/tasks.html#list-logarithm" class="navbutton">LIST OF LOGARITHM</a>';
        echo '</p>';
        echo '<div class="clear"></div>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h2>Integer factorization problem</h2>';
        echo '<p>We are looking for prime numbers <em>p</em><sub>1</sub>, <em>p</em><sub>2</sub>, &hellip;, <em>p</em><sub><em>k</em></sub> where <em>n</em> = <em>p</em><sub>1</sub>&middot;<em>p</em><sub>2</sub>&middot; &hellip; &middot; <em>p</em><sub><em>k</em></sub></p>';
        $this->plotIntegerFactList();
        $this->plotIntegerFactForm($_GET['action'] == 'edit-factorization' ? $_GET['integer_n'] : null );
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h2>Discrete logarithm problem</h2>';
        echo '<p>We are looking for integer <em>k</em> where <em>g<sup>k</sup></em> &equiv; <em>a</em> (mod <em>n</em>) for given integers <em>g</em>,<em>a</em> and <em>n</em>.</p>';
        $this->plotDiscreteLogarithmList();
        $this->plotDiscreteLogForm($_GET['action'] == 'edit-logarithm' ? $_GET['integer_n'] : null );
        $this->plotBoxEnd();
        
    }
}

