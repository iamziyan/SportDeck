<?php
/**
 * =====================================================
 * Teams API - Sports Tournament Management
 * =====================================================
 * Returns teams as JSON for a given tournament_id.
 * Used by matches form for dynamic team loading.
 * =====================================================
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$tournamentId = isset($_GET['tournament_id']) ? (int) $_GET['tournament_id'] : 0;
if (!$tournamentId) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, name FROM teams WHERE tournament_id = ? ORDER BY name');
    $stmt->execute([$tournamentId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}
