<?php
// reports.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../database/db_connect.php';

$fullname = $_SESSION['fullname'] ?? 'Admin';

// Defaults
$report_type = $_GET['report_type'] ?? 'workers';
$from = $_GET['from'] ?? date('Y-m-01'); // start of current month by default
$to = $_GET['to'] ?? date('Y-m-d');

// Helper to sanitize WHERE date clause (applies only when table has created_at / date fields)
function dateWhere($field, $from, $to) {
    $f = $from ? $field . " >= '" . $from . " 00:00:00'" : '1=1';
    $t = $to   ? $field . " <= '" . $to   . " 23:59:59'" : '1=1';
    return "($f AND $t)";
}

// Build query & headers for each report type (basic)
$tableRows = [];
$tableHeaders = [];
switch ($report_type) {
    case 'workers':
        $sql = "SELECT id, fullname, position, status, DATE(created_at) AS created_at FROM workers ORDER BY created_at DESC";
        $tableHeaders = ['ID','Fullname','Position','Status','Created At'];
        break;

    case 'clients':
        $sql = "SELECT id, company_name, contact_person, contact_email, DATE(created_at) AS created_at FROM clients ORDER BY created_at DESC";
        $tableHeaders = ['ID','Company','Contact Person','Email','Created At'];
        break;

    case 'deployments':
        // allow date filter if created_at exists on deployments
        $where = dateWhere('date_deployed', $from, $to);
        $sql = "SELECT id, client_name, position, quantity, status, DATE(date_deployed) AS date_deployed FROM deployments WHERE $where ORDER BY date_deployed DESC";
        $tableHeaders = ['ID','Client','Position','Qty','Status','Date Deployed'];
        break;

    case 'payroll':
        $where = dateWhere('pay_date', $from, $to);
        $sql = "SELECT id, worker_name, amount, payment_method, DATE(pay_date) AS pay_date FROM payroll WHERE $where ORDER BY pay_date DESC";
        $tableHeaders = ['ID','Worker','Amount','Method','Date'];
        break;

    case 'requests':
        $where = dateWhere('date_requested', $from, $to);
        $sql = "SELECT id, client_name, position, quantity, status, DATE(date_requested) AS date_requested FROM requests WHERE $where ORDER BY date_requested DESC";
        $tableHeaders = ['ID','Client','Position','Qty','Status','Date Requested'];
        break;

    default:
        $sql = "SELECT id, fullname, position, status, DATE(created_at) AS created_at FROM workers ORDER BY created_at DESC";
        $tableHeaders = ['ID','Fullname','Position','Status','Created At'];
        break;
}

// execute and fetch rows
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $tableRows[] = $r;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reports & Analytics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background:#f4f6f9; }
    .card { border-radius:12px; }
    .filters .form-control { min-width:160px; }
    .table-wrap { max-height:420px; overflow:auto; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?> <!-- keep your sidebar -->
<div class="container-fluid px-4 py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Reports & Analytics</h4>
    <div>Welcome, <b><?= htmlspecialchars($fullname) ?></b></div>
  </div>

  <!-- Controls -->
  <div class="card mb-3 p-3">
    <form id="reportForm" method="get" class="row g-2 align-items-center">
      <div class="col-auto">
        <label class="form-label">Report</label>
        <select name="report_type" class="form-select" onchange="document.getElementById('reportForm').submit()">
          <option value="workers" <?= $report_type=='workers'?'selected':'' ?>>Workers</option>
          <option value="clients" <?= $report_type=='clients'?'selected':'' ?>>Clients</option>
          <option value="deployments" <?= $report_type=='deployments'?'selected':'' ?>>Deployments</option>
          <option value="payroll" <?= $report_type=='payroll'?'selected':'' ?>>Payroll</option>
          <option value="requests" <?= $report_type=='requests'?'selected':'' ?>>Requests</option>
        </select>
      </div>

      <div class="col-auto filters">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
      </div>

      <div class="col-auto filters">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
      </div>

      <div class="col-auto mt-3">
        <button type="submit" class="btn btn-primary mt-2">Apply</button>
      </div>

      <div class="col-auto mt-3">
        <!-- Export to CSV posts to export_csv.php -->
        <form action="export_csv.php" method="post" id="exportForm">
          <input type="hidden" name="report_type" value="<?= htmlspecialchars($report_type) ?>">
          <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
          <input type="hidden" name="to"   value="<?= htmlspecialchars($to) ?>">
          <button type="submit" class="btn btn-outline-success mt-2">Export CSV</button>
        </form>
      </div>
    </form>
  </div>

  <div class="row g-3">

    <!-- Left: Table -->
    <div class="col-12 col-lg-7">
      <div class="card p-0">
        <div class="card-header bg-light">
          <strong class="me-2">Report:</strong> <?= ucfirst($report_type) ?>
          <span class="text-muted float-end"><?= date('M d, Y', strtotime($from)) ?> — <?= date('M d, Y', strtotime($to)) ?></span>
        </div>
        <div class="card-body p-0">
          <div class="table-wrap">
            <table class="table table-striped mb-0">
              <thead class="table-secondary">
                <tr>
                  <?php foreach ($tableHeaders as $th): ?><th><?= $th ?></th><?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php if (count($tableRows) === 0): ?>
                  <tr><td colspan="<?= count($tableHeaders) ?>" class="text-center py-4 text-muted">No records found</td></tr>
                <?php else: ?>
                  <?php foreach ($tableRows as $r): ?>
                    <tr>
                      <?php foreach ($tableHeaders as $key => $h): 
                          // map headers to array values in row (simple heuristic)
                          $vals = array_values($r);
                          $val = $vals[$key] ?? '';
                      ?>
                        <td><?= htmlspecialchars($val) ?></td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Charts -->
    <div class="col-12 col-lg-5">
      <div class="card mb-3 p-3">
        <h6>Monthly Deployments (Last 12 months)</h6>
        <canvas id="deployChart" height="160"></canvas>
      </div>

      <div class="card p-3">
        <h6>Monthly Payroll (Last 12 months)</h6>
        <canvas id="payrollChart" height="160"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for charts: last 12 months
$labels = []; $deployData = []; $payrollData = [];
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $labels[] = date('M Y', strtotime($m . '-01'));
    // deployments count for month
    $q1 = $conn->query("SELECT COALESCE(SUM(quantity),0) AS total FROM deployments WHERE DATE_FORMAT(date_deployed,'%Y-%m') = '". $m ."'");
    $deployData[] = ($q1 && $q1->fetch_assoc()) ? intval($q1->fetch_assoc()['total']) : 0;
    // payroll sum for month
    $q2 = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM payroll WHERE DATE_FORMAT(pay_date,'%Y-%m') = '". $m ."'");
    $payrollData[] = ($q2 && $q2->fetch_assoc()) ? floatval($q2->fetch_assoc()['total']) : 0;
}
// NOTE: Above uses simple queries. If your column names differ, adjust date_deployed/pay_date accordingly.
?>

<script>
const labels = <?= json_encode($labels) ?>;
const deployData = <?= json_encode($deployData) ?>;
const payrollData = <?= json_encode($payrollData) ?>;

new Chart(document.getElementById('deployChart'), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Deployed (qty)',
      data: deployData,
      backgroundColor: Array(labels.length).fill('#0d6efd')
    }]
  },
  options: { responsive:true, plugins:{ legend:{ display:false } } }
});

new Chart(document.getElementById('payrollChart'), {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      label: 'Payroll (₱)',
      data: payrollData,
      fill: false,
      borderColor: '#198754'
    }]
  },
  options: { responsive:true, plugins:{ legend:{ display:false } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
