<!DOCTYPE html>
<html>

    <head>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
		<link rel = "stylesheet" href = "main.css"> 
    </head>




<body>
<center>




<?php

$unameID = 0;

function checkInput($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

session_start();

$_SESSION['error'] = "Unknown error";



if (empty($_POST["UNAME"])  || empty($_POST["PASSWD1"]) || empty($_POST["PASSWD2"])) $_SESSION['error'] = "All fields are required";
else
{
    $uname = checkInput($_POST["UNAME"]);
    $passwd1 = checkInput($_POST["PASSWD1"]);
    $passwd2 = checkInput($_POST["PASSWD2"]);

    if ($passwd1 != $passwd2) $_SESSION['error'] = "Passwords do not match";
    else
    {
        $connection = mysqli_connect("localhost","s411005","urygapiotr") or die("Connection with the database is impossible!");
        $db = mysqli_select_db($connection ,"s411005")or die("Unable to select the database!");

        $sql = "SELECT ID, UNAME, PASSWD FROM DogeUsers";
		$result = mysqli_query($connection,$sql) or die("Problems with reading the data!");

		while ($row = mysqli_fetch_array($result))
		{
			if ($row["UNAME"] == $uname)
			{
				$_SESSION['error'] = "Username already taken";
                $unameID = $row["ID"];
				break;
			}
		}

        if ($unameID == 0)
        {
            $sql = "INSERT INTO DogeUsers (UNAME, PASSWD) VALUES ('$uname', '$passwd1')";
            //echo "Registering<br>";
            if(mysqli_query($connection, $sql))$_SESSION['error'] = "Registered<br>";

            header("Location: ./index.php");
			die();
        }

    }
}
 


mysqli_close($connection);
?> 

<form method="post">

    Username:
    <input type="text" name="UNAME"/><br>
    <br>

    Password:
    <input type="password" name="PASSWD1"/><br>
    <br>

    Repeat password:
    <input type="password" name="PASSWD2"/><br>
    <br>
    <?php echo $_SESSION['error'];?>
    <br>

    <input type="submit" value="Register"/>

</form>



</center>
</body>
</html>