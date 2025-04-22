<?php
require 'connection.php';

header('Content-Type: application/json');
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST values
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $facultyId = $_POST["facultyId"];
    $department = $_POST["department"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if faculty ID already exists
    $checkFacultySQL = "SELECT * FROM Faculty WHERE faculty_id = :faculty_id";
    $checkFacultyStmt = oci_parse($conn, $checkFacultySQL);
    oci_bind_by_name($checkFacultyStmt, ":faculty_id", $facultyId);
    oci_execute($checkFacultyStmt);

    if ($row = oci_fetch_assoc($checkFacultyStmt)) {
        $response = [
            "status" => "error",
            "title" => "Registration Failed",
            "message" => "Faculty ID already registered."
        ];
    } else {
        // Get new users_id
        $getIdSQL = "SELECT users_seq.NEXTVAL as new_id FROM dual";
        $getIdStmt = oci_parse($conn, $getIdSQL);
        oci_execute($getIdStmt);
        $row = oci_fetch_assoc($getIdStmt);
        $usersId = $row['NEW_ID'];

        // Insert into Users
        $userSQL = "INSERT INTO Users (users_id, first_name, last_name, password)
                    VALUES (:users_id, :first_name, :last_name, :password)";
        $userStmt = oci_parse($conn, $userSQL);
        oci_bind_by_name($userStmt, ":users_id", $usersId);
        oci_bind_by_name($userStmt, ":first_name", $firstName);
        oci_bind_by_name($userStmt, ":last_name", $lastName);
        oci_bind_by_name($userStmt, ":password", $password);

        $userExec = oci_execute($userStmt);
        if ($userExec) {
            // Insert into Faculty
            $facultySQL = "INSERT INTO Faculty (users_id, faculty_id, department)
                           VALUES (:users_id, :faculty_id, :department)";
            $facultyStmt = oci_parse($conn, $facultySQL);
            oci_bind_by_name($facultyStmt, ":users_id", $usersId);
            oci_bind_by_name($facultyStmt, ":faculty_id", $facultyId);
            oci_bind_by_name($facultyStmt, ":department", $department);

            $facultyExec = oci_execute($facultyStmt);
            if ($facultyExec) {
                $response = [
                    "status" => "success",
                    "title" => "Registration Successful!",
                    "message" => "Redirecting to login page..."
                ];
            } else {
                // Rollback Users entry if Faculty insert fails
                $deleteSQL = "DELETE FROM Users WHERE users_id = :users_id";
                $deleteStmt = oci_parse($conn, $deleteSQL);
                oci_bind_by_name($deleteStmt, ":users_id", $usersId);
                oci_execute($deleteStmt);

                $e = oci_error($facultyStmt);
                $response = [
                    "status" => "error",
                    "title" => "Registration Failed",
                    "message" => "Error inserting into Faculty table: " . $e['message']
                ];
            }

            oci_free_statement($facultyStmt);
        } else {
            $e = oci_error($userStmt);
            $response = [
                "status" => "error",
                "title" => "Registration Failed",
                "message" => "Error inserting into Users table: " . $e['message']
            ];
        }

        oci_free_statement($getIdStmt);
        oci_free_statement($userStmt);
    }

    oci_free_statement($checkFacultyStmt);
    oci_close($conn);

    echo json_encode($response);
}
?>
