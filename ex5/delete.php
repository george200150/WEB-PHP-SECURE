<?php
function validate() {
    $ok = true;
    if (isset($_GET['imageId']) && !is_numeric($_GET['imageId'])) # avoid SQLI by verifying the type data received through HTTP
        $ok = false;
    if (!isset($_SESSION['username']) && !isset($_SESSION['password'])) # prevent unauthenticated user from deleting pictures
        $ok = false;
    if (!$ok)
        echo ("<h2>Bad Request</h2>");
    return $ok;
}
session_start();

if (!validate())
	return;


try { # test connection to the database
    $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: ".$e->getMessage());
}

$q = $conn->prepare("SELECT ID FROM users WHERE username = :usern"); # get the ID of the user (the session does not store it)
$q->bindParam(":usern", $_SESSION['username']);
$q->execute();
$retreived_id = $q->fetchAll()[0]["ID"]; # retreived the ID from the users table


$quer = $conn->prepare("SELECT Picture FROM pictures WHERE ID = :id AND UserId = :userid");
$quer->bindParam(":id", $_GET['imageId']);
$quer->bindParam(":userid", $retreived_id);
$quer->execute();

# never forget. once you fetched all, there is nothing left in the query !!!
$fileName = $quer->fetchAll()[0]["Picture"]; # retreive the filename

$cmd = $conn->prepare("DELETE FROM pictures WHERE ID = :id");
$cmd->bindParam(":id", $_GET['imageId']);
$cmd->execute();

unlink('./images/' . $fileName); # delete the file from disk (no memory leaks)
header("Location:website.php"); # redirect the user to the main page after the deletion

$conn = null;
?>