<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Fetch user capsules only (don’t auto-unlock here)
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$capsules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Capsules</title>
<style>
    body { font-family: 'Poppins', sans-serif; background:#f0fdf4; color:#2f473a; margin:0; padding:0; }
    .main-header { background:#d7f5dd; padding:20px; display:flex; justify-content:space-between; align-items:center; }
    .main-header h1 { margin:0; font-size:28px; }
    .navbar a { margin-left:15px; text-decoration:none; color:#2f473a; font-weight:600; }
    main { max-width:900px; margin:30px auto; }
    h2 { font-size:32px; font-weight:700; letter-spacing:0.5px; margin-bottom:20px; }
    .btn { padding:10px 18px; background:#a1e2b8; color:#2f473a; text-decoration:none; border-radius:6px; font-weight:600; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    table th, table td { padding:12px; border-bottom:1px solid #ccc; text-align:left; }
    table th { background:#d7f5dd; }
    .status-cell { font-weight:600; }
    .status-locked { color:#c0392b; }
    .status-unlocked { color:#2ecc71; }
    #statusCard { 
        display:none; background:#d7f5dd; padding:15px; border-radius:8px; margin-bottom:20px; border-left:6px solid #2f473a;
    }
    /* Delete Modal */
    #deleteModal {
        display:none;
        position:fixed;
        top:50%;
        left:50%;
        transform:translate(-50%, -50%);
        background:#d7f5dd;
        padding:20px 25px;
        border-radius:10px;
        box-shadow:0 5px 15px rgba(0,0,0,0.3);
        z-index:1000;
        min-width:300px;
        text-align:center;
    }
    #modalOverlay {
        display:none;
        position:fixed;
        top:0; left:0;
        width:100%; height:100%;
        background:rgba(0,0,0,0.2);
        z-index:900;
    }
    #deleteModal button { border:none; border-radius:6px; padding:8px 15px; cursor:pointer; }
    #confirmDeleteBtn { background:#c0392b; color:#fff; margin-right:10px; }
    #cancelDeleteBtn { background:#a1e2b8; color:#2f473a; }
</style>
</head>
<body>

<div id="statusCard"></div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal">
    <p id="deleteModalText" style="margin-bottom:20px; font-weight:600;"></p>
    <button id="confirmDeleteBtn">Yes, Delete</button>
    <button id="cancelDeleteBtn" onclick="closeDeleteModal()">Cancel</button>
</div>
<div id="modalOverlay"></div>

<header class="main-header">
    <h1>Digital Time Capsule</h1>
    <nav class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="shared_capsules.php">Shared With Me</a>
        <a href="notifications.php">Notifications</a>
        <a href="../src/api/logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Your Space</h2>
    <a href="create_capsule.html" class="btn">+ Create New Capsule</a>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Unlock Date</th>
                <th>Visibility</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($capsules): ?>
                <?php foreach ($capsules as $capsule): ?>
                <tr data-id="<?= $capsule['id'] ?>">
                    <td><?= htmlspecialchars($capsule['title']) ?></td>
                    <td><?= htmlspecialchars($capsule['unlock_date']) ?></td>
                    <td><?= htmlspecialchars($capsule['visibility']) ?></td>
                    <td class="status-cell <?= $capsule['status']==='locked'?'status-locked':'status-unlocked' ?>">
                        <?= ucfirst($capsule['status']) ?>
                    </td>
                    <td class="action-cell">
                        <a href="#" onclick="toggleCapsuleStatus(<?= $capsule['id'] ?>)" style="font-weight:600; color:<?= $capsule['status']==='locked'?'#2ecc71':'#c0392b' ?>">
                            <?= $capsule['status'] === 'locked' ? 'Unlock' : 'Lock' ?>
                        </a> |
                        <a href="edit_capsule.php?id=<?= $capsule['id'] ?>" style="color:#2f473a;">Edit</a> |
                        <a href="share_capsule.php?id=<?= $capsule['id'] ?>" style="color:#2f473a;">Share</a> |
                        <a href="#" onclick="deleteCapsule(<?= $capsule['id'] ?>)" style="color:#e74c3c;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No capsules found yet. Create one!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<script>
function toggleCapsuleStatus(id){
    fetch("../src/api/lock_capsule.php?id=" + id)
    .then(res => res.json())
    .then(data => {
        const box = document.getElementById("statusCard");
        box.style.display = "block";
        box.textContent = data.message;

        if(data.success){
            const row = document.querySelector(`tr[data-id='${id}']`);
            const statusCell = row.querySelector('.status-cell');
            const actionCell = row.querySelector('.action-cell a:first-child');

            statusCell.textContent = data.status;
            statusCell.className = 'status-cell ' + (data.status === 'Locked' ? 'status-locked' : 'status-unlocked');
            actionCell.textContent = data.status === 'Locked' ? 'Unlock' : 'Lock';
            actionCell.style.color = data.status === 'locked' ? '#2ecc71' : '#c0392b';

            setTimeout(()=>{ box.style.display='none'; }, 2000);
        }
    });
}

let capsuleToDelete = null;

function deleteCapsule(id){
    capsuleToDelete = id;
    document.getElementById("deleteModalText").textContent = "Are you sure you want to delete this capsule?";
    document.getElementById("deleteModal").style.display = "block";
    document.getElementById("modalOverlay").style.display = "block";
}

document.getElementById("confirmDeleteBtn").addEventListener("click", function(){
    if(!capsuleToDelete) return;

    fetch("../src/api/delete_capsule.php?id=" + capsuleToDelete)
    .then(res => res.json())
    .then(data => {
        const box = document.getElementById("statusCard");
        box.style.display = "block";
        box.textContent = data.message;

        if(data.success){
            const row = document.querySelector(`tr[data-id='${capsuleToDelete}']`);
            row.remove();
            capsuleToDelete = null;
            closeDeleteModal();
            setTimeout(()=>{ box.style.display='none'; }, 2000);
        }
    });
});

function closeDeleteModal(){
    document.getElementById("deleteModal").style.display = "none";
    document.getElementById("modalOverlay").style.display = "none";
    capsuleToDelete = null;
}
</script>

</body>
</html>
