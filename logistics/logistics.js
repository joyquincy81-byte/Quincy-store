document.querySelectorAll('[data-filter]').forEach(function (input) {
  input.addEventListener('input', function () {
    var query = input.value.toLowerCase();
    document.querySelectorAll('[data-row]').forEach(function (row) {
      row.hidden = query && row.textContent.toLowerCase().indexOf(query) < 0;
    });
  });
});
