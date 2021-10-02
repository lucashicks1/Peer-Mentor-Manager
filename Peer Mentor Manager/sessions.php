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

function sendmail($email, $fname, $lname, $message)
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
        $mail->Subject = 'Session Enrollment - Terrace Mentor Program';
        $mail->Body = $message;
        $mail->AltBody = $message;
        $mail->send();
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

require('connect.php');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Mentor Manager - Sessions</title>
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

    <?php
    if (isset($_GET['a'])) {
        if ($_GET['a'] == '2') {
            echo "
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
                    text: 'Create',
                    click: function() {
                        window.location.href = 'sessions.php';
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
                listview: 'fa-list'
            },
            events: 'fetch-event.php?a=" . $_GET['s'] . "',
        });

        calendar.render();
    });
    </script>
        ";
        }
    }
    ?>

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
                    <li class="nav-item">
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

    <div class="container">
        <h1 class="text-center heading my-4">Session Manager</h1>

        <?php

        if (isset($_GET['a'])) {
            // The user has opted to create or look for a session
            if ($_GET['a'] == '1') {
                echo '
                <div class="row">
                    <div class="col-8 offset-2 text-center pb-4">
                        <p class="lead">Create a new session</p>
                        <div class="col-12 col-sm-10 col-md-6 align-items-center mx-auto text-center">
                                <form method="post" action="sessions.php" name="subcreate" id="subcreate">
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-calendar fa-2x"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Date:"
                                            aria-label="Date" required name="sdate" id="dt1">
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-clock fa-2x"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Start Time:"
                                            aria-label="Start Time" required name="stime" id="dt2">
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                            <i class="far fa-hourglass fa-2x"></i></span>
                                        </div>
                                    <input type="text" autocomplete="false" class="form-control" placeholder="Duration (mins):" aria-label="Duration" required name="duration" list="times" />
                                    <datalist id="times">
                                        <option value="30">30</option>
                                        <option value="60">60</option>
                                        <option value="90">90</option>
                                        <option value="120">120</option>
                                    </datalist>
                                    </div>
                                </div>
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="far fa-user fa-2x"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Student Limit:"
                                            aria-label="Student Limit" required name="slimit" >
                                    </div>
                                </div>                                
                                ';
                if ($_SESSION['utype'] == 'm') {
                    # code...
                    echo '
                    <div class="form-group my-3 py-1">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar fa-2x"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Year Level:"
                                aria-label="Year Level" required name="yr" list="years">
                            <datalist id="years">';
                    $list = DB::queryFirstColumn('
                    Select distinct ylv
                    from usersubjects a, subjects b
                    where a.uid = %s
                    AND b.subjectname = %s
                    AND a.subjectid = b.subjectid', $_SESSION['uid'], $_GET['s']);
                    for ($i = 0; $i < count($list); $i++) {
                        echo '<option value="' . $list[$i] . '">' . $list[$i] . '</option>';
                    }

                    echo '
                            </datalist>
                        </div>
                    </div>
                    ';
                    // Date, Start Time, Duration, Year Level, Student Limit
                }
                echo '
                                <input type="hidden" name="subject" value="' . $_GET['s'] . '">
                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="create" form="subcreate">
                                    <i class="fas fa-plus fa-lg formicon"></i>  Create Session
                                </button>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                ';
            } elseif ($_GET['a'] == '2') {
                echo '
                <div class="row">
                    <div class="col-8 offset-2 text-center pb-4">
                        <p class="lead">Look for existing sessions</p>
                    </div>
                </div>
                <div class="container">
                    <div id="calendar"></div>
                </div>
                ';
            } else {
                header('Location:sessions.php');
            }
        } elseif (isset($_POST['create'])) {
            // Create session
            if ($_SESSION['utype'] == 's') {
                $sub = DB::queryFirstField('Select subjectid from subjects where subjectname = %s AND ylv = %s', $_POST['subject'], $_SESSION['yr']);
                $mentor = null;
            } else {
                $sub = DB::queryFirstField('Select subjectid from subjects where subjectname = %s AND ylv = %s', $_POST['subject'], $_POST['yr']);
                $mentor = $_SESSION['uid'];
            }
            $slimit = $_POST['slimit'];
            $stime = date('H:i:s', strtotime($_POST['stime']));
            $add = strtotime('+' . $_POST['duration'] . ' minutes', strtotime($_POST['stime']));
            $etime = date('H:i:s', $add);
            $room = DB::queryFirstField('Select room from rooms where room NOT IN (
                Select room
                from sessions
                WHERE ((
                    CAST(stime As TIME) < CAST(%s As TIME)
                    AND CAST(%s As TIME) < CAST(etime As TIME)
                ) OR (
                    CAST(stime As TIME) < CAST(%s As TIME)
                    AND CAST(%s As TIME) <= CAST(etime As TIME)
                ) OR (
                    CAST(%s As TIME) < CAST(stime As TIME)
                    AND CAST(%s As TIME) > CAST(etime As TIME)
                )) AND sdate = %s)
            LIMIT 1', $stime, $stime, $etime, $etime, $stime, $etime, $_POST['sdate']);
            DB::insert(
                'sessions',
                [
                    'sdate' => $_POST['sdate'],
                    'stime' => $stime,
                    'etime' => $etime,
                    'subid' => $sub,
                    'mentor' => $mentor,
                    'slimit' => $slimit,
                    'initiator' => $_SESSION['uid'],
                    'room' => $room
                ]
            );
            $sid = DB::queryFirstField('Select sessionid from sessions ORDER BY sessionid DESC LIMIT 1');
            if ($_SESSION['utype'] == 's') {
                DB::insert(
                    'studentsessions',
                    [
                        'studentid' => $_SESSION['uid'],
                        'sessionid' => $sid,
                        'attendance' => ''
                    ]
                );
            }
            echo '
            <div class="row">
                <div class="col-8 offset-2 text-center pb-4">
                    <p class="lead">Sessions</p>
                    <div class="col-12 col-sm-10 col-md-6 align-items-center mx-auto text-center">
                    <p>Your tutoring session has been created.</p>
                    </div>
                </div>
            </div>
            <script>
			    setTimeout(function(){window.location.href = "calendar.php";},2000);
            </script>
            ';
        } elseif (isset($_POST['subject'])) {
            if (isset($_POST['new'])) {
                // Create session
                header('Location:sessions.php?a=1&s=' . $_POST['subject']);
            } else {
                // Search for existing sessions
                header('Location:sessions.php?a=2&s=' . $_POST['subject']);
            }
            // Gets the inputs from the form (POST Values) and will redirect using Get variables
        } elseif (isset($_GET['m'])) {
            $info = DB::queryFirstRow('Select * from sessions where sessionid = %s', $_GET['m']);
            if ($info) {
                $check = DB::queryfirstrow('Select * from studentsessions where sessionid = %s AND studentid = %s', $_GET['m'], $_SESSION['uid']);
                $subject = DB::queryfirstrow('Select subjectname, ylv from subjects where subjectid = %s', $info['subid']);
                $initparts = DB::queryFirstRow('Select fname, lname from users where uid = %s', $info['initiator']);
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
                                        <p>Year Level: ' . $subject['ylv'] . '</p>
                                        <p>Year Level: ' . $subject['subjectname'] . '</p>
                                        <p>Student Limit: ' . $info['slimit'] . '</p>
                                        <p>Mentor: ' . $mname . '</p>
                                        <p>Room: ' . $info['room'] . '</p>
                                    </div>
                            </div>';
                echo '  <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="sessions.php?r=' . $_GET['m'] . '">
                                    <i class="fas fa-plus"></i>  Enrol
                            </a>
                        <br/>
                            <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="sessions.php?a=2&s=' . $subject['subjectname'] . '">
                                <i class="fas fa-times"></i>  Close
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
            } else {
                header('location:calendar.php');
            }
        } elseif (isset($_GET['r'])) {
            $test = DB::queryfirstfield('Select * from sessions where sessionid = %s', $_GET['r']);
            if ($test) {
                if ($_SESSION['utype'] == 's') {
                    DB::insert('studentsessions', [
                        'studentid' => $_SESSION['uid'],
                        'sessionid' => $_GET['r']
                    ]);
                    $sessioninfo = DB::queryfirstrow('
                        select sdate, stime, subjectname
                        from sessions a, subjects c
                        where a.sessionid = %s
                        AND c.subjectid = a.subid
                    ', $_GET['r'], $_SESSION['uid']);
                    // create entry into student sessions
                    $info = DB::queryfirstrow('
                    Select email, fname, lname, sdate, stime, subjectname
                    from sessions a, users b, subjects c
                    where a.sessionid = %s
                    AND a.mentor = b.uid
                    AND a.subid = c.subjectid
                    ', $_GET['r']);

                    $message = $_SESSION['fname'] . ' ' . $_SESSION['lname'] . ' has enrolled in the ' . $info['subjectname'] . ' session that you are mentoring on ' . $info['sdate'] . ' at ' . $info['stime'];

                    sendmail($info['email'], $info['fname'], $info['lname'], $message);
                } elseif ($_SESSION['utype'] == 'm') {
                    DB::update('sessions', ['mentor' => $_SESSION['uid']], "sessionid=%s", $_GET['r']);
                    // Update session - mentor field
                    $sessioninfo = DB::queryfirstrow('
                        select sdate, stime, fname, lname, subjectname
                        from sessions a, users b, subjects c
                        where a.sessionid = %s
                        AND b.uid = %s
                        AND c.subjectid = a.subid
                    ', $_GET['r'], $_SESSION['uid']);
                    $initiator = DB::queryfirstrow('
                    Select email, fname, lname
                    from users a, sessions b
                    WHERE a.uid = b.initiator
                    AND b.sessionid = %s
                    ', $_GET['r']);
                    $message =
                        $sessioninfo['fname'] . ' ' . $sessioninfo['lname'] . ' has enrolled as a mentor in your ' . $sessioninfo['subjectname'] . ' session on ' . $sessioninfo['sdate'] . ' at ' . $sessioninfo['stime'];
                    sendmail($initiator['email'], $initiator['fname'], $initiator['lname'], $message);
                }
                echo '
                <div class="row">
                    <div class="col-8 offset-2 text-center pb-4">
                        <p class="lead">Sessions</p>
                        <div class="col-12 col-sm-10 col-md-6 align-items-center mx-auto text-center">
                        <p>You have enrolled in this session</p>
                        </div>
                    </div>
                </div>
                <script>
                    setTimeout(function(){window.location.href = "calendar.php";},2000);
                </script>';
            }
        } else {
            echo
            '
            <div class="row">
                <div class="col-8 offset-2 text-center pb-4">
                <p class="lead">Choose a subject area</p>
                    <div class="col-12 col-sm-10 col-md-6 align-items-center mx-auto text-center">
                                <form method="post" action="sessions.php" name="subsearch" id="subsearch">
                                    <div class="form-group my-3 py-1">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                <i class="fas fa-graduation-cap fa-2x"></i></span>
                                            </div>
                                        <input type="text" autocomplete="false" class="form-control" placeholder="Subject:" aria-label="Subject" required name="subject" list="cats" /><datalist id="cats">
            ';
            $cats = DB::query('Select distinct subjectname from subjects s, usersubjects u where u.subjectid = s.subjectid AND uid = %s', $_SESSION['uid']);
            foreach ($cats as $cat) {
                echo '<option value="' . $cat['subjectname'] . '">' . $cat['subjectname'] . '</option>';
            }
            echo
            '
                                        </datalist>
                                        </div>
                                    </div>
                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="new" form="subsearch">
                                    <i class="fas fa-plus fa-lg formicon"></i>  New Session
                                </button>
                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="existing" form="subsearch">
                                    <i class="far fa-calendar formicon"></i>  Existing Session
                                </button>
                                </form>
                                </div>
                    </div>
                </div>
            </div>
        ';
        }

        ?>
    </div>

    <?php

    if (isset($_GET['m'])) {
        echo '
      <script type="text/JavaScript">
         $(document).ready(function() {
         $("#myModal").modal("show");
         });
      </script>
      ';
    }
    ?>

    <script>
    $(document).ready(function() {
        $("#dt1").focus(function() {
            $(this).attr({
                type: 'date'
            });
        });
    });
    $(document).ready(function() {
        $("#dt2").focus(function() {
            $(this).attr({
                type: 'time'
            });
        });
    });
    </script>

</body>

</html>