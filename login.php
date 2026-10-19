<?php
require_once '../inc/db_connection.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $role       = filter_var($_POST['role'] ?? '', FILTER_SANITIZE_STRING);
    $identifier = filter_var($_POST['username'] ?? '', FILTER_SANITIZE_STRING); // Can be username or email
    $pass       = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($role) || empty($identifier) || empty($pass)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    // Map each role to its corresponding database table
    $roleToTable = [
        'admin'   => 'admins',
        'student' => 'students',
        'parent'  => 'students', // Assuming parents are stored in the students table
        'teacher' => 'teachers'
    ];

    // Validate role and corresponding table
    if (!array_key_exists($role, $roleToTable)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid role.'
        ]);
        exit;
    }

    $table = $roleToTable[$role];

    // Define which login columns are available for each role.
    // For example, assume that:
    // - Admins and teachers use both "username" and "email" for login.
    // - Students and parents use only "email" for login.
    $roleToLoginColumns = [
        'admin'   => ['username', 'email'],
        'teacher' => [ 'email'],
        'student' => ['email'],
        'parent'  => ['email']
    ];

    $loginColumns = $roleToLoginColumns[$role];

    // Build the SQL query based on the available login columns
    if (count($loginColumns) > 1) {
        $query = "SELECT * FROM $table WHERE {$loginColumns[0]} = :identifier OR {$loginColumns[1]} = :identifier";
    } else {
        $query = "SELECT * FROM $table WHERE {$loginColumns[0]} = :identifier";
    }

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();

        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password against the stored hash
        if ($userRecord && password_verify($pass, $userRecord['password'])) {
            // Start the session and regenerate the session ID for security
            session_start();
            session_regenerate_id(true);
            $_SESSION['user'] = $userRecord;
            $_SESSION['role'] = $role;

            // Determine the redirect URL based on the role
            $redirectUrl = '';
            switch ($role) {
                case 'admin':
                    $redirectUrl = '../frontend/admin/admin_dashboard.php';
                    break;
                case 'student':
                    $redirectUrl = '../frontend/student/student_dashboard.php';
                    break;
                case 'parent':
                    $redirectUrl = '../frontend/parent/parent_dashboard.php';
                    break;
                case 'teacher':
                    $redirectUrl = '../frontend/teacher/teacher_dashboard.php';
                    break;
            }

            echo json_encode([
                'status'   => 'success',
                'redirect' => $redirectUrl
            ]);
            exit;
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Login failed. Please check your credentials.'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>
