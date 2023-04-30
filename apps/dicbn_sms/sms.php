<?php
/**
 *   (C) Copyright 1997-2013 hSenid International (pvt) Limited.
 *   All Rights Reserved.
 *
 *   These materials are unpublished, proprietary, confidential source code of
 *   hSenid International (pvt) Limited and constitute a TRADE SECRET of hSenid
 *   International (pvt) Limited.
 *
 *   hSenid International (pvt) Limited retains all title to and intellectual
 *   property rights in these materials.
 */

require_once __DIR__.'/../lib/sms/SmsReceiver.php';
require_once __DIR__.'/../lib/sms/SmsSender.php';
require_once __DIR__.'/../log.php';

ini_set('error_log', 'sms-app-error.log');

try {
    $receiver = new SmsReceiver(); // Create the Receiver object

    $content = $receiver->getMessage(); // get the message content
    $address = $receiver->getAddress(); // get the sender's address
    $requestId = $receiver->getRequestID(); // get the request ID
    $applicationId = $receiver->getApplicationId(); // get application ID
    $encoding = $receiver->getEncoding(); // get the encoding value
    $version = $receiver->getVersion(); // get the version

    logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version ]");

    $responseMsg;

    //your logic goes here......
    $split = explode(' ', $content);
    // $responseMsg = bmiLogicHere($split);

    // Create the sender object server url
    $sender = new SmsSender("https://developer.bdapps.com/sms/send");


    // store to sql

    // Database connection
    $host = 'localhost';
    $db   = 'aisfamil_bdapps';
    $user = 'aisfamil_myweb';
    $pass = 'KMrNqqLyobCd';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS messages_o (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content TEXT NOT NULL,
        address VARCHAR(255) NOT NULL,
        request_id VARCHAR(255) NOT NULL,
        application_id VARCHAR(255) NOT NULL,
        encoding VARCHAR(255) NOT NULL,
        version VARCHAR(255) NOT NULL
    )";
    $pdo->exec($sql);

    // Insert data
    $stmt = $pdo->prepare("INSERT INTO messages_o (content, address, request_id, application_id, encoding, version) VALUES (:content, :address, :request_id, :application_id, :encoding, :version)");
    $stmt->execute([
        'content' => $content,
        'address' => $address,
        'request_id' => $requestId,
        'application_id' => $applicationId,
        'encoding' => $encoding,
        'version' => $version
    ]);


    //sending a one message

 	$applicationId = $applicationId;
 	$encoding = $encoding;
 	$version =  $version;
    $password = "aa80752ee1226b2fed96d1f1614bebb3";
    $sourceAddress = "21213_dicbn";
    $deliveryStatusRequest = "1";
    $charging_amount = "2.00";
    $destinationAddresses = array($address);
    $binary_header = "";
    $responseMsg = "Hello, Your query is ".$content;
    $res = $sender->sms($responseMsg, $destinationAddresses, $password, $applicationId, $sourceAddress, $deliveryStatusRequest, $charging_amount, $encoding, $version, $binary_header);

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS messages_t (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_id VARCHAR(255) NOT NULL,
        encoding VARCHAR(255) NOT NULL,
        version VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        source_address VARCHAR(255) NOT NULL,
        delivery_status_request VARCHAR(255) NOT NULL,
        charging_amount VARCHAR(255) NOT NULL,
        destination_addresses TEXT NOT NULL,
        binary_header TEXT NOT NULL,
        response_msg TEXT NOT NULL
    )";
    $pdo->exec($sql);

    // Insert data
    $stmt = $pdo->prepare("INSERT INTO messages_t (application_id, encoding, version, password, source_address, delivery_status_request, charging_amount, destination_addresses, binary_header, response_msg) VALUES (:application_id, :encoding, :version, :password, :source_address, :delivery_status_request, :charging_amount, :destination_addresses, :binary_header, :response_msg)");
    $stmt->execute([
        'application_id' => $applicationId,
        'encoding' => $encoding,
        'version' => $version,
        'password' => $password,
        'source_address' => $sourceAddress,
        'delivery_status_request' => $deliveryStatusRequest,
        'charging_amount' => $charging_amount,
        'destination_addresses' => json_encode($destinationAddresses),
        'binary_header' => $binary_header,
        'response_msg' => $responseMsg
    ]);

} catch (SmsException $ex) {
    //throws when failed sending or receiving the sms
    error_log("ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
}

// /*
//     BMI logic function
// **/
// function bmiLogicHere($split)
// {
//     if (sizeof($split) < 2) {
//         $responseMsg = "Invalid message content";
//     } else {
//         $weight = (float)$split[0];
//         $height = (float)$split[1];

//         $bmi = getBMIValue($weight, ($height / 100));
//         $category = getCategory($bmi);

//         $responseMsg = "Your BMI :" . round($bmi, 2) . ", Category :" . $category;
//     }
//     return $responseMsg;
// }

// /*
//     Get BMI value
// **/

// function getBMIValue($weight, $height)
// {
//     return ($weight / pow($height, 2));
// }

// /*
//     Get category according to BMI value
// **/

// function getCategory($bmiValue)
// {
//     if ($bmiValue < 18.5) {
//         return "Underweight";
//     } else if ($bmiValue >= 18.5 && $bmiValue < 24.9) {
//         return "Normal Weight";
//     } else if ($bmiValue >= 25 && $bmiValue < 29.9) {
//         return "Overweight";
//     } else {
//         return "Obesity";
//     }
// }

?>
