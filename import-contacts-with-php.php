<!DOCTYPE html>
<html lang="EN">
<head>
    <title>Google API import contacts example - www.design19.org/blog</title>
    <meta charset="UTF-8">
    <!-- All other meta tag here -->
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0" >
    <!-- CSS styles -->
    <link rel="stylesheet" href="bootstrap.min.css" type="text/css">
</head>
<body>
<?php
session_start();
//include google api library
require_once 'google-api-php-client/src/Google/autoload.php';
$google_client_id = '5854367626-bim2lqk87v7leunke4fklk7sh3kbcqos.apps.googleusercontent.com';
$google_client_secret = 'mXdwlFTX7tjFfRroQ9sRvNJD';
$google_redirect_uri = 'http://localhost/Import-Google-contacts-master/import-contacts-with-php.php';

//setup new google client
$client = new Google_Client();
$client -> setApplicationName('My application');
$client -> setClientid($google_client_id);
$client -> setClientSecret($google_client_secret);
$client -> setRedirectUri($google_redirect_uri);
$client -> setAccessType('online');
$client -> setScopes('https://www.google.com/m8/feeds');
$googleImportUrl = $client -> createAuthUrl();

//curl function
function curl($url, $post = "") {
    $curl = curl_init();
    $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
    curl_setopt($curl, CURLOPT_URL, $url);
    //The URL to fetch. This can also be set when initializing a session with curl_init().
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    //The number of seconds to wait while trying to connect.
    if ($post != "") {
        curl_setopt($curl, CURLOPT_POST, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
    //The contents of the "User-Agent: " header to be used in a HTTP request.
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    //To follow any "Location: " header that the server sends as part of the HTTP header.
    curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
    //To automatically set the Referer: field in requests where it follows a Location: redirect.
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    //The maximum number of seconds to allow cURL functions to execute.
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    //To stop cURL from verifying the peer's certificate.
    $contents = curl_exec($curl);
    curl_close($curl);
    return $contents;
}

//google response with contact. We set a session and redirect back
if (isset($_GET['code'])) {
    $auth_code = $_GET["code"];
    $_SESSION['google_code'] = $auth_code;
}

if(isset($_SESSION['google_code'])) {
    $auth_code = $_SESSION['google_code'];
    $max_results = 200;
    $fields=array(
        'code'=>  urlencode($auth_code),
        'client_id'=>  urlencode($google_client_id),
        'client_secret'=>  urlencode($google_client_secret),
        'redirect_uri'=>  urlencode($google_redirect_uri),
        'grant_type'=>  urlencode('authorization_code')
    );
    $post = '';
    foreach($fields as $key=>$value)
    {
        $post .= $key.'='.$value.'&';
    }
    $post = rtrim($post,'&');


    $result = curl('https://accounts.google.com/o/oauth2/token',$post);
    $response =  json_decode($result);
    $accesstoken = $response->access_token;
    $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&alt=json&v=3.0&oauth_token='.$accesstoken;
    $xmlresponse =  curl($url);
    $contacts = json_decode($xmlresponse,true);

    $return = array();
    if (!empty($contacts['feed']['entry'])) {
        foreach($contacts['feed']['entry'] as $contact) {

            //$contactidlink = explode('/',$contact['id']['$t']);
            //$contactId = end($contactidlink);

            //retrieve user photo
            if (isset($contact['link'][0]['href'])) {

                $url =   $contact['link'][0]['href'];

                $url = $url . '&access_token=' . urlencode($accesstoken);

                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_TIMEOUT, 15);
                curl_setopt($curl, CURLOPT_VERBOSE, true);

                //$image = curl_exec($curl);
                curl_close($curl);

            }

            //retrieve Name + email and store into array
            $return[] = array (
                'name'=> $contact['title']['$t'],
                'email' =>isset($contact['gd$email'][0]['address']) ? $contact['gd$email'][0]['address'] : false,
                'phone' => isset($contact['gd$phoneNumber'][0]['$t']) ? $contact['gd$phoneNumber'][0]['$t'] :false

                //'image' => $image
            );
        }
    }

    $google_contacts = $return;

    unset($_SESSION['google_code']);

    //Now that we have the google contacts stored in an array, display all in a table
    if(!empty($google_contacts)) {
        echo '<div class="container">';
        echo "<strong>Here is your contact list. Good luck</strong><br><br>";
        echo '<table class="table table-striped">';

        foreach ($google_contacts as $contact) {
            echo '<tr>';
            echo '<td>'.$contact['name'].'</td>';

            echo '<td>'.$contact['email'].'</td>';
            echo '<td>'.$contact['phone'].'</td>';
            /*if(!empty($contact['image']) and $contact['image']!='Photo not found') :
            ?>
                <td><img src="data:image/jpeg;base64,<?php echo base64_encode( $contact['image'] ); ?>" /></td>
            <?php
            else:
                echo '<td></td>';
            endif;*/


            echo '<td><a href=import-contacts-with-php.php?cno='.$contact['phone'].'>Edit</a></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
}
?>
<div class="container">
    <div class="row">
        <br><br><br>
        <div class="col-lg-12">
            <?php
            if(isset($_GET['cno']))
            {
                $cno=$_GET['cno'];
                echo "your Contact No. is ".$cno;
                echo "<input type='text' name='txtcno' value='$cno'>";

            } ?>
            <!--This page is a practical example on how to import google contacts. This is related with the folowing article
            <br>
            <a href="https://www.design19.org/blog/import-google-contacts-with-php-or-javascript-using-google-contacts-api-and-oauth-2-0/" target="_blank">
                Import Google contacts with PHP or Javascript using Google Contacts API and OAUTH 2.0
            </a>-->
            <br>
            <br>
            <a class="btn btn-primary" href="<?php echo $googleImportUrl; ?>" role="button">Import google contacts</a>

        </div>
    </div>
</div>

<!-- Google CDN's jQuery -->
<script src="jquery.min.js"></script>

</body>
</html>