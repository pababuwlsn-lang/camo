<?php
// dashboard.php - v2 (FINAL VIBRANT UI & CORRECT LOGIC)

require_once 'header.php'; 

// header.php handles session_start() and security checks
// $user_id and $user_data are available from header.php

// --- FETCH DASHBOARD-SPECIFIC DATA ---
$stats = [
    'jobs_posted' => 0,
    'tasks_completed' => 0,
];

try {
    // Fetch count of jobs this user has posted
    $stmt_jobs = $pdo->prepare("SELECT COUNT(id) FROM jobs WHERE employer_id = ?");
    $stmt_jobs->execute([$user_id]);
    $stats['jobs_posted'] = $stmt_jobs->fetchColumn();

    // --- CORE LOGIC FIX: Count completed tasks from the `job_submissions` table ---
    $stmt_tasks = $pdo->prepare("SELECT COUNT(id) FROM job_submissions WHERE worker_id = ? AND status = 'satisfied'");
    $stmt_tasks->execute([$user_id]);
    $stats['tasks_completed'] = $stmt_tasks->fetchColumn();

} catch (PDOException $e) {
    // Gracefully handle errors so the page doesn't crash
    $dashboard_error = "Could not load some dashboard statistics.";
    error_log("Dashboard fetch error: " . $e->getMessage());
}

?>

<title>Dashboard</title>
<style>
    /* --- === 2025 VIBRANT UI STYLES === --- */
    :root {
        --lime-green: #A3E635; --fuchsia-pink: #D946EF; --cyan-blue: #22D3EE;
        --indigo-purple: #818CF8; --amber-orange: #F59E0B; --rose-red: #F43F5E;
        --text-primary: #1F2937; --text-secondary: #6B7280; --background-color: #F9FAFB;
        --card-background: #FFFFFF; --border-color: #E5E7EB; --font-family: 'Segoe UI', Arial, sans-serif;
        --transition-main: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: var(--font-family); background-color: var(--background-color);
        margin: 0; padding: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
    }
    .content-wrapper { max-width: 1200px; margin: 0 auto; padding: 30px; }
    .page-header { margin-bottom: 30px; }
    .page-title { font-size: 2.5em; font-weight: 700; color: var(--text-primary); }
    .welcome-subtitle { font-size: 1.2em; color: var(--text-secondary); margin-top: 5px; }

    /* Primary Stats Grid */
    .stats-grid { 
        display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); 
        gap: 25px; margin-bottom: 30px;
    }
    .stat-card {
        color: white; border-radius: 20px; padding: 25px; display: flex;
        align-items: center; gap: 20px; border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); transition: var(--transition-main);
    }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
    
    /* Gradient Backgrounds */
    .bg-gradient-green { background: linear-gradient(135deg, var(--lime-green) 0%, #4ADE80 100%); }
    .bg-gradient-indigo { background: linear-gradient(135deg, var(--indigo-purple) 0%, #6366F1 100%); }
    .bg-gradient-amber { background: linear-gradient(135deg, var(--amber-orange) 0%, #FBBF24 100%); }
    .bg-gradient-fuchsia { background: linear-gradient(135deg, var(--fuchsia-pink) 0%, #C026D3 100%); }
    .bg-gradient-cyan { background: linear-gradient(135deg, var(--cyan-blue) 0%, #2DD4BF 100%); }

    .stat-card-icon {
        font-size: 1.8em; width: 60px; height: 60px; display: grid;
        place-items: center; border-radius: 50%; background-color: rgba(255,255,255,0.2);
    }
    .stat-card-content .value { font-size: 2.2em; font-weight: 600; line-height: 1.1; margin: 0; }
    .stat-card-content .label { font-size: 1em; opacity: 0.9; margin: 0; }
    
    /* Bento Grid */
    .bento-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
    .bento-card {
        background: var(--card-background); border-radius: 20px; padding: 30px;
        border: 1px solid var(--border-color); box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .bento-card h2 { margin-top: 0; margin-bottom: 25px; font-size: 1.6em; font-weight: 600; color: var(--text-primary); }
    .bento-card p { color: var(--text-secondary); line-height: 1.6; }

    .quick-actions-grid { 
        display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 20px; 
    }
    .action-button {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 12px; padding: 20px 10px; border-radius: 15px;
        background-color: #f3f4f6; text-decoration: none; color: var(--text-primary);
        font-weight: 600; font-size: 0.95em; text-align: center;
        transition: var(--transition-main);
    }
    .action-button:hover { transform: scale(1.05); background-color: #e5e7eb; }
    .action-button i { font-size: 2em; line-height: 1; }

    @media (max-width: 768px) {
        .page-title { font-size: 2em; }
        .welcome-subtitle { font-size: 1em; }
        .stats-grid, .bento-grid { grid-template-columns: 1fr; }
        .content-wrapper { padding: 20px; }
    }
</style>

<div class="content-wrapper">
    <div class="page-header">
        <h1 class="page-title">Welcome Back, <?php echo htmlspecialchars(explode(' ', $user_data['full_name'])[0]); ?>!</h1>
        <p class="welcome-subtitle">Here is your dashboard overview.</p>
    </div>

    <?php if (isset($dashboard_error)): ?>
        <div class="message-area error" style="display: flex;"><?php echo $dashboard_error; ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card bg-gradient-green">
            <div class="stat-card-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-card-content"><p class="value">$<?php echo number_format($user_data['earning_balance'], 4); ?></p><p class="label">Earning Balance</p></div>
        </div>
        <div class="stat-card bg-gradient-indigo">
            <div class="stat-card-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-card-content"><p class="value">$<?php echo number_format($user_data['deposit_balance'], 4); ?></p><p class="label">Deposit Balance</p></div>
        </div>
        <div class="stat-card bg-gradient-amber">
            <div class="stat-card-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-card-content"><p class="value">$<?php echo number_format($user_data['pending_balance'], 4); ?></p><p class="label">Pending Balance</p></div>
        </div>
        <div class="stat-card bg-gradient-fuchsia">
            <div class="stat-card-icon"><i class="fas fa-briefcase"></i></div>
            <div class="stat-card-content"><p class="value"><?php echo (int)($stats['jobs_posted']); ?></p><p class="label">Jobs Posted</p></div>
        </div>
        <div class="stat-card bg-gradient-cyan">
            <div class="stat-card-icon"><i class="fas fa-check-double"></i></div>
            <div class="stat-card-content"><p class="value"><?php echo (int)($stats['tasks_completed']); ?></p><p class="label">Tasks Completed</p></div>
        </div>
    </div>

    <div class="bento-grid">
        <div class="bento-card">
            <h2>Quick Actions</h2>
            <div class="quick-actions-grid">
                <a href="find_jobs.php" class="action-button"><i class="fas fa-search" style="color:var(--indigo-purple);"></i><span>Find Jobs</span></a>
                <a href="post_job.php" class="action-button"><i class="fas fa-plus-circle" style="color:var(--lime-green);"></i><span>Post a Job</span></a>
                <a href="deposit.php" class="action-button"><i class="fas fa-donate" style="color:var(--cyan-blue);"></i><span>Deposit</span></a>
                <a href="withdraw.php" class="action-button"><i class="fas fa-hand-holding-usd" style="color:var(--amber-orange);"></i><span>Withdraw</span></a>
                <a href="support.php" class="action-button"><i class="fas fa-headset" style="color:var(--fuchsia-pink);"></i><span>Support</span></a>
                <a href="notifications.php" class="action-button"><i class="fas fa-bell" style="color:var(--rose-red);"></i><span>Notifications</span></a>
            </div>
        </div>

        <div class="bento-card">
            <h2>Getting Started</h2>
            <p>Welcome to your new dashboard! Use the Quick Actions to navigate to the most common pages. Your key financial statistics are always visible at the top of the page.</p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
