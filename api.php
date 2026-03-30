<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please login.']);
    exit;
}

require_once 'db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT bulan, tahun, reportNum, updated_at FROM reports ORDER BY tahun DESC, bulan DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $results]);
    }
    elseif ($action === 'load') {
        $bulan = (int)($_GET['m'] ?? 0);
        $tahun = (int)($_GET['y'] ?? 0);
        
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE bulan = ? AND tahun = ? LIMIT 1");
        $stmt->execute([$bulan, $tahun]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($report) {
            // Decode back to JSON objects for JS
            $report['kegData'] = json_decode($report['kegData'], true);
            $report['attData'] = json_decode($report['attData'], true);
            $report['notesData'] = json_decode($report['notesData'], true);
            echo json_encode(['status' => 'success', 'data' => $report]);
        } else {
            echo json_encode(['status' => 'success', 'data' => null]);
        }
    }
    elseif ($action === 'save') {
        // Get POST data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data || !isset($data['bulan']) || !isset($data['tahun'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            exit;
        }

        $bulan = (int)$data['bulan'];
        $tahun = (int)$data['tahun'];
        $reportNum = (int)($data['reportNum'] ?? 1);
        $kegData = json_encode($data['kegData'] ?? []);
        $attData = json_encode($data['attData'] ?? []);
        $notesData = json_encode($data['notesData'] ?? []);

        // Upsert logic (INSERT ... ON DUPLICATE KEY UPDATE)
        $stmt = $pdo->prepare("INSERT INTO reports (bulan, tahun, reportNum, kegData, attData, notesData) 
            VALUES (:bulan, :tahun, :reportNum, :kegData, :attData, :notesData)
            ON DUPLICATE KEY UPDATE 
            reportNum = VALUES(reportNum), kegData = VALUES(kegData), attData = VALUES(attData), notesData = VALUES(notesData)");
            
        $stmt->execute([
            ':bulan' => $bulan,
            ':tahun' => $tahun,
            ':reportNum' => $reportNum,
            ':kegData' => $kegData,
            ':attData' => $attData,
            ':notesData' => $notesData
        ]);

        echo json_encode(['status' => 'success']);
    }
    elseif ($action === 'delete') {
        $bulan = (int)($_GET['m'] ?? 0);
        $tahun = (int)($_GET['y'] ?? 0);
        
        $stmt = $pdo->prepare("DELETE FROM reports WHERE bulan = ? AND tahun = ?");
        $stmt->execute([$bulan, $tahun]);
        echo json_encode(['status' => 'success']);
    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
