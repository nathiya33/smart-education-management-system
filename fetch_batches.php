<?php
require_once '../inc/db_connection.php';

if (isset($_GET['department_id'])) {
    $department_id = $_GET['department_id'];
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM batches WHERE department_id = :department_id");
        $stmt->execute(['department_id' => $department_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]);
    }
}
?>
