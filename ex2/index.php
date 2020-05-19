<html>
<head>
<title>two</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>


<style>
table { border-collapse: collapse; }

tr, td, th { border: 1px solid black; text-align: center; padding: 1px; }

tr:nth-child(even) { background-color: #f2f2f2; }

tr:not(:first-child):hover { background-color: cornflowerblue; }
</style>


<body>
<!-- numarul de elemente pe pagina
metoda de trimitere catre php e POST -->
<form action="" method="post">
	<select name="select1">
		<option value="1">1</option>
		<option selected value="2">2</option> <!--By default, this is the selected value (also must change code if we change it)-->
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
	</select>
	<input type="submit" name="submit" value="Go" />
</form>

<table>
<?php
# check if anything was selected with "isset" method
if(isset($_POST['select1'])){
	$no_of_records_per_page = $_POST['select1'];
} else if (isset($_GET['no_of_records_per_page'])) { # if there has been sent the number of records per page, update it
	$no_of_records_per_page = $_GET['no_of_records_per_page'];
} else { # else, by default, display 3 records/page
	$no_of_records_per_page = 3;
}

if (isset($_GET['pageno'])) { # get the page number via the HTTP request
	$pageno = $_GET['pageno'];
} else { # else, by default, set it to 1
	$pageno = 1;
}


#DB connection parameters
$mysql_u = 'cgir2476'; # user
$mysql_p = ''; # password
$server_name = 'localhost'; # server
$mysql_t = 'pwajax'; # database

$conn=@mysqli_connect($server_name, $mysql_u, $mysql_p); # '@' tells PHP to suppress error messages
@mysqli_set_charset($conn, "utf8");
if(!$conn) {
	echo("<span class='error'>Nu s-a putut realiza conexiunea la baza de date: " . @mysqli_error($conn) . "</span>");
	exit(); # exit programme, in order to stop code from executing on a null connection to the DB
}
else {
$test_select_db=mysqli_select_db($conn,$mysql_t); # test connection to the DB
	if(!$test_select_db) {
		echo("<span class='error'>Nu s-a putut selecta baza de date: " . @mysqli_error($conn) . "</span>");
		exit();
	}
}

$total_pages_sql = "SELECT COUNT(*) FROM people";
$result = mysqli_query($conn,$total_pages_sql);
if ($result == FALSE) { # check if the table exists and if there is data in the table
	echo('<tr><td>THERE IS NO DATA!</td></tr>');
	mysqli_close($conn);
	exit(); # if not, stop the programme from further computations
}
	
$total_rows = mysqli_fetch_array($result)[0]; # get the records from the DB
$total_pages = ceil($total_rows / $no_of_records_per_page); # get the total number of pages according to the pagination value


$offset = min(($pageno-1) * $no_of_records_per_page, $total_rows - $no_of_records_per_page);
# starting offset of the page is guided by the current page and the total number of pages.
# if it happens to change pagination value while at the last page, the offset will not overflow the total number of pages.
# we artificially stop the current page value from getting too big when increasing the number of records/page.
$pageno = max(0, ceil($offset / $no_of_records_per_page) + 1); # plus one, because page index starts at 1.
# also, according to the offset and the pagination number, we compute again, the correct value of the page.
# else, the page number would overflow and the user would have to spam the previous button until it reaches the actual range.


$sql = "SELECT * FROM people LIMIT $offset, $no_of_records_per_page";
# return $no_of_records_per_page records, starting from $offset+1
$res_data = mysqli_query($conn,$sql);
while($row = mysqli_fetch_array($res_data)) { # display the data in a HTML table format
	echo '<tr><td>'.$row[0]."</td> "."<td>".$row[1]."</td>"."<td>".$row[2]."</td></tr>";
}

mysqli_close($conn);
?>

</table>
<!--The anchors on the buttons have specific request parameters for each case, that are dynamically changed after each request using php code.-->
<ul class="pagination"> <!--Create the buttons for pagination-->
	<li><a href="?pageno=1&no_of_records_per_page=<?php echo $no_of_records_per_page; ?>">First</a></li>
	<!--FIRST sends you to the first page straightaway-->
	<li class="<?php if($pageno <= 1) { echo 'disabled'; } ?>"> <!--If first page is reached, disable "PREV"-->
		<a href="<?php if($pageno <= 1) { echo '#'; } else { echo "?pageno=".($pageno - 1)."&no_of_records_per_page=".$no_of_records_per_page; } ?>">Prev</a> <!--Decrement the page index-->
	</li>
	<li class="<?php if($pageno >= $total_pages) { echo 'disabled'; } ?>"> <!--If last page is reached, disable "NEXT"-->
		<a href="<?php if($pageno >= $total_pages) { echo '#'; } else { echo "?pageno=".($pageno + 1)."&no_of_records_per_page=".$no_of_records_per_page;; } ?>">Next</a> <!--Increment the page index-->
	</li>
	<li><a href="?pageno=<?php echo $total_pages; ?>&no_of_records_per_page=<?php echo $no_of_records_per_page; ?>">Last</a></li>
	<!--LAST sends you to the last page straightaway-->
</ul>
</body>
</html>
