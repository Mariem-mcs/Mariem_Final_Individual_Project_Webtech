<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin for more security:
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}
$_SESSION['language'] = 'en';
$lang = 'en';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$stats = [];
$stats['total_users'] = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type != 'admin'")->fetch_assoc()['total'];
$stats['citizens'] = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'citizen'")->fetch_assoc()['total'];
$stats['non_citizens'] = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'non_citizen'")->fetch_assoc()['total'];
$stats['pending_applications'] = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'pending'")->fetch_assoc()['total'];
$stats['approved_applications'] = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'approved'")->fetch_assoc()['total'];
$stats['rejected_applications'] = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'rejected'")->fetch_assoc()['total'];
$stats['documents_pending'] = $conn->query("SELECT COUNT(*) as total FROM documents WHERE verification_status = 'pending' OR verification_status IS NULL")->fetch_assoc()['total'];

$recent_applications = [];
$result = $conn->query("
    SELECT a.*, u.full_name, u.email, u.user_type 
    FROM applications a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 15
");
while ($row = $result->fetch_assoc()) {
    $recent_applications[] = $row;
}

$documents_to_verify = [];
$result = $conn->query("
    SELECT d.*, u.full_name, a.application_type 
    FROM documents d
    JOIN applications a ON d.application_id = a.id
    JOIN users u ON a.user_id = u.id
    WHERE d.verification_status = 'pending' OR d.verification_status IS NULL
    ORDER BY d.uploaded_at DESC
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $documents_to_verify[] = $row;
}

$recent_activity = [];
$result = $conn->query("
    SELECT a.*, u.full_name 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $recent_activity[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = intval($_POST['app_id']);
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    $stmt = $conn->prepare("UPDATE applications SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $status, $notes, $app_id);
    
    if ($stmt->execute()) {
        log_activity($user_id, 'application_update', "Updated application #$app_id to $status");
        $success_message = "Application status updated successfully!";
    }
    $stmt->close();
}

// Handle document verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_document'])) {
    $doc_id = intval($_POST['doc_id']);
    $verification_status = $_POST['verification_status'];
    $notes = $_POST['verification_notes'] ?? '';
    
    $stmt = $conn->prepare("UPDATE documents SET verification_status = ?, verification_notes = ?, verified_at = NOW(), verified_by = ? WHERE id = ?");
    $stmt->bind_param("ssii", $verification_status, $notes, $user_id, $doc_id);
    
    if ($stmt->execute()) {
        log_activity($user_id, 'document_verification', "Verified document #$doc_id as $verification_status");
        $success_message = "Document verification status updated!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IDTrack</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="authentifactionAuthorizer.png" alt="Logo" onerror="this.style.display='none'">
                <span>IDTrack Admin</span>
            </div>
            
            <div class="nav-menu">
                <a href="#dashboard" class="nav-item active">Dashboard</a>
                <a href="#applications" class="nav-item">Applications</a>
                <a href="#verification" class="nav-item">Document Verification</a>
                <a href="#reports" class="nav-item">Reports</a>
                <a href="#activity" class="nav-item">Activity Log</a>
            </div>
            
            <a href="login.php?logout=1" class="logout-btn">Logout</a>
        </nav>
        <main class="main-content">
            <header class="header">
                <div class="welcome">
                    <h1>Welcome back, <?php echo htmlspecialchars($admin['full_name']); ?>!</h1>
                    <p>Here's what's happening with your platform today.</p>
                </div>
                
                <div class="admin-badge">
                    <span>ADMIN PANEL</span>
                </div>
            </header>
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <div class="stats-grid" id="dashboard">
                <div class="stat-card">
                    <div class="stat-details">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-details">
                        <h3><?php echo $stats['citizens']; ?></h3>
                        <p>Citizens</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-details">
                        <h3><?php echo $stats['non_citizens']; ?></h3>
                        <p>Residents</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-details">
                        <h3><?php echo $stats['pending_applications']; ?></h3>
                        <p>Pending Apps</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-details">
                        <h3><?php echo $stats['approved_applications']; ?></h3>
                        <p>Approved</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-details">
                        <h3><?php echo $stats['documents_pending']; ?></h3>
                        <p>Docs to Verify</p>
                    </div>
                </div>
            </div>

            <!-- The Application Management Section: -->
            <div class="card" id="applications">
                <div class="card-header">
                    <h3>Manage Applications</h3>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_applications as $app): ?>
                            <tr>
                                <td>#<?php echo $app['id']; ?></td>
                                <td><?php echo htmlspecialchars($app['full_name']); ?><br>
                                    <small><?php echo htmlspecialchars($app['email']); ?></small></td>
                                <td><?php echo ucfirst($app['application_type'] ?? 'N/A'); ?></td>
                                <td><span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span></td>
                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                        <select name="status" required>
                                            <option value="">Update Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="under_review">Under Review</option>
                                            <option value="approved">Approve</option>
                                            <option value="rejected">Reject</option>
                                            <option value="needs_info">Needs More Info</option>
                                        </select>
                                        <input type="text" name="notes" placeholder="Notes (optional)">
                                        <button type="submit" name="update_status" class="btn-action">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_applications)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: rgba(100, 116, 139, 1);">No applications found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- The Document Verification Section: -->
            <div class="card" id="verification">
                <div class="card-header">
                    <h3>Document Verification</h3>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Applicant</th>
                                <th>Type</th>
                                <th>Current Status</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents_to_verify as $doc): ?>
                            <tr>
                                <td>Document #<?php echo $doc['id']; ?><br>
                                    <small><?php echo $doc['document_type']; ?></small></td>
                                <td><?php echo htmlspecialchars($doc['full_name']); ?></td>
                                <td><?php echo ucfirst($doc['application_type']); ?></td>
                                <td><?php echo $doc['verification_status'] ? ucfirst($doc['verification_status']) : 'Not Verified'; ?></td>
                                <td>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                                        <select name="verification_status" required>
                                            <option value="">Verify As</option>
                                            <option value="verified">Verified ✓</option>
                                            <option value="rejected">Reject ✗</option>
                                            <option value="needs_clarity">Needs Clarity</option>
                                        </select>
                                        <input type="text" name="verification_notes" placeholder="Notes (optional)">
                                        <button type="submit" name="verify_document" class="btn-action">Verify</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($documents_to_verify)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">No documents need verification</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- The Reports Section: -->
            <div class="card" id="reports">
                <div class="card-header">
                    <h3>Reports & Monitoring</h3>
                </div>
                <div class="card-body">
                    <div class="report-grid">
                        <div class="report-box">
                            <h4>Application Status Breakdown</h4>
                            <?php
                            $status_report = $conn->query("
                                SELECT status, COUNT(*) as count 
                                FROM applications 
                                GROUP BY status
                            ");
                            while ($report = $status_report->fetch_assoc()):
                            ?>
                            <p><?php echo ucfirst($report['status']); ?>: <?php echo $report['count']; ?></p>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="report-box">
                            <h4>Daily Applications (Last 7 Days)</h4>
                            <?php
                            $daily = $conn->query("
                                SELECT DATE(created_at) as date, COUNT(*) as count 
                                FROM applications 
                                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                GROUP BY DATE(created_at)
                                ORDER BY date DESC
                            ");
                            while ($day = $daily->fetch_assoc()):
                            ?>
                            <p><?php echo date('M d', strtotime($day['date'])); ?>: <?php echo $day['count']; ?> applications</p>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="report-box">
                            <h4>User Registration Trend</h4>
                            <?php
                            $users_trend = $conn->query("
                                SELECT DATE(created_at) as date, COUNT(*) as count 
                                FROM users 
                                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                AND user_type != 'admin'
                                GROUP BY DATE(created_at)
                                ORDER BY date DESC
                            ");
                            while ($trend = $users_trend->fetch_assoc()):
                            ?>
                            <p><?php echo date('M d', strtotime($trend['date'])); ?>: <?php echo $trend['count']; ?> new users</p>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Recent Activity Section: -->
            <div class="card" id="activity">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-content">
                            <p>
                                <strong><?php echo htmlspecialchars($activity['full_name'] ?? 'System'); ?></strong>
                                <?php echo htmlspecialchars($activity['description']); ?>
                            </p>
                            <small><?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($recent_activity)): ?>
                    <p style="text-align: center; color: rgba(100, 116, 139, 1); padding: 2rem;">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="admin_dashboard.js"></script>
</body>
</html>