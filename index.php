<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This is initial script of system
 */
declare(strict_types=1);

require_once './Page.php';
require_once './HomePage.php';
require_once './TaskPage.php';
require_once './StationsPage.php';
require_once './UsersPage.php';
require_once './UserGuidePage.php';


/**
 * Start of the system
 * @return int Error id
 * @author David Salac
 */
function init() : int {
    $page = null;
    if($_GET['id'] == 'log-out') {
        /* DESTROY ALL SESSION'S DATA */
        session_start();
        session_unset(); 
        session_destroy(); 

        /* This latter print Sing-in form */
        $page = new HomePage();
    }
    else if($_GET['id'] == 'tasks') {
        $page = new TaskPage();
    }
    else if($_GET['id'] == 'stations') {
        $page = new StationsPage();
    }
    else if($_GET['id'] == 'users') {
        $page = new UsersPage();
    }
    else if($_GET['id'] == 'user-guide') {
        $page = new UserGuidePage();
    }
    else {
        $page = new HomePage();
    }
    $page->plotPage();
    
    return 0;
}
init();