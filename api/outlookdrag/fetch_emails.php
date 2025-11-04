<?php
include_once('config.php');

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

$where = '';
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $where = "WHERE subject LIKE '%$safe%' OR file_name LIKE '%$safe%'";
}

$totalRes = $conn->query("SELECT COUNT(*) AS cnt FROM emails $where");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$pages = ceil($total / $limit);

$sql = "SELECT id, subject, file_name FROM emails $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$res = $conn->query($sql);

echo '<div class="table-responsive">';
echo '<table class="table table-bordered table-sm align-middle">';
echo '<thead class="table-light"><tr><th>ID</th><th>Subject</th><th>File Name</th><th>Action</th></tr></thead><tbody>';
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . htmlspecialchars($row['subject']) . '</td>';
        echo '<td>' . htmlspecialchars($row['file_name']) . '</td>';
        echo '<td><a class="btn btn-sm btn-primary" target="_blank" href="view.php?id=' . $row['id'] . '">View</a></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center text-muted">No records found</td></tr>';
}
echo '</tbody></table></div>';

if ($pages > 1) {
    echo '<nav><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = ($i == $page) ? 'active' : '';
        echo "<li class='page-item $active'><a href='#' data-page='$i' class='page-link'>$i</a></li>";
    }
    echo '</ul></nav>';
}
