<?php

require_once("../../database/databaseLogin.php");
session_start();
$customer_id = $_SESSION['customer_id'];

try {
    $pdo = new PDO($attr, $user, $pass, $opts);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$rental_date = filter_input(INPUT_POST, 'rentaldate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$return_date = filter_input(INPUT_POST, 'returndate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$vehicle_reg_number = filter_input(INPUT_POST, 'rnumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);



$rentalDateTime = new DateTime($rental_date);
$returnDateTime = new DateTime($return_date);

$interval = $rentalDateTime->diff($returnDateTime);
$days = $interval->days;

$total_KM = $days*100;


try {

    //inserting the rental information
    $query = "INSERT INTO `rental`(`Rental_date`,`Return_date`,`Customer_ID`,`vehicle_registration_number`,`Rental_status`,`Total_KM`) 
    VALUES(:rentaldate,:returndate,:cusID,:vregnumber,'Ongoing',:totKM);";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':cusID', $customer_id);
    $stmt->bindParam(':rentaldate', $rental_date);
    $stmt->bindParam(':returndate', $return_date);
    $stmt->bindParam(':vregnumber', $vehicle_reg_number);
    $stmt->bindParam(':totKM', $total_KM);

    $stmt->execute();

    //quering the ID of the rental
    $query2 = "SELECT * FROM `rental` WHERE `Rental_ID` = LAST_INSERT_ID();";
    $stmt2 = $pdo->query($query2);
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);

    $rental_ID = $result["Rental_ID"];

    //creating a notification
    $query3 = "INSERT INTO `notification`(`Title`,`Time`,`Customer_ID`,`Rental_ID`,`vehicle_Registration_number`) 
    VALUES ('New Booking!', NOW(), :cusID, :rentalID, :vregnumber);";
    $stmt3 = $pdo->prepare($query3);

    $stmt3->bindParam(':cusID', $customer_id);
    $stmt3->bindParam(':rentalID', $rental_ID );
    $stmt3->bindParam(':vregnumber', $vehicle_reg_number);

    $stmt3->execute();

} catch (PDOException $e) {

    echo "Error: " . $e->getMessage();
}
