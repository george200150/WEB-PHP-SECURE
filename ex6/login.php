<?php
function validate() { # check if there is information sent through POST and if it follows the format rules
	$ok = true;
	if (isset($_POST['username']) && !preg_match('/[a-zA-Z0-9_]/', $_POST['username'])) # validate username (SQLI proof)
		$ok = false;
	if (isset($_POST['password']) && !preg_match('/[a-zA-Z0-9_]/', $_POST['password'])) # validate username (SQLI proof)
		$ok = false;
	if (!$ok)
		echo "<h1>Bad Request</h1>";
	return $ok;
}

function login($username, $password) { # check DB connection
	try {
		$conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		die("Connection failed: " . $e->getMessage());
	}
	$statement = $conn->prepare("SELECT * FROM admini WHERE username = :user AND password = :pass"); # find admin by credentials
	$statement->bindParam(":user", $username);
	$statement->bindParam(":pass", $password);
	$statement->execute();
	$ok = count($statement->fetchAll()) == 1; # make sure we found the admin account in the DB
	$conn = null;
	return $ok;
}

if (!validate()) # no information is sent when first accessing the site; also, validation fails when credentials are wrong.
	return;

session_start(); # session will remember the access credentials as long as the user does not close the browser.
if ((isset($_SESSION['username']) && isset($_SESSION['password']) && login($_SESSION['username'],
			$_SESSION['password'])) || (isset($_POST['username']) && isset($_POST['password']) &&
		login($_POST['username'], $_POST['password']))) {
	# if the session is not expired and there are credentials saved, or there are parameters sent through POST, and, in both cases,
	# the login has successfully been done, (in the second case, we save the credentials in the session, in order to automatically
	# redirect the user when he tries to login) we redirect the user to the admin page.
	if (!isset($_SESSION['username']))
		$_SESSION['username'] = $_POST['username'];
	if (!isset($_SESSION['password']))
		$_SESSION['password'] = $_POST['password'];
	header("Location:admin.php"); # if the credentials are ok, redirect the user to the admin page.
}
else { # do not redirect the user, instead show the error message and ask for credentials again.
	session_destroy(); # destroy the session, to clear the wrong credentials
	if (isset($_POST['username']))
		echo "Wrong Username/Password";
}

?>
<form method="post" action="login.php">
	<input type="text" placeholder="Username" name="username"><br><br>
	<input type="password" placeholder="Password" name="password"><br><br>
	<input type="submit" value="Log In">
</form>
