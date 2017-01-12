<html>
<head>
    <title>Insert Contacts in Google Using PHP</title>
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro|Open+Sans+Condensed:300|Raleway' rel='stylesheet' type='text/css'>
</head>
<body>
<div class="container-fluid">
    <h1>Insert Contacts in Google Using PHP</h1>
    <div id="login">
        <div id="h2" class="h2 row">
            <div class="col-md-12"><h2><span>Insert Contacts in Google</span></h2></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form action="http://localhost/Import-Google-contacts-master/import-contacts-with-php.php" method="get">
                <input type="text" placeholder="First Name" name="name"/><br><br>
                <input type="text" placeholder="Last Name" name="lname"/><br><br>
                <input type="email" placeholder="Contact Email" name="email"/><br><br>
                <input type="text" placeholder="Phone Number" name="phone"/><br><br>
                <input type="submit" value="Add Contact" name="submit"/>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
/**we are fetching the form data from browser using GET method**/
if (isset($_GET['submit'])) {
    $data['name'] = $_GET['name'];
    $data['lname'] = $_GET['lname'];
    $data['email'] = $_GET['email'];
    $data['email'] = $_GET['phone'];
}
?>