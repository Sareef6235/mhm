async function fetchOptions(endpoint) {
  const res = await fetch(endpoint);
  const data = await res.json();
  if (!data.success) throw new Error(data.message || 'Unable to load list');
  return data.data;
}

function renderSelect(select, items, valueKey, labelKey, placeholder) {
  if (!select) return;
  select.innerHTML = '';
  const baseOption = document.createElement('option');
  baseOption.value = '';
  baseOption.textContent = placeholder;
  select.appendChild(baseOption);

  items.forEach((item) => {
    const opt = document.createElement('option');
    opt.value = item[valueKey];
    opt.textContent = item[labelKey];
    select.appendChild(opt);
  });
}

async function loadUstadsAndWeeks() {
  try {
    const [ustads, weeks] = await Promise.all([
      fetchOptions('settings.php?action=list_ustads'),
      fetchOptions('settings.php?action=list_weeks'),
    ]);

    renderSelect(document.getElementById('ustadSelect'), ustads, 'name', 'name', '-- Select Ustad --');
    renderSelect(document.getElementById('weekSelect'), weeks, 'week_name', 'week_name', '-- Select Week --');

    renderSelect(document.getElementById('searchUstadSelect'), ustads, 'name', 'name', 'All Ustads');
    renderSelect(document.getElementById('searchWeekSelect'), weeks, 'week_name', 'week_name', 'All Weeks');
  } catch (error) {
    console.error(error);
  }
}

function openSettings() {
  window.location.href = 'settings.php';
}

document.addEventListener('DOMContentLoaded', loadUstadsAndWeeks);
