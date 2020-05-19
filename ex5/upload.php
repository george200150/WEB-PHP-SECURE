<?php
# validare a tipului de fisier pentru upload, cu scopul de a evita incarcarea fisierelor sursa malicioase
function validate() {
    $ok = true;
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        # validare poza
        $ext = substr($file['name'], strpos($file['name'], '.') + 1); # extragem extensia pozei din numele fisierului
        if ($ext != "png" && $ext != "gif" && $ext != "jpg" && $ext != "jpeg") # verificare server-side a extensiei fisierului
            $ok = false;
    }
    return $ok;
}

if (!validate()) { # if the file type is not accepted by the site, we notify the user about the impossibility of uploading the file
	echo("<br/><br/><span>Uh oh.. file type not accepted!<br/><a href='website.php'>> Go back to the main menu <</a></span>");
	exit();
}

session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['password'])) # do not allow unauthorised users to upload files
    header("Location:index.php");

if (isset($_FILES['image'])) {
    $image = $_FILES['image']['name'];
    var_dump($_FILES['image']);
  	
  	# image file directory
	$image = strval(time()) . $image;
	# added timestamp to the image in order to avoid deleting an image that has multiple references.
	# (NullPointerExeptions: the file names still persist in the database, but the image itself does not appear)
	
  	$target = "./images/" . basename($image); # join the directories and file names together to create the path to the source file
	chmod($target, 0775);
	# /images/ is stored on server. in the database we only store the paths to the files in this folder.
	
	if($image == '') { # check if the user has not accidentally clicked on the upload without choosing a file from system
		ob_end_clean(); # clean the code echoed by php before
		echo("<br/><br/><span>Uh oh.. upload failed!<br/><a href='website.php'>> Go back to the main menu <</a></span>");
		# notify the user that his upload failed
		exit();
	}

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
		# move the file content (bitmap) from the temp file to target source file (that we provided)
  		$msg = "Image uploaded successfully";
  	} else {
  		$msg = "Failed to upload image";
  	}
    echo($msg);
	
    try {
        $conn = new PDO("mysql:host=localhost;dbname=pwajax", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
	
	$q = $conn->prepare("SELECT ID FROM users WHERE username = :usern"); # get the ID of the user (the session does not store it)
	$q->bindParam(":usern", $_SESSION['username']);
	$q->execute();
	
	$retreived_id = $q->fetchAll()[0]["ID"]; # retreived the ID from the users table
	# There will always be an existing user in the DataBase that has the corresponding username stored in the session.
	# this query cannot fail unless the information stored in the session is corrupted (hopefully not possible)
	
    $cmd = $conn->prepare("SELECT ID FROM pictures_users WHERE Username = :user"); # check if there is an user in the table linkage 
    $cmd->bindParam(":user", $_SESSION['username']);
    $cmd->execute();
	$rez = $cmd->fetchAll(); # (possible empty array)
	if (count($rez) == 0) { # if there is no user in the linkage table, add the user
		$quer = $conn->prepare("INSERT INTO pictures_users(ID, Username) VALUES (:id, :usern)");
    	$quer->bindParam(":usern", $_SESSION['username']);
		
		$quer->bindParam(":id", $retreived_id); # use the id received from the above query (q)
    	$quer->execute(); # executed the insertion into the linkage table
	}
    $cmd = $conn->prepare("INSERT INTO pictures(Picture, UserId) VALUES (:pic, :userId)");
    $cmd->bindParam(":pic", $image);
    $cmd->bindParam(":userId", $retreived_id);
    $cmd->execute(); # insert the picture into the database
	
    $conn = null;
    header("Location:website.php"); #redirect the user to the main menu after the file upload
}
?>

<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="image" accept=".gif,.jpg,.jpeg,.png"><br><br>
    <input type="submit" value="Upload">
</form>
