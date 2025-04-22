<?php
require 'connection.php';

header('Content-Type: application/json');
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $facultyId = $_POST['facultyId'];
    $password = $_POST['password'];

    // SQL to get password from Users via Faculty
    $sql = "SELECT u.password
            FROM Faculty f
            JOIN Users u ON f.users_id = u.users_id
            WHERE f.faculty_id = :faculty_id";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":faculty_id", $facultyId);
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
            "message" => "Invalid faculty ID or password."
        ];
    }

    oci_free_statement($stmt);
    oci_close($conn);

    echo json_encode($response);
}
?>
