<?php
include('./config.php');

if (isset($_POST['firstname'])) {
    $success = False;
    while (!$success) {
        $confcode = rand(10000000,99999999);
        $query = "SELECT * FROM `registrations` WHERE `confcode`='$confcode' AND `confirmed`=0";
        $result = mysql_query($query,$cxn);
        if (!$result) {
            die('Sorry, we could not register you at this time, please try again later. Error 1');
        }
        if (mysql_num_rows($result) == 0) {
            $success = True;
        }
    }
    $query = sprintf("INSERT INTO `registrations` (`firstname`,`lastname`,`guid`,`confcode`,`confirmed`) VALUES ('%s','%s','%s','%s',0)",mysql_real_escape_string($_POST['firstname']),mysql_real_escape_string($_POST['lastname']),mysql_real_escape_string($_POST['guid']),$confcode);
    $result = mysql_query($query,$cxn);
    if ($result) {
        print("Please send the following message to the chatbot: \"REGISTER $confcode\"");
    } else {
        print('Sorry, we could not register you at this time, please try again later. Error 2');
    }
}
else if (isset($_POST['confirmcode'])) {
    $query = sprintf("SELECT * FROM `registrations` WHERE `confcode`='%s' AND `confirmed`=0",$_POST['confirmcode']);
    $result = mysql_query($query,$cxn);
    if ((!$result) || (mysql_num_rows($result) != 1)) {
        print('Sorry, we could not register you at this time, please try again later. Error 3');
    } else {
        $row = mysql_fetch_assoc($result);
        $query = sprintf("UPDATE `registrations` SET `confirmed`=1 WHERE `id` = %s",$row['id']);
        $result = mysql_query($query,$cxn);
        if (!$result) {
            print('Sorry, we could not register you at this time, please try again later. Error 4');
        } else {
            print($row['id']);
        }
    }
}
else {
?>
<html>
<head>
    <title>Please register to use the system.</title>
</head>
<body>
    <h1>Please register to use the system.</h1>
    <form action="register.php" method="post">
        <p><label for="firstname">First Name: </label><input type="text" name="firstname" id="firstname" /></p>
        <p><label for="lastname">Last Name: </label><input type="text" name="lastname" id="lastname" /></p>
        <p><label for="guid">GUID: </label><input type="text" name="guid" id="guid" /></p>
        <p><input type="submit" /></p>
    </form>
</body>
</html>
<?php
}
?>