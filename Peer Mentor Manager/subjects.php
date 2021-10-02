<?php
require('connect.php');
session_start();
if (!$_SESSION['fname']) {
    header('Location: login.php');
} elseif ($_SESSION['utype'] == 'a') {
    header('Location: index.php');
}


if (isset($_GET['r'])) {
    $id = $_GET['r'];
    $check = DB::queryFirstRow('Select subjectname, ylv from subjects where subjectid = %s', $id);
    if ($check) {
        echo '
            <div class="modal" tabindex="-1" role="dialog" id="myModal">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content text-center">
                        <div class="modal-header text-center">
                            <div class="col-12">
                                <h3 class="text-center modal-title col-12 heading">Remove Subject</h3>
                            </div>
                        </div>
                            <div class="modal-body">
                                <div class="col-12 text-center">';
        if ($_SESSION['utype'] == 's') {
            echo '<p>' . $check['subjectname'] . ' has been removed from your subject list</p>';
        } else {
            echo '<p>Year ' . $check['ylv'] . ' ' . $check['subjectname'] . ' has been removed from your subject list</p>';
        }
        DB::delete('usersubjects', 'uid=%s AND subjectid=%s', $_SESSION['uid'], $id);
        echo '
                                </div>
                                <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="subjects.php">
                                    <i class="fas fa-times"></i>Close
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    } else {
        header('Location:subjects.php');
    }
}

if (isset($_GET['a'])) {
    echo
    '
            <div class="modal" tabindex="-1" role="dialog" id="myModal">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content text-center">
                        <div class="modal-header text-center">
                            <div class="col-12">
                                <h3 class="text-center modal-title col-12 heading">Add Subject</h3>
                            </div>
                        </div>
                            <div class="modal-body">
                                <div class="col-12 col-sm-10 col-md-6 align-items-center mx-auto text-center">
                                <form method="post" action="subjects.php?b=1" name="subadd" id="subadd">
                                    <div class="form-group my-3 py-1">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                <i class="fas fa-graduation-cap fa-2x"></i></span>
                                            </div>
                                        <input type="text" autocomplete="false" class="form-control" placeholder="Subject:" aria-label="Subject" required name="subjectname" list="cats" /><datalist id="cats">
';
    if ($_SESSION['utype'] == 's') {
        $cats = DB::query('Select distinct subjectname from subjects where ylv = %s', $_SESSION['yr']);
    } else {
        $cats = DB::query('Select distinct subjectname from subjects');
    }
    foreach ($cats as $cat) {
        echo '<option value="' . $cat['subjectname'] . '">' . $cat['subjectname'] . '</option>';
    }
    echo
    '
                                        </datalist>
                                        </div>
                                    </div>';

    if ($_SESSION['utype'] == 'm') {
        echo '
    
                                    
                                    <div class="form-group my-3 py-1">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                <i class="fas fa-clock fa-2x"></i></span>
                                            </div>
                                        <input type="text" autocomplete="false" class="form-control" placeholder="Year Level:" aria-label="Year Level" required name="ylv" list="levels" /><datalist id="levels">
';
        $levels = DB::query('Select distinct ylv from subjects');
        foreach ($levels as $level) {
            echo '<option value="' . $level['ylv'] . '">' . $level['ylv'] . '</option>';
        }
        echo '
                                        </datalist>
                                        </div>
                                    </div>';
    }
    echo '
                                <button type="submit" class="tbutton mx-2" style="color: #fff;" name="subadd" form="subadd">
                                    <i class="fas fa-plus fa-lg formicon"></i>Add Subject
                                </button>
                                </form>
                                </div>
                        <a class="tbutton mx-2 mt-3" style="color:#fff;display:inline-block;" href="subjects.php">
                            <i class="fas fa-times"></i>Close
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
        ';
}

if (isset($_GET['b'])) {
    if ($_SESSION['utype'] == 's') {
        $subid = DB::queryFirstField('Select subjectid from subjects where subjectname = %s AND ylv = %s', $_POST['subjectname'], $_SESSION['yr']);
    } else {
        $subid = DB::queryFirstField('Select subjectid from subjects where subjectname = %s AND ylv = %s', $_POST['subjectname'], $_POST['ylv']);
    }
    DB::insert(
        'usersubjects',
        [
            'uid' => $_SESSION['uid'],
            'subjectid' => $subid
        ]

    );
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Terrace Mentor Manager - Subjects</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="calendar.php">My Calendar</a>
                    </li>
                    <?php
                    if ($_SESSION['utype'] != 'a') {
                        echo '
                      <li class="nav-item active">
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
        <h1 class="text-center heading my-4">Manage Subjects</h1>
        <div class="row">
            <div class="col-8 offset-2 text-center pb-4">
                <?php
                $subjects = DB::query('Select subjectname, ylv, u.subjectid from subjects s, usersubjects u where s.subjectid = u.subjectid AND u.uid = %s', $_SESSION['uid']);

                if ($subjects) {
                    if ($_SESSION['utype'] == 's') {
                        echo '
                            <div class="container">
                            <div class="table-responsive table-striped">
                                <table class="table ver1">
                                    <thead>
                                        <tr>
                                            <th scope="col">Subject Name</th>
                                            <th scope="col">Modify</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        ';
                        for ($j = 0; $j < count($subjects); $j++) {
                            echo '
                                <tr>
                                <td>' . $subjects[$j]['subjectname'] . '</td>
                                <td><a href="subjects.php?r=' . $subjects[$j]['subjectid'] . '">Remove</a></td>
                                </tr>
                            ';
                        }
                        echo '
                            </tbody>
                            </table>
                            </div>
                            </div>
                        ';
                    } else {
                        echo '
                            <div class="container">
                            <div class="table-responsive table-striped">
                                <table class="table ver1">
                                    <thead>
                                        <tr>
                                            <th scope="col">Subject Name</th>
                                            <th scope="col">Year Level</th>
                                            <th scope="col">Modify</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        ';
                        for ($j = 0; $j < count($subjects); $j++) {
                            echo '
                                <tr>
                                <td>' . $subjects[$j]['subjectname'] . '</td>
                                <td>' . $subjects[$j]['ylv'] . '</td>
                                <td><a href="subjects.php?r=' . $subjects[$j]['subjectid'] . '">Remove</a></td>
                                </tr>
                            ';
                        }
                        echo '
                            </tbody>
                            </table>
                            </div>
                            </div>
                        ';
                    }
                } else {
                    echo 'Your subject list is currently empty';
                }
                ?>
                <br />
                <br />
                <a class="tbutton mt-3 addb" style="color: #fff" href="subjects.php?a=1">Add Subject</a>
            </div>
        </div>
    </div>

    <?php

    if ((isset($_GET['r'])) || (isset($_GET['a']))) {
        echo '
      <script type="text/JavaScript">
         $(document).ready(function() {
         $("#myModal").modal("show");
         });
      </script>
      ';
    }
    ?>


</body>

</html>