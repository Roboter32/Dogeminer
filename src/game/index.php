<!DOCTYPE html>
<html>

<head>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="../main.css">
    <meta http-equiv="refresh" content="900;url=../index.php" />
</head>

<body>

    <style>
        #static
        {
            position: absolute;
            background: white;
        }
        #static:hover
        {
            opacity: 0;
        }
        
    </style>



    <?php

    $currentCookieParams = session_get_cookie_params();
    $rand=rand();

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


    function checkInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }


    $newuser = false;

    if (isset($_SESSION['LastRequestTime']))$PreviousRequestTime = $_SESSION['LastRequestTime'];
    else $newuser = true;
    //echo "Previous refresh time: ".$PreviousRequestTime."<br>";

    
    
    
    
    $_SESSION['LastRequestTime'] = time();
    $LastRequestTime = $_SESSION['LastRequestTime'];
    if ($newuser)$PreviousRequestTime = $LastRequestTime;
    $DeltaTime = $LastRequestTime - $PreviousRequestTime;

    //echo "Last refresh time: ".$_SESSION['LastRequestTime']."<br>";


    if ($DeltaTime > 900 && $_SESSION['loggedin'])
    {
        // session timed out, last request is longer than 15 minutes ago
        $_SESSION = array();
        session_unset();
        session_destroy();
    }
 
   

    if ($_SESSION['balance'] == NULL) $_SESSION['balance'] = 1000;
    if ($_SESSION['loggedin'] == NULL) $_SESSION['loggedin'] = false;

    if ($_SESSION['BabyDoges'] == NULL) $_SESSION['BabyDoges'] = 0;
    if ($_SESSION['MiningDoges'] == NULL) $_SESSION['MiningDoges'] = 0;
    if ($_SESSION['ChadDoges'] == NULL) $_SESSION['ChadDoges'] = 0;
    if ($_SESSION['Excavators'] == NULL) $_SESSION['Excavators'] = 0;
    if ($_SESSION['DOGOClusters'] == NULL) $_SESSION['DOGOClusters'] = 0;
    if ($_SESSION['Starships'] == NULL) $_SESSION['Starships'] = 0;

    

    

    

    if ($_SESSION['loggedin']) {

        //A: User logged in


        //1. Connect to the database and select all game progress data

        $_SESSION['error'] = "Unknown error";

        $connection = mysqli_connect("localhost", "s411005", "urygapiotr") or die("Connection with the database is impossible!");
        $db = mysqli_select_db($connection, "s411005") or die("Unable to select the database!");
        $sql = "SELECT ID, BALANCE, SAVETIME, MiningDoges, BabyDoges, ChadDoges, Excavators, DOGOClusters, Starships FROM DogeUsers";
        $result = mysqli_query($connection, $sql) or die("Problems with reading the data!");

        //echo "Logged in!";

        //2. Find the user's account

        while ($row = mysqli_fetch_array($result))//I have to use this stupid slow loop because the WHERE statement DOESN'T WORK on the server.
        {
            if ($row["ID"] == $_SESSION['unameID'])
            {
                //3. Load all the data

                $_SESSION['balance'] = $row["BALANCE"];
                $PreviousRequestTime = $row["SAVETIME"];
                $_SESSION['MiningDoges'] = $row["MiningDoges"];
                $_SESSION['BabyDoges'] = $row["BabyDoges"];
                $_SESSION['ChadDoges'] = $row["ChadDoges"];
                $_SESSION['Excavators'] = $row["Excavators"];
                $_SESSION['DOGOClusters'] = $row["DOGOClusters"];
                $_SESSION['Starships'] = $row["Starships"];
                
                
                break; 
            }
        }
        

    }

    
    $DeltaTime = $LastRequestTime - $PreviousRequestTime;
    //echo "DeltaTime: ".$DeltaTime."<br>";

    //B: User playing as guest, progress already loaded on session_start();

    //4. Calculate current pricing:

    $_Price["BabyDoges"] = 500;
    $_Price["MiningDoges"] = 2500;
    $_Price["ChadDoges"] = 12500;
    $_Price["Excavators"] = 50000;
    $_Price["DOGOClusters"] = 1000000;
    $_Price["Starships"] = 25000000;
    
    for ($i = 0; $i < $_SESSION['BabyDoges']; $i++)$_Price["BabyDoges"] *= 1.05;
    for ($i = 0; $i < $_SESSION['MiningDoges']; $i++)$_Price["MiningDoges"] *= 1.05;
    for ($i = 0; $i < $_SESSION['ChadDoges']; $i++)$_Price["ChadDoges"] *= 1.05;
    for ($i = 0; $i < $_SESSION['Excavators']; $i++)$_Price["Excavators"] *= 1.05;
    for ($i = 0; $i < $_SESSION['DOGOClusters']; $i++)$_Price["DOGOClusters"] *= 1.05;
    for ($i = 0; $i < $_SESSION['Starships']; $i++)$_Price["Starships"] *= 1.05;

    $_Price["BabyDoges"] = round($_Price["BabyDoges"]);
    $_Price["MiningDoges"] = round($_Price["MiningDoges"]);
    $_Price["ChadDoges"] = round($_Price["ChadDoges"]);
    $_Price["Excavators"] = round($_Price["Excavators"]);
    $_Price["DOGOClusters"] = round($_Price["DOGOClusters"]);
    $_Price["Starships"] = round($_Price["Starships"]);
    
    


    //5. Calculate mining rate and gains:

    $MiningRate  = 5 * $_SESSION['BabyDoges'];
    $MiningRate += 30 * $_SESSION['MiningDoges'];
    $MiningRate += 180 * $_SESSION['ChadDoges'];
    $MiningRate += 1000 * $_SESSION['Excavators'];
    $MiningRate += 50000 * $_SESSION['DOGOClusters'];
    $MiningRate += 250000 * $_SESSION['Starships'];

    if ($DeltaTime > 900)$DeltaTime = 900;//limit AFK mining time to 15 minutes as this is not intended to work like an idle tycoon

    $Gain = $DeltaTime * $MiningRate;
    $_SESSION['balance'] += $Gain;
    //echo "Rate: ".$MiningRate."<br>";
    //echo "Gain: ".$Gain."<br>";

    $_Rate["BabyDoges"] = 5;
    $_Rate["MiningDoges"] = 30;
    $_Rate["ChadDoges"] = 180;
    $_Rate["Excavators"] = 1000;
    $_Rate["DOGOClusters"] = 50000;
    $_Rate["Starships"] = 250000;






    //6. Process any player purchases:

    
    
    if (isset($_POST['submit']) && $_POST['randcheck'] == $_SESSION['rand'])
    {
        //echo "Buying<br>";
        $Item = $_POST['Item'];
        $Balance = $_SESSION['balance'];
        
        if ($_SESSION['balance'] >= $_Price[$Item])
        {
            $_SESSION[$Item]++;
            //echo "Old balance: ".$_SESSION['balance']."<br>";
            $_SESSION['balance'] -= $_Price[$Item];
            //echo "Price: ".$_Price[$Item]."<br>";
            //echo "New balance: ".$_SESSION['balance']."<br>";
            $Balance = $_SESSION['balance'];
            $MiningRate += $_Rate[$Item];
            $_Price[$Item] += (0.05 * $_Price[$Item]);
            $_Price[$Item] = round($_Price[$Item]);
        }

        $UID = $_SESSION['unameID'];
        $sql = "UPDATE DogeUsers SET BALANCE='$Balance', $Item='$_SESSION[$Item]' WHERE id='$UID'";
        mysqli_query($connection, $sql);
        $_POST['Item'] = NULL;
            

        

    }

    //echo $_SESSION['unameID'];

    $sql = "UPDATE DogeUsers SET SAVETIME='$LastRequestTime' WHERE id='$UID'";
    mysqli_query($connection, $sql);

    mysqli_close($connection);

    

    
    





    ?>






    <div class="container">
        <div class="row justify-content-md-center">

            <!-- <div class="col">

            </div> -->
            <div class="col-10">
                <!--Main layout-->
                <main class="my-5">
                    <div class="container">
                        <!--Section: Content-->
                        <section class="text-center">
                            <h4 class="mb-5"><strong>Dogecoin mine</strong></h4>

                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <div class="card">

                                        <div class="card-body">
                                            <h5 class="card-title">Your mine:</h5>

                                            <div class="container">
                                                <!-- <div class = "row"></div> -->

                                                <div class="row align-items-end">
                                                    <div class="col-md-auto">
                                                        Dogecoins:
                                                    </div>

                                                    <div class="col-md-auto" id="counter">
                                                        <?php echo $_SESSION['balance']; ?>
                                                    </div>

                                                </div>

                                                <div class="row align-items-end">
                                                    <?php echo "Baby Doges: ".$_SESSION['BabyDoges']; ?>
                                                </div>

                                                <div class="row align-items-end">
                                                    <?php echo "Mining Doges: ".$_SESSION['MiningDoges']; ?>
                                                </div>

                                                <div class="row align-items-end">
                                                    <?php echo "Chad Doges: ".$_SESSION['ChadDoges']; ?>
                                                </div>

                                                <div class="row align-items-end">
                                                    <?php echo "Excavators: ".$_SESSION['Excavators']; ?>
                                                </div>

                                                <div class="row align-items-end">
                                                    <?php echo "DOGOClusters: ".$_SESSION['DOGOClusters']; ?>
                                                </div>

                                                <div class="row align-items-end">
                                                    <?php echo "Starships: ".$_SESSION['Starships']; ?>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-auto">
                                                        Dogecoins per second:
                                                    </div>

                                                    <div class="col-md-auto" id="rate">
                                                        <?php echo $MiningRate; ?>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-4">

                                    <div class="row">
                                        <div class="col-lg-4 mb-4">
                                            <div class="card">
                                                <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                                                    
                                                    <img src="./BabyDogeUp.png" class="img-fluid" id="static"/>
                                                    <img src="./BabyDoge.gif" class="img-fluid" id="moving"/>
                                                    
                                                    <a href="#!">
                                                        <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                                    </a>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">Baby Doge</h5>
                                                    <p class="card-text">
                                                        Mining rate:<br>5 DOGE/s
                                                    </p>
                                                    <form method="POST">
                                                    <?php
                                                        $_SESSION['rand']=$rand;
                                                    ?>
                                                        <input type="hidden" value="<?php echo $rand; ?>" name="randcheck" />
                                                        <input type="hidden" name="Item" value="BabyDoges">
                                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo $_Price["BabyDoges"]; ?> DOGE</button>
                                                    </form>

                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-lg-4 mb-4">
                                            <div class="card">
                                                <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                                                    <img src="./DogeUp.png" class="img-fluid" id="static"/>
                                                    <img src="./doge.gif" class="img-fluid" id="moving"/>
                                                    <a href="#!">
                                                        <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                                    </a>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">Miner Doge</h5>
                                                    <p class="card-text">
                                                        Mining rate:<br>30 DOGE/s
                                                    </p>
                                                    <form method="POST">
                                                    <?php
                                                        $_SESSION['rand']=$rand;
                                                    ?>
                                                        <input type="hidden" value="<?php echo $rand; ?>" name="randcheck" />
                                                        <input type="hidden" name="Item" value="MiningDoges">
                                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo $_Price["MiningDoges"]; ?> DOGE</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 mb-4">
                                            <div class="card">
                                                <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                                                    <img src="./ChadDogeUp.png" class="img-fluid" id="static"/>
                                                    <img src="./ChadDoge.gif" class="img-fluid" id="moving"/>
                                                    <a href="#!">
                                                        <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                                    </a>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">Chad Doge</h5>
                                                    <p class="card-text">
                                                        Mining rate:<br>180 DOGE/s
                                                    </p>
                                                    <form method="POST">
                                                    <?php
                                                        $_SESSION['rand']=$rand;
                                                    ?>
                                                        <input type="hidden" value="<?php echo $rand; ?>" name="randcheck" />
                                                        <input type="hidden" name="Item" value="ChadDoges">
                                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo $_Price["ChadDoges"]; ?> DOGE</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 mb-4">
                                            <div class="card">
                                                <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                                                    <img src="./ExcavatorDown.png" class="img-fluid" id="static"/>
                                                    <img src="./Excavator.gif" class="img-fluid" id="moving"/>
                                                    <a href="#!">
                                                        <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                                    </a>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">Excavator</h5>
                                                    <p class="card-text">
                                                        Mining rate:<br>1000 DOGE/s
                                                    </p>
                                                    <form method="POST">
                                                    <?php
                                                        $_SESSION['rand']=$rand;
                                                    ?>
                                                        <input type="hidden" value="<?php echo $rand; ?>" name="randcheck" />
                                                        <input type="hidden" name="Item" value="Excavators">
                                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo $_Price["Excavators"]; ?> DOGE</button>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-lg-4 mb-4">
                                            <div class="card">
                                                <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                                                    <img src="./DOJO.png" class="img-fluid" id="static"/>
                                                    <img src="./DOJO.gif" class="img-fluid" id="moving"/>
                                                    <a href="#!">
                                                        <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                                    </a>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">DOGO Cluster</h5>
                                                    <p class="card-text">
                                                        Mining rate:<br>50 000 DOGE/s
                                                    </p>
                                                    <form method="POST">
                                                    <?php
                                                        $_SESSION['rand']=$rand;
                                                    ?>
                                                        <input type="hidden" value="<?php echo $rand; ?>" name="randcheck" />
                                                        <input type="hidden" name="Item" value="DOGOClusters">
                                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo $_Price["DOGOClusters"]; ?> DOGE</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-4">
                                            <div class="card">
                                                <div class="bg-image hover-overlay ripple" data-mdb-ripple-color="light">
                                                    <img src="./Starship.png" class="img-fluid" id="static"/>
                                                    <img src="./Starship.gif" class="img-fluid" id="moving"/>
                                                    <a href="#!">
                                                    <a href="#!">
                                                        <div class="mask" style="background-color: rgba(251, 251, 251, 0.15);"></div>
                                                    </a>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">Starship</h5>
                                                    <p class="card-text">
                                                        Mining rate:<br>250 000 DOGE/s
                                                    </p>
                                                    <form method="POST">
                                                    <?php
                                                        $_SESSION['rand']=$rand;
                                                    ?>
                                                        <input type="hidden" value="<?php echo $rand; ?>" name="randcheck" />
                                                        <input type="hidden" name="Item" value="Starships">
                                                        <button type="submit" name="submit" class="btn btn-primary"><?php echo $_Price["Starships"]; ?> DOGE</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>


                            </div>
                    </div>

            </div>


            <script>
                var balance = document.getElementById("counter").innerHTML;
                var rate = document.getElementById("rate").innerHTML;
                i = parseInt(balance);
                j = Number(rate);
                function cntLoop() {
                    setTimeout(function() {
                        document.getElementById("counter").innerHTML = i;
                        k = Number(j/10);
                        l = parseInt(k);
                        i += k;
                        cntLoop();
                    }, 100)
                }
                    
                if (j > 0)cntLoop();
                
            </script>
            



</body>

</html>