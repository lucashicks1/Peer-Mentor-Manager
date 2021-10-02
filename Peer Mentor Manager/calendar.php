<?php
session_start();
require_once('mail/SMTP.php');
require_once('mail/PHPMailer.php');
require_once('mail/Exception.php');
require('connect.php');

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

if (!$_SESSION['fname']) {
    header('Location: login.php');
}


function sendmail($email, $fname, $lname, $message, $subject)
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
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = $message;
        $mail->send();
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}


require('connect.php');
if (isset($_GET['a'])) {
    $info = DB::queryFirstRow('Select * from sessions where sessionid = %s', $_GET['a']);
    if ($info) {
        $check = DB::queryfirstrow('Select * from studentsessions where sessionid = %s AND studentid = %s', $_GET['a'], $_SESSION['uid']);
        if (($_SESSION['utype'] == 'a') || ($check) || $info['mentor'] == $_SESSION['uid']) {
            $subject = DB::queryFirstField('Select subjectname from subjects where subjectid = %s', $info['subid']);
            $initparts = DB::queryFirstRow('Select fname, lname from users where uid = %s', $info['initiator']);
            $initiator = $initparts['fname'] . ' ' . $initparts['lname'];
            $theDate    = new DateTime($info['sdate']);
            $platedDate = $theDate->format('l F d');
            $s = new DateTime($info['stime']);
            $stime = $s->format('g:i A');
            $e = new DateTime($info['etime']);
            $etime = $e->format('g:i A');
            echo '
            <div class="modal" tabindex="-1" role="dialog" id="myModal">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content text-center">
                        <div class="modal-header text-center">
                            <div class="col-12">
                                <h3 class="text-center modal-title col-12 heading">Session Summary</h3>
                                <h6 class="text-center modal-title my-0">' . $platedDate . '</h6>
                                <h6 class="text-center modal-title my-0" style="font-weight:600;">' . $stime . ' - ' . $etime . '</h6>';
            if ($info['mentor']) {
                $mnameparts = DB::queryFirstRow('Select fname, lname from users where uid = %s', $info['mentor']);
                $mname = $mnameparts['fname'] . ' ' . $mnameparts['lname'];
            } else {
                echo '<h5 class="text-center modal-title mt-3" style="font-weight:600;">Not Confirmed</h5>';
                $mname = 'Unassigned';
            }

            echo '  </div>
                        </div>
                            <div class="modal-body">
                                <div class="col-12 text-center">
                                    <h4 class="modal-title my-0" style="font-weight:600;">Information:</h4>
                                        <div class="container">
                                            <p>Subject: ' . $subject . '</p>
                                            <p>Student Limit: ' . $info['slimit'] . '</p>
                                            <p>Mentor: ' . $mname . '</p>
                                            <p>Initiator: ' . $initiator . '</p>
                                            <p>Room: ' . $info['room'] . '</p>
                                        </div>
                                </div>';
            $merge = new DateTime($theDate->format('Y-m-d') . ' ' . $etime);
            $datetime = $merge->format('Y-m-d H:i:s');
            if ($_SESSION['utype'] == 's') {
                $mentor = DB::queryFirstField('
                Select mentor
                from sessions
                where sessionid = %s
                ', $_GET['a']);
                if ($mentor) {
                    # code...

                    if (date('Y-m-d H:i:s') < $datetime) { // Claim that you're absent from session
                        $status = DB::queryFirstField('
                    select stat
                    from studentsessions
                    where sessionid = %s
                    AND studentid = %s
                    ', $_GET['a'], $_SESSION['uid']);
                        if (!$status) {
                            echo '
                        <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php?b=' . $_GET['a'] . '">
                            <i class="fas fa-user-clock"></i>  Absent
                        </a>
                        ';
                        } else {
                            echo '
                        <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php?b=' . $_GET['a'] . '">
                            <i class="fas fa-user-clock"></i>  Present
                        </a>
                        ';
                        }
                    } else {
                        $test = DB::queryFirstField('
                            Select mrating
                            from studentsessions
                            where sessionid = %s
                            AND studentid = %s
                            ', $_GET['a'], $_SESSION['uid']);
                        if (!$test) {
                            echo '
                            <div class="col-6 col-sm-6 col-md-4 align-items-center mx-auto text-center">
                                <form method="post" action="calendar.php?r=' . $_GET['a'] . '" name="rate" id="rate">
                                    <div class="form-group my-3 py-1">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                <i class="far fa-star fa-2x"></i></span>
                                            </div>
                                        <input type="text" autocomplete="false" class="form-control" placeholder="Rating (1-5):" aria-label="Rating" required name="rating"/>
                                        </div>
                                    </div>
                                    <button type="submit" class="tbutton mx-2" style="color: #fff;" name="ratebutton" form="rate">
                                        <i class="far fa-star fa-lg formicon" style="color:#fff"></i>  Rate Mentor
                                    </button>
                                </form>
                            </div>
                        ';
                        }
                    }
                }
            } else {
                // TODO Admin / mentor options
                if (date('Y-m-d H:i:s') < $datetime) {
                    $status = DB::queryFirstField('
                    select stat
                    from sessions
                    where sessionid = %s
                    AND mentor = %s
                    ', $_GET['a'], $_SESSION['uid']);
                    if (!$status) {
                        echo '
                        <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php?c=' . $_GET['a'] . '">
                            <i class="fas fa-times"></i>  Cancel Session
                        </a>
                        ';
                    } else {
                        echo '
                        <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php?c=' . $_GET['a'] . '">
                            <i class="fas fa-check"></i>  Approve Session
                        </a>
                        ';
                    }
                } else {
                    echo '
                    <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php?v=' . $_GET['a'] . '">
                        <i class="fas fa-user"></i>  View Students
                    </a>
                    ';
                }
            }
            echo '<br/><a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php">
                                    <i class="fas fa-times"></i>Close
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        } else {
            header('location:calendar.php');
        }
    } else {
        header('location:calendar.php');
    }
} elseif (isset($_GET['b'])) {
    $status = DB::queryFirstField('
                    select stat
                    from studentsessions
                    where sessionid = %s
                    AND studentid = %s
                    ', $_GET['b'], $_SESSION['uid']);
    $info = DB::queryFirstRow('
    Select email, fname, lname, subjectname, sdate, stime
    from sessions a, users b, subjects c
    where a.mentor = b.uid
    AND a.subid = c.subjectid
    AND a.sessionid = %s
    ', $_GET['b']);
    $student = DB::queryFirstRow('
    select fname, lname
    from users
    where uid = %s
    ', $_SESSION['uid']);
    if ($status) {
        $stat = null;
        $message = $student['fname'] . ' ' . $student['lname'] . ' can attend your ' . $info['subjectname'] . ' session at ' . $info['stime'] . ' on ' . $info['sdate'];
        $subject = 'Student present for session';
    } else {
        $stat = 'c';
        $message = $student['fname'] . ' ' . $student['lname'] . ' cannot attend your ' . $info['subjectname'] . ' session at ' . $info['stime'] . ' on ' . $info['sdate'];
        $subject = 'Student absent for session';
    }
    DB::query('
    UPDATE studentsessions
    SET stat = %s
    WHERE sessionid = %s
    AND studentid = %s', $stat, $_GET['b'], $_SESSION['uid']);
    sendmail($info['email'], $info['fname'], $info['lname'], $message, $subject);
    echo '
    <div class="modal" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
                <div class="modal-header text-center">
                    <div class="col-12">
                        <h3 class="text-center modal-title col-12 heading">Session</h3>
                    </div>
                </div>
                    <div class="modal-body">
                        <div class="col-12 text-center">
                                <div class="container">
                                    <p>Your planned attendance record has been updated</p>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
} elseif (isset($_GET['c'])) {
    // TODO Cancel session
    $status = DB::queryFirstField('
                    select stat
                    from sessions
                    where sessionid = %s
                    AND mentor = %s
                    ', $_GET['c'], $_SESSION['uid']);
    $students = DB::query(
        'select *
        from studentsessions a, users b
        where a.studentid = b.uid
        AND a.sessionid = %s',
        $_GET['c']
    );
    if ($status) {
        // Need to approve
        $stat = null;
        $subject = 'Mentor approved session';
    } else {
        // Cancel session
        $stat = 'c';
        $subject = 'Mentor cancelled session';
    }
    $emailinfo = DB::queryFirstRow('
    Select subjectname, sdate, stime
    from subjects a, sessions b
    where b.subid = a.subjectid
    AND b.sessionid = %s
    ', $_GET['c']);
    $message = $_SESSION['fname'] . ' ' . $_SESSION['lname'] . ' has cancelled your ' . $emailinfo['subjectname'] . ' session at ' . $emailinfo['stime'] . ' on ' . $emailinfo['sdate'];
    // Update DB
    DB::query('
    UPDATE sessions
    SET stat = %s
    WHERE sessionid = %s', $stat, $_GET['c']);
    // SEND EMAILS
    for ($i = 0; $i < count($students); $i++) {
        sendmail($students[$i]['email'], $students[$i]['fname'], $students[$i]['lname'], $message, $subject);
    }
    // MODAL
    echo '
    <div class="modal" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content text-center">
                <div class="modal-header text-center">
                    <div class="col-12">
                        <h3 class="text-center modal-title col-12 heading">Session</h3>
                    </div>
                </div>
                    <div class="modal-body">
                        <div class="col-12 text-center">
                                <div class="container">
                                    <p>The status of the session has been updated</p>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
} elseif (isset($_GET['r'])) {
    if (isset($_POST['rating'])) {
        $test = DB::queryFirstField('
        Select *
        from studentsessions
        where sessionid = %s
        AND studentid = %s
        ', $_GET['r'], $_SESSION['uid']);
        if ($test) {
            DB::query('
            UPDATE studentsessions
            SET mrating = %s
            WHERE sessionid = %s
            AND studentid = %s', $_POST['rating'], $_GET['r'], $_SESSION['uid']);
            echo '
        <div class="modal" tabindex="-1" role="dialog" id="myModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content text-center">
                    <div class="modal-header text-center">
                        <div class="col-12">
                            <h3 class="text-center modal-title col-12 heading">Session</h3>
                        </div>
                    </div>
                        <div class="modal-body">
                            <div class="col-12 text-center">
                                    <div class="container">
                                        <p>You have successfully given the mentor a rating</p>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        }
    } else {
        header('Location:calendar.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Mentor Manager - Calendar</title>
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
    <link href='calendar/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.1/css/all.css' rel='stylesheet'>
    <script src='calendar/main.min.js'></script>

    <style>
    .btn-primary,
    .btn-primary:hover,
    .btn-primary:active,
    .btn-primary:active:focus,
    .btn-primary:focus {
        background-color: #ba0c2f !important;
        border-color: #fff;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .btn-primary.disabled,
    .btn-primary:disabled {
        color: #fff;
        background-color: #ba0c2f;
        border-color: #fff;
    }
    </style>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            nowIndicator: true,
            customButtons: {
                gridview: {
                    text: 'Grid View',
                    click: function() {
                        calendar.changeView('dayGridMonth');
                    }
                },
                listview: {
                    text: 'List View',
                    click: function() {
                        calendar.changeView('listWeek');
                    }
                },
                sessions: {
                    text: 'Sessions',
                    click: function() {
                        window.location.href = "sessions.php";
                    }
                }
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'sessions gridview,listview'
            },
            titleFormat: {
                month: 'long',
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
            },
            themeSystem: 'bootstrap',
            bootstrapFontAwesome: {
                close: 'fa-times',
                prev: 'fa-chevron-left',
                next: 'fa-chevron-right',
                prevYear: 'fa-angle-double-left',
                nextYear: 'fa-angle-double-right',
                gridview: 'fa-th-large',
                listview: 'fa-list',
            },
            events: "fetch-event.php",
        });

        calendar.render();
    });
    </script>
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="imgs/Terrace.png" width="270" height="100%" alt="" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span>Menu</span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home<span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="calendar.php">My Calendar</a>
                    </li>
                    <?php
                    if ($_SESSION['utype'] != 'a') {
                        echo '
                      <li class="nav-item">
                         <a class="nav-link" href="Subjects.php">Subjects</a>
                      </li>
                      ';
                    } else {
                        echo '
                        <li class="nav-item">
                            <a class="nav-link" href="Dashboard.php">Dashboard</a>
                        </li>
                        ';
                    }
                    ?>
                    <li class="nav-item cta dropdown">
                        <a class="nav-link" id="navbarDropdown" role="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-user fa-lg"></i>
                            <?php
                            echo $_SESSION['fname'];
                            ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="logout.php">LOG OUT</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php

    if ((!isset($_GET['v']) && !(isset($_GET['s'])))) {
        echo '
    <h1 class="text-center heading my-4">My Calendar</h1>
    <div class="container">
        <div id="calendar"></div>
    </div>    
    ';
    } elseif (isset($_GET['v'])) {
        echo '
        <div class="container">
        <h1 class="text-center heading my-4">View Students</h1>
        ';
        $info = DB::query('
        Select fname, lname,stat, srating, note, attendance, uid
        from users a, studentsessions b
        where b.sessionid = %s
        AND a.uid = b.studentid
        ', $_GET['v']);
        if (isset($_POST['srating'])) {

            if ($_POST['attend'] == 'Present') {
                $attend = 1;
            } else {
                $attend = 0;
            }
            DB::query('
            UPDATE studentsessions
            SET attendance = %s, note = %s, srating = %s
            WHERE sessionid = %s
            AND studentid = %s', $attend, $_POST['note'], $_POST['srating'], $_GET['v'], $_POST['sid']);
        }
        if ($info) {
            echo '
            <div class="table-responsive table-striped text-center">
                <table class="table ver1">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Student Rating</th>
                            <th scope="col">Note</th>
                            <th scope="col">Attendance</th>
                            <th scope="col">Modify</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            for ($i = 0; $i < count($info); $i++) {
                if ($info[$i]['stat']) {
                    $status = 'Cancelled';
                } else {
                    $status = 'Attending';
                }
                if (!$info[$i]['srating']) {
                    $info[$i]['srating'] = 'Unrated';
                }
                if (!$info[$i]['note']) {
                    $info[$i]['note'] = 'Blank';
                }
                if ($info[$i]['attendance']) {
                    $attend = 'Present';
                } else {
                    $attend = 'Absent';
                }
                echo '
                <tr>
                    <td>' . $info[$i]['fname'] . ' ' . $info[$i]['lname'] . '</td>
                    <td>' . $status . '</td>
                    <td>' . $info[$i]['srating'] . '</td>
                    <td>' . $info[$i]['note'] . '</td>
                    <td>' . $attend . '</td>
                    <td><a href="calendar.php?s=' . $_GET['v'] . '&stud=' . $info[$i]['uid'] . '">Modify</a></td>
                </tr>
                ';
            }
            echo '
            </tbody>
            </table>
            <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="calendar.php?a=' . $_GET['v'] . '">
                Return
            </a>
            </div>
            </div>
            ';
        } else {
            echo '<p class="lead my-4 text-center">No students have enrolled in the session</p>';
        }
        echo '
        </div>
        ';
    } elseif ($_GET['s']) {
        // GET s = sessionid
        // GET stud = studentid
        $sessionid = $_GET['s'];
        $studentid = $_GET['stud'];
        $sinfo = DB::queryFirstRow('
        Select *
        from studentsessions
        where studentid = %s
        AND sessionid = %s
        ', $studentid, $sessionid);
        if ($sinfo['attendance']) {
            $attendance = 'Present';
        } else {
            $attendance = 'Absent';
        }
        echo '
        <div class="container">
            <h1 class="text-center heading my-4">View Students</h1>
        </div>
        <div class="container align-items-center mx-auto text-center m-4">
            <div class="row">
                <div class="mx-auto col-8 col-sm-8 col-md-6 col-lg-4">
                    <form method="post" action="calendar.php?v=' . $sessionid . '" name="studentupdate" id="studentupdate">
                        <input type="hidden" name="sid" value="' . $studentid . '">
                        <div class="form-group my-3 py-1">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                        <i class="far fa-star fa-2x"></i></span>
                                </div>
                                <input type="text" class="form-control" placeholder="Rating (1-5):" aria-label="Student Rating" required name="srating" value="' . $sinfo['srating'] . '"/>
                            </div>
                        </div>
                        <div class="form-group my-3 py-1">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                        <i class="fas fa-pen fa-2x"></i></span>
                                </div>
                                <input type="text" class="form-control" placeholder="Notes:" aria-label="Notes" required name="note" value="' . $sinfo['note'] . '"/>
                            </div>
                        </div>
                        <div class="form-group my-3 py-1">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                        <i class="far fa-user fa-2x"></i></span>
                                </div>
                                <input type="text" class="form-control" placeholder="Attendance:" aria-label="Attendance" required name="attend" list="ops""/>
                                <datalist id="ops">
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                </datalist>
                            </div>
                        </div>
                        <button type="submit" class="tbutton mx-2" style="color: #fff;" name="submitupdate" form="studentupdate">
                <i class="fas fa-user-check fa-lg formicon"></i>
                Update
                </button>
                    </form>
                </div>
            </div>
        </div>  
        ';
    }




    if (isset($_GET['a'])) {
        echo '
      <script type="text/JavaScript">
         $(document).ready(function() {
         $("#myModal").modal("show");
         });
      </script>
      ';
    } elseif (isset($_GET['b']) || isset($_GET['r']) || isset($_GET['c'])) {
        echo '
        <script>
        $(document).ready(function() {
         $("#myModal").modal("show");
         });
        setTimeout(function(){window.location.href = "calendar.php";},2000);
        </script>
        ';
    }
    ?>


</body>

</html>