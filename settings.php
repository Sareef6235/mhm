<?php
require_once __DIR__ . '/db.php';

function jsonResponse(bool $success, string $message = '', array $data = []): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ]);
    exit;
}

function cleanString(?string $value): string
{
    return trim((string) $value);
}

$action = $_GET['action'] ?? '';

if ($action !== '') {
    try {
        if ($action === 'list_ustads') {
            $stmt = $pdo->query('SELECT id, name, created_at FROM ustads ORDER BY name ASC');
            jsonResponse(true, 'Ustad list loaded.', $stmt->fetchAll());
        }

        if ($action === 'add_ustad') {
            $name = cleanString($_POST['name'] ?? '');
            if ($name === '' || mb_strlen($name) > 150) {
                jsonResponse(false, 'Please enter a valid ustad name (max 150 chars).');
            }

            $stmt = $pdo->prepare('INSERT INTO ustads (name) VALUES (?)');
            $stmt->execute([$name]);
            jsonResponse(true, 'Ustad added successfully.');
        }

        if ($action === 'update_ustad') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = cleanString($_POST['name'] ?? '');

            if ($id < 1 || $name === '' || mb_strlen($name) > 150) {
                jsonResponse(false, 'Invalid ustad update payload.');
            }

            $stmt = $pdo->prepare('UPDATE ustads SET name = ? WHERE id = ?');
            $stmt->execute([$name, $id]);
            jsonResponse(true, 'Ustad updated successfully.');
        }

        if ($action === 'delete_ustad') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id < 1) {
                jsonResponse(false, 'Invalid ustad id.');
            }

            $stmt = $pdo->prepare('DELETE FROM ustads WHERE id = ?');
            $stmt->execute([$id]);
            jsonResponse(true, 'Ustad deleted successfully.');
        }

        if ($action === 'list_weeks') {
            $stmt = $pdo->query('SELECT id, week_name, created_at FROM weeks ORDER BY id ASC');
            jsonResponse(true, 'Week list loaded.', $stmt->fetchAll());
        }

        if ($action === 'add_week') {
            $weekName = cleanString($_POST['week_name'] ?? '');
            if ($weekName === '' || mb_strlen($weekName) > 50) {
                jsonResponse(false, 'Please enter a valid week name (max 50 chars).');
            }

            $stmt = $pdo->prepare('INSERT INTO weeks (week_name) VALUES (?)');
            $stmt->execute([$weekName]);
            jsonResponse(true, 'Week added successfully.');
        }

        if ($action === 'update_week') {
            $id = (int) ($_POST['id'] ?? 0);
            $weekName = cleanString($_POST['week_name'] ?? '');

            if ($id < 1 || $weekName === '' || mb_strlen($weekName) > 50) {
                jsonResponse(false, 'Invalid week update payload.');
            }

            $stmt = $pdo->prepare('UPDATE weeks SET week_name = ? WHERE id = ?');
            $stmt->execute([$weekName, $id]);
            jsonResponse(true, 'Week updated successfully.');
        }

        if ($action === 'delete_week') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id < 1) {
                jsonResponse(false, 'Invalid week id.');
            }

            $stmt = $pdo->prepare('DELETE FROM weeks WHERE id = ?');
            $stmt->execute([$id]);
            jsonResponse(true, 'Week deleted successfully.');
        }

        jsonResponse(false, 'Unknown action.');
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000) {
            jsonResponse(false, 'Duplicate value is not allowed.');
        }

        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Plan Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="header glass">
        <button class="btn" onclick="window.location.href='index.php'">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <h2><i class="fa-solid fa-gear"></i> Settings</h2>
    </div>

    <div class="panel glass">
        <div class="tabs">
            <button type="button" class="btn tab active" data-tab="ustads">USTADS</button>
            <button type="button" class="btn tab" data-tab="weeks">WEEKS</button>
        </div>

        <section id="ustadsPanel">
            <div class="row">
                <input type="text" id="ustadName" placeholder="Add Ustad Name">
                <button class="btn primary" id="addUstadBtn">Add Ustad</button>
            </div>
            <input type="text" id="ustadSearch" placeholder="Search ustads..." style="margin-top:10px; width: 100%;">
            <div class="table-wrap" style="margin-top:10px;">
                <table>
                    <thead><tr><th>Name</th><th>Actions</th></tr></thead>
                    <tbody id="ustadsBody"></tbody>
                </table>
            </div>
        </section>

        <section id="weeksPanel" class="hidden">
            <div class="row">
                <input type="text" id="weekName" placeholder="Add Week Name (e.g. Week 5)">
                <button class="btn primary" id="addWeekBtn">Add Week</button>
            </div>
            <input type="text" id="weekSearch" placeholder="Search weeks..." style="margin-top:10px; width: 100%;">
            <div class="table-wrap" style="margin-top:10px;">
                <table>
                    <thead><tr><th>Week</th><th>Actions</th></tr></thead>
                    <tbody id="weeksBody"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="toastHost"></div>

<script>
let ustads = [];
let weeks = [];

async function api(action, payload = {}) {
    const body = new URLSearchParams(payload);
    const response = await fetch(`settings.php?action=${encodeURIComponent(action)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body,
    });

    const json = await response.json();
    if (!json.success) throw new Error(json.message || 'Request failed.');
    return json;
}

async function apiGet(action) {
    const response = await fetch(`settings.php?action=${encodeURIComponent(action)}`);
    const json = await response.json();
    if (!json.success) throw new Error(json.message || 'Request failed.');
    return json.data;
}

function toast(message) {
    const host = document.getElementById('toastHost');
    const node = document.createElement('div');
    node.className = 'toast';
    node.textContent = message;
    host.appendChild(node);
    setTimeout(() => node.remove(), 2200);
}

function buildRow(item, labelField, type) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input value="${item[labelField].replace(/"/g, '&quot;')}" data-input /></td>
        <td class="actions">
            <button class="btn primary" data-save>Save</button>
            <button class="btn danger" data-delete>Delete</button>
        </td>
    `;

    tr.querySelector('[data-save]').addEventListener('click', async () => {
        const value = tr.querySelector('[data-input]').value.trim();
        if (!value) return toast('Value is required.');

        if (type === 'ustad') await api('update_ustad', { id: item.id, name: value });
        if (type === 'week') await api('update_week', { id: item.id, week_name: value });

        toast('Updated successfully.');
        await refreshAll();
    });

    tr.querySelector('[data-delete]').addEventListener('click', async () => {
        const ok = confirm('Delete this item?');
        if (!ok) return;

        if (type === 'ustad') await api('delete_ustad', { id: item.id });
        if (type === 'week') await api('delete_week', { id: item.id });

        toast('Deleted successfully.');
        await refreshAll();
    });

    return tr;
}

function renderUstads() {
    const filter = document.getElementById('ustadSearch').value.trim().toLowerCase();
    const body = document.getElementById('ustadsBody');
    body.innerHTML = '';

    ustads
        .filter((u) => u.name.toLowerCase().includes(filter))
        .forEach((u) => body.appendChild(buildRow(u, 'name', 'ustad')));
}

function renderWeeks() {
    const filter = document.getElementById('weekSearch').value.trim().toLowerCase();
    const body = document.getElementById('weeksBody');
    body.innerHTML = '';

    weeks
        .filter((w) => w.week_name.toLowerCase().includes(filter))
        .forEach((w) => body.appendChild(buildRow(w, 'week_name', 'week')));
}

async function refreshAll() {
    [ustads, weeks] = await Promise.all([
        apiGet('list_ustads'),
        apiGet('list_weeks')
    ]);

    renderUstads();
    renderWeeks();
}

document.getElementById('addUstadBtn').addEventListener('click', async () => {
    try {
        const input = document.getElementById('ustadName');
        const name = input.value.trim();
        if (!name) return toast('Ustad name is required.');
        await api('add_ustad', { name });
        input.value = '';
        toast('Ustad added.');
        await refreshAll();
    } catch (e) {
        toast(e.message);
    }
});

document.getElementById('addWeekBtn').addEventListener('click', async () => {
    try {
        const input = document.getElementById('weekName');
        const week_name = input.value.trim();
        if (!week_name) return toast('Week name is required.');
        await api('add_week', { week_name });
        input.value = '';
        toast('Week added.');
        await refreshAll();
    } catch (e) {
        toast(e.message);
    }
});

document.getElementById('ustadSearch').addEventListener('input', renderUstads);
document.getElementById('weekSearch').addEventListener('input', renderWeeks);

document.querySelectorAll('[data-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-tab]').forEach((x) => x.classList.remove('active'));
        tab.classList.add('active');

        const target = tab.getAttribute('data-tab');
        document.getElementById('ustadsPanel').classList.toggle('hidden', target !== 'ustads');
        document.getElementById('weeksPanel').classList.toggle('hidden', target !== 'weeks');
    });
});

refreshAll().catch((e) => toast(e.message));
</script>
</body>
</html>
