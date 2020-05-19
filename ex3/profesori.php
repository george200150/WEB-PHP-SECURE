<?php

# redirect to login page if user is not logged in (session does not have "username" and "password" as existing parameters)
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
    header("Location:login.php");
}

function is_valid() {
    $ok = true;
    if (isset($_POST['student']) && !is_numeric($_POST['student'])) # check if there is a correct id sent through POST
        $ok = false;
    if (isset($_POST['materie']) && !is_numeric($_POST['materie'])) # check if there is another correct id sent through POST
        $ok = false;
    if (isset($_POST['nota']) && !is_numeric($_POST['nota'])) # check if there is a correct grade value sent through POST
        $ok = false; # by default, the grade is selected from a combobox that has numbers ranging from 1 to 10.
    if (!$ok)
        echo "<h1>Bad Request</h1>";
    return $ok;
}
# check if there is a logout request. if any, destroy the session in order to delete the credentials and redirect the user.
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location:index.php");
}

if (!is_valid()) # first time accessing the page there is no info sent through POST
    return;

try { # test the database connection
    $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: ".$e->getMessage());
}

$cmd = $conn->query("SELECT * FROM materii"); # get all the courses (in order to display them in the combobox)
$materii = $cmd->fetchAll();

$cmd = $conn->query("SELECT * FROM studenti"); # get all the students (in order to display them in the combobox)
$studenti = $cmd->fetchAll();
# if the form was submitted and there is information about a student, a course and a grade value received through POST
if (isset($_POST['student']) && isset($_POST['materie']) && isset($_POST['nota'])) {
    $cmd = $conn->prepare("SELECT id FROM profesori where username = :user"); # select the currently logged in teacher from the DB
    $cmd->bindParam(":user", $_SESSION['username']); # search the teacher by their username
    $cmd->execute();
    $profesor_id = $cmd->fetchAll()[0]['id']; # retreive the teacher's id fom the database
	# insert the grade having the chosen grade value into the DB for the selected course, student and logged in teacher.
    $cmd  = $conn->prepare("INSERT INTO note(MaterieID, ProfesorID, StudentID, Nota) VALUES (:m, :p, :s, :n)");
    $cmd->bindParam(":m", $_POST['materie'], PDO::PARAM_INT);
    $cmd->bindParam(":p", $profesor_id, PDO::PARAM_INT);
    $cmd->bindParam(":s", $_POST['student'], PDO::PARAM_INT);
    $cmd->bindParam(":n", $_POST['nota'], PDO::PARAM_INT);
    $cmd->execute();
}

$conn = null; # close connection

?>
<style>
.dropbtn {
	padding: 16px;
	font-size: 16px;
	border: none;
	cursor: pointer;
}

.dropdown {
	position: relative;
	display: inline-block;
}

.dropdown-content {
	display: none;
	position: absolute;
	background-color: #f9f9f9;
	min-width: 160px;
	box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
	z-index: 1;
}

.dropdown-content a {
	color: black;
	padding: 12px 16px;
	text-decoration: none;
	display: block;
}

.dropdown-content a:hover { background-color: #f1f1f1; }

.dropdown:hover .dropdown-content { display: block; }

.dropdown:hover .dropbtn { background-color: #3e8e41; }
</style>


<form action="profesori.php" method="post">
    <label>Student : </label><select name="student">
        <?php
            foreach ($studenti as $student) # display all the students as options in the student combobox
                echo "<option value='".$student['id']."'>".$student['nume']."</option>";
        ?>
    </select><br>
    <label>Materie : </label><select name="materie">
        <?php
        foreach ($materii as $materie) # display all the courses as options in the course combobox
            echo "<option value='".$materie['id']."'>".$materie['nume']."</option>";
        ?>
    </select><br>
    <label>Nota : </label><select name="nota">
        <?php
        for ($i = 1; $i <= 10; $i++) # display all the grade values as options in grade combobox
            echo "<option value='".$i."'>".$i."</option>";
        ?>
    </select><br>
    <input type="submit" name="Da nota">
</form>
<a href="profesori.php?logout=true">Log Out</a> <!--logout request-->
