<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script define user guide page of web system
 */

/**
 * Plot the manual page for the system
 * @author David Salac
 */
class UserGuidePage extends Page {
    public function __construct() {
        parent::__construct();
        $this->setPageTitle("User guide");
    }
    /**
     * Plot the page content
     */
    protected function plotContent() {
        $this->plotBoxStart();
        echo '<h2>User guide</h2>';
        echo '<p>Here is manual for this application.</p>';
        echo '<ol class="listLargeStep">';
            echo '<li><a href="#new-task">How to insert new task to system</a></li>';
            echo '<li><a href="#edit-task">How to edit task in system</a></li>';
            echo '<li><a href="#remove-task">How to remove task in system</a></li>';
            
            echo '<li><a href="#new-station">How to insert new station (nod) to system</a></li>';
            echo '<li><a href="#edit-station">How to edit station (nod) in system</a></li>';
            echo '<li><a href="#remove-station">How to remove station (nod) in system</a></li>';
            
            echo '<li><a href="#users">Manage of users in system</a></li>';
        echo '</ol>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h3 id="new-task">How to insert new task to system</h3>';
        echo '<p>Go to the field called Tasks in menu bar of application. Here click to the button Insert new task. It is on the top of the page. After that fill your task to the form. When you are finished, click to the button INSERT.</p>';
        echo '<p><strong>Warning:</strong> if you insert your task in Suspend priority, system would not distributed task to stations of system.</p>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h3 id="edit-task">How to edit task in system</h3>';
        echo '<p>Go to the field called Tasks in menu bar of application. Here click to icon of pencil shown in column Task priority. After that you could change priority level of task and submit this by click to button EDIT.</p>';
        echo '<p><strong>Warning:</strong> You could edit only priority level of tasks, not the content of task. If you would like to change content of task, you have to remove it and insert the new one. </p>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h3 id="remove-task">How to remove task in system</h3>';
        echo '<p>Go to the field called Tasks in menu bar of application. Here click to icon of cross shown in column Remove. After that you could remove task by click to button YES.</p>';
        echo '<p><strong>Warning:</strong> after click to button YES, you could not undo this step!</p>';
        $this->plotBoxEnd();
        
        
        
        $this->plotBoxStart();
        echo '<h3 id="new-station">How to insert new station (nod) to system</h3>';
        echo '<p>Go to the field called Stations in menu bar of application. Click to button INSERT STATION, page will scroll down (if not, do it manually) to the form where you could insert information about new station. Station ID is unique identifier of station. It is integer value. Assigned task field is task that you would like to assign to your station. After you finished with filling of form, click to button INSERT.</p>';
        echo '<p><strong>Warning:</strong> assigned task is only task that your would prefere. System could ignore it in some situations.</p>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h3 id="edit-station">How to edit station (nod) in system</h3>';
        echo '<p>Go to the field called Stations in menu bar of application. Click to ID of station that you would like to edit (in column Station ID). After that system would scroll to the form (if not, do it yourself). Here you could edit information about the station </p>';
        echo '<p><strong>Notice:</strong> almost everything is same like it was in case you inserting new station, which is described above. </p>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h3 id="remove-station">How to remove station (nod) in system</h3>';
        echo '<p>Go to the field called Stations in menu bar of application. Here click to icon of cross shown in column Remove. After that you could remove task by click to button YES on the top of the page.</p>';
        echo '<p><strong>Warning:</strong> after click to button YES, you could not redo this step!</p>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h3 id="users">Manage of users in system</h3>';
        echo '<p>Click to menu item Users on the left menu bar. Here you could simply add new user by click the button INSERT USER (after that fill the form on the button of page, than click to button INSERT). Editing of user could be done by clicking to username (rest of procedure is same as above). Deleting of user could be done by clicking to cross in last column.</p>';
        echo '<p><strong>Warning:</strong> you has to have administrator privileges to manage of users!</p>';
        $this->plotBoxEnd();
    }
}
