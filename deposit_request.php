<?php
// Check if user is logged in
include_once 'server.php';
 

// Simulating login user_id

// Handle Approve/Reject via AJAX
if (isset($_POST['action'], $_POST['id']) && in_array($_POST['action'], ['approve', 'reject'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    // Fetch the record first
    $stmt = $conn->prepare("SELECT * FROM collection_deposit_master WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        echo json_encode(["success" => false, "message" => "Record not found"]);
        exit;
    }

    if ($action === 'approve') {
        // Update status to Approved
        $conn->query("UPDATE collection_deposit_master SET status = 'Approved' WHERE id = '$id'");

        // Adjust user collections
        $conn->query("UPDATE user_master SET collection = collection - " . $item['amount'] . " WHERE user_id = '" . $item['raised_by'] . "'");
        $conn->query("UPDATE user_master SET collection = collection + " . $item['amount'] . " WHERE user_id = '" . $item['pending_with'] . "'");

        $status = 'Approved';
    } elseif ($action === 'reject') {
        // Update status to Rejected
        $conn->query("UPDATE collection_deposit_master SET status = 'Rejected' WHERE id = '$id'");
        $status = 'Rejected';
    } else {
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
    }

    echo json_encode(["success" => true, "status" => $status]);
    exit;
}
include 'header.php'; // Include header for consistent styling  

$loggedInUserId = $_SESSION['user_id'] ?? 0;

// Fetch deposits for logged-in user
$sql = "
SELECT 
    cd.id,
    cd.amount,
    cd.date,
    cd.status,
    ru.username AS raised_by_name,
    pu.username AS pending_with_name
FROM collection_deposit_master cd
LEFT JOIN user_master ru ON cd.raised_by = ru.user_id
LEFT JOIN user_master pu ON cd.pending_with = pu.user_id
WHERE cd.pending_with = ?
ORDER BY cd.date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container">
    <h2 class="mb-4">Collection Deposit Report</h2>

    <!-- Date Range Filter -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">From Date:</label>
            <input type="text" id="minDate" class="form-control" placeholder="YYYY-MM-DD">
        </div>
        <div class="col-md-3">
            <label class="form-label">To Date:</label>
            <input type="text" id="maxDate" class="form-control" placeholder="YYYY-MM-DD">
        </div>
    </div>

    <table id="collectionTable" class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Raised By</th>
                <th>Pending With</th>
                <th>Amount (₹)</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $row): ?>
            <tr id="row-<?= $row['id'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['raised_by_name']) ?></td>
                <td><?= htmlspecialchars($row['pending_with_name']) ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
                <td><?= date("Y-m-d", strtotime($row['date'])) ?></td>
                <td>
                    <span class="badge 
                        <?= $row['status'] === 'approved' ? 'bg-success' : 
                            ($row['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($row['status'] === 'Pending'): ?>
                        <button class="btn btn-success btn-sm action-btn" data-id="<?= $row['id'] ?>" data-action="approve">Approve</button>
                        <button class="btn btn-danger btn-sm action-btn" data-id="<?= $row['id'] ?>" data-action="reject">Reject</button>
                    <?php else: ?>
                        <em>No action</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.1/css/dataTables.dateTime.min.css">
<script src="https://cdn.datatables.net/datetime/1.5.1/js/dataTables.dateTime.min.js"></script>
<script>
$(document).ready(function() {
    let minDate = new DateTime($('#minDate'), { format: 'YYYY-MM-DD' });
    let maxDate = new DateTime($('#maxDate'), { format: 'YYYY-MM-DD' });

    let table = $('#collectionTable').DataTable();
    // Sort by date descending on load
    table.order([4, 'desc']).draw();
    // FIX: Compare using Date objects
    $.fn.dataTable.ext.search.push(function(settings, data) {
        let min = minDate.val();
        let max = maxDate.val();
        let dateStr = data[4]; // Date column value (YYYY-MM-DD)
        let date = new Date(dateStr); // Convert to Date object

        if (
            (min === null && max === null) ||
            (min === null && date <= new Date(max)) ||
            (new Date(min) <= date && max === null) ||
            (new Date(min) <= date && date <= new Date(max))
        ) {
            return true;
        }
        return false;
    });

    $('#minDate, #maxDate').on('change', function() {
        table.draw();
    });

    // Approve / Reject buttons
    $(document).on('click', '.action-btn', function() {
        let id = $(this).data('id');
        let action = $(this).data('action');
        let row = $('#row-' + id);

        $.post("deposit_request.php", { id: id, action: action }, function(response) {
            let res = JSON.parse(response);
            if (res.success) {
                let badgeClass = action === 'approve' ? 'bg-success' : 'bg-danger';
                row.find('td:eq(5)').html('<span class="badge ' + badgeClass + '">' + res.status + '</span>');
                row.find('td:eq(6)').html('<em>No action</em>');
            }
        });
    });
});

</script>
<?php include 'footer.php'; ?>  
