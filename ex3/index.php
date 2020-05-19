<?php

/* PDO este o metoda de a face o conexiune cu baza de date,
utila pentru comenzi precum fetchAll. Totodata, PDO este SQLI proof. */
try {
    $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$cmd = $conn->query("SELECT * FROM note"); # display all the grades on the main window ("students are not authenticated users")
$note = $cmd->fetchAll();

function get_one($conn, $table, $id) { # returneaza o inregistrare din tabelul dat ca parametru unde id-ul inregistrarii este $id.
    $cmd = $conn->prepare("SELECT * FROM " . $table . " WHERE id = :id");
    $cmd->bindParam(":id", $id, PDO::PARAM_INT);
    $cmd->execute();
    return $cmd->fetchAll()[0];
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>three</title>
</head>


<style>
table { border-collapse: collapse; }
tr, td, th { border: 1px solid black; text-align: center; padding: 5px; }
tr:nth-child(even){background-color: #f2f2f2} /*din fiecare a doua celula este gri deschis*/
tr:not(:first-child):hover { background-color: cornflowerblue; } /*cand avem cursorul deasupra unei celule, se schimba culoarea*/
</style>


<body>
<table>
	<tr>
		<th>Student</th>
		<th>Materie</th>
		<th>Nota</th>
		<th>Profesor Evaluator</th>
	</tr>
	<?php
	# afiseaza toate notele acordate de toti profesorii, pentru toti studentii, la toate materiile
		foreach ($note as $nota) {
			$student = get_one($conn, "studenti", $nota['StudentID']); # functia anterior definita get_one returneaza o inregistrare din baza de date care se potriveste criteriului de cautare (aici, "StudentID" este cheie primara).
			$profesor = get_one($conn,"profesori", $nota['ProfesorID']);
			$materie = get_one($conn,"materii", $nota['MaterieID']);
			echo("<tr>
				<td>" . $student['nume'] . "</td>
				<td>" . $materie['nume'] . "</td>
				<td>" . $nota['Nota'] . "</td>
				<td>" . $profesor['username'] . "</td></tr>");
		}
	?>
</table>
<a href="profesori.php">Profesori</a> <!--link to login page-->
</body>
</html>
