

<!DOCTYPE html>
<html>
	
	
  
	<head>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
		<link rel = "stylesheet" href = "main.css"> 
    </head>




	<body>

		<title>Dogeminer 2022</title>


		<center>

			<?php
			
			$currentCookieParams = session_get_cookie_params(); 

			$rootDomain = 'mts.wibro.agh.edu.pl'; 

			session_set_cookie_params
			( 
    			$currentCookieParams["lifetime"], 
    			"/~s411005", 
    			$rootDomain, 
    			$currentCookieParams["secure"], 
    			$currentCookieParams["httponly"] 
			);
			
			
			
			session_name("Dogeminer");
			session_start();

			$unameID = 0;

			function checkInput($data)
			{
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}
			
			
			
			
			$_SESSION['error'] = "Unknown error";
			if (empty($_POST["UNAME"])  || empty($_POST["PASSWD"])) $_SESSION['error'] = "Both fields are required";

			else
			{
				//echo 'Connecting';
				$uname = checkInput($_POST['UNAME']);
				$passwd = checkInput($_POST['PASSWD']);

				$connection = mysqli_connect("localhost","s411005","urygapiotr") or die("Connection with the database is impossible!");
				$db = mysqli_select_db($connection ,"s411005")or die("Unable to select the database!");

				$sql = "SELECT ID, UNAME, PASSWD FROM DogeUsers";
				$result = mysqli_query($connection,$sql) or die("Problems with reading the data!");

				while ($row = mysqli_fetch_array($result))
				{
					if ($row["UNAME"] == $uname)
					{
						$_SESSION['unameID'] = $row["ID"];
						break;
					}
				}


				if ($_SESSION['unameID'] == 0) $_SESSION['error'] = "Invalid username";
				elseif ($row["PASSWD"] != $passwd)
				{
					$_SESSION['error'] = "Invalid password";
				}
				else
				{
					$_SESSION['error'] = "Login successfull";
					$_SESSION['loggedin'] = true;


					header("Location: game/index.php");
					die();
				}
			}
			

			
			?>

			<h1><strong>Log in you first must!</strong></h1>

			<FORM method ="post">

			Username:
			<input type="text" name="UNAME" /><br/>
			<br>

			Password:
			<input type="password" name="PASSWD" /><br/>
			<br>
			
			<?php if (isset($_SESSION['error'])) echo $_SESSION['error'] . "<br>" ?>
			


			<input type="submit" class="btn btn-primary" value="Log in"/>
			</FORM>
			<br>
			<a href="./game/index.php" class="btn btn-primary">Play as guest</a>

			
			
			

		</center>

	</body>
</html>
