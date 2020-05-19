<?php

session_start(); # start the session

if (isset($_GET['logout']) && $_GET['logout'] == 'true') { # if there is a logout request sent through GET
    session_destroy(); # then destroy the session
    header("Location:index.php"); # and redirect the user to the start
}

if (!isset($_SESSION['username']) || !isset($_SESSION['password']))
    header("Location:index.php");

try {
    $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: ".$e->getMessage());
}

# pozele unui utilizator
function get_pictures($conn, $userId)
{
    $cmd = $conn->prepare("SELECT * FROM pictures WHERE UserId = :user");
    $cmd->bindParam(":user", $userId);
    $cmd->execute();
    return $cmd->fetchAll();
}

# pozele utilizatorului logat (toti utilizatorii care au poze incarcate se afla in aceasta tabela)
$cmd = $conn->prepare("SELECT * FROM pictures WHERE UserId = (SELECT ID FROM pictures_users where Username = :user)");
# use linkage table to get the ID of the user
$cmd->bindParam(":user", $_SESSION['username']);
$cmd->execute();
$my_pictures = $cmd->fetchAll();

# ceilalti useri
$data = array();
$cmd = $conn->prepare("SELECT * FROM pictures_users WHERE Username != :user");
$cmd->bindParam(":user",$_SESSION['username']);
$cmd->execute();
$users = $cmd->fetchAll();

foreach ($users as $user)
    $data[$user['ID']] = array($user, get_pictures($conn, $user['ID']));

$conn = null;

# afiseaza poza, with_delete = true doar pt pozele utilizatorului logat
function render($picture, $with_delete=false) {
    return("<div style='padding: 20px;display: flex;flex-direction: column;align-items: center'>
        <img width='100px' height='100px' src='images/" . $picture['Picture'] . "'>
        " . ($with_delete == true ? "<a href='delete.php?imageId=" . $picture['ID'] . "'>Delete</a>" : "") . "</div>");
}

?>
<h2>My profile</h2>
<div style="display: flex">
<?php
    if (count($my_pictures) == 0) {
        echo ("No pictures");
	}
    foreach ($my_pictures as $picture) {
        echo (render($picture, true));
	}
?>
</div>
<a href="upload.php">Upload new pictures</a>
<h2>Other Users</h2>
<div style="display: flex;flex-direction: column">
    <?php
        foreach ($data as $user => $item) {
            echo "<h3>".$item[0]['Username'].": </h3><div style='display: flex'>";
            if (count($item[1]) == 0) {
                echo ("No pictures");
			}
            foreach ($item[1] as $picture) {
                echo (render($picture));
			}
            echo ("</div>");
        }
    ?>
</div>
<br> <a href="website.php?logout=true">Log Out</a>