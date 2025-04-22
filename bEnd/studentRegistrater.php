<?php
require 'connection.php';

header('Content-Type: application/json');
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST values
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $studentNumber = $_POST["studentNumber"];
    $course = $_POST["course"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if student number already exists in the Students table
    $checkStudentSQL = "SELECT * FROM Students WHERE student_number = :student_number";
    $checkStudentStmt = oci_parse($conn, $checkStudentSQL);
    oci_bind_by_name($checkStudentStmt, ":student_number", $studentNumber);
    oci_execute($checkStudentStmt);

    // If a record exists, return error response
    if ($row = oci_fetch_assoc($checkStudentStmt)) {
        $response = [
            "status" => "error",
            "title" => "Registration Failed",
            "message" => "Student number already registered."
        ];
    } else {
        // Generate new users_id using a sequence
        $getIdSQL = "SELECT users_seq.NEXTVAL as new_id FROM dual";
        $getIdStmt = oci_parse($conn, $getIdSQL);
        oci_execute($getIdStmt);
        $row = oci_fetch_assoc($getIdStmt);
        $usersId = $row['NEW_ID'];

        // Insert into Users table
        $userSQL = "INSERT INTO Users (users_id, first_name, last_name, password)
                    VALUES (:users_id, :first_name, :last_name, :password)";
        $userStmt = oci_parse($conn, $userSQL);
        oci_bind_by_name($userStmt, ":users_id", $usersId);
        oci_bind_by_name($userStmt, ":first_name", $firstName);
        oci_bind_by_name($userStmt, ":last_name", $lastName);
        oci_bind_by_name($userStmt, ":password", $password);
        
        $userExec = oci_execute($userStmt);
        if ($userExec) {
            // Insert into Students table
            $studentSQL = "INSERT INTO Students (users_id, student_number, course)
                           VALUES (:users_id, :student_number, :course)";
            $studentStmt = oci_parse($conn, $studentSQL);
            oci_bind_by_name($studentStmt, ":users_id", $usersId);
            oci_bind_by_name($studentStmt, ":student_number", $studentNumber);
            oci_bind_by_name($studentStmt, ":course", $course);
            
            $studentExec = oci_execute($studentStmt);
            if ($studentExec) {
                // Success response
                $response = [
                    "status" => "success",
                    "title" => "Registration Successful!",
                    "message" => "Redirecting to login page..."
                ];
            } else {
                // Rollback Users entry if student insert fails
                $deleteSQL = "DELETE FROM Users WHERE users_id = :users_id";
                $deleteStmt = oci_parse($conn, $deleteSQL);
                oci_bind_by_name($deleteStmt, ":users_id", $usersId);
                oci_execute($deleteStmt);

                // Get and return the error message from the student insert failure
                $e = oci_error($studentStmt);
                $response = [
                    "status" => "error",
                    "title" => "Registration Failed",
                    "message" => "Error inserting into Students table: " . $e['message']
                ];
            }

            oci_free_statement($studentStmt);
        } else {
            // Get and return the error message from the user insert failure
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

    oci_free_statement($checkStudentStmt);
    oci_close($conn);

    echo json_encode($response);
}
?>
