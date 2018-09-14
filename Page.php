<?php
/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This script define abstract class for later pages (particularly layouts etc.)
 */

/**
 * Fundamental application's class with basic functionality and layouts
 * @author David Salac
 */
abstract class Page {
    private $dbh;
    private $errorMessage;
    private $pageTitle;
    private $userLogin;
    private $userPrivilege;
    
    /**
     * Get login information of current user
     * @return string user's login
     */
    protected final function getUserLogin() {
        return $this->userLogin;
    }
    
    /**
     * Get privilege level of current user
     * @return int Privilege level
     */
    protected final function getUserPrivilege() {
        return $this->userPrivilege;
    }
    
    /**
     * Get database handle
     * @return \PDO Database handle
     */
    protected function getDBH() : PDO {
        return $this->dbh;
    }
    /**
     * Set title (editable part) of current page
     * @param string $title Title of current page
     */
    protected function setPageTitle(string $title) {
        $this->pageTitle = $title;
        $this->errorMessage = "";
    }
    /**
     * Starts session and set up database connection
     */
    public function __construct() {
        session_start();
        require_once './Config.php';
        $this->dbh = new PDO(MYSQL_SERVER, /* DATABASE NAME */
        MYSQL_DATABASE_USER, /* DATABASE USER */
        MYSQL_DATABASE_PASSWORD); /* DATABASE PASSWORD */
    }

    /**
     * Plot head part of page
     */
    protected function plotHead() {
        echo '<!DOCTYPE html><html><head><title>Diploma thesis | '.$this->pageTitle.'</title><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" media="all" href="icon/css/font-awesome.css"><link rel="stylesheet" media="all" href="style.css"><script src="/jquery.js"></script><script src="/biginteger.js" type="text/javascript"></script></head><body>';
    }
    /**
     * Plot aside part of page
     */
    protected function plotAside() {
        echo '<aside><div class="content"><a href="sitemap.html#sitemap" title="Site map" id="siteMap">&nbsp;</a><ul class="menu"><li>';
        echo '<a href="/" title="Overview" class="icon fa-flag'.(!isset($_GET['id']) ? " selected" : "").'"><span>Overview</span></a></li><li>'; 
        echo '<a href="/user-guide.html" title="User guide" class="icon fa-file-text-o'.($_GET['id'] == "user-guide" ? " selected" : "").'"><span>User guide</span></a></li><li>'; 
        echo '<a href="/tasks.html" title="Tasks" class="icon fa-list-ol'.($_GET['id'] == "tasks" ? " selected" : "").'"><span>Tasks</span></a></li><li>'; 
        echo '<a href="/stations.html" title="Stations" class="icon fa-server'.($_GET['id'] == "stations" ? " selected" : "").'"><span>Stations</span></a></li>'; 
        if($this->userPrivilege == "admin") {
            echo '<li><a href="/users.html" title="Users" class="icon fa-users'.($_GET['id'] == "users" ? " selected" : "").'"><span>Users</span></a></li>'; 
        }
        echo'</ul></div></aside>';
    }
    /**
     * Plot page header
     */
    protected function plotHeader() {
        $time = time() - $_SESSION['time'];
        $timeMin = (string)((int)($time / 60) % 60);
        $timeHours = (string)((int)($time / 3600));
        echo '<header><ul id="topMenu"><li class="separator"><a class="icon fa-user" href="users.html">'.$_SESSION['login'].'</a></li><li class="timeOfLogin">Time of login: '.$timeHours.'&nbsp;h '.$timeMin.'&nbsp;m</li><li id="logout"><a href="log-out.html" class="icon fa-sign-out">Logout</a></li></ul><div class="clear"></div></header>';
    }
    /**
     * Plot current content of page
     */
    protected abstract function plotContent();
    /**
     * Plot graphically separated box in page
     * @param string $class class attribute of box
     */
    protected function plotBoxStart(string $class = null) {
        echo '<div class="box'.($class == null ? "" : " $class").'" >';
    }
    /**
     * Plot ending tag of box element
     */
    protected function plotBoxEnd() {
        echo '</div>';
    }
    /**
     * Add error message to log
     * @param string $message Error message
     */
    protected function addErrorMessage(string $message) {
        if(strlen($message) > 0) {
            $this->errorMessage .= "<p><strong>Error: </strong> ".$message."</p>";
        }
    }
    /**
     * Plots visible error message log
     */
    protected function plotErrorLog() {
        if(strlen($this->errorMessage) > 0 ) {
            $this->plotBoxStart('error');
                echo $this->errorMessage;
            $this->plotBoxEnd();
        }
    }
    /**
     * Plot main part of page with content
     */
    protected function plotArticle() {
        echo '<article><div id="logo"><a href="/" title="Back to Overview"><img src="logo.svg" alt="Diploma Thesis" ><span>DIPLOMA THESIS</span></a><div class="right"><strong>DIPLOMA THESIS APPLICATION</strong><p>Welcome to application designed for cryptanalysis of public key cryptosystem.</p><em>Author: David Salač</em></div><div class="clear"></div></div>'; 
        $this->plotErrorLog();
        echo $this->plotContent();
        echo '</article>';
    }
    /**
     * Plot footer of page
     */
    protected function plotFooter() { 
        echo '<footer><div class="box">Author of this application is <a href="mailto:david(dot)salac(at)tul(dot)cz">David Salač</a>. This application is part of diploma thesis. The copyright holder of this application (and also the whole content of diploma thesis) is <a href="http://www.tul.cz">Technical University of Liberec</a>.</div></footer></body></html>';
    }
    /**
     * Plot the complete sing-in page
     * @param bool $wrongLogin Indicate unsuccessful attempt for sing-in
     */
    private function plotLoginPage(bool $wrongLogin = false) {
        if(!$wrongLogin) {
            echo '<!DOCTYPE html><html><head><title>Diploma thesis | Sing-in to the system</title><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" media="all" href="login.css"></head><body><!--[if IE]><p><strong>Please do not use your current browser - Microsoft Internet Explorer is deprecated. You could simply install and use Mozilla Firefox or Google Chrome browsers - both are available for free. Via this browser application will not work correctly.</strong></p><![endif]--><h1>Sing-in form</h1><form action="index.php" method="post"><div class="whiteBack"><h2>Login form</h2><div class="clear"></div><div class="left"><input type="text" name="login" required="" placeholder="Login" ><input type="password" name="password" required="" placeholder="Password"></div><div class="right"><input type="submit" value="login"><span>Are there any troubles?</span><a id="contact" href="mailto:david(dot)salac(at)tul(dot)cz" title="Send me an email to david(dot)salac(at)tul(dot)cz">contact</a></div><div class="clear"></div></div></form><p>This web is part of diploma thesis made by David Salač, Faculty of Mechatronics, Informatics and Interdisciplinary Studies, Technical University of Liberec</p></body></html>';
        }
        else {
            echo '<!DOCTYPE html><html><head><title>Diploma thesis | Sing-in to the system</title><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" media="all" href="login.css"></head><body><!--[if IE]><p><strong>Please do not use your current browser - Microsoft Internet Explorer is deprecated. You could simply install and use Mozilla Firefox or Google Chrome browsers - both are available for free. Via this browser application will not work correctly.</strong></p><![endif]--><h1>Sing-in form</h1><form action="index.php" method="post"><div class="whiteBack"><h2>Login form</h2><div class="clear"></div><div class="left"><input type="text" name="login" required="" placeholder="Login" ><input type="password" name="password" required="" placeholder="Password"></div><div class="right"><input type="submit" value="login"><span>Are there any troubles?</span><a id="contact" href="mailto:david(dot)salac(at)tul(dot)cz" title="Send me an email to david(dot)salac(at)tul(dot)cz">contact</a></div><div class="clear"></div><strong>You filled wrong login or password in form!</strong></div></form><p>This web is part of diploma thesis made by David Salač, Faculty of Mechatronics, Informatics and Interdisciplinary Studies, Technical University of Liberec</p></body></html>';
        }
    }
    /**
     * Check if visitor is singed-in
     * @return bool True if visitor is singed-in
     */
    private function isLogIn() : bool {
        if(!isset($_SESSION['time'])) {
            return false;
        }
        
        $time = $_SESSION['time'];
        /* CHECK TIME STAMP DIFFERENCE - MAX 300 min = 18 000 s */
        if( (time() - $time) > 18000) {
            return false;
        }
        $this->userPrivilege = $_SESSION['privilege'];
        $this->userLogin = $_SESSION['login'];
        
        /* CHECK VALUE OF LOG */
        $logIn = $this->dbh->prepare("SELECT log_user FROM log WHERE log_privilege=:privilege AND log_time=FROM_UNIXTIME(:time) AND log_user=:user LIMIT 1");
        $logIn->bindParam(':privilege', $this->userPrivilege, PDO::PARAM_STR);
        $logIn->bindParam(':time', $time, PDO::PARAM_INT);
        $logIn->bindParam(':user', $this->userLogin, PDO::PARAM_STR);
        $logIn->execute();        
        
        if($logIn->rowCount() == 0) {
            return false;
        }        
        return true;
    }
    /**
     * Plot static page content
     */
    private function plotStaticPage() {
        $this->plotHead();
        $this->plotAside();
        $this->plotHeader();
        $this->plotArticle();
        $this->plotFooter();
    }
    /**
     * Plot page content (universal)
     */
    public function plotPage() {
        if(!empty($_POST["login"]) && !empty($_POST['password'])) {
            $singIn = $this->dbh->prepare("SELECT user_privilege FROM user WHERE user_name=:login AND user_password_hash=:password_hash LIMIT 1");
            $singIn->bindParam(':login', $_POST["login"], PDO::PARAM_STR);
            $singIn->bindParam(':password_hash', sha1($_POST["password"]), PDO::PARAM_STR);
            $singIn->execute();
            if($singIn->rowCount() == 0) {
                $this->plotLoginPage(true);
            }
            else {
                $user = $singIn->fetch(PDO::FETCH_ASSOC);
                $login = $_POST['login'];
                $time = time();
                $privilege = $user['user_privilege'];
                $_SESSION['login'] = (string)$login;
                $_SESSION['time'] = (int)$time;
                $_SESSION['privilege'] = (string)$privilege;

                $log = $this->dbh->prepare("INSERT INTO log VALUES(:privilege, FROM_UNIXTIME(:time), :login)");
                $log->bindParam(':privilege', $privilege, PDO::PARAM_STR);
                $log->bindParam(':time', $time, PDO::PARAM_INT);
                $log->bindParam(':login', $login, PDO::PARAM_STR);
                $log->execute();

                $this->plotStaticPage();
            }
        }
        else if($this->isLogIn()) {
            $this->plotStaticPage();
        }
        else {
            $this->plotLoginPage();
        }
    }
}
