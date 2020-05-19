<?php

# validare
function validate()
{
    $ok = true;
    if (isset($_POST['username']) && !preg_match('/[a-zA-Z0-9_]/', $_POST['username']))
        $ok = false;
    if (isset($_POST['password']) && !preg_match('/[a-zA-Z0-9_]/', $_POST['password']))
        $ok = false;
    if (!$ok)
        echo "<h1>Bad Request</h1>";
    return $ok;
}

# verifica daca exista user-ul
function login($username, $password)
{
    try {
        $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: ".$e->getMessage());
    }
    $statement = $conn->prepare("SELECT * FROM profesori WHERE username = :user AND password = :pass"); # get the user from the DB
    $statement->bindParam(":user", $username);
    $statement->bindParam(":pass", $password);
    $statement->execute();
    $ok = count($statement->fetchAll()) == 1; # $ok este True daca am gasit utilizatorul
    $conn = null;
    return $ok;
}


if (!validate()) # daca nu se trece de validare, terminam executia
    return;

# porneste sesiunea
session_start();

if ((isset($_SESSION['username']) && isset($_SESSION['password']) && login($_SESSION['username'],
            $_SESSION['password'])) || (isset($_POST['username']) && isset($_POST['password']) &&
        login($_POST['username'], $_POST['password'])))
{
    if (!isset($_SESSION['username']))
        $_SESSION['username'] = $_POST['username'];
    if (!isset($_SESSION['password']))
        $_SESSION['password'] = $_POST['password'];
    header("Location:profesori.php");
}
else
{
    # distruge sesiunea
    session_destroy();
    if (isset($_POST['username']))
        echo "Wrong Username/Password";
}
?>

<form method="post" action="login.php">
    <input type="text" placeholder="Username" name="username"><br><br>
    <input type="password" placeholder="Password" name="password"><br><br>
    <input type="submit" value="Log In">
</form>
