<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start(); ?>
<!DOCTYPE html>
<html class="no-js" lang="en"/>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />
    <title>Google Contacts API</title>
</head>

<body>
<h2>Google Contacts API v3.0</h2>
<?php
require_once 'google-api-php-client/src/Google/autoload.php';
require 'google-api-php-client/src/Google/Config.php';
require 'google-api-php-client/src/Google/Client.php';

$client_id = '5854367626-bim2lqk87v7leunke4fklk7sh3kbcqos.apps.googleusercontent.com';
$client_secret = 'mXdwlFTX7tjFfRroQ9sRvNJD';
$redirect_uri = 'http://localhost/Import-Google-contacts-master/import-contacts-with-php.php';

$client = new Google_Client();
$client -> setApplicationName('My application');
$client -> setClientid($client_id);
$client -> setClientSecret($client_secret);
$client -> setScopes('https://www.google.com/m8/feeds');
$client -> setRedirectUri($redirect_uri);
$client -> setAccessType('online');

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    header('Location: ' . $redirect_uri);
}

if(!isset($_SESSION['token']))
{
    $url = $client->createAuthUrl($_SESSION['token']);
    echo '<a href="' . $url . '">Import Google Contacts</a>';
}else{
        $client->setAccessToken($_SESSION['token']);
        $token = json_decode($_SESSION['token']);
        $token->access_token;
        $curl = curl_init("https://www.google.com/m8/feeds/contacts/default/full?alt=json&max-results=1000&access_token=" . $token->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $contacts_json = curl_exec($curl);
        curl_close($curl);
        $contacts = json_decode($contacts_json, true);
        $return = array();
        foreach($contacts['feed']['entry'] as $contact){
            $return[] = array(
            'name' => $contact['title']['$t'],
            'email' => isset($contact['gd$email'][0]['address']) ? $contact['gd$email'][0]['address'] : false,
            'phone' => isset($contact['gd$phoneNumber'][0]['$t']) ? $contact['gd$phoneNumber'][0]['$t'] :false,
            );
        }
        echo "<pre>";
        var_dump($return);
        echo "</pre>";
    }       
?>

</body>
</html>