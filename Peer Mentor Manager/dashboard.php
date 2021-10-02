<?php
session_start();
if (!$_SESSION['fname']) {
    header('Location: login.php');
}
if ($_SESSION['utype'] != 'a') {
    header('Location:index.php');
}
require('connect.php');


if (isset($_FILES['filea'])) {
    // Minimise error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    require('connect.php');
    // Get file information
    $file_name = $_FILES['filea']['name'];
    $file_size = $_FILES['filea']['size'];
    $file_tmp = $_FILES['filea']['tmp_name'];
    $file_type = $_FILES['filea']['type'];
    $file_ext = strtolower(end(explode('.', $_FILES['filea']['name'])));
    // Check if csv file extension
    if ($file_ext == 'csv') {
        // Open file
        $h = fopen($file_tmp, "r");
        $array = [];
        // Open the file for reading
        if (($h = fopen($file_tmp, "r")) !== FALSE) {
            // Each line in the file is converted into an individual array that we call $data
            // The items of the array are comma separated
            while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
                // Each individual array is being pushed into the nested array
                $array[] = $data;
            }
            // Close the file
            fclose($h);
        }
        // Then enter into that table

        for ($l = 0; $l < count($array); $l++) {
            DB::insert("userbase", array('sid' => $array[$l][0], 'lname' => $array[$l][1], 'fname' => $array[$l][2], 'yr' => $array[$l][3], 'tutg' => $array[$l][4], 'house' => $array[$l][5], 'email' => $array[$l][6]));
        }
    } else {
        echo 'File is not a csv file';
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Mentor Manager - Dashboard</title>
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

    if (isset($_GET['a'])) {
        if ($_GET['a'] == '1') {
            # code...
            echo '
    <div class="container">
        <h1 class="text-center heading my-4">Admin Dashboard</h1>
        <div class="row">
            <div class="col-12 text-center pb-4">
                <h4 class="my-2 text-center" style="font-weight:600;">Student Attendance</h4>
                <div class="table-responsive table-striped text-center">
                    <table class="table ver1">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Mentor</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Attendance</th>
                                <th scope="col">View More</th>
                            </tr>
                        </thead>';


            $entry = DB::query('
                        Select c.fname as e, c.lname as f, d.fname as g, d.lname as h ,mentor, sdate, stime, attendance, subjectname, a.sessionid, studentid
                        from sessions a, studentsessions b, users c, users d, subjects
                        WHERE a.sessionid = b.sessionid
                        AND b.studentid = c.uid
                        AND a.mentor = d.uid
                        AND a.subid = subjectid
                        AND sdate <= CAST( NOW() AS Date )
                        ORDER BY sdate DESC
                        ');
            for ($i = 0; $i < count($entry); $i++) {
                $theDate    = new DateTime($entry[$i]['sdate']);
                $platedDate = $theDate->format('l F d');
                $s = new DateTime($entry[$i]['stime']);
                if ($entry[$i]['attendance']) {
                    $attendance = 'Present';
                } else {
                    $attendance = 'Absent';
                }
                $stime = $s->format('g:i A');
                echo '
                            <tr>
                                <td>' . $entry[$i]['e'] . ' ' . $entry[$i]['f'] . '</td>
                                <td>' . $entry[$i]['subjectname'] . '</td>
                                <td>' . $entry[$i]['g'] . ' ' . $entry[$i]['h'] . '</td>
                                <td>' . $platedDate . '</td>
                                <td>' . $stime . '</td>
                                <td>' . $attendance . '</td>
                                <td><a href="dashboard.php?stud=' . $entry[$i]['sessionid'] . '&sid=' . $entry[$i]['studentid'] . '">View More</a></td>
                            </tr>
                            ';
            }

            echo '
                        <tbody>
                        </tbody>
                    </table>
                    <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="dashboard.php">
                        Return
                    </a>
                </div>
            </div>
        </div>
    </div>
    ';
        } elseif ($_GET['a'] == '2') {
            echo
            '
    <div class="container">
        <h1 class="text-center heading my-4">Admin Dashboard</h1>
        <div class="row">
            <div class="col-12 text-center pb-4">
                <h4 class="my-2 text-center" style="font-weight:600;">Blue Card Admissions</h4>

        <div class="table-responsive table-striped text-center">
            <table class="table ver1">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Image</th>
                        <th scope="col">Approve</th>
                    </tr>
                </thead>
                <tbody>';
            $mentors = DB::query('
        Select fname, lname, email, img, a.uid
        from users a, mentors b
        where a.uid = b.uid
        AND b.verified = 0
        LIMIT 5
        ');
            for ($i = 0; $i < count($mentors); $i++) {
                echo '
            <tr>
                <td>' . $mentors[$i]['fname'] . ' ' . $mentors[$i]['lname'] . '</td>
                <td>' . $mentors[$i]['email'] . '</td>
                <td><a href="dashboard.php?i=' . $mentors[$i]['img'] . '">View Image</a></td>
                <td><a href="dashboard.php?approve=' . $mentors[$i]['uid'] . '">Approve</a></td>
            </tr>
            ';
            }
            echo '
                        <tbody>
                        </tbody>
                    </table>
                    <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="dashboard.php">
                        Return
                    </a>
                </div>
            </div>
        </div>
    </div>
    ';
        }
    } elseif (isset($_GET['approve'])) {
        DB::query('
        UPDATE mentors
        SET verified = %s
        WHERE uid = %s
        ', 1, $_GET['approve']);
        header('Location:dashboard.php');
    } else {

        echo '
    <div class="container">
        <h1 class="text-center heading my-4">Admin Dashboard</h1>
        <div class="row">
            <div class="col-12 text-center pb-4">
                <h4 class="my-2 text-center" style="font-weight:600;">Student Attendance</h4>
                <div class="table-responsive table-striped text-center">
                    <table class="table ver1">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Mentor</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Attendance</th>
                                <th scope="col">View More</th>
                            </tr>
                        </thead>
                        <tbody>';


        $entry = DB::query('
                        Select c.fname as e, c.lname as f, d.fname as g, d.lname as h ,mentor, sdate, stime, attendance, subjectname, a.sessionid, studentid
                        from sessions a, studentsessions b, users c, users d, subjects
                        WHERE a.sessionid = b.sessionid
                        AND b.studentid = c.uid
                        AND a.mentor = d.uid
                        AND a.subid = subjectid
                        AND sdate <= CAST( NOW() AS Date )
                        LIMIT 5
                        ');
        for ($i = 0; $i < count($entry); $i++) {
            $theDate    = new DateTime($entry[$i]['sdate']);
            $platedDate = $theDate->format('l F d');
            $s = new DateTime($entry[$i]['stime']);
            if ($entry[$i]['attendance']) {
                $attendance = 'Present';
            } else {
                $attendance = 'Absent';
            }
            $stime = $s->format('g:i A');
            echo '
                            <tr>
                                <td>' . $entry[$i]['e'] . ' ' . $entry[$i]['f'] . '</td>
                                <td>' . $entry[$i]['subjectname'] . '</td>
                                <td>' . $entry[$i]['g'] . ' ' . $entry[$i]['h'] . '</td>
                                <td>' . $platedDate . '</td>
                                <td>' . $stime . '</td>
                                <td>' . $attendance . '</td>
                                <td><a href="dashboard.php?stud=' . $entry[$i]['sessionid'] . '&sid=' . $entry[$i]['studentid'] . '">View More</a></td>
                            </tr>
                            ';
        }

        echo '
                        </tbody>
                    </table>
                    <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="dashboard.php?a=1">
                        View All
                    </a>
                </div>
            </div>
        </div>
        <br />





        <h4 class="my-2 text-center" style="font-weight:600;">Blue Card Admissions</h4>

        <div class="table-responsive table-striped text-center">
            <table class="table ver1">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Image</th>
                        <th scope="col">Approve</th>
                    </tr>
                </thead>
                <tbody>';
        $mentors = DB::query('
        Select fname, lname, email, img, a.uid
        from users a, mentors b
        where a.uid = b.uid
        AND b.verified = 0
        LIMIT 5
        ');
        for ($i = 0; $i < count($mentors); $i++) {
            echo '
            <tr>
                <td>' . $mentors[$i]['fname'] . ' ' . $mentors[$i]['lname'] . '</td>
                <td>' . $mentors[$i]['email'] . '</td>
                <td><a href="dashboard.php?i=' . $mentors[$i]['img'] . '">View Image</a></td>
                <td><a href="dashboard.php?approve=' . $mentors[$i]['uid'] . '">Approve</a></td>
            </tr>
            ';
            # code...
        }

        echo '
                </tbody>
            </table>
            <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="dashboard.php?a=2">
                View All
            </a>
                </div>
            </div>
        </div>
        <br />
        <div class="mx-auto col-sm-12 col-md-10 col-lg-5 text-center mx-auto">
                    <div class="row">
                        <div class="col-10 offset-1 col-lg-8 offset-lg-2 my-4">
                            <h4 class="my-2 text-center" style="font-weight:600;">Import Userbase</h4>
                            <form method="post" action="" name="fileimport" id="import" enctype="multipart/form-data">
                                <div class="form-group my-3 py-1">
                                    <div class="input-group mb-3">
                                        <div class="file-upload text-center mx-auto">
                                            <input class="file-upload__input" type="file" name="filea" id="file"
                                                required>
                                            <button class="tbutton file-upload__button" type="button"><span
                                                    class="file-input-text">File</span></button>
                                            <span class="file-upload__label" style="color:red;"></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="oadd"
                                    form="import">
                                    <i class="fas fa-upload fa-lg formicon"></i>
                                    Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
    </div>';
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
            const defaultLabelText = "Select File";

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