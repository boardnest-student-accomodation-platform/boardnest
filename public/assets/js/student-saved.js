(function () {
  var dialog = document.getElementById("removeSavedDialog");
  var forms = document.querySelectorAll("[data-remove-saved-form]");
  if (!dialog || !forms.length) return;

  var listingName = dialog.querySelector("[data-remove-listing-name]");
  var cancelButton = dialog.querySelector("[data-remove-cancel]");
  var confirmButton = dialog.querySelector("[data-remove-confirm]");
  var pendingForm = null;
  var lastTrigger = null;

  function closeDialog() {
    dialog.close();
  }

  forms.forEach(function (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      pendingForm = form;
      lastTrigger = event.submitter || form.querySelector("button[type='submit']");
      listingName.textContent = form.getAttribute("data-listing-title") || "this listing";
      dialog.showModal();
      cancelButton.focus();
    });
  });

  cancelButton.addEventListener("click", closeDialog);

  confirmButton.addEventListener("click", function () {
    if (!pendingForm) return;
    confirmButton.disabled = true;
    pendingForm.submit();
  });

  dialog.addEventListener("cancel", function (event) {
    event.preventDefault();
    closeDialog();
  });

  dialog.addEventListener("click", function (event) {
    if (event.target === dialog) closeDialog();
  });

  dialog.addEventListener("close", function () {
    pendingForm = null;
    confirmButton.disabled = false;
    if (lastTrigger) lastTrigger.focus();
  });
})();
