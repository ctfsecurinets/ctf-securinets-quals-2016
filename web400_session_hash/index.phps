<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
session_start();

require_once(/*$_SERVER['DOCUMENT_ROOT'].*/'php/config.php');

$expire = 365*24*3600;
$_COOKIE["visite"]=(isset($_COOKIE["visite"]) && !empty($_COOKIE["visite"])) ? securiser($_COOKIE["visite"]) : '';
$_COOKIE["PHPSESSID"]=(isset($_COOKIE["PHPSESSID"]) && !empty($_COOKIE["PHPSESSID"])) ? securiser($_COOKIE["PHPSESSID"]) : '';
if((existance_str($_COOKIE["visite"]) || $_COOKIE["visite"]=="") && (existance_str($_COOKIE["PHPSESSID"]) || $_COOKIE["PHPSESSID"]=="")){
if($_COOKIE["PHPSESSID"]==""){session_id();echo "Refresh the page to begin. If you have already nothing please activate your cookies.";}else{

if($_COOKIE["visite"]!=""){//old visitor
  $found=0;
  $_SESSION["visite"]=hash('sha1',$_COOKIE["visite"],false);//i don't trust the numeric vars so i hash them and i put the result in the session
  $req = $bdd->prepare('SELECT * FROM webSessions WHERE id = ? AND session= ?');
  $req->execute(array($_COOKIE['visite'],$_COOKIE["PHPSESSID"]));
  //we verify if the visit ID hashed is equal to the hash to avoid that a visitor steal the place of the older vistors
  while ($donnees = $req->fetch()){
    $found=1;
    if($_COOKIE["visite"]==$progression)$_COOKIE["visite"]=0;//if it's the turn of this user to see the secret of this page, we remove the visit ID in order to tell the user the chance to see this page only once (next time he will join the queue in the last position)
  }$req->closeCursor();
  //else we know that this user changed manually the visit ID. if he takes a visit ID known by the system as an ID of an older visitor we mark him as a new visitor because this page know that this ID is stolen
  if($found==0 && $_COOKIE["visite"]<=$max_queue){
    setcookie("visite",$max_queue+1,time()+$expire);//a new visitor have to initialise his visit ID
    $_COOKIE["visite"]=$max_queue+1;
  }
}else{//new visitor
  setcookie("visite",$max_queue+1,time()+$expire);
  $_COOKIE["visite"]=$max_queue+1;
  $_SESSION["visite"]=hash('sha1',$max_queue+1,false);//i don't trust the numeric vars so i hash them and i put the result in the session
  nothing($bdd);//(really it's nothing. You lose your time here)
  $sql = "INSERT INTO webSessions(id,session,ip) VALUES ('',:sess,'')";
  $stmt = $bdd->prepare($sql);
  $stmt->bindParam(':sess', $_COOKIE['PHPSESSID'], PDO::PARAM_STR);
  $stmt->execute();
  $max_queue = $bdd->lastInsertId();
  $_COOKIE["visite"]=$max_queue;
  $_SESSION["visite"]=hash('sha1',$max_queue,false);
  $stmt->closeCursor();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="template/style.css" />
<title>Session Hash</title>
</head>

<body>
  <div id="page">
    <div id="header">
      <img  src="template/logo.png" style=" margin-left:35%; margin-right:35%; ">
    </div>
    <div id="bar">
      <h2>Session Hash</h2>
    </div>
    <div class="contentTitle"></div>
    <div class="contentText">
    <!-- DEBUT DE L'EPREUVE --><!-- there is no form and there is nothing to send with a form -->
    <p>Each visitor must wait quietly for his turn to see my treasure.</p>
    <p>No visitor has the right to steal the place of the old visitors because everything is recorded.</p>
    <p>Each visitor who wisely waited his turn shall be accorded a minute to see my treasure.</p>
    <p>Your position in the queue: <?php echo $_COOKIE["visite"];?>/<?php echo $max_queue;?></p>
    <p>Progression of the queue: <?php echo progression();?></p>
    <p><?php if($_SESSION["visite"]=="0")echo $treasure; ?></p>
    <a href="index.php?view_source=1">Need Help ?</a> - <a href="index.phps">Download source code</a>
    <?php
	$_GET["view_source"]=(isset($_GET["view_source"]) && !empty($_GET["view_source"])) ? securiser($_GET["view_source"]) : '';
	if($_GET["view_source"]){echo "<div>";
	highlight_file("index.php");echo "</div>";
    	}?>
    <!-- FIN DE L'EPREUVE -->
    </div>
  </div>
  <div id="footer">www.securinets.com</div>
</body>
</html>
<?php }}else echo "attack detected";?>
