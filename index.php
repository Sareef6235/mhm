<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Monthly Plan Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Malayalam:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="app-shell page-enter">
    <header class="topbar glass">
      <div>
        <h1>Monthly Plan Management</h1>
        <p>Modern single-page planning dashboard</p>
      </div>
      <div class="right-actions">
        <label class="theme-switch">
          <input type="checkbox" id="themeToggle" />
          <span class="slider"></span>
        </label>
        <button id="newPlanBtn" class="btn primary"><i class="fa-solid fa-plus"></i> Add Plan</button>
      </div>
    </header>

    <section class="kpi-grid">
      <article class="kpi-card glass"><h3>Total Plans</h3><p id="totalPlans">0</p></article>
      <article class="kpi-card glass"><h3>Active Month</h3><p id="activeMonth">All Months</p></article>
      <article class="kpi-card glass"><h3>Subjects</h3><p id="subjectCount">0</p></article>
    </section>

    <section class="panel glass">
      <div class="panel-controls">
        <div class="search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input id="searchInput" type="text" placeholder="Live search..." />
        </div>
        <select id="monthFilter" class="select">
          <option value="">All Months</option>
          <option>January</option><option>February</option><option>March</option><option>April</option>
          <option>May</option><option>June</option><option>July</option><option>August</option>
          <option>September</option><option>October</option><option>November</option><option>December</option>
        </select>
      </div>

      <div id="skeleton" class="skeleton-wrap"></div>

      <div class="table-card">
        <div class="table-wrap">
          <table id="plansTable">
            <thead>
              <tr>
                <th>Week</th><th>Period</th><th>Subject</th><th>Lesson</th><th>Details</th><th>Activities</th><th>Smart Date</th><th>Exam Date</th><th>Actions</th>
              </tr>
            </thead>
            <tbody id="tableBody"></tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <div class="modal-backdrop" id="planModal" aria-hidden="true">
    <div class="modal glass">
      <div class="modal-head">
        <h3 id="modalTitle">Create Plan</h3>
        <button class="icon-btn" data-close-modal><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="planForm">
        <input type="hidden" name="id" id="editId" />
        <div class="step active" data-step="1">
          <div class="field"><select name="month" required><option value="">Select Month</option><option>January</option><option>February</option><option>March</option><option>April</option><option>May</option><option>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option></select></div>
          <div class="field"><select name="ustad" id="ustadSelect" required></select></div>
          <div class="field"><input name="week" required placeholder="Week" /></div>
          <div class="field"><input name="period" required placeholder="Period" /></div>
        </div>
        <div class="step" data-step="2">
          <div class="field"><select name="subject" id="subjectSelect" required></select></div>
          <div class="field"><input name="lesson" required placeholder="Lesson" /></div>
          <div class="field"><input name="details" placeholder="Details" /></div>
          <div class="field"><input name="activity" placeholder="Activities" /></div>
        </div>
        <div class="step" data-step="3">
          <div class="field"><input type="date" name="smart" /></div>
          <div class="field"><input type="date" name="exam" /></div>
          <p class="helper">Ctrl + Enter to save</p>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn ghost" id="prevStep">Back</button>
          <button type="button" class="btn ghost" id="nextStep">Next</button>
          <button type="submit" class="btn primary">Save Plan</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-backdrop" id="confirmModal" aria-hidden="true">
    <div class="modal small glass">
      <h3>Delete this plan?</h3>
      <p>This action cannot be undone.</p>
      <div class="modal-actions compact">
        <button type="button" class="btn ghost" data-close-confirm>Cancel</button>
        <button type="button" class="btn danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>

  <div id="toastHost"></div>

  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
  <script src="app.js"></script>
</body>
</html>
