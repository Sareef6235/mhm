<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Monthly Plan</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
    <div class="header glass">
      <button class="menu-item btn" onclick="openSettings()">
        <i class="fa-solid fa-gear"></i> Settings
      </button>
      <h1>Monthly Plan</h1>
    </div>

    <div class="panel glass">
      <h3>Create Monthly Plan</h3>
      <form>
        <div class="row">
          <select name="ustad" id="ustadSelect" required></select>
          <select name="week" id="weekSelect" required></select>
        </div>
      </form>
    </div>

    <div class="panel glass">
      <h3>Search Filters</h3>
      <div class="row">
        <select id="searchUstadSelect"></select>
        <select id="searchWeekSelect"></select>
      </div>
    </div>
  </div>

  <script src="app.js"></script>
</body>
</html>
