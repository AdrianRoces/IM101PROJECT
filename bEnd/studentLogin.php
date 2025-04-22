<?php
require 'connection.php';

header('Content-Type: application/json');
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentNumber = $_POST['studentNumber'];
    $password = $_POST['password'];

    // Query to find the student and join with the Users table
    $sql = "SELECT u.password
            FROM Students s
            JOIN Users u ON s.users_id = u.users_id
            WHERE s.student_number = :student_number";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":student_number", $studentNumber);
    oci_execute($stmt);

    $user = oci_fetch_assoc($stmt);

    if ($user && password_verify($password, $user['PASSWORD'])) {
        $response = [
            "status" => "success",
            "title" => "Login Successful!",
            "message" => "Redirecting to your dashboard..."
        ];
    } else {
        $response = [
            "status" => "error",
            "title" => "Login Failed",
            "message" => "Invalid student number or password."
        ];
    }

    oci_free_statement($stmt);
    oci_close($conn);

    echo json_encode($response);
}
?>
