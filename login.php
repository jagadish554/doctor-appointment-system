<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
        
    <title>Login</title>

    
    
</head>
<body>
    <?php

    //learn from w3schools.com
    //Unset all the server side variables

    session_start();
    
    $max_failed_attempts = 3;
    $lockout_duration = 60;
    $account_block_duration = 60;
    
    
    $_SESSION["user"] = "";
    $_SESSION["usertype"] = "";
    
    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');
    $_SESSION["date"] = $date;
    
    // Import database
    include("connection.php");
    // Changes made for Brute Force attack
    if (isset($_SESSION["lockout_time"]) && (time() - $_SESSION["lockout_time"]) < $lockout_duration) {
        echo '<p style="color: red;">Account temporarily locked due to too many failed attempts. Please try again later.</p>';
        exit;
    }
    
    if (isset($_SESSION["block_time"]) && (time() - $_SESSION["block_time"]) < $account_block_duration) {
        echo '<p style="color: red;">Account temporarily blocked. Please try again in 60 seconds.</p>';
        exit;
    }
    
    $_SESSION["login_attempts"] = isset($_SESSION["login_attempts"]) ? $_SESSION["login_attempts"] : 0;
    $_SESSION["login_attempts"]++;
    
    if ($_SESSION["login_attempts"] >= $max_failed_attempts) {
        unset($_SESSION["user"]);
        $_SESSION["lockout_time"] = time();
        echo '<p style="color: red;">Account temporarily locked due to too many failed attempts. Please try again later.</p>';
        
        // Reset login attempts and set block time
        $_SESSION["login_attempts"] = 0;
        $_SESSION["block_time"] = time();
        
        exit;
    }
    

    if($_POST){
        // changes made for SQL
        $email = $database->real_escape_string($_POST['useremail']);
        $password = $database->real_escape_string($_POST['userpassword']);
        
        $error='<label for="promter" class="form-label"></label>';

        $stmt = $database->prepare("SELECT * FROM webuser WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();



        if ($result->num_rows == 1) {
            $utype = $result->fetch_assoc()['usertype'];
        
            // Define the common query template for different user types
            $queryTemplate = "SELECT * FROM %s WHERE %semail=? AND %spassword=?";
            
            if ($utype == 'p') {
                $table = "patient";
                $columnPrefix = "p";
            } elseif ($utype == 'a') {
                $table = "admin";
                $columnPrefix = "a";
            } elseif ($utype == 'd') {
                $table = "doctor";
                $columnPrefix = "doc";
            } else {
              
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid user type</label>';
                
                exit;
            }
        
            $query = sprintf($queryTemplate, $table, $columnPrefix, $columnPrefix);
            $statement = $database->prepare($query);
            $statement->bind_param("ss", $email, $password);
            $statement->execute();
            $checker = $statement->get_result();
            $statement->close();
        
            if ($checker->num_rows == 1) {
                // Set session variables and redirect based on user type
                $_SESSION['user'] = $email;
                $_SESSION['usertype'] = $utype;
        
                if ($utype == 'p') {
                    header('location: patient/index.php');
                } elseif ($utype == 'a') {
                    header('location: admin/index.php');
                } elseif ($utype == 'd') {
                    header('location: doctor/index.php');
                }
            } else {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
            }
        } else {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid Email or Password</label>';
        }
        
    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
    }

    ?>

    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="useremail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="userpassword" class="form-label">Password: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                </td>
            </tr>


            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="submit" value="Login" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
                        
                        
    
                        
                    </form>
        </table>

    </div>
</center>
</body>
</html>