<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Appointments</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if (isset($_SESSION["user"])) {
        if ($_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    // Import database
    include("../connection.php");

    $userrow = $database->prepare("SELECT * FROM doctor WHERE docemail = ?");
    $userrow->bind_param("s", $useremail);
    $userrow->execute();
    $result = $userrow->get_result();
    $userfetch = $result->fetch_assoc();
    $userid = $userfetch["docid"];
    $username = $userfetch["docname"];
    ?>

    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username, 0, 13); ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail, 0, 22); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu">
                            <div><p class="menu-text">Dashboard</p></div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment menu-active menu-icon-appoinment-active">
                        <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active">
                            <div><p class="menu-text">My Appointments</p></div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu">
                            <div><p class="menu-text">My Sessions</p></div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu">
                            <div><p class="menu-text">My Patients</p></div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu">
                            <div><p class="menu-text">Settings</p></div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style="border-spacing: 0; margin: 0; padding: 0; margin-top: 25px;">
                <tr>
                    <td width="13%">
                        <a href="appointment.php">
                            <button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top: 11px; padding-bottom: 11px; margin-left: 20px; width: 125px">
                                <font class="tn-in-text">Back</font>
                            </button>
                        </a>
                    </td>
                    <td>
                        <p style="font-size: 23px; padding-left: 12px; font-weight: 600;">Appointment Manager</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px; color: rgb(119, 119, 119); padding: 0; margin: 0; text-align: right;">Today's Date</p>
                        <p class="heading-sub12" style="padding: 0; margin: 0;">
                            <?php
                            date_default_timezone_set('Asia/Kolkata');
                            $today = date('Y-m-d');
                            echo $today;

                            $list110 = $database->query("SELECT * FROM schedule INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid INNER JOIN patient ON patient.pid = appointment.pid INNER JOIN doctor ON schedule.docid = doctor.docid WHERE doctor.docid = $userid");
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex; justify-content: center; align-items: center;">
                            <img src="../img/calendar.svg" width="100%">
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top: 10px; width: 100%;">
                        <p class="heading-main12" style="margin-left: 45px; font-size: 18px; color: rgb(49, 49, 49)">My Appointments (<?php echo $list110->num_rows; ?>)</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top: 0px; width: 100%;">
                        <center>
                            <table class="filter-container" border="0">
                                <tr>
                                    <td width="10%"></td>
                                    <td width="5%" style="text-align: center;">Date:</td>
                                    <td width="30%">
                                        <form action="" method="post">
                                            <input type="date" name="sheduledate" id="date" class="input-text filter-container-items" style="margin: 0; width: 95%;">
                                    </td>
                                    <td width="12%">
                                        <input type="submit" name="filter" value=" Filter" class="btn-primary-soft btn button-icon btn-filter" style="padding: 15px; margin: 0; width: 100%">
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </td>
                </tr>

                <?php
                $sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, patient.pname, schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate FROM schedule INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid INNER JOIN patient ON patient.pid = appointment.pid INNER JOIN doctor ON schedule.docid = doctor.docid WHERE doctor.docid = $userid";

                if ($_POST) {
                    if (!empty($_POST["sheduledate"])) {
                        $sheduledate = $_POST["sheduledate"];
                        $sqlmain .= " AND schedule.scheduledate = ?";
                    }
                }
                ?>

                <tr>
                    <td colspan="4">
                        <center>
                            <div class="abc scroll">
                                <table width="93%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Patient name</th>
                                            <th class="table-headin">Appointment number</th>
                                            <th class="table-headin">Session Title</th>
                                            <th class="table-headin">Session Date & Time</th>
                                            <th class="table-headin">Appointment Date</th>
                                            <th class="table-headin">Events</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($_POST && !empty($_POST["sheduledate"])) {
                                            $stmt = $database->prepare($sqlmain);
                                            $stmt->bind_param("s", $sheduledate);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                        } else {
                                            $result = $database->query($sqlmain);
                                        }

                                        if ($result->num_rows == 0) {
                                            echo '<tr><td colspan="7"><br><br><br><br><center><img src="../img/notfound.svg" width="25%"><br><p class="heading-main12" style="font-size: 20px; color: rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p><a class="non-style-link" href="appointment.php"><button class="login-btn btn-primary-soft btn" style="display: flex; padding-left: 40px; padding-right: 40px; margin-left: 20px;">Show all Appointments</button></a></center><br><br><br><br></td></tr>';
                                        } else {
                                            while ($row = $result->fetch_assoc()) {
                                                $appoid = $row["appoid"];
                                                $scheduleid = $row["scheduleid"];
                                                $title = $row["title"];
                                                $docname = $row["docname"];
                                                $scheduledate = $row["scheduledate"];
                                                $scheduletime = $row["scheduletime"];
                                                $pname = $row["pname"];
                                                $apponum = $row["apponum"];
                                                $appodate = $row["appodate"];
                                                echo '<tr><td style="padding:20px;">' . substr($pname, 0, 25) . '</td><td style="padding:20px; font-size: 13px;">' . $apponum . '</td><td style="padding:20px;">' . substr($title, 0, 15) . '</td><td style="text-align: center; padding: 20px;">' . substr($scheduledate, 0, 10) . ' @' . substr($scheduletime, 0, 5) . '</td><td style="text-align: center;">' . $appodate . '</td><td><div style="display: flex; justify-content: center;"><a href="?action=view&id=' . $appoid . '" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-view" style="padding: 12px 40px 12px 40px;">View</button></a>&nbsp;&nbsp;&nbsp;</div></td></tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </center>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>