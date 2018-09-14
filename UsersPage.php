<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script handle users of system
 */

/**
 * Handle the users of system
 * @author David Salac
 */
class UsersPage extends Page {
    
    /**
     * Procedure for saving / editing of existing user
     * @param string $id Identification of edited user
     * @return string Error message
     */
    private function saveUser(string $id = null) : string {
        $login = (string) $_POST['username'];
        $password = sha1((string) $_POST['pass']);
        $privilege = (string) $_POST['privilege'];
        $description = (string) $_POST['description'];
        if($id == null) {
            try {
                $insertS = $this->getDBH()->prepare("INSERT INTO user VALUES(:name, :privilege, :description, :password)");
                $insertS->bindParam(":name", $login, PDO::PARAM_STR);
                $insertS->bindParam(":password", $password, PDO::PARAM_STR);
                $insertS->bindParam(":privilege", $privilege, PDO::PARAM_STR);
                $insertS->bindParam(":description", $description, PDO::PARAM_STR);
                $insertS->execute();
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        } else {
            try {
                $updateS = $this->getDBH()->prepare("UPDATE user SET user_name=:name, user_privilege=:privilege, user_description=:description, user_password_hash=:password WHERE user_name=:old");
                $updateS->bindParam(":name", $login, PDO::PARAM_STR);
                $updateS->bindParam(":password", $password, PDO::PARAM_STR);
                $updateS->bindParam(":privilege", $privilege, PDO::PARAM_STR);
                $updateS->bindParam(":description", $description, PDO::PARAM_STR);
                $updateS->bindParam(":old", $id, PDO::PARAM_STR);
                $updateS->execute();
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        }
        return "";
    }
    /**
     * Deleting the user of system
     * @param string $id Identification of user
     * @return string Error message
     */
    private function deleteUser(string $id) : string {
        $delete = $this->getDBH()->prepare("DELETE FROM user WHERE user_name=:username");
        
        try {
            $delete->bindParam(":username", $id, PDO::PARAM_STR);
            $delete->execute();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }
    
    public function __construct() {
        parent::__construct();
        
        $this->setPageTitle("Users");
        if(isset($_POST['action'])) {
            if($_POST['action'] == "insert-user") {
                $this->addErrorMessage($this->saveUser());
            } else if($_POST['action'] == "edit-user") {
                $this->addErrorMessage($this->saveUser($_POST['oldusername']));
            } else if($_POST['action'] == "delete-user") {
                $this->addErrorMessage($this->deleteUser($_POST['username']));
            }
        }
    }
    
    /**
     * Plot the form for inserting / editing of user
     * @param string $id Identification of user
     * @param bool $readonly True for administrator (could edit privilege level)
     */
    private function plotUsersForm(string $id = null, bool $readonly = false) {
        $this->plotBoxStart();
        if($id == null) {
            echo '<h3 id="insert-user">Insert user to system</h3>';
            echo '<form action="users.html" method="post">';
                echo '<input type="hidden" name="action" value="insert-user">';
                echo '<label for="username">Login:</label>';
                echo '<input type="text" pattern="[a-zA-Z\d]+" id="username" name="username" maxlength="120" required placeholder="Username">';
                echo '<label for="pass">Password:</label>';
                echo '<input type="password" pattern="[a-zA-Z\d]+" id="pass" name="pass" maxlength="120" required placeholder="Password">';
                echo '<label for="privilege">Privilege level:</label>';
                echo '<select id="privilege" name="privilege">';
                    echo '<option value="admin">Administrator</option>';
                    echo '<option value="user">User</option>';
                echo '</select>';
                echo '<label for="description">Description of user:</label>';
                echo '<input type="text" id="description" name="description" maxlength="256" required placeholder="Description">';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="INSERT" id="submitF">';
            echo '</form>';
        }
        else {
            $users = $this->getDBH()->prepare("SELECT * FROM user WHERE user_name=:login");
            $users->bindParam(":login", $id, PDO::PARAM_STR);
            $users->execute();
            $user = $users->fetch(PDO::FETCH_ASSOC);
            
            echo '<h3 id="edit-user">Edit user of the system</h3>';
            echo '<form action="users.html" method="post">';
                echo '<input type="hidden" name="action" value="edit-user">';
                echo '<input type="hidden" name="oldusername" value="'.$user['user_name'].'">';
                echo '<label for="username">Login:</label>';
                echo '<input type="text" pattern="[a-zA-Z\d]+" id="username" readonly name="username" maxlength="120" required placeholder="Username" value="'.$user['user_name'].'">';
                echo '<label for="pass">Password:</label>';
                echo '<input type="password" pattern="[a-zA-Z\d]+" id="pass" name="pass" maxlength="120" required placeholder="Password">';
                if(!$readonly) {
                    echo '<label for="privilege">Privilege level:</label>';
                    echo '<select id="privilege" name="privilege">';
                        echo '<option value="admin">Administrator</option>';
                        echo '<option value="user" '.($user['user_privilege'] == 'user' ? "selected" : "").'>User</option>';
                    echo '</select>';
                }
                else {
                    echo '<input type="hidden" id="privilege" name="privilege" value="'.$user['user_privilege'].'">';
                }
                echo '<label for="description">Description of user:</label>';
                echo '<input type="text" id="description" name="description" maxlength="256" required placeholder="Description" value="'.$user['user_description'].'">';
                echo '<label for="submitF">Submit request:</label>';
                echo '<input type="submit" value="EDIT" id="submitF">';
            echo '</form>';
        }
        $this->plotBoxEnd();
        
    }
    
    /**
     * Plot the list of all users of system
     */
    private function plotUsersList() {
        $this->plotBoxStart();
        echo '<h3 id="list">List of users in system</h3>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<td>Username</td>';
        echo '<td>Privilege</td>';
        echo '<td>Description</td>';
        echo '<td>Delete</td>';
        echo '</tr>';
        echo '<tbody>';
            $users = $this->getDBH()->query("SELECT * FROM user ORDER BY user_name");
            while($user = $users->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                    echo '<td>';
                    echo '<a href="index.php?id=users&action=edit-user&username=' . $user['user_name'].'#edit-user" title="Click to edit" >' . $user['user_name']."</a>";
                    echo '</td>';
                    echo '<td>';
                    echo $user['user_privilege'];
                    echo '</td>';
                    echo '<td>';
                    echo $user['user_description'];
                    echo '</td>';
                    echo '<td>';
                    echo '<a href="index.php?id=users&action=delete-user&username=' . $user['user_name'].'#delete-user" title="Click to delete" > <span class="fa fa-times"></span> </a>';
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
        
        if($this->getUserPrivilege() == "admin") {
            
            if($_GET['action'] == "delete-user") {
                $this->plotBoxStart('warning');
                echo '<p>';
                echo 'Do you really want to remove item <strong>';
                echo $_GET['username'];
                echo "</strong>?";
                echo '</p>';
                echo '<form action="users.html" method="post">';
                echo '<a href="users.html" class="button left">CANCEL</a>';
                echo '<input type="hidden" name="action" value="delete-user">';
                echo '<input type="hidden" name="username" value="'.$_GET['username'].'">';
                echo '<input type="submit" class="left" value="YES" >';
                echo '<div class="clear"></div>';
                echo '</form>';
                echo '<div class="clear"></div>';
                $this->plotBoxEnd();
            }
            
            $this->plotBoxStart();
            echo '<p>';
            echo '<a href="/users.html#list" class="navbutton">LIST OF USERS</a>'; 
            echo '<a href="/users.html#insert-user" class="navbutton">INSERT USER</a>';
            echo '</p>';
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();

            $this->plotBoxStart();
            echo '<h2>Users of system</h2>';
            $this->plotUsersList();
            $this->plotUsersForm($_GET['action'] == 'edit-user' ? $_GET['username'] : null );
            $this->plotBoxEnd();
        }
        else if($this->getUserPrivilege() == "user") {
            $this->plotBoxStart('notice');
            echo '<p>';
            echo '<strong>Notice:</strong> Your current privilege level permits only to change your current profile.';
            echo '</p>';
            echo '<div class="clear"></div>';
            $this->plotBoxEnd();

            $this->plotBoxStart();
            echo '<h2>Edit your profile</h2>';
            $this->plotUsersForm($this->getUserLogin(), true);
            $this->plotBoxEnd();
        }
    }
}

