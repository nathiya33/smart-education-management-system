<?php
require_once '../inc/db_connection.php';

if (isset($_GET['batch_id'])) {
    $batch_id = $_GET['batch_id'];
    try {
        $stmt = $pdo->prepare("
            SELECT 
                students.id, 
                students.name, 
                students.email, 
                batches.name AS batch_name, 
                departments.name AS department_name 
            FROM students
            JOIN batches ON students.batch_id = batches.id
            JOIN departments ON students.department_id = departments.id
            WHERE students.batch_id = :batch_id
        ");
        $stmt->execute(['batch_id' => $batch_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]);
    }
}
?>
