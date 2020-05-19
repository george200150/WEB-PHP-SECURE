<?php
session_start();

# check if username and password are provided to this session. if they are not, the site redirects the admin to the login form.
if (!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
    header("Location:login.php");
}
# check if there is a logout request. if there is one, the session is closed and the admin is redirected to the main page.
# (happens when the admin clicks on the logout link)
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location:index.php");
}

try { # test database connection with PDO (prevents SQLI)
    $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
# check if there is a request to approve a comment. if there is one, set the comment's status flag as approved
# (happens when the admin clicks on a message's "Approve" label)
if (isset($_GET['approve'])) {
    $cmd = $conn->prepare("UPDATE comentarii SET Status = 1 WHERE Comentariu = :comm");
    $cmd->bindParam(":comm", $_GET['approve']); # identify the comment by its content
    $cmd->execute();
}

$cmd = $conn->query("SELECT * FROM comentarii WHERE Status = 0"); # display all the comments that need approval
$cmd->execute();
$comentarii = $cmd->fetchAll();

$conn = null; # close the connection
?>


<table>
    <tr>
        <th>Nume Utilizator</th>
        <th>Comentariu</th>
        <th>Approve</th>
    </tr>
    <?php
        foreach ($comentarii as $item) {
            echo("<tr>
				<td>" . $item['Nume'] . "</td>
				<td>" . $item['Comentariu'] . "</td>
				<td><a href='admin.php?approve=" . $item['Comentariu'] . "'>Approve</a></td>
				</tr>");
			# add link to the "Approve" label of the message to this site with extra parameter the comment's content as identifier
        }
    ?>
</table>
<a href="admin.php?logout=true">Log Out</a> <!--access this site with extra parameter "logout" that is now true-->


<style>
    table { border-collapse: collapse; }
	
    tr, td, th { border: 1px solid black; text-align: center; }
	
    tr:nth-child(even){ background-color: #f2f2f2; }
	
	tr:not(:first-child):hover { background-color: cornflowerblue; }
</style>
