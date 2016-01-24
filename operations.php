<?php
require_once('db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

ValidateAjaxRequest();

function ValidateAjaxRequest() {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        ValidateAction();
    }
}

function ValidateAction(){
    if (isset($_POST["Action"]) && !empty($_POST["Action"])) {
        $Action = $_POST["Action"];
        DBOperation($Action);
    }
}

function DBOperation($Action){
    switch($Action) {
        case "UnitTest": UnitTest($Action);
            break;
        case "Register": Register($Action);
            break;
        case "SignIn": SignIn($Action);
            break;
        case "StartSession": StartSession($Action);
            break;
        case "FetchError": FetchError($Action);
            break;
        case "InsertJSError": InsertJSError($Action);
            break;
        case "FetchActivity": FetchActivity($Action);
            break;
        case "FetchPet": FetchPet($Action);
            break;
        case "CheckSession": CheckSession();
            break;
        case "ResetActivationCode": ResetActivationCode();
            break;
        case "ResetPassword": ResetPassword();
            break;
        case "ChangePassword": ChangePassword();
            break;
    }
}

function ResetActivationCode(){
    $Email = stripslashes($_POST["Email"]);
    $ActivationCode = hash('sha256', uniqid(rand(), true));
    global $PDOconn;
    $Query = 'UPDATE djkabau1_petsignin.Account set ActivationCode = (?) where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $ActivationCode, PDO::PARAM_INT, 64);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    mail($Email,"Activate account","Please verify your account by clicking on this link: https://petsignin.alibkaba.com/petsignin/activate.php?confirm=$ActivationCode");
    echo json_encode("0");
    $PDOconn = null;
}

function ResetPassword(){
    $Email = stripslashes($_POST["Email"]);
    $ActivationCode = hash('sha256', uniqid(rand(), true));
    global $PDOconn;
    $Query = 'UPDATE djkabau1_petsignin.Account set ActivationCode = (?) where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $ActivationCode, PDO::PARAM_INT, 64);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    mail($Email,"Activate account","Please verify your account by clicking on this link: https://petsignin.alibkaba.com/petsignin/activate.php?confirm=$ActivationCode");
    echo json_encode("0");
    $PDOconn = null;
}

function FetchPet($Action){
    $Email = "blenjar@gmail.com";
    global $PDOconn;
    $Query = 'SELECT * FROM djkabau1_petsignin.Pet where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->execute();
    $Response = $Statement->fetchAll();
    echo json_encode($Response);
    $PDOconn = null;
}

function FetchActivity($Action){
    $Email = "blenjar@gmail.com";
    global $PDOconn;
    $Query = 'SELECT ActivityMSG, LogDate FROM djkabau1_petsignin.Activity where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->execute();
    $Response = $Statement->fetchAll();
    echo json_encode($Response);
    $PDOconn = null;
}

//needs attention
//$Statement = $PDOconn->prepare($Query);           needs an error handling procedure
//$Statement->execute();                            needs an error handling procedure
//$Statement->fetch(PDO::FETCH_ASSOC);              needs an error handling procedure


//Multiple use
function Execute($Statement,$Action,$Email){
    try {
        if(!$Statement->execute()) {
            $Response = array('action' => $Action, 'status' => "0");
            echo json_encode($Response);
        }
    } catch (PDOException $e) {
        //echo 'Connection failed: ' . $e->getMessage() . "\n";
        $ErrorMSG = 'Execute statement failed: ' . $e->getMessage() . "\n";
        Error($Action,$Email,$ErrorMSG);
    }
}

function Fetch($Statement,$Action,$Email){
    try {
        if(!$Response = $Statement->fetch(PDO::FETCH_ASSOC)) {

        }
    } catch (PDOException $e) {
        //echo 'Connection failed: ' . $e->getMessage() . "\n";
        $ErrorMSG = 'Fetch statement failed: ' . $e->getMessage() . "\n";
        Error($Action,$Email,$ErrorMSG);
    }
}

function Error($Action,$ErrorMSG,$Email){
    if (!isset($Email)) {
        $Email = NULL;
    }
    global $PDOconn;
    $Query = 'INSERT INTO djkabau1_petsignin.Error (Email, Action, ErrorMSG) VALUES (?,?,?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->bindParam(2, $Action, PDO::PARAM_STR, 45);
    $Statement->bindParam(3, $ErrorMSG, PDO::PARAM_STR, 100);
    $Statement->execute();
    $PDOconn = null;
}

function InsertJSError($Action){
    $ErrorMSG = $_POST["ErrorMSG"];
    global $PDOconn;
    $Query = 'INSERT INTO djkabau1_petsignin.Error (Action, ErrorMSG) VALUES (?,?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Action, PDO::PARAM_STR, 45);
    $Statement->bindParam(2, $ErrorMSG, PDO::PARAM_STR, 100);
    $Statement->execute();
    $PDOconn = null;
}

function InsertActivity($Email,$ActivityMSG){
    global $PDOconn;
    $Query = 'INSERT INTO djkabau1_petsignin.Activity (Email, ActivityMSG) VALUES (?,?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->bindParam(2, $ActivityMSG, PDO::PARAM_STR, 45);
    $Statement->execute();
}

function HashIt($Password){
    $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);
    return $HashedPassword;
}

//Single use
function UnitTest($Action){
    global $PDOconn;
    $Query = 'DROP TABLE IF EXISTS djkabau1_petsignin.UnitTest ;
	CREATE TABLE IF NOT EXISTS djkabau1_petsignin.UnitTest (
	TestColumn INT NOT NULL,
	PRIMARY KEY (TestColumn))
	ENGINE = InnoDB;
	USE djkabau1_petsignin';
    $Statement = $PDOconn->prepare($Query);
    $Statement->execute();

    $Value = "1";
    $Query = 'INSERT INTO djkabau1_petsignin.UnitTest (TestColumn) VALUES (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Value, PDO::PARAM_INT);
    $Statement->execute();

    $UpdatedValue = "2";
    $Query = 'UPDATE djkabau1_petsignin.UnitTest set TestColumn = (?) where TestColumn = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $UpdatedValue, PDO::PARAM_INT);
    $Statement->bindParam(2, $Value, PDO::PARAM_INT);
    $Statement->execute();

    $Query = 'DELETE FROM djkabau1_petsignin.UnitTest WHERE TestColumn = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $UpdatedValue, PDO::PARAM_INT);
    $Statement->execute();

    $Query = 'DROP TABLE IF EXISTS djkabau1_petsignin.UnitTest';
    $Statement = $PDOconn->prepare($Query);
    $Statement->execute();
    echo json_encode("Unit Test successful");//do I need try and catch for unit test? do I need to fetch?
    $PDOconn = null;
}

function AddAttempt($UserData,$Email){
    $NewAttempt = $UserData['Attempts'];
    $NewAttempt++;
    global $PDOconn;
    $Query = 'UPDATE djkabau1_petsignin.Account set Attempts = (?) where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $NewAttempt, PDO::PARAM_INT, 1);
    $Statement->bindParam(2, $Email, PDO::PARAM_STR, 45);
    $Statement->execute();
}

function ValidatePassword($Password,$HashedPassword){
    if (password_verify($Password, $HashedPassword)) {
        return 1;
    } else {
        return 0;
    }
}

function SignIn($Action){
    $Email = stripslashes($_POST["Email"]);
    $UserData['ValidateEmail'] = 1;
    $Password = stripslashes($_POST["Password"]);
    $UserData = GrabUserData($Email);
    $HashedPassword = $UserData['Password'];
    $PasswordResponse = ValidatePassword($Password,$HashedPassword);
    if($Email == $UserData['Email'] && $PasswordResponse == 1){
        if($UserData['Attempts'] < 5){
            if($UserData['ValidateEmail'] == 0) {
                AddAttempt($UserData,$Email);
                $ActivityMSG = "You attempted to sign in but your account wasn't activated.";
                InsertActivity($Email,$ActivityMSG);
                echo json_encode("3");
                $PDOconn = null;
                exit;
            }else{
                ResetAttempts($Email);
                $ActivityMSG = "You signed in.";
                InsertActivity($Email,$ActivityMSG);
                StartSession($Email);
                echo json_encode("2");
                $PDOconn = null;
                exit;
            }
        }else{
            $ActivityMSG = "Your account is locked out because someone attempted to sign in with your email 5 times in a row.";
            InsertActivity($Email,$ActivityMSG);
            echo json_encode("0");
            $PDOconn = null;
            exit;
        }
    }else{
        if($UserData['Attempts'] < 5){
            AddAttempt($UserData,$Email);
            $ActivityMSG = "Your account will be locked out if you fail to sign in 5 times in a row.";
            InsertActivity($Email,$ActivityMSG);
            echo json_encode("1");
            $PDOconn = null;
            exit;
        }else{
            $ActivityMSG = "Your account is locked out because someone attempted to sign in with your email 5 times in a row.";
            InsertActivity($Email,$ActivityMSG);
            echo json_encode("0");
            $PDOconn = null;
            exit;
        }
    }
}

function ResetAttempts($Email){
    global $PDOconn;
    $Query = 'UPDATE djkabau1_petsignin.Account set Attempts = ("0") where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->execute();
}

function Register($Action){
    $Email = stripslashes($_POST["Email"]);
    $Password = stripslashes($_POST["Password"]);
    $UserData = GrabUserData($Email);
    if($Email == $UserData['Email']){
        if($UserData['Attempts'] < 5){
            AddAttempt($UserData,$Email);
            $ActivityMSG = "Your account will be locked out when someone attempts to register with you account 5 times in a row.";
            InsertActivity($Email,$ActivityMSG);
            echo json_encode("1");
            exit;
        }else{
            $ActivityMSG = "Your account is locked out because someone attempted to register an account using your email 5 times in a row.";
            InsertActivity($Email,$ActivityMSG);
            echo json_encode("0");
            exit;
        }
    }
    $HashedPassword = HashIt($Password);
    $ValidateEmail = 0;
    $Disabled = 0;
    $Attempts = 0;
    $AdminCode = 0;
    $ActivationCode = hash('sha256', uniqid(rand(), true));
    global $PDOconn;
    $Query = 'INSERT INTO djkabau1_petsignin.Account (Email, Password, ValidateEmail, Disabled, Attempts, AdminCode, ActivationCode) VALUES (?,?,?,?,?,?,?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->bindParam(2, $HashedPassword, PDO::PARAM_STR, 255);
    $Statement->bindParam(3, $ValidateEmail, PDO::PARAM_INT, 1);
    $Statement->bindParam(4, $Disabled, PDO::PARAM_STR, 64);
    $Statement->bindParam(5, $Attempts, PDO::PARAM_INT, 1);
    $Statement->bindParam(6, $AdminCode, PDO::PARAM_INT, 1);
    $Statement->bindParam(7, $ActivationCode, PDO::PARAM_INT, 1);
    $Statement->execute();
    mail($Email,"Activate account","Please verify your account by clicking on this link: https://petsignin.alibkaba.com/petsignin/activate.php?confirm=$ActivationCode");
    echo json_encode("2");
    $PDOconn = null;
}

function GrabUserData($Email){
    global $PDOconn;
    $Query = 'SELECT * FROM djkabau1_petsignin.Account where Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $Email, PDO::PARAM_STR, 45);
    $Statement->execute();
    $Response = $Statement->fetch(PDO::FETCH_ASSOC);
    return $Response;
}

function MailOut($Email, $Subject, $EmailMSG){ //fix this later, from and reply not working
    $Headers = 'From: alibkaba@alibkaba.com' . " " .
        'Reply-To: alibkaba@gmail.com' . " " .
        'X-Mailer: PHP/' . phpversion();
    mail($Email,$Subject,$EmailMSG,$Headers);
}

function SignOut(){
    session_unset();
    session_destroy();
}

function CheckSession(){
    $Page = stripslashes($_POST["Page"]);
    ini_set('session.cookie_lifetime', 1800);
    ini_set('session.gc_maxlifetime', 1800);
    session_start();
    if(isset($_SESSION['Session_ID'])){
        $SessionID = $_SESSION["Session_ID"];
        $SessionData = GrabSessionData($SessionID);
        $BrowserData = GetBrowserData();
        if($SessionData['IP'] !== $BrowserData['IP'] && $SessionData['Browser'] !== $BrowserData['Browser'] && $SessionData['Platform'] !== $BrowserData['Platform']){
            echo json_encode("1");
        }else{
            if($Page == "index"){
                echo json_encode("2");
            }else{
                echo json_encode("3");
            }
        }
    }else{
        if($Page == "dashboard"){
            echo json_encode("0");
        }else{
            echo json_encode("4");
        }
    }
}

function GrabSessionData($SessionID){
    global $PDOconn;
    $Query = 'SELECT * FROM djkabau1_petsignin.Session SessionID Email = (?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $SessionID, PDO::PARAM_STR, 64);
    $Statement->execute();
    $Response = $Statement->fetch(PDO::FETCH_ASSOC);
    return $Response;
}

function StartSession($Email){
    ini_set('session.cookie_lifetime', 1800); //client side
    ini_set('session.gc_maxlifetime', 1800); //server size
    session_start();
    $BrowserData = GetBrowserData();
    $SessionID = hash('sha256', uniqid(rand(), true));
    $_SESSION["Session_ID"] = $SessionID;
    $SessionIP = $BrowserData['IP'];
    $SessionBrowser = $BrowserData['Browser'];
    $SessionPlatform = $BrowserData['Platform'];

    global $PDOconn;
    $Query = 'INSERT INTO djkabau1_petsignin.Session (SessionID, Email, IP, Browser, Platform) VALUES (?,?,?,?,?)';
    $Statement = $PDOconn->prepare($Query);
    $Statement->bindParam(1, $SessionID, PDO::PARAM_STR, 64);
    $Statement->bindParam(2, $Email, PDO::PARAM_STR, 45);
    $Statement->bindParam(3, $SessionIP, PDO::PARAM_STR, 45);
    $Statement->bindParam(4, $SessionBrowser, PDO::PARAM_STR, 45);
    $Statement->bindParam(5, $SessionPlatform, PDO::PARAM_STR, 45);
    $Statement->execute();
}

function GetBrowserData(){
    $SessionIP = $_SERVER['REMOTE_ADDR'];
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $BrowserName = 'Unknown';
    $Platform = 'Unknown';

    if (preg_match('/linux/i', $u_agent)) {
        $Platform = 'Linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $Platform = 'Mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $Platform = 'Windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $BrowserName = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $BrowserName = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $BrowserName = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $BrowserName = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $BrowserName = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $BrowserName = 'Netscape';
        $ub = "Netscape";
    }

    return array(
        'IP' => $SessionIP,
        'Browser' => $BrowserName,
        'Platform' => $Platform
    );
}