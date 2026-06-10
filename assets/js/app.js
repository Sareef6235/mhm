document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-check-all]').forEach((trigger) => {
    trigger.addEventListener('change', () => {
      document.querySelectorAll(trigger.dataset.checkAll).forEach((box) => { box.checked = trigger.checked; });
    });
  });
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (event) => {
      if (!confirm(el.dataset.confirm)) event.preventDefault();
    });
  });
});
