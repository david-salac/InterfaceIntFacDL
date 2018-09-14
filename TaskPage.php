<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script handling submitting of tasks in systems
 */

/**
 * Class for handling of tasks in system
 * @author David Salac
 */
class TaskPage extends Page {
    
    /**
     * Save or update the task in system
     * @param string $id Identification of task if it is updated
     * @return string Error message
     */
    private function saveTask(string $id = null) : string {
        $task_type = (string) $_POST['task_type'];
        $priority = (string) $_POST['priority'];
        
        $rsa_n = null;
        $rsa_c = null;
        $rsa_e = null;

        $elgamal_p = null;
        $elgamal_g = null;
        $elgamal_h = null;
        $elgamal_c1 = null;
        $elgamal_c2 = null;

        $diffiehellman_p = null;
        $diffiehellman_g = null;
        $diffiehellman_gpowa = null;
        $diffiehellman_gpowb = null;
        
        if($task_type == "rsa") {
            $rsa_n = (string) $_POST['rsa_n'];
            $rsa_c = (string) $_POST['rsa_c'];
            $rsa_e = (string) $_POST['rsa_e'];
        } else if($task_type == "elgamal") {
            $elgamal_p = (string) $_POST['elgamal_p'];
            $elgamal_g = (string) $_POST['elgamal_g'];
            $elgamal_h = (string) $_POST['elgamal_h'];
            $elgamal_c1 = (string) $_POST['elgamal_c1'];
            $elgamal_c2 = (string) $_POST['elgamal_c2'];
        } else if($task_type == "diffiehellman") {
            $task_type = "dh";
            $diffiehellman_p = (string) $_POST['diffiehellman_p'];
            $diffiehellman_g = (string) $_POST['diffiehellman_g'];
            $diffiehellman_gpowa = (string) $_POST['diffiehellman_gpowa'];
            $diffiehellman_gpowb = (string) $_POST['diffiehellman_gpowb'];
            
        }
            
        try {
            if($id != null) {
                $edit = $this->getDBH()->prepare("UPDATE task SET task_priority=:priority WHERE task_id=:id");
                $edit->bindParam(':id', $id, PDO::PARAM_INT);
                $edit->bindParam(':priority', $priority, PDO::PARAM_STR);
                $edit->execute();
            }
            else {
                $time = time();

                $insert = $this->getDBH()->prepare("INSERT INTO task VALUES(DEFAULT, FROM_UNIXTIME(:time), NULL, :priority, :type, FALSE, NULL, NULL, "
                . ":task_rsa_n, :task_rsa_e, :task_rsa_c, NULL, NULL, "
                . ":task_elgamal_p, :task_elgamal_g, :task_elgamal_h, :task_elgamal_c1, :task_elgamal_c2, NULL, NULL,"
                . ":task_dh_p, :task_dh_g, :task_dh_pow_a, :task_dh_pow_b, NULL, NULL)");

                $insert->bindParam(':time', $time, PDO::PARAM_INT);
                $insert->bindParam(':priority', $priority, PDO::PARAM_STR);
                $insert->bindParam(':type', $task_type, PDO::PARAM_STR);

                $insert->bindParam(':task_rsa_n', $rsa_n, PDO::PARAM_STR);
                $insert->bindParam(':task_rsa_e', $rsa_e, PDO::PARAM_STR);
                $insert->bindParam(':task_rsa_c', $rsa_c, PDO::PARAM_STR);

                $insert->bindParam(':task_elgamal_p', $elgamal_p, PDO::PARAM_STR);
                $insert->bindParam(':task_elgamal_g', $elgamal_g, PDO::PARAM_STR);
                $insert->bindParam(':task_elgamal_h', $elgamal_h, PDO::PARAM_STR);
                $insert->bindParam(':task_elgamal_c1', $elgamal_c1, PDO::PARAM_STR);
                $insert->bindParam(':task_elgamal_c2', $elgamal_c2, PDO::PARAM_STR);

                $insert->bindParam(':task_dh_p', $diffiehellman_p, PDO::PARAM_STR);
                $insert->bindParam(':task_dh_g', $diffiehellman_g, PDO::PARAM_STR);
                $insert->bindParam(':task_dh_pow_a', $diffiehellman_gpowa, PDO::PARAM_STR);
                $insert->bindParam(':task_dh_pow_b', $diffiehellman_gpowb, PDO::PARAM_STR);

                $insert->execute();
            }
        }
        catch (PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }
    
    /**
     * Delete the task in system
     * @param string $id Identification of the task
     * @return string Error message
     */
    private function deleteTask(string $id) : string {
        try {
            //Solving of referential integrity problem:
            $deletePre = $this->getDBH()->prepare("UPDATE station SET station_task=NULL WHERE station_task=:id");
            $deletePre->bindParam(":id", $id);
            $deletePre->execute();
            
            //Removing of row
            $deleteP = $this->getDBH()->prepare("DELETE FROM task WHERE task_id=:id");
            $deleteP->bindParam(":id", $id);
            $deleteP->execute();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return "";
    }
    
    /**
     * Plot the detail of the task
     * @param string $id Identification of the task
     * @return string Error message
     */
    private function plotTaskDetail(string $id) {
        try {
            $detailQ = $this->getDBH()->query("SELECT * FROM task WHERE task_id=".$id);
            $detailF = $detailQ->fetch(PDO::FETCH_ASSOC);
            
            $this->plotBoxStart();
            echo "<h2>Task detail</h2>";
            
            echo "<table>";
            echo "<thead>";
            echo "<tr><td>Type</td><td>Value</td><td>Data</td><td>Value</td></tr>";
            echo "</thead>";
            echo "<tbody>";
            
            $val1 = "<td class='strong'>Task type:</td><td>";
            if($detailF['task_type'] == "rsa") {
                $val1 .= "RSA";
            } else if ($detailF['task_type'] == "elgamal") {
                $val1 .= "ElGamal";
            } else if ($detailF['task_type'] == "dh") {
                $val1 .= "Diffie-Hellman";
            }
            $val1 .= "</td>";
            $val2 = "<td class='strong'>Privilege: </td><td>".$detailF['task_priority']."</td>";
            $val3 = "<td></td><td></td>";
            $val4 = "<td></td><td></td>";
            $val5 = "<td></td><td></td>";
            
            if($detailF['task_solved'] == false) {
                $val3 = "<td class='strong'>Solved: </td><td>False</td>";
            } else {
                $val3 = "<td class='strong'>Solved: </td><td>True</td>";
                $val4 = "<td class='strong'>Solving time: </td><td>".$detailF['task_solving_time']." s (cca.)"."</td>";
                $val5 = "<td class='strong'>Solving station: </td><td>".$detailF['task_solving_station'] ?: "N / A"."</td>";
            }
            
            
            if($detailF['task_type'] == "rsa") {
                echo "<tr>";
                    echo $val1;
                    echo '<td class="strong"><em>n</em></td>';
                    echo "<td>"; echo "".$detailF['task_rsa_n']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val2;
                    echo "<td>"; echo "<em>e</em></td><td>".$detailF['task_rsa_e']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val3;
                    echo "<td>"; echo "<em>c</em></td><td>".$detailF['task_rsa_c']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val4;
                    echo "<td>"; echo "<em>m</em></td><td>".($detailF['task_rsa_m'] ?: "N / A"); echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val5;
                    echo "<td>"; echo "<em>d</em></td><td>".($detailF['task_rsa_d'] ?: "N / A"); echo "</td>";
                echo "</tr>";
            } else if ($detailF['task_type'] == "elgamal") {
                echo "<tr>";
                    echo $val1;
                    echo "<td>"; echo "<em>p</em></td><td>".$detailF['task_elgamal_p']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val2;
                    echo "<td>"; echo "<em>g</em></td><td>".$detailF['task_elgamal_g']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val3;
                    echo "<td>"; echo "<em>h</em></td><td>".$detailF['task_elgamal_h']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val4;
                    echo "<td>"; echo "<em>c</em><sub>1</sub></td><td>".$detailF['task_elgamal_c1']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val5;
                    echo "<td>"; echo "<em>c</em><sub>2</sub></td><td>".$detailF['task_elgamal_c2']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo "<td></td><td></td><td>"; echo "<em>x</em></td><td>".($detailF['task_elgamal_x'] ?: "N / A"); echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo "<td></td><td></td><td>"; echo "<em>m</em></td><td>".($detailF['task_elgamal_m'] ?: "N / A"); echo "</td>";
                echo "</tr>";
            } else if ($detailF['task_type'] == "dh") {
                echo "<tr>";
                    echo $val1;
                    echo "<td>"; echo "<em>p</em></td><td>".$detailF['task_dh_p']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val2;
                    echo "<td>"; echo "<em>g</em></td><td>".$detailF['task_dh_g']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val3;
                    echo "<td>"; echo "<em>g<sup>a</sup></em></td><td>".$detailF['task_dh_pow_a']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val4;
                    echo "<td>"; echo "<em>g<sup>b</sup></em></td><td>".$detailF['task_dh_pow_b']; echo "</td>";
                echo "</tr>";
                echo "<tr>";
                    echo $val5;
                    echo "<td>"; echo "Shared key</td><td>".($detailF['task_dh_common_key'] ?: "N / A"); echo "</td>";
                echo "</tr>";
                
            }
            echo "</tbody>";
            echo "</table>";
            /*
            echo '<div class="leftcolumn">';
            echo '<h3>Type</h3>';
            echo "<p>"; 
            echo "<strong>";
            echo "Task type: ";
            echo "</strong> "; 
            if($detailF['task_type'] == "rsa") {
                echo "RSA";
            } else if ($detailF['task_type'] == "elgamal") {
                echo "ElGamal";
            } else if ($detailF['task_type'] == "dh") {
                echo "Diffie-Hellman";
            }
            echo "</p>";
            
            if($detailF['task_solved'] == false) {
                echo "<p>"; 
                echo "<strong>";
                echo "Solved: ";
                echo "</strong> "; 
                echo "False";
                echo "</p>";
            } else {
                echo "<p>"; 
                echo "<strong>";
                echo "Solved: ";
                echo "</strong> "; 
                echo "True";
                echo "</p>";
                
                echo "<p>"; 
                echo "<strong>";
                echo "Solving time: ";
                echo "</strong> "; 
                echo $detailF['task_solving_time']." s (approximately)";
                echo "</p>";

                echo "<p>"; 
                echo "<strong>";
                echo "Solving station: ";
                echo "</strong> "; 
                echo $detailF['task_solving_station'] ?: "N / A";
                echo "</p>";
            }
            
            echo "<p>"; 
            echo "<strong>";
            echo "Privilege: ";
            echo "</strong> "; 
            echo $detailF['task_priority'];
            echo "</p>";
            
            echo '</div><div class="rightcolumn">';
            echo '<h3>Data set</h3>';
            if($detailF['task_type'] == "rsa") {
                echo "<p>"; echo "<strong><em>n</em></strong>: ".$detailF['task_rsa_n']; echo "</p>";
                echo "<p>"; echo "<strong><em>e</em></strong>: ".$detailF['task_rsa_e']; echo "</p>";
                echo "<p>"; echo "<strong><em>c</em></strong>: ".$detailF['task_rsa_c']; echo "</p>";
                echo "<p>"; echo "<strong><em>m</em></strong>: ".($detailF['task_rsa_m'] ?: "N / A"); echo "</p>";
                echo "<p>"; echo "<strong><em>d</em></strong>: ".($detailF['task_rsa_d'] ?: "N / A"); echo "</p>";
            } else if ($detailF['task_type'] == "elgamal") {
                echo "<p>"; echo "<strong><em>p</em></strong>: ".$detailF['task_elgamal_p']; echo "</p>";
                echo "<p>"; echo "<strong><em>g</em></strong>: ".$detailF['task_elgamal_g']; echo "</p>";
                echo "<p>"; echo "<strong><em>h</em></strong>: ".$detailF['task_elgamal_h']; echo "</p>";
                echo "<p>"; echo "<strong><em>c</em><sub>1</sub></strong>: ".$detailF['task_elgamal_c1']; echo "</p>";
                echo "<p>"; echo "<strong><em>c</em><sub>2</sub></strong>: ".$detailF['task_elgamal_c2']; echo "</p>";
                echo "<p>"; echo "<strong><em>x</em></strong>: ".($detailF['task_elgamal_x'] ?: "N / A"); echo "</p>";
                echo "<p>"; echo "<strong><em>m</em></strong>: ".($detailF['task_elgamal_m'] ?: "N / A"); echo "</p>";
            } else if ($detailF['task_type'] == "dh") {
                echo "<p>"; echo "<strong><em>p</em></strong>: ".$detailF['task_dh_p']; echo "</p>";
                echo "<p>"; echo "<strong><em>g</em></strong>: ".$detailF['task_dh_g']; echo "</p>";
                echo "<p>"; echo "<strong><em>g<sup>a</sup></em></strong>: ".$detailF['task_dh_pow_a']; echo "</p>";
                echo "<p>"; echo "<strong><em>g<sup>b</sup></em></strong>: ".$detailF['task_dh_pow_b']; echo "</p>";
                echo "<p>"; echo "<strong><em>a</em></strong>: ".($detailF['task_dh_a'] ?: "N / A"); echo "</p>";
                echo "<p>"; echo "<strong>Shared key</strong>: ".($detailF['task_dh_common_key'] ?: "N / A"); echo "</p>";
            }
            echo "</div>"; */
            
            echo '<div class="clear"></div>';
            
            $this->plotBoxEnd();
        } catch(PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }
    
    /**
     * Plot the form for editing or creating task
     * @param string $id Identification of the task
     */
    private function plotTaskForm(string $id = null) {
        $this->plotBoxStart();
        if($id == null) {
            echo '<div id="randomOptionsRSA">';
            echo '<a href="javascript:;" class="navbutton rsaRandGen" rel="64">Random 64 bit task</a>'; 
            echo '<a href="javascript:;" class="navbutton rsaRandGen" rel="128">Random 128 bit task</a>'; 
            echo '<a href="javascript:;" class="navbutton rsaRandGen" rel="256">Random 256 bit task</a>'; 
            echo '</div>';
            
            echo '<div id="randomOptionsDH">';
            echo '<a href="javascript:;" class="navbutton dhRandGen" rel="16">Random 16 bit task</a>'; 
            echo '<a href="javascript:;" class="navbutton dhRandGen" rel="32">Random 32 bit task</a>'; 
            echo '<a href="javascript:;" class="navbutton dhRandGen" rel="48">Random 48 bit task</a>'; 
            //echo '<a href="javascript:;" class="navbutton dhRandGen" rel="64">Random 64 bit task</a>'; 
            echo '</div>';
            
            echo '<div id="randomOptionsElGamal">';
            echo '<a href="javascript:;" class="navbutton egRandGen" rel="16">Random 16 bit task</a>'; 
            echo '<a href="javascript:;" class="navbutton egRandGen" rel="32">Random 32 bit task</a>'; 
            echo '<a href="javascript:;" class="navbutton egRandGen" rel="48">Random 48 bit task</a>'; 
            //echo '<a href="javascript:;" class="navbutton egRandGen" rel="64">Random 64 bit task</a>'; 
            echo '</div>';
            
            echo '<div class="clear"></div>';
            
            echo '<h2 id="insert-task">Insert new task to system</h2>';
            echo '<form action="tasks.html" method="post" id="taskForm">';
                echo '<input type="hidden" name="action" value="insert-task">';
                echo '<label for="task_type">Task to cryptanalysis: </label>';
                echo '<select id="task_type" name="task_type">';
                    echo '<option value="rsa">RSA cypher</option>';
                    echo '<option value="elgamal">ElGamal cypher</option>';
                    echo '<option value="diffiehellman">Diffie-Hellman key exchange</option>';
                echo '</select> ';
                
                echo '<label for="priority">Priority of task:</label>';
                echo '<select id="priority" name="priority">';
                echo '<option value="suspend">Suspend</option>';
                echo '<option value="low">Low</option>';
                echo '<option value="medium">Medium</option>';
                echo '<option value="high">High</option>';
                echo '</select>';              
                
                echo '<div class="clear">&nbsp;</div>
                    <div id="task-content-part"></div>
                    
                    <label for="submitF">Submit request:</label>
                    <input type="submit" value="INSERT" id="submitF">
                    <script type="text/javascript">
                        function plotRSA() {
                            return \'<div id="rsa"><label for="rsa_n">Modulus <em>n</em> as hexadecimal number:</label><input type="text" pattern="[a-fA-F0-9]+" id="rsa_n" name="rsa_n" required placeholder="Modulus n"><label for="rsa_c">Cypher text <em>c</em> as hexadecimal number:</label><input type="text" pattern="[a-fA-F0-9]+" id="rsa_c" name="rsa_c" required placeholder="Cyphertext c"><label for="rsa_e">Exponent <em>e</em> as hexadecimal number:</label><input type="text" pattern="[a-fA-F0-9]+" id="rsa_e" name="rsa_e" required placeholder="Exponent e"></div>\';
                        }
                        function plotElGamal() {
                            return \'<div id="elgamal"> <label for="elgamal_p">Modulus <em>p</em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="elgamal_p" name="elgamal_p" required placeholder="Modulus p">  <label for="elgamal_g">Group generator <em>g</em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="elgamal_g" name="elgamal_g" required placeholder="Group generator g">  <label for="elgamal_h">Pubic key <em>h = g<sup>x</sup></em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="elgamal_h" name="elgamal_h" required placeholder="Public key h">  <label for="elgamal_c1">Cypher element <em>c<sub>1</sub></em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="elgamal_c1" name="elgamal_c1" required placeholder="Cyphertext c1">  <label for="elgamal_c2">Cypher element <em>c<sub>2</sub></em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="elgamal_c2" name="elgamal_c2" required placeholder="Cyphertext c2"> </div>\';
                        }
                        function plotDiffieHellman() {
                            return \'<div id="diffiehellman"> <label for="diffiehellman_p">Modulus <em>p</em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="diffiehellman_p" name="diffiehellman_p" required placeholder="Modulus p"> <label for="diffiehellman_g">Group generator <em>g</em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="diffiehellman_g" name="diffiehellman_g" required placeholder="Group generator g"> <label for="diffiehellman_gpowa">Key <em>g<sup>a</sup></em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="diffiehellman_gpowa" name="diffiehellman_gpowa" required placeholder="Key (Alice)"> <label for="diffiehellman_gpowb">Key <em>g<sup>b</sup></em> as hexadecimal number:</label> <input type="text" pattern="[a-fA-F0-9]+" id="diffiehellman_gpowb" name="diffiehellman_gpowb" required placeholder="Key (Bob)"> </div>\';
                        }
                        function taskTypeVisibility(taskType) {
                                if(taskType === "rsa") {
                                    $("#task-content-part").html(plotRSA());
                                    $("#randomOptionsRSA").show();
                                    $("#randomOptionsDH").hide();
                                    $("#randomOptionsElGamal").hide();
                                } else if(taskType === "elgamal") {
                                    $("#task-content-part").html(plotElGamal());
                                    $("#randomOptionsRSA").hide();
                                    $("#randomOptionsDH").hide();
                                    $("#randomOptionsElGamal").show();
                                } else if(taskType === "diffiehellman") {
                                    $("#task-content-part").html(plotDiffieHellman());
                                    $("#randomOptionsRSA").hide();
                                    $("#randomOptionsDH").show();
                                    $("#randomOptionsElGamal").hide();
                                }
                            }
                        $(document).ready(function() {
                            taskTypeVisibility($("#task_type option:checked").val());
                            $("#task_type").change(function () {
                                taskTypeVisibility($("#task_type option:checked").val());
                                
                            });
                            $("#submitF").click(function(event) {
                                event.preventDefault();
                                taskType = $("#task_type option:checked").val();
                                numberOneBI = BigInteger.parse("1", 16);
                                if(taskType === "rsa") {
                                    rsaN = BigInteger.parse(String($("#rsa_n").val()), 16);
                                    rsaC = BigInteger.parse(String($("#rsa_c").val()), 16);
                                    rsaE = BigInteger.parse(String($("#rsa_e").val()), 16);
                                    if(rsaN.compare(numberOneBI) > 0 && 
                                       rsaC.compare(numberOneBI) > 0 && rsaC.compare(rsaN) < 0 &&
                                       rsaE.compare(numberOneBI) > 0 && rsaE.compare(rsaN) < 0) {
                                            document.getElementById("taskForm").submit();
                                    }
                                    else { 
                                        alert("Wrong inputs!");
                                    }
                                } else if(taskType === "elgamal") {
                                    elgamalP  = BigInteger.parse(String($("#elgamal_p").val()), 16);
                                    elgamalG  = BigInteger.parse(String($("#elgamal_g").val()), 16);
                                    elgamalH  = BigInteger.parse(String($("#elgamal_h").val()), 16);
                                    elgamalC1 = BigInteger.parse(String($("#elgamal_c1").val()), 16);
                                    elgamalC2 = BigInteger.parse(String($("#elgamal_c2").val()), 16);
                                    if(elgamalP.compare(numberOneBI)  > 0 && 
                                       elgamalG.compare(numberOneBI)  > 0 && elgamalG.compare(elgamalP) < 0 &&
                                       elgamalH.compare(numberOneBI)  > 0 && elgamalH.compare(elgamalP) < 0 &&
                                       elgamalC1.compare(numberOneBI) > 0 && elgamalC1.compare(elgamalP) < 0 &&
                                       elgamalC2.compare(numberOneBI) > 0 && elgamalC2.compare(elgamalP) < 0) {
                                            document.getElementById("taskForm").submit();
                                    }
                                    else { 
                                        alert("Wrong inputs!");
                                    }
                                } else if(taskType === "diffiehellman") {
                                    diffiehellmanP     = BigInteger.parse(String($("#diffiehellman_p").val()), 16);
                                    diffiehellmanG     = BigInteger.parse(String($("#diffiehellman_g").val()), 16);
                                    diffiehellmanGpowA = BigInteger.parse(String($("#diffiehellman_gpowa").val()), 16);
                                    diffiehellmanGpowB = BigInteger.parse(String($("#diffiehellman_gpowb").val()), 16);
                                    if(diffiehellmanP.compare(numberOneBI)     > 0 && 
                                       diffiehellmanG.compare(numberOneBI)     > 0 && diffiehellmanG.compare(diffiehellmanP)     < 0 &&
                                       diffiehellmanGpowA.compare(numberOneBI) > 0 && diffiehellmanGpowA.compare(diffiehellmanP) < 0 &&
                                       diffiehellmanGpowB.compare(numberOneBI) > 0 && diffiehellmanGpowB.compare(diffiehellmanP) < 0) {
                                            document.getElementById("taskForm").submit();
                                    }
                                    else { 
                                        alert("Wrong inputs!");
                                    }
                                }
                            });
                            $(".rsaRandGen").click(function(event) { 
                                $.ajax({
                                    method: "GET",
                                    url: "randomTask.php",
                                    data: { type: "rsa", size: $(this).attr("rel") }
                                })
                                .done(function( valuesSet ) {
                                    jsonObj = JSON.parse(valuesSet);
                                    $("#rsa_n").val(jsonObj.n);
                                    $("#rsa_e").val(jsonObj.e);
                                    $("#rsa_c").val(jsonObj.c);
                                });
                            });
                            $(".dhRandGen").click(function(event) { 
                                $.ajax({
                                    method: "GET",
                                    url: "randomTask.php",
                                    data: { type: "diffiehellman", size: $(this).attr("rel") }
                                })
                                .done(function( valuesSet ) {
                                    jsonObj = JSON.parse(valuesSet);
                                    $("#diffiehellman_p").val(jsonObj.p);
                                    $("#diffiehellman_g").val(jsonObj.g);
                                    $("#diffiehellman_gpowa").val(jsonObj.gPowA);
                                    $("#diffiehellman_gpowb").val(jsonObj.gPowB);
                                });
                            });
                            $(".egRandGen").click(function(event) {
                                $.ajax({
                                    method: "GET",
                                    url: "randomTask.php",
                                    data: { type: "elgamal", size: $(this).attr("rel") }
                                })
                                .done(function( valuesSet ) {
                                    jsonObj = JSON.parse(valuesSet);
                                    $("#elgamal_p").val(jsonObj.p);
                                    $("#elgamal_g").val(jsonObj.g);
                                    $("#elgamal_h").val(jsonObj.h);
                                    $("#elgamal_c1").val(jsonObj.c1);
                                    $("#elgamal_c2").val(jsonObj.c2);
                                });
                            });
                        });
                    </script>
                </form>';
        }
        else {
            
            $priorityQ = $this->getDBH()->query("SELECT task_priority FROM task WHERE task_id=".$id);
            $priorityF = $priorityQ->fetch(PDO::FETCH_ASSOC);
            $priorityL = $priorityF['task_priority'];
            echo '<h2 id="edit-task">Edit task in system</h2>';
            echo '<form action="tasks.html" method="post">';
                echo '<input type="hidden" name="action" value="edit-task">';
                echo '<input type="hidden" name="tid" value="'.$id.'">';
                
                echo '<label for="priority">Priority of task:</label>';
                echo '<select id="priority" name="priority">';
                echo '<option value="suspend" '.($priorityL == "suspend" ? "selected" : "").'>Suspend</option>';
                echo '<option value="low"     '.($priorityL == "low" ? "selected" : "").'>Low</option>';
                echo '<option value="medium"  '.($priorityL == "medium" ? "selected" : "").'>Medium</option>';
                echo '<option value="high"    '.($priorityL == "high" ? "selected" : "").'>High</option>';
                echo '</select>';              
                
                echo '<label for="submitF">Submit request:</label>
                    <input type="submit" value="EDIT" id="submitF">
                </form>';
            echo '<div class="clear">&nbsp;</div>';
            $this->plotTaskDetail($id);
        }
        $this->plotBoxEnd();
    }
    
    /**
     * Plot the form for removing the task
     * @param string $id Identification of the task
     */
    private function plotTaskDelete(string $id) { 
        $this->plotBoxStart();
        echo '<h2>Delete task</h2>';
        
        echo '<h3>Would you like to remove this task?</h3>';
        echo '<form method="post" action="tasks.html">';
            echo '<input type="hidden" name="action" value="delete-task">';
            echo '<input type="hidden" name="tid" value="'.$id.'">';
            echo '<input type="submit" value="YES" >';
        echo '</form>';
        $this->plotTaskDetail($id);
        $this->plotBoxEnd();
    }
    
    /**
     * Plot the list of all tasks in the system
     */
    private function plotTaskList() {
        $this->plotBoxStart();
        echo '<h2>List of all tasks in system</h2>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
            echo '<td>Task ID</td>';
            echo '<td>Task type</td>';
            echo '<td>Task priority</td>';
            echo '<td>Inserted</td>';
            echo '<td>Last activity</td>';
            echo '<td>Detail</td>';
            echo '<td>Remove</td>';
        echo '</tr>';
        echo '</thead>';
        
        echo '<tbody>';
            $results = $this->getDBH()->query("SELECT task_id, task_type, task_priority, task_time, task_solved, task_last_active FROM task ORDER BY task_time DESC");
            while($task = $results->fetch(PDO::FETCH_ASSOC)) {
                $tasktype = "";
                if($task['task_type'] == "rsa") { $tasktype = "RSA"; } else if($task['task_type'] == "dh") { $tasktype = "Diffie-Hellman"; } else if($task['task_type'] == "elgamal") { $tasktype = "ElGamal"; }
                
                if($task['task_solved'] == true) {
                echo "<tr>";
                } 
                else if(isset($task['task_last_active'])) {
                    echo "<tr class='rightNowSolving'>";    
                }
                else {
                echo "<tr class='notSolved'>";    
                }
                    echo "<td>";
                        echo '<a href="index.php?id=tasks&action=detail-task&tid='.$task['task_id'].'" title="Click to details" >';
                            echo $task['task_id'];
                        echo '</a>';
                    echo '</td>';
                    
                    echo "<td>";
                    echo $tasktype;
                    echo '</td>';
                    
                    if($task['task_solved'] == true) { 
                        echo "<td>";
                        echo 'SOLVED';
                        echo '</td>';
                    }
                    else {
                        echo "<td>";
                        echo '<a href="/index.php?id=tasks&action=edit-task&tid='.$task['task_id'].'" title="Click to edit" >'.$task['task_priority'].' <span class="fa fa-pencil-square-o"></span> </a>';
                        echo '</td>';
                    }
                    
                    echo "<td>";
                    echo $task['task_time'];
                    echo '</td>';
                    
                    echo "<td>";
                    echo $task['task_last_active'] ?: "N / A";
                    echo '</td>';
                    
                    echo "<td>";
                    echo '<a href="index.php?id=tasks&action=detail-task&tid='.$task['task_id'].'" title="Click to details" > <span class="fa fa-object-group"></span> </a>';
                    echo '</td>';
                    
                    echo "<td>";
                    echo '<a href="index.php?id=tasks&action=delete-task&tid='.$task['task_id'].'#delete-task" title="Click to delete" > <span class="fa fa-times"></span> </a>';
                    echo '</td>';
                    
                echo "</tr>";
            }
        echo '</tbody>';
        
        echo '</table>';
        $this->plotBoxEnd();
    }
    
    /**
     * Plot the content of the page
     */
    protected function plotContent() {
        
        if($_GET['action'] == "insert-task") {
            $this->plotBoxStart();
            echo '<a href="/tasks.html" class="navbutton">Task list</a>'; 
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();
            $this->plotTaskForm();
        } else if($_GET['action'] == "edit-task") {
            $this->plotBoxStart();
            echo '<a href="/index.php?id=tasks&action=insert-task" class="navbutton">Insert new task</a>'; 
            echo '<a href="/tasks.html" class="navbutton">Task list</a>'; 
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();
            $this->plotTaskForm($_GET['tid']);
        } else if($_GET['action'] == "detail-task") {
            $this->plotBoxStart();
            echo '<a href="/index.php?id=tasks&action=insert-task" class="navbutton">Insert new task</a>'; 
            echo '<a href="/tasks.html" class="navbutton">Task list</a>'; 
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();
            $this->plotTaskDetail($_GET['tid']);
        } else if($_GET['action'] == "delete-task") {
            $this->plotBoxStart();
            echo '<a href="/index.php?id=tasks&action=insert-task" class="navbutton">Insert new task</a>'; 
            echo '<a href="/tasks.html" class="navbutton">Task list</a>'; 
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();
            $this->plotTaskDelete($_GET['tid']);
        } 
        else {
            $this->plotBoxStart();
            echo '<a href="/index.php?id=tasks&action=insert-task" class="navbutton">Insert new task</a>'; 
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();
            $this->plotTaskList();
        }
        
    }
    
    /**
     * Create instance of the class and commits routines
     */
    public function __construct() {
        parent::__construct();
        
        $this->setPageTitle("Tasks");
        if(isset($_POST['action'])) {
            if($_POST['action'] == "insert-task") {
                $this->addErrorMessage($this->saveTask());
            }
            else if($_POST['action'] == "edit-task") {
                $this->addErrorMessage($this->saveTask($_POST['tid']));
            }
            else if($_POST['action'] == "delete-task") {
                $this->addErrorMessage($this->deleteTask($_POST['tid']));
            }
        }
    }

}