<?php
// export_csv.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../database/db_connect.php';

$report_type = $_POST['report_type'] ?? 'workers';
$from = $_POST['from'] ?? null;
$to = $_POST['to'] ?? null;

function dateWhere($field, $from, $to) {
    if ($from && $to) {
        return "$field >= '$from 00:00:00' AND $field <= '$to 23:59:59'";
    } elseif ($from) {
        return "$field >= '$from 00:00:00'";
    } elseif ($to) {
        return "$field <= '$to 23:59:59'";
    }
    return '1=1';
}

switch ($report_type) {
    case 'workers':
        $sql = "SELECT id, fullname, position, status, created_at FROM workers ORDER BY created_at DESC";
        $filename = "workers_report_" . date('Ymd_His') . ".csv";
        $headers = ['ID','Fullname','Position','Status','Created At'];
        break;
    case 'clients':
        $sql = "SELECT id, company_name, contact_person, contact_email, created_at FROM clients ORDER BY created_at DESC";
        $filename = "clients_report_" . date('Ymd_His') . ".csv";
        $headers = ['ID','Company','Contact Person','Email','Created At'];
        break;
    case 'deployments':
        $where = dateWhere('date_deployed', $from, $to);
        $sql = "SELECT id, client_name, position, quantity, status, date_deployed FROM deployments WHERE $where ORDER BY date_deployed DESC";
        $filename = "deployments_report_" . date('Ymd_His') . ".csv";
        $headers = ['ID','Client','Position','Quantity','Status','Date Deployed'];
        break;
    case 'payroll':
        $where = dateWhere('pay_date', $from, $to);
        $sql = "SELECT id, worker_name, amount, payment_method, pay_date FROM payroll WHERE $where ORDER BY pay_date DESC";
        $filename = "payroll_report_" . date('Ymd_His') . ".csv";
        $headers = ['ID','Worker','Amount','Method','Date'];
        break;
    case 'requests':
        $where = dateWhere('date_requested', $from, $to);
        $sql = "SELECT id, client_name, position, quantity, status, date_requested FROM requests WHERE $where ORDER BY date_requested DESC";
        $filename = "requests_report_" . date('Ymd_His') . ".csv";
        $headers = ['ID','Client','Position','Quantity','Status','Date Requested'];
        break;
    default:
        exit('Invalid report type');
}

$result = $conn->query($sql);
if (!$result) {
    exit('Error running query: ' . $conn->error);
}

// Output headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Open output stream
$out = fopen('php://output', 'w');
fputcsv($out, $headers);

// Fetch rows and output
while ($row = $result->fetch_assoc()) {
    // Map row to headers order: use values in order
    $line = array_values($row);
    fputcsv($out, $line);
}
fclose($out);
$conn->close();
exit;
