<?php
// Establish database connection
$serverName = "sql213.infinityfree.com";
$userName = "if0_36532265";
$password = "IjTdJwffSo";

$conn = mysqli_connect($serverName, $userName, $password);
if ($conn) {
    // echo "Connection successful <br>";
} else {
    // echo "Failed to connect" . mysqli_connect_errno();
}

// Create database if not exists
$createDatabase = "CREATE DATABASE IF NOT EXISTS if0_36532265_arpan";
if (mysqli_query($conn, $createDatabase)) {
    // echo "Database created or already exists <br>";
} else {
    // echo "Failed to create database <br>" . mysqli_connect_errno();
}

// Select the weather database
mysqli_select_db($conn, 'if0_36532265_arpan');

// Create weather table if not exists with column Temperature, Wind Speed, Humidity, Pressure, timezone, and lastUpdated
$createTable = "CREATE TABLE IF NOT EXISTS weather (
       city VARCHAR(200) NOT NULL,
       temperature FLOAT NOT NULL,
       humidity FLOAT NOT NULL,
       weatherDescription VARCHAR(255) NOT NULL,
       wind FLOAT NOT NULL,
       pressure FLOAT NOT NULL,
       timezone INT NOT NULL,
       lastUpdated INT NOT NULL
);";

if (mysqli_query($conn, $createTable)) {
    // echo "Table created or already exists <br>";
} else {
    // echo "Failed to create table <br>" . mysqli_connect_errno();
}

if (isset($_GET['q'])) {
    $cityName = $_GET['q'];
    // echo $cityName;
} else {
    $cityName = "Preston";
}

$selectAllData = "SELECT * FROM weather WHERE city = '$cityName'";
$result = mysqli_query($conn, $selectAllData);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $currentTime = time();
    $lastUpdated = $row['lastUpdated']; // Fixed variable name
    $timeDifference = $currentTime - $lastUpdated;
    if ($timeDifference > 7200) {
        $apiKey = "fd7485def60c303e5aec0cf8880bcbcc";   
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . $cityName . "&units=metric&appid=" . $apiKey;
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $humidity = $data['main']['humidity'];
        $wind = $data['wind']['speed'];
        $pressure = $data['main']['pressure'];
        $temperature = $data['main']['temp']; // Changed to match table column
        $weatherDescription = $data['weather'][0]['description'];
        $timezone = $data['timezone'];
        $currentTime = time();

        $updateData = "UPDATE weather SET humidity='$humidity', wind='$wind', pressure='$pressure', temperature='$temperature', weatherDescription='$weatherDescription',
        timezone='$timezone', lastUpdated='$currentTime' WHERE city='$cityName'";
        if (mysqli_query($conn, $updateData)) {
            // echo "Data updated Successfully";
        } else {
            // echo "Failed to update data" . mysqli_error($conn);
        }
    }
} else {
    $apiKey = "fd7485def60c303e5aec0cf8880bcbcc";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . $cityName . "&units=metric&appid=" . $apiKey;
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $humidity = $data['main']['humidity'];
    $wind = $data['wind']['speed'];
    $pressure = $data['main']['pressure'];
    $temperature = $data['main']['temp'];
    $weatherDescription = $data['weather'][0]['description'];
    $timezone = $data['timezone'];
    $currentTime = time();

    $insertData = "INSERT INTO weather (city, temperature, humidity, wind, pressure, weatherDescription, timezone, lastUpdated)
    VALUES ('$cityName', '$temperature', '$humidity', '$wind', '$pressure', '$weatherDescription', '$timezone', '$currentTime')";

    if (mysqli_query($conn, $insertData)) {
        // echo "Data inserted Successfully";
    } else {
        // echo "Failed to insert data" . mysqli_error($conn);
    }
}

// Fetching data from weather table based on city name again after insertion
$result = mysqli_query($conn, $selectAllData);
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// Encoding fetched data to JSON and sending as response
$json_data = json_encode($rows);
header('Content-Type: application/json');
echo $json_data;
?>