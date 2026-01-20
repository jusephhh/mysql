<?php 
session_start(); 
require 'connection.php'; 
$connect = Connect(); 

if (isset($_SESSION['username'])) {
    $username = h($_SESSION['username']);
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } 

// ===============================
// SEARCH HANDLING
// ===============================
$search = $_GET['search'] ?? '';
if ($search) { 
    $stmt = $connect->prepare("
        SELECT * FROM tbl_employees 
        WHERE emp_id LIKE :search1
           OR firstname LIKE :search2
           OR lastname LIKE :search3
           OR age LIKE :search4
    ");
    $stmt->execute([
        'search1' => "%$search%",
        'search2' => "%$search%",
        'search3' => "%$search%",
        'search4' => "%$search%"
    ]);
} else {
    $stmt = $connect->prepare("SELECT * FROM tbl_employees"); 
    $stmt->execute(); 
}

$rows = $stmt->fetchAll(); 
$toast = $_SESSION['toast'] ?? null; 
unset($_SESSION['toast']); 
?> 

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Management</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #f4f7f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
    margin-top: 20px;
    font-weight: 600;
    text-align: center;
}

/* Logout Button - fixed */
#logoutBtn {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1040; /* below modal (1050) */
    background-color: #ff4d4f;
    color: white;
    font-weight: bold;
    border-radius: 50px;
    padding: 10px 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: background-color 0.3s, transform 0.2s;
}
#logoutBtn:hover {
    background-color: #e04344;
    transform: translateY(-2px);
}

/* Top controls */
.top-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px auto;
    max-width: 90%;
}

.top-controls .form-control {
    width: 250px;
}

/* Table container */
.table-container {
    max-width: 90%;
    margin: 0 auto 50px auto;
    background-color: #ffffff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

table th, table td {
    vertical-align: middle !important;
    text-align: center;
}

.table th {
    background-color: #0d6efd;
    color: #fff;
}

.table-hover tbody tr:hover {
    background-color: #e9f2ff;
    cursor: pointer;
}

/* Buttons */
.btn-warning { background-color: #ffc107; border: none; }
.btn-warning:hover { background-color: #e0a800; }
.btn-danger { background-color: #dc3545; border: none; }
.btn-danger:hover { background-color: #bb2d3b; }

/* Modal */
.modal-content { border-radius: 12px; }
.modal-header { background-color: #0d6efd; color: white; border-bottom: none; }
.modal-footer { border-top: none; }
.btn-close { filter: invert(1); }

/* Toast */
.toast-body strong { color: #0d6efd; }
</style>
</head>

<body>
<h2>Employee Management System</h2>

<!-- Logout Button -->
<a href="login.php" id="logoutBtn">Logout</a>

<!-- TOAST CONTAINER -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;"> 
  <?php if ($toast): ?> 
    <?php $bgClass = "text-bg-" . ($toast['type'] ?? 'info'); ?> 
    <div id="liveToast" class="toast <?= h($bgClass) ?>" role="alert" aria-live="assertive" aria-atomic="true"> 
      <div class="d-flex"> 
        <div class="toast-body"> 
          <strong><?= h($toast['title'] ?? 'Notice') ?>:</strong> 
          <?= h($toast['body'] ?? '') ?> 
        </div> 
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button> 
      </div> 
    </div> 
  <?php endif; ?> 
</div> 

<!-- ADD BUTTON + SEARCH -->
<div class="top-controls">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        + Add New Employee
    </button>

    <form method="GET" class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search by No., Name, Age..." name="search" value="<?= h($search) ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>

<!-- EMPLOYEE TABLE -->
<div class="table-container">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee No.</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Age</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows): ?> 
            <?php $i = 1; foreach ($rows as $row): ?> 
            <tr>
                <td><?= $i++; ?></td>
                <td><?= h($row->emp_id); ?></td>
                <td><?= h($row->firstname); ?></td>
                <td><?= h($row->lastname); ?></td>
                <td><?= h($row->age); ?></td>
                <td class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal-<?= h($row->id) ?>">Update</button>
                    <form method="POST" action="delete_process.php" onsubmit="return confirm('Are you sure to delete this record?');">
                        <input type="hidden" name="id" value="<?= h($row->id) ?>">
                        <button type="submit" class="btn btn-danger" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?> 
            <?php else: ?> 
            <tr><td colspan="6">No Record Found</td></tr> 
            <?php endif; ?> 
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="add_process.php">
        <div class="modal-header">
          <h5 class="modal-title">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Employee Number</label>
            <input type="text" class="form-control" name="emp_id" required>
          </div>
          <div class="mb-2">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="fname" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="lname" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Age</label>
            <input type="number" class="form-control" name="age" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="add">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- UPDATE MODALS -->
<?php if ($rows): foreach ($rows as $row): ?>
<div class="modal fade" id="updateModal-<?= h($row->id) ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="update_process.php">
        <div class="modal-header">
          <h5 class="modal-title">Update Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= h($row->id) ?>">
          <div class="mb-2">
            <label class="form-label">Employee Number</label>
            <input type="text" class="form-control" name="emp_id" value="<?= h($row->emp_id) ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="fname" value="<?= h($row->firstname) ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="lname" value="<?= h($row->lastname) ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Age</label>
            <input type="number" class="form-control" name="age" value="<?= h($row->age) ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="update">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () { 
    var toastEl = document.getElementById('liveToast'); 
    if (toastEl) new bootstrap.Toast(toastEl, { delay: 3000 }).show(); 
})();
</script>

</body>
</html>
