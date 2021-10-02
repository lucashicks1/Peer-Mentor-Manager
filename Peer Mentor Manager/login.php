<?php
session_start();
if (isset($_SESSION['fname'])) {
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Mentor Manager - Login</title>
    <!-- Bootstrap css-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" />
    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" />
    <!-- Local Files -->
    <link rel="stylesheet" href="css/main.css" />
    <link rel="shortcut icon" href="favicon.ico">
</head>

<body>

    <?php

    if (isset($_POST['username'])) {
        require('connect.php');
        $username = trim(stripslashes((htmlspecialchars(($_POST['username'])))));
        $email = ($username . '@terrace.qld.edu.au');
        $salt = DB::queryFirstField('Select salt from users where (email = %s) OR (uid = %s AND utype IN ("a","m"))', $email, $username);

        if (!$salt) {
            session_unset();
            session_destroy();
            // a is used as a variable to display an alert message
            $a = 1;
        } else {
            $pass = hash('sha256', $salt . $_POST['password']);
            $check = DB::queryFirstRow('Select * from users where ((email = %s) OR (uid = %s AND utype IN ("a","m"))) AND password = %s', $email, $username, $pass);
            if ($check) {
                $_SESSION['uid'] = $check['uid'];
                $_SESSION['utype'] = $check['utype'];
                $_SESSION['fname'] = $check['fname'];
                $_SESSION['lname'] = $check['lname'];
                $_SESSION['email'] = $check['email'];
                if ($check['utype'] == 's') {
                    $info = DB::queryFirstRow('Select * from students where uid = %s', $_SESSION['uid']);
                    $_SESSION['yr'] = $info['yr'];
                    $_SESSION['tutg'] = $info['tutg'];
                    $_SESSION['house'] = $info['house'];
                    // Account Logged in a=4
                    $a = 4;
                } elseif ($check['utype'] == "m") {
                    $info = DB::queryFirstRow('Select * from mentors where uid = %s', $_SESSION['uid']);
                    if ($info['verified'] == 0) {
                        // Blue card not confirmed a=3
                        $a = 3;
                        session_unset();
                        session_destroy();
                    } else {
                        $a = 4;
                    }
                } else {
                    $a = 4;
                }
            } else {
                session_unset();
                session_destroy();
                $a = 1;
            }
        }
    }

    ?>


    <div class="content">
        <h1 class="text-center heading my-4">Login</h1>


        <div class="container align-items-center mx-auto text-center m-4">
            <div class="row">
                <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
                    <form method="post" action="login.php" name="login" id="login">
                        <div class="form-group my-3 py-1">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-user fa-2x"></i></span>
                                </div>
                                <input type="text" class="form-control" placeholder="Username:" aria-label="Username"
                                    required name="username" />
                            </div>
                        </div>
                        <div class="form-group my-3 py-1">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock fa-2x"></i></span>
                                </div>
                                <input type="password" class="form-control" placeholder="Password:"
                                    aria-label="Password" required name="password" />
                            </div>
                        </div>

                        <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submit" form="login">
                            <i class="fas fa-user-check fa-lg formicon"></i>
                            Login
                        </button>
                    </form>
                    <a href="register.php">
                        <p class="lead py-2 my-2">Create Account</p>
                    </a>
                    <a href="forgot.php">
                        <p class="lead py-1 my-1">Forgot Password?</p>
                    </a>
                </div>
            </div>
        </div>

        <?php

        if (isset($a)) {
            if ($a == 1) {
                // Incorrect details
                echo '
                <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
                    <div class="toast r-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                        <div class="toast-body">
                            <span>
                                <span class="alert-text ml-3 mr-1">Username or password is incorrect</span>
                                <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                            </span>
                        </div>
                    </div>
                </div>
                ';
            } elseif ($a == 3) {
                echo '
                <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
                    <div class="toast r-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                        <div class="toast-body">
                            <span>
                                <span class="alert-text ml-3 mr-1">Your blue card is yet to be confirmed</span>
                                <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                            </span>
                        </div>
                    </div>
                </div>
                ';
            } else {
                echo '
                <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
                    <div class="toast g-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                        <div class="toast-body">
                            <span>
                                <span class="alert-text ml-3 mr-1">You are now logged in</span>
                                <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                            </span>
                        </div>
                    </div>
                </div>
                <script>
			        setTimeout(function(){window.location.href = "index.php";},2000);
			    </script>
                ';
            }
            echo '
        <script>
        $(document).ready(function() {
            $(".toast").toast("show");
        });
        </script>
        ';
        }
        echo '<div class="content">';
        ?>

</body>

</html>