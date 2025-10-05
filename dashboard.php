<?php 
require_once 'functions.php'; 
require_login(); 

$user = current_user(); 
$user_id = $user['id']; 

$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY id");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll(); //category

$stmt = $pdo->prepare("
    SELECT t.*, c.name as category_name, c.color as category_color 
    FROM tasks t 
    LEFT JOIN categories c ON t.category_id = c.id 
    WHERE t.user_id = ? 
    ORDER BY t.is_done ASC, 
             t.due_date IS NULL, 
             t.due_date ASC, 
             t.priority DESC, 
             t.created_at DESC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();  //task

$score = weekly_consistency_score($user_id); //consistency

$stmt = $pdo->prepare("
    SELECT DATE(action_at) AS d, COUNT(*) AS c 
    FROM task_history 
    WHERE user_id = ? 
      AND action = 'completed' 
      AND action_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY DATE(action_at)
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();

$completed_map = [];
foreach ($rows as $r) {
    $completed_map[$r['d']] = (int)$r['c'];
}

$labels = [];//days name
$values = [];//count of completed tasks 
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($day));
    $values[] = $completed_map[$day] ?? 0;
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - Smart Productivity</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gradient">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Smart Productivity</a>
      <div class="ms-auto">
        <span class="me-3">Hi, <?= htmlspecialchars($user['name']) ?></span>
        <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <div class="row g-4">

      <!-- Left: Add Task + Categories -->
      <div class="col-lg-4">
        <!-- Add Task -->
        <div class="card p-3 shadow-sm">
          <h5>Add Task</h5>
          <form action="add_task.php" method="post">
            <div class="mb-2">
              <input name="title" class="form-control" placeholder="Task title" required>
            </div>
            <div class="mb-2">
              <textarea name="description" class="form-control" placeholder="Optional description"></textarea>
            </div>
            <div class="mb-2">
              <select name="category_id" class="form-select">
                <option value="">No category</option>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row g-2">
              <div class="col-6">
                <input name="due_date" type="date" class="form-control">
              </div>
              <div class="col-6">
                <select name="priority" class="form-select">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
            </div>
            <div class="mt-3 d-grid">
              <button class="btn btn-gradient" type="submit">Add Task</button>
            </div>
          </form>
        </div>

        <!-- Categories -->
        <div class="card p-3 mt-3 shadow-sm">
          <h6 class="mb-2">Categories</h6>
          <ul class="list-unstyled">
            <?php foreach ($categories as $c): ?>
              <li class="mb-2">
                <span class="category-dot" style="background:<?= $c['color'] ?>"></span>
                <?= htmlspecialchars($c['name']) ?>
              </li>
            <?php endforeach; ?>
          </ul>

          <!-- Quick Add Category -->
          <form action="add_task.php" method="post" class="mt-2">
            <div class="input-group">
              <input name="new_category" class="form-control" placeholder="New category (e.g. Study)">
              <input type="color" name="new_category_color" value="#ff7675" 
                     class="form-control form-control-color" style="width:60px">
              <button class="btn btn-outline-primary" name="action" value="add_category">Add</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Center: Tasks List -->
      <div class="col-lg-5">
        <div class="card p-3 shadow-sm">
          <h5 class="mb-3">Tasks</h5>
          <?php if (empty($tasks)): ?>
            <div class="text-center text-muted">No tasks yet. Add a task to get started!</div>
          <?php endif; ?>
          <ul class="list-unstyled">
            <?php foreach ($tasks as $t): ?>
              <li class="d-flex align-items-start gap-3 mb-3 task-row <?= $t['is_done'] ? 'done' : '' ?>">
                <input type="checkbox" class="form-check-input mt-1 task-checkbox" 
                       data-id="<?= $t['id'] ?>" <?= $t['is_done'] ? 'checked' : '' ?>>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between">
                    <div>
                      <strong><?= htmlspecialchars($t['title']) ?></strong>
                      <?php if ($t['category_name']): ?>
                        <span class="badge cat-badge" style="background:<?= $t['category_color'] ?>">
                          <?= htmlspecialchars($t['category_name']) ?>
                        </span>
                      <?php endif; ?>
                      <?php if ($t['priority'] === 'high'): ?>
                        <span class="badge bg-danger">High</span>
                      <?php endif; ?>
                    </div>
                    <div class="text-muted small">
                      <?= $t['due_date'] ? date('d M', strtotime($t['due_date'])) : '' ?>
                    </div>
                  </div>
                  <div class="small text-muted"><?= htmlspecialchars($t['description']) ?></div>
                  <div class="mt-2">
                    <a href="edit_task.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <a href="delete_task.php?id=<?= $t['id'] ?>" 
                       class="btn btn-sm btn-outline-danger" 
                       onclick="return confirm('Delete this task?')">Delete</a>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!-- Right: Analytics + Pomodoro -->
      <div class="col-lg-3">
        <!-- Weekly Consistency -->
        <div class="card p-3 mb-3 shadow-sm">
          <h6>Weekly Consistency</h6>
          <div class="text-center my-2">
            <!-- IMPORTANT: Added id="consistency-score" for JavaScript to target -->
            <div id="consistency-score" class="score-circle"><?= $score ?>%</div>
            <div class="small text-muted">Days with completed tasks this week</div>
          </div>
        </div>

        <!-- Completion History -->
        <div class="card p-3 mb-3 shadow-sm">
          <h6>Completion history (last 7 days)</h6>
          <canvas id="historyChart" height="200"></canvas>
        </div>

        <!-- Pomodoro -->
        <div class="card p-3 shadow-sm">
          <h6>Pomodoro Focus Timer</h6>
          <div id="pomodoro" class="text-center">
            <div id="timer-display" class="display-5">25:00</div>
            <div class="mt-2">
              <button class="btn btn-sm btn-primary" id="start-btn">Start</button>
              <button class="btn btn-sm btn-outline-secondary" id="stop-btn">Stop</button>
              <button class="btn btn-sm btn-link" id="reset-btn">Reset</button>
            </div>
            <div class="small text-muted mt-2">
              Focus sessions help improve consistency.
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    const labels = <?= json_encode($labels) ?>;
    const values = <?= json_encode($values) ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>