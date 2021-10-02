<?php
session_start();
require_once('mail/SMTP.php');
require_once('mail/PHPMailer.php');
require_once('mail/Exception.php');
require('connect.php');

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['fname'])) {
   header('Location: index.php');
}

function ccode()
{
   echo '

   <div class="container align-items-center mx-auto text-center m-4">
        <div class="row">
            <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">       
                <form method="get" action="register.php" name="confirm" id="confirm">
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                    <i class="fas fa-user fa-2x"></i
                  ></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Code:" aria-label="code" required name="code"/>
                        </div>
                    </div>
                    <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submit" form="confirm">
              <i class="fas fa-user-check fa-lg formicon"></i>
              Register
            </button>
                </form>
            </div>
        </div>
    </div>
   ';
}

function sendmail($email, $fname, $lname, $code)
{
   //Send email with code attached
   $mail = new PHPMailer(true); // Passing `true` enables exceptions

   try {
      //settings
      $mail->SMTPDebug = 0; // Enable verbose debug output
      $mail->isSMTP(); // Set mailer to use SMTP
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true; // Enable SMTP authentication
      $mail->Username = 'gtdigisol2020@gmail.com'; // SMTP username
      $mail->Password = 'securepassword'; // SMTP password
      $mail->SMTPSecure = 'ssl';
      $mail->Port = 465;

      $mail->addAddress($email, $fname . ' ' . $lname);
      $mail->setFrom('mentoring@terrace.qld.edu.au', 'Terrace Mentor Program');

      //content
      $mail->isHTML(true); // Set email format to HTML
      $mail->Subject = 'Confirmation Code - Terrace Mentor Program';
      $mail->Body = 'Your confirmation code for account registration is: ' . $code;
      $mail->AltBody = 'Your confirmation code for account registration is: ' . $code;
      $mail->send();
   } catch (Exception $e) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
   }
}


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Terrace Mentor Manager - Register</title>
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

    <h1 class="text-center heading my-2">Register</h1>

    <?php
   $num = random_int(5, 8);
   $salt = substr(md5(time()), $num, $num + 6);

   if (isset($_POST['submitstudent'])) {
      $utype = 's';
      $username = trim(stripslashes(htmlspecialchars($_POST['username'])));
      $num = random_int(5, 8);
      $salt = substr(md5(time()), $num, $num + 6);
      $pw = hash('sha256', $salt . $_POST['pass']);
      $pw2 = hash('sha256', $salt . $_POST['cpass']);
      $email = ($username . '@terrace.qld.edu.au');
      $row1 = DB::queryFirstRow('select * from users where email = %s', $email);
      $userinfo = DB::queryFirstRow('select * from userbase where email = %s', $email);
      if ($row1) {
         $b = 2;
      } elseif (!$userinfo) {
         $b = 3;
      } else {
         // Keep going
         if ($pw != $pw2) {
            $b = 4;
         } else {
            // Save the details in the session
            $_SESSION['details'] = [$username, $pw, $salt, $userinfo, $utype];
            // Generates a random code that will be sent in the email
            $_SESSION['code'] = substr(md5(microtime()), rand(0, 26), 5);
            sendmail($userinfo['email'], $userinfo['fname'], $userinfo['lname'], $_SESSION['code']);
            $b = 5;
            ccode();
         }
      }
   } elseif (isset($_POST['submitmentor'])) {
      $utype = 'm';
      $username = trim(stripslashes(htmlspecialchars($_POST['username'])));
      $num = random_int(5, 8);
      $salt = substr(md5(time()), $num, $num + 6);
      $pw = hash('sha256', $salt . $_POST['pass']);
      $pw2 = hash('sha256', $salt . $_POST['cpass']);
      $email = trim(stripslashes(htmlspecialchars($_POST['email'])));
      $row1 = DB::queryfirstrow('Select * from users u, userbase b where u.uid = %s OR u.email = %s OR b.sid = %s OR b.email = %s', $username, $email, $username, $email);
      // Check if username or email is in users
      // Check if username or email is in the userbase
      if ($row1) {
         $b = 2;
      } else {
         if ($pw != $pw2) {
            $b = 4;
         } else {
            $userinfo = [
               "email" => $email,
               "fname" => trim(stripslashes((htmlspecialchars($_POST['fname'])))),
               "lname" => trim(stripslashes((htmlspecialchars($_POST['lname']))))
            ];
            $_SESSION['details'] = [$username, $pw, $salt, $userinfo, $utype];
            $_SESSION['code'] = substr(md5(microtime()), rand(0, 26), 5);
            sendmail($userinfo['email'], $userinfo['fname'], $userinfo['lname'], $_SESSION['code']);
            $b = 5;
            ccode();
         }
      }
      echo 'mentor form is submitted';
   }

   if (isset($_GET['code'])) {
      if (isset($_SESSION['details'])) {
         // If the code inputted is correct
         if (trim(stripslashes(htmlspecialchars($_GET['code']))) == $_SESSION['code']) {
            $utype = $_SESSION['details'][4];
            $uid = $_SESSION['details'][0];
            if ($utype == "s") {
               $uid = substr($uid, 2);
            }
            DB::insert('users', array(
               'uid' => $uid,
               'utype' => $utype,
               'password' => $_SESSION['details'][1],
               'fname' => $_SESSION['details'][3]['fname'],
               'lname' => $_SESSION['details'][3]['lname'],
               'email' => $_SESSION['details'][3]['email'],
               'salt' => $_SESSION['details'][2]
            ));
            if ($utype == "s") {
               DB::insert('students', array(
                  'uid' => $uid,
                  'tutg' => $_SESSION['details'][3]['tutg'],
                  'house' => $_SESSION['details'][3]['house'],
                  'yr' => $_SESSION['details'][3]['yr'],
               ));
            } else {
               DB::insert('mentors', array(
                  'uid' => $uid,
                  'verified' => 0
               ));
            }
            session_unset();
            session_destroy();
            session_start();
            $b = 1; // Account creation good 
            $complete = DB::queryfirstrow('Select * from users where uid = %s', $uid);
            $_SESSION['uid'] = $uid;
            $_SESSION['utype'] = $complete['utype'];
            $_SESSION['fname'] = $complete['fname'];
            $_SESSION['lname'] = $complete['lname'];
            $_SESSION['email'] = $complete['email'];
            if ($utype == "s") {
               $scheck = DB::queryFirstRow('Select * from students where uid = %s', $uid);
               $_SESSION['yr'] = $scheck['yr'];
               $_SESSION['tutg'] = $scheck['tutg'];
               $_SESSION['house'] = $scheck['house'];
            }
         } else {
            header('Location:register.php');
         }
      }
   }

   if (isset($_GET['type'])) {
      if ($_GET['type'] == "s") {
         echo '
         <p class="lead my-2 text-center">Student Registration</p>
            <div class="container align-items-center mx-auto text-center m-4">
                  <div class="row">
                        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
                           <form method="post" action="register.php?b=0" name="studentreg" id="studentreg">
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="far fa-user fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="text" class="form-control" placeholder="Student Number:" aria-label="Student Number" required name="username"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="fas fa-lock fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="password" class="form-control" placeholder="Password:" aria-label="Password" required name="pass"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="fas fa-lock fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="password" class="form-control" placeholder="Confirm Password:" aria-label="Confirm Password" required name="cpass"/>
                                    </div>
                              </div>

                              <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submitstudent" form="studentreg">
                        <i class="fas fa-user-check fa-lg formicon"></i>
                        Send Code
                        </button>
                           </form>
                           <a href="login.php">
                              <p class="lead py-3 my-3">Login Instead</p>
                           </a>
                        </div>
                  </div>
               </div>         
';
      } elseif ($_GET['type'] == "m") {
         echo '
         <p class="lead my-2 text-center">Mentor Registration</p>
            <div class="container align-items-center mx-auto text-center m-4">
                  <div class="row">
                        <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
                           <form method="post" action="register.php?b=0" name="mentorreg" id="mentorreg">
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="far fa-user fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="text" class="form-control" placeholder="Username:" aria-label="Username" required name="username"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="fas fa-lock fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="password" class="form-control" placeholder="Password:" aria-label="Password" required name="pass"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="fas fa-lock fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="password" class="form-control" placeholder="Confirm Password:" aria-label="Confirm Password" required name="cpass"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="far fa-user fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="text" class="form-control" placeholder="First Name:" aria-label="First Name" required name="fname"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="far fa-user fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="text" class="form-control" placeholder="Last Name:" aria-label="Last Name" required name="lname"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                       <div class="input-group-prepend">
                                          <span class="input-group-text">
                              <i class="far fa-envelope fa-2x"></i
                              ></span>
                                       </div>
                                       <input type="email" class="form-control" placeholder="Email:" aria-label="Email Address" required name="email"/>
                                    </div>
                              </div>
                              <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="file-upload text-center mx-auto">
                                            <input class="file-upload__input" type="file" name="filea" id="file"
                                                >
                                            <button class="tbutton file-upload__button" type="button"><span
                                                    class="file-input-text">File</span></button>
                                            <span class="file-upload__label" style="color:red;"></span>
                                        </div>
                                    </div>
                                </div>
                              <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submitmentor" form="mentorreg">
                        <i class="fas fa-user-check fa-lg formicon"></i>
                        Send Code
                        </button>
                           </form>
                           <a href="login.php">
                              <p class="lead py-3 my-3">Login Instead</p>
                           </a>
                        </div>
                  </div>
               </div>         
';
      }
   } elseif ((!isset($_GET['b']))) {
      echo '
      <p class="lead my-2 text-center">Select a user type</p>
         <div class="container my-4">
        <div class="row text-center mx-auto align-items-center mt-4">
            <div class="mx-auto col-sm-12 col-md-10 col-lg-5 text-center py-2">
                <a href="register.php?type=s">
                    <div class="regiconbox align-middle">
                        <div class="my-4">
                            <i class="fas fa-user fa-7x regicon"></i>
                            <br />
                            <h3 class="text-center heading">Student</h3>
                        </div>
                    </div>
                </a>
            </div>
            <br />
            <br />
            <br />
            <div class="mx-auto col-sm-12 col-md-10 col-lg-5 text-center py-2">
                <a href="register.php?type=m">
                    <div class="regiconbox align-middle">
                        <div class="my-4">
                            <i class="fas fa-user-graduate fa-7x regicon"></i>
                            <br />
                            <h3 class="text-center heading">Mentor</h3>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
      ';
   }

   if (isset($b)) {
      if ($b == 5) {
         echo '
            <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
               <div class="toast g-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                  <div class="toast-body">
                     <span>
                        <span class="alert-text ml-3 mr-1">A code has been sent to your email</span>
                        <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                     </span>
                  </div>
               </div>
            </div>
      ';
      } elseif ($b == 1) {
         echo '
            <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
               <div class="toast g-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                  <div class="toast-body">
                     <span>
                        <span class="alert-text ml-3 mr-1">Your account was created</span>
                        <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                     </span>
                  </div>
               </div>
            </div>
      <script>
         setTimeout(function(){window.location.href = "index.php";},2500);
      </script>
      ';
      } elseif ($b == 2) {
         echo '
            <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
               <div class="toast r-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                  <div class="toast-body">
                     <span>
                        <span class="alert-text ml-3 mr-1">That email/username is already in use</span>
                        <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                     </span>
                  </div>
               </div>
            </div>
      ';
      } elseif ($b == 3) {
         echo '
            <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
               <div class="toast r-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                  <div class="toast-body">
                     <span>
                        <span class="alert-text ml-3 mr-1">That is not a valid student number</span>
                        <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                     </span>
                  </div>
               </div>
            </div>
      ';
      } elseif ($b == 4) {
         echo '
            <div class="fixed-bottom justify-content-center align-items-center flex-column-reverse toast-holder">
               <div class="toast r-warning" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
                  <div class="toast-body">
                     <span>
                        <span class="alert-text ml-3 mr-1">Passwords do not match</span>
                        <span class="alert-close" type="button" data-dismiss="toast">&times;</span>
                     </span>
                  </div>
               </div>
            </div>
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

   ?>


    <script>
    Array.prototype.forEach.call(
        document.querySelectorAll(".file-upload__button"),
        function(button) {
            const hiddenInput = button.parentElement.querySelector(
                ".file-upload__input"
            );
            const label = button.parentElement.querySelector(".file-input-text");
            const defaultLabelText = "Blue Card Image";

            // Set default text for label
            label.textContent = defaultLabelText;
            label.title = defaultLabelText;

            button.addEventListener("click", function() {
                hiddenInput.click();
            });

            hiddenInput.addEventListener("change", function() {
                const filenameList = Array.prototype.map.call(hiddenInput.files, function(
                    file
                ) {
                    return file.name;
                });

                label.textContent = filenameList;
                label.title = label.textContent;
            });
        }
    );
    </script>


</body>

</html>

</html>