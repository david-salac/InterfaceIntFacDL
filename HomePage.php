<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script define home-page of web system
 */

/**
 * Plot the homepage of system
 * @author David Salac
 */
class HomePage extends Page {
    public function __construct() {
        parent::__construct();
        $this->setPageTitle("Overview");
    }

    /**
     * Plot the content of the page
     */
    protected function plotContent() {
        $this->plotBoxStart();
        echo '<h2>Welcome</h2>';
        echo '<p>Welcome to control panel of distributed application for cryptanalysis of public-key cryptosystems.</p>';
        $this->plotBoxEnd();
        
        $this->plotBoxStart();
        echo '<h2 id="sitemap">Popular links</h2>';
        echo '<p>Internal system links to favourite features:</p>';
        echo '<p>';
        echo '<a href="user-guide.html" class="navbutton">Manual</a> <a href="tasks.html" class="navbutton">Tasks</a> <a href="stations.html" class="navbutton">Stations (nods)</a>  <a href="users.html" class="navbutton">Users</a>';
        echo '</p>';
        echo '<div class="clear"></div>';
        $this->plotBoxEnd();
    }
}
