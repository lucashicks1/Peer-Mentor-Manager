<?php
session_start();
require('connect.php');

if (isset($_SESSION['utype'])) {
    if (isset($_GET['a'])) {
        // afd
        // TODO
        if ($_SESSION['utype'] == 's') {
            $info = DB::query('
            Select *
            from sessions a, subjects b
            where b.subjectname = %s
            AND b.ylv = %s
            AND a.subid = b.subjectid
            AND a.mentor IS NOT NULL
            AND a.sessionid IN (
                select sessionid
                from (Select a.sessionid, slimit
                from sessions a LEFT JOIN studentsessions b ON 
                a.sessionid = b.sessionid
                GROUP BY a.sessionid
                HAVING COUNT(*) <= a.slimit) as c
            )
            AND a.sessionid NOT IN (
                select sessionid
                from studentsessions
                where studentid = %s
            )
            AND sdate >= CAST( NOW() AS Date )
            ', $_GET['a'], $_SESSION['yr'], $_SESSION['uid']);
            // STUDENT
        } else {
            $info = DB::query('
            select *
            from sessions a, subjects b
            where subid IN (
                Select a.subjectid
                from usersubjects a, subjects b
                where a.subjectid = b.subjectid
                AND b.subjectname = %s
                AND uid = %s
            )
            AND mentor IS NULL
            AND a.subid = b.subjectid
            AND sdate >= CAST( NOW() AS Date )
            ', $_GET['a'], $_SESSION['uid']);
        }
        $eventarray = array();
        for ($i = 0; $i < count($info); $i++) {
            $date = $info[$i]['sdate'];
            $eventarray[$i]['title'] = $info[$i]['subjectname'] . " session";
            $eventarray[$i]['start'] = $date . 'T' . $info[$i]['stime'];
            $eventarray[$i]['end'] = $date . 'T' . $info[$i]['etime'];
            $eventarray[$i]['url'] = 'sessions.php?m=' . $info[$i]['sessionid'];
            if ($info[$i]['mentor']) {
                $eventarray[$i]['color'] = '#00ff67';
            } else {
                $eventarray[$i]['color'] = '#ff6700';
            }
        }
    } else {
        // asdfas
        require('connect.php');
        if ($_SESSION['utype'] == 's') {
            $info = DB::query('
                Select a.sessionid, a.sdate, a.stime, a.etime, a.mentor, c.subjectname
                from sessions a, studentsessions b, subjects c 
                where a.sessionid = b.sessionid
                AND c.subjectid = a.subid
                AND studentid = %s
                ', $_SESSION['uid']);
        } elseif ($_SESSION['utype'] == 'm') {
            $info = DB::query('
                select sessionid, sdate, stime, etime, mentor, subjectname
                from sessions, subjects
                where subjectid = subid
                AND mentor = %s
                    ', $_SESSION['uid']);
        } else {
            $info = DB::query('
                select sessionid, sdate, stime, etime, mentor, subjectname
                from sessions, subjects
                where subjectid = subid
                ');
        }
        $eventarray = array();
        for ($i = 0; $i < count($info); $i++) {
            $date = $info[$i]['sdate'];
            $eventarray[$i]['title'] = $info[$i]['subjectname'] . " session";
            $eventarray[$i]['start'] = $date . 'T' . $info[$i]['stime'];
            $eventarray[$i]['end'] = $date . 'T' . $info[$i]['etime'];
            $eventarray[$i]['url'] = 'calendar.php?a=' . $info[$i]['sessionid'];
            if ($info[$i]['mentor']) {
                $eventarray[$i]['color'] = '#00ff67';
            } else {
                $eventarray[$i]['color'] = '#ff6700';
            }
        }
    }
    echo json_encode($eventarray);
} else {
    header('location:index.php');
}