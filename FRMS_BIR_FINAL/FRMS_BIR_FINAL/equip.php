<?php

include('authenticate/authenticate.php');
require_once('connection/connect.php');
$file = basename($_SERVER['PHP_SELF'], ".php");
//$month_now = getdate()['mon'];
//$date_now = getdate()['date'];
//echo $today;
//init
$html = "";
$query = "";
$content = "";
$desc = "";
$sernum = "";
$number = "";
$avail = "";
$result = "";
$error = "";
$success = "";
$qty = "";
$exist = "";

if (isset($_GET['btnSearch']) && isset($_GET['txtSearch'])) {
    if (isset($_GET['btnReserve'])) {
        
    }
    if (!isset($_GET['btnReserve'])) {
        $query = "SELECT * FROM equip WHERE eq_desc LIKE '%{$_GET['txtSearch']}%' OR eq_serial_num LIKE '%{$_GET['txtSearch']}%'";
        $result = mysql_query($query);
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                $html .= "<tr>";
                $html .= "<td>{$rs['eq_itemnumber']}</td>";
                $html .= "<td>{$rs['eq_desc']}</td>";
                $html .= "<td>{$rs['eq_serial_num']}</td>";
                $html .= "<td>{$rs['eq_avail']}</td>";
                $html .= "<td><a href=\"equip.php?item={$rs['eq_itemnumber']}\">Reserve</a></td>";
                $html .= "</tr>";
            }
            $exist = "";
        } else {
            $exist = "The item is not existed.";
            $exist = "<h4>{$exist}</h4>";
            $exist = "<div class=\"alert alert-block\">{$exist}</div>";
        }
        $content = "templates/tplEquip.php";
    }
}
if (!isset($_GET['btnSearch'])) {

    if (isset($_GET['item'])) {
        $query = "SELECT * FROM equip WHERE eq_itemnumber = {$_GET['item']}";
        $result = mysql_query($query);
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                $number = $rs['eq_itemnumber'];
                $desc = $rs['eq_desc'];
                $sernum = $rs['eq_serial_num'];
                $avail = $rs['eq_avail'];
            }
        }
        $content = "templates/tplReserve.php";
    }
    if (!isset($_GET['item'])) {
        if (isset($_GET['btnReserve'])) {
            $query = "SELECT * FROM equip WHERE eq_itemnumber = {$_GET['txtItem']}";
            $result = mysql_query($query) or die(mysql_error());
            if (mysql_num_rows($result) > 0) {
                while ($rs = mysql_fetch_array($result)) {
                    $number = $rs['eq_itemnumber'];
                    $desc = $rs['eq_desc'];
                    $sernum = $rs['eq_serial_num'];
                    $avail = $rs['eq_avail'];
                }
            }
            if (is_numeric($_GET['txtQty'])) { //txtQuantity is a number
                if (0 < $_GET['txtQty'] && $_GET['txtQty'] <= (int) $_GET['txtAvail']) { //0 < x <= avail
                    if ($_GET['txtQty'] - (int) $_GET['txtQty'] == 0) {//Check if it is integer
                        //International standard date format
                        $now = date('Y-m-d', time());

                        //MySQL Standard date format
                        $year = date('Y', time());
                        $month = sprintf("%02d", (int) date('m', time()) - 1);
                        $date = date('d', time());
                        $now_sql = "{$year}-{$month}-{$date}";

                        //SET TRANSACTION (MySQL)
                        $query = "INSERT INTO transaction(emp_num, itemnumber, qty, datereserved)
            VALUES ({$_COOKIE['emp_num']}, {$_GET['txtItem']},
                {$_GET['txtQty']}, '{$now_sql}')";
                        $result = mysql_query($query) or die(mysql_error());
                        //SET TRANSACTION REPORTS (MySQL)
                        $query = "INSERT INTO transactionreports
            (emp_num, itemnumber, qty, datereserved, date)
            VALUES
            ({$_COOKIE['emp_num']}, {$_GET['txtItem']}, {$_GET['txtQty']},  
             '{$now_sql}', '{$now_sql}')";

                        $result = mysql_query($query) or die(mysql_error());
                        //SET BOOK (MySQL)
                        $query = "UPDATE equip
            SET eq_reserved = (eq_reserved + {$_GET['txtQty']}),
             eq_avail = (eq_avail - {$_GET['txtQty']})
            WHERE eq_itemnumber = {$_GET['txtItem']}";
                        $result = mysql_query($query) or die(mysql_error());
                        $success = "<div class=\"alert alert-success\"><p class=\"text-center\">The items {$_GET['txtItem']} is reserved.</p></div>";
                        $content = "templates/tplEquip.php";
                    } else {
                        $qty = $_GET['txtQty'];
                        $error = "<div class=\"alert alert-error\"><p class=\"text-center\">Check the quantity.</p></div>";
                        $content = "templates/tplReserve.php";
                    }
                } else {
                    $qty = $_GET['txtQty'];
                    $error = "<div class=\"alert alert-error\"><p class=\"text-center\">Check the available.</p></div>";
                    $content = "templates/tplReserve.php";
                }
            } else { //txtQuantity is not a number.
                $qty = $_GET['txtQty'];
                $error = "<div class=\"alert alert-error\"><p class=\"text-center\">Please enter numeric value.</p></div>";
                $content = "templates/tplReserve.php";
            }
        }
        if (!isset($_GET['btnReserve'])) {
            $content = "templates/tplEquip.php";
        }
    }
}

$navbar = 'templates/tplNavBar.php';
include('templates/tplMaster.php');
?>