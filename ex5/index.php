<?php
# validate username and password in order to avoid unusual characters in the textfields
# (message sent through post -> the user cannot type scripts into the search bar and inject code into the page/database)
function validate() {
    $ok = true;
    if (isset($_POST['username']) && !preg_match('/[a-zA-Z0-9_]/', $_POST['username']))
        $ok = false;
    if (isset($_POST['password']) && !preg_match('/[a-zA-Z0-9_]/', $_POST['password']))
        $ok = false;
    if (!$ok)
        echo "<h1>Bad Request</h1>";
    return $ok;
}

function login($username, $password) {
    try { # test the database connection
        $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: ".$e->getMessage());
    }
    $statement = $conn->prepare("SELECT * FROM users WHERE username = :user AND password = :pass"); # looks a bit like hibernate
	# search for the user having the given access credentials
    $statement->bindParam(":user", $username);
    $statement->bindParam(":pass", $password);
    $statement->execute();
    $ok = count($statement->fetchAll()) == 1; # check if the user was really found 
    $conn = null;
    return $ok;
}

if (!validate()) # validate the information sent through the HTTP request
    return;

session_start(); # start a session
if ((isset($_SESSION['username']) && isset($_SESSION['password']) && login($_SESSION['username'],
            $_SESSION['password'])) || (isset($_POST['username']) && isset($_POST['password']) &&
        login($_POST['username'], $_POST['password']))) {
    if (!isset($_SESSION['username'])) # store the user's credentials in the session for avoiding further repeated logins
        $_SESSION['username'] = $_POST['username'];
    if (!isset($_SESSION['password'])) # session will expire when the browser is closed
        $_SESSION['password'] = $_POST['password'];
    header("Location:website.php"); # redirect the user to the main site
}
else {
    session_destroy(); # delete the bad credentials from the session and the session itself
    if (isset($_POST['username']))
        echo "Wrong Username/Password";
}

?>
<form action="index.php" method="post">
    <input type="text" name="username" placeholder="Username"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <input type="submit" value="Log In">
</form>