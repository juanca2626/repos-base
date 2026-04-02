<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 6/06/2019
 * Time: 17:51
 */
$endPoint = "https://test.api.amadeus.com";
$uri = $endPoint . "/v1/security/oauth2/token";
$client_id = '6W3myJL31xoj5TsuscyY7emxgLfWTAJG';
$client_secret = 'SsT5nyKiPzD06Vy9';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id=' . $client_id . '&client_secret=' . $client_secret . '&grant_type=client_credentials');
$string = curl_exec($ch);
curl_close($ch);

$auth = json_decode($string);
echo $uri . "/v1/security/oauth2/token" . "<br/>";
echo "POST<br/>";
echo "Header: 'Content-Type: application/x-www-form-urlencoded'<br/>";
echo "Body: 'client_id=6W3myJL31xoj5TsuscyY7emxgLfWTAJG&client_secret=SsT5nyKiPzD06Vy9&grant_type=client_credentials'<br/>";
echo "<pre>" . json_encode($auth, JSON_PRETTY_PRINT) . "</pre>";

$palabreClave = 'lima'; // obligatorio
$uri = $endPoint . "/v1/reference-data/locations?subType=CITY&keyword=" . $palabreClave . "&page[limit]=5";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $auth->access_token));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id=6W3myJL31xoj5TsuscyY7emxgLfWTAJG&client_secret=SsT5nyKiPzD06Vy9&grant_type=client_credentials');
$string = curl_exec($ch);
curl_close($ch);

$json = json_decode($string);
echo $uri . "<br/>";
echo "GET<br/>";
echo "Header: 'Authorization: Bearer " . $auth->access_token . "'<br/>";
echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";


