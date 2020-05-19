<?php
function validate() { # validate the information sent through the HTTP POST request.
    $ok = true;
    if (isset($_POST['nume']) && !preg_match('/[A-Za-z \-]/', $_POST['nume'])) # check if a name is sent through HTTP
        $ok = false;
    if (isset($_POST['comentariu']) && ! (strlen($_POST['comentariu']) > 0 && strlen($_POST['comentariu']) <= 250)) # check if a comment is sent through HTTP and its length does not exceed 250 characters
        $ok = false;
    if (!$ok) # if conditions are satisfied, True is returned.
        echo "<h2>Bad Request</h2>";
    return $ok;
}

if (!validate())
    return;

try {
    $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (isset($_POST['nume']) && isset($_POST['comentariu'])) { # if there is information received through POST
    $_POST['comentariu'] = htmlentities($_POST['comentariu']); # avoid XSS (cross-site-scripting) by transforming all the comment's applicable characters to html entities. (Partially solves the SQLI problem)
	# Nonetheless, XSS can be solved using strip_tags()
    $cmd = $conn->prepare("INSERT INTO comentarii(Nume, Comentariu, Status) VALUES (:nume, :comentariu, 0)");
	# insert the values into the Comments DB table, with the comment's status == 0, which means it needs to be approved.
    $cmd->bindParam(":nume", $_POST['nume']);
    $cmd->bindParam(":comentariu", $_POST['comentariu']);
    $cmd->execute();
}

$cmd = $conn->query("SELECT * FROM comentarii WHERE Status = 1");
# display in the public site all the comments that have been approved.
$cmd->execute();
$comentarii = $cmd->fetchAll();

$conn = null; # close DB connection
?>


<a href="admin.php">Login Admin</a><br><br>
<article>CHEC<br><br>
INGREDIENTE
<br>6 oua
<br>6 linguri faina
<br>3 linguri ulei
<br>6 linguri zahar
<br>2 linguri de cacao
<br>1 lingurita praf de copt
<br>un praf sare
</article><br><br>
<p>Comentarii: </p>
<div class='wrap'>
    <?php
        foreach ($comentarii as $item) { # add the approved comments fetched from the DB to the page
            echo "<div class='main'>
                    <p><strong>User: " . $item['Nume'] . "</strong></p>
                    " . $item['Comentariu'] . "</div>";
        }
    ?>
    <form method="post" action="index.php">
        <input type="text" placeholder="Nume" name="nume"><br><br>
        <textarea placeholder="Comentariu" name="comentariu" maxlength="250"></textarea><br><br> <!--allow no more that 250 characters in a comment - avoid overflow in the database and through the HTTP request-->
        <input type="submit" value="Adauga Comentariu">
    </form>
</div>


<style>
.main { display: flex; flex-direction: column; border: 1px solid black; margin-bottom: 10px; }
.wrap{ display: flex; flex-direction: column; }
</style>