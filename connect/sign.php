<?php

//PROJECT CONNECT
require 'connect/DB.php';
require 'core/load.php';

//REGISTRATION
if(isset($_POST["first-name"]) && !empty($_POST["first-name"])){
    $upFirst = $_POST["first-name"];
    $upLast = $_POST["last-name"];
    $upEmail = $_POST["up-email"];
    $upPassword = $_POST["up-password"];
    $birthDay = $_POST["birth-day"];
    $birthMonth = $_POST["birth-month"];
    $birthYear = $_POST["birth-year"];
    if(empty($_POST["gen"])){
        $upGen = $_POST["gen"];
    }
    $birth = "".$birthYear."-".$birthMonth."-".$birthDay."";
    
    if(empty($upFirst) or empty($upLast) or empty($upEmail) or empty($upGen)){
        $error = "All fields are required";
    }else{
        $first_name = $loadFromUser->checkInput($upFirst);
        $last_name = $loadFromUser->checkInput($upLast);
        $email = $loadFromUser->checkInput($upEmail);
        $password = $loadFromUser->checkInput($upPassword);
        $screenName = "".$first_name."_".$last_name."";

        if(DB::query("SELECT screenName FROM users WHERE screenName = :screenName", array(":screenName" => $screenName))){
            $screenRand = rand();
            $userLink = "".$screenName."".$screenRand."";    
        }else{
            $userLink = $screenName;
        }
        if(!preg_match("^[_a-z0-9-]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^",$email)){
            $error = 'Email id is not correct. Please try again.';
        }else{
            if(!filter_var($email)){
                $error = "Invalid Email Format";
            }else if(strlen($first_name) > 20){
                $error = "Name must be between 2-20 character";
            }else if(strlen($password) < 5 && strlen($password) >= 60){
                $error = "The password is either too shor or too long";
            }else{
                if((filter_var($email, FILTER_VALIDATE_EMAIL)) && $loadFromUser->checkEmail($email) === true){
                    $error = "Email is already in use";
                }else{
                    $user_id = $loadFromUser->create("users", array("first_name"=>$first_name,"last_name"=>$last_name, "email" => $email, "password"=>password_hash($password, PASSWORD_BCRYPT),"screenName"=>$screenName,"userLink"=>$userLink, "birthday"=>$birth, "gender"=>$upGen));
                
                    $tstrong = true;
                    $token = bin2hex(openssl_random_pseudo_bytes(64, $tstrong));
                    $loadFromUser->create('token', array('token'=>$token, 'user_id'=>$user_id));

                    setcookie('FBID', $token, time()+60*60*24*7, '/', NULL, NULL, true);

                    header('Location: index.php');
                }
            }
        }     
    }
}

//LOGIN SYSTEM
if(isset($_POST["in-email"]) && !empty($_POST["in-email"])){
    $email = $_POST["in-email"];
    $in_pass = $_POST["in-pass"];
    
    if(!preg_match("^[_a-z0-9-]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)){
        $error = 'Email is not correct. Please try again';       
    }else{
        if(DB::query("SELECT email FROM users WHERE email = :email", array(":email"=>$email))){
            if(password_verify($in_pass, DB::query("SELECT password FROM users WHERE email=:email", array(":email"=>$email))[0]["password"])){
                $user_id=DB::query("SELECT user_id FROM users WHERE email=:email", array(":email"=>$email))[0]["user_id"];
                $tstrong = true;
                $token = bin2hex(openssl_random_pseudo_bytes(64, $tstrong));
                $loadFromUser->create("token", array("token"=>sha1($token), "user_id"=>$user_id));

                setcookie("FBID", $token, time()+60*60*24*7, "/", NULL, NULL, true);

                header("Location: index.php");
            }else{
                $error="Password is not correct";
            }
        }else{
            $error = "User has not faund";
        }        
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>connect</title>
    <link rel="stylesheet" href="assets/css/style.css" class="css">
</head>

<body>
<div class="container">
        <div class="login">
            <div class="logoconnect">
                <img src="assets/css/image/logo.png" alt="" class="logo">
            </div>
            <form action="sign.php" method="post">
                <div class="sign-in-form">
                    <input type="text" name="in-email" id="email-login" class="input-text-field" placeholder="Email address">
                    <input type="password" name="in-pass" id="in-password" class="input-text-field" placeholder="••••••">
                    <input type="submit" value="GO" id="sign-up-button" class="input-text-field">
                </div>    
            </from>    
        </div>

        <div class="separate-mobil">
            <h1 class="separate-mobil-h1-1">OR</h1>
            <hr class="separate-mobil-hr">
            <h1 class="separate-mobil-h1-2">REGISTRATION</h1>
        </div>

        <div class="main">
            <div class="main-form">

            <!-- <?php if(!empty($error)){echo $error;}?> -->

            <h1 class="registration-header">Create an account</h1>
            <form action="sign.php" method="post" name="user-sign-up">
                <div class="sign-up-form">
                    <div class="sign-up-name">
                        <input type="text" name="first-name" id="first-name" class="text-field" placeholder="First Name">
                        <input type="text" name="last-name" id="last-name" class="text-field" placeholder="Last Name">
                    </div>
                        <div class="sign-wrap-email">
                        <input type="text" name="up-email" id="up-email" placeholder="Email address" class="text-input">
                    </div>
                    <div class="sign-up-password">
                        <input type="password" name="up-password" id="up-password" placeholder="••••••" class="text-input">
                    </div>
                    <div class="sign-up-birthday">
                        <div class="bday">Birthday</div>
                        <div class="form-birthday">
                            <select name="birth-day" id="days" class="select-body"></select>
                            <select name="birth-month" id="months" class="select-body"></select>
                            <select name="birth-year" id="years" class="select-body"></select>
                        </div>
                    </div>
                    <div class="gender-wrap">
                        <input type="radio" name="gen" id="fem" value="female" class="m0">
                        <label for="fem" calss="gender">Female</label>
                        <input type="radio" name="gen" id="male" value="male" class="m0">
                        <label for="male" calss="gender">Male</label>
                    </div>
                    <div class="term">
                        By clicking Sign Up, you agree to our terms, Data policy and Cokkie policy. 
                    </div>
                    <input type="submit" value="Sign Up" class="sign-up">
                </div>
            </form>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.js"></script>

    <script>
        for (i = new Date().getFullYear(); i > 1900; i--) {
            $("#years").append($("<option/>").val(i).html(i));

        }
        for (i = 1; i < 13; i++) {
            $('#months').append($('<option/>').val(i).html(i));
        }
        updateNumberOfDays();

        function updateNumberOfDays() {
            $('#days').html('');
            month = $('#months').val();
            year = $('#years').val();
            days = daysInMonth(month, year);
            for (i = 1; i < days + 1; i++) {
                $('#days').append($('<option/>').val(i).html(i));
            }

        }
        $('#years, #months').on('change', function() {
            updateNumberOfDays();
        })

        function daysInMonth(month, year) {
            return new Date(year, month, 0).getDate();
        }
    </script>

</body>

</html>