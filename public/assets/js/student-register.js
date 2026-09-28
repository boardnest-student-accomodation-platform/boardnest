(function () {
  var password = document.getElementById("studentPassword");
  var toggle = document.querySelector("[data-password-toggle]");
  var strengthLabel = document.querySelector("[data-password-strength]");
  var strengthBars = Array.prototype.slice.call(document.querySelectorAll(".registration-strength i"));
  var fileInput = document.querySelector("[data-file-input]");
  var uploadArea = document.querySelector("[data-upload-area]");
  var preview = document.querySelector("[data-file-preview]");
  var fileName = document.querySelector("[data-file-name]");
  var fileSize = document.querySelector("[data-file-size]");
  var removeFile = document.querySelector("[data-file-remove]");

  if (password && toggle) {
    toggle.addEventListener("click", function () {
      var reveal = password.type === "password";
      password.type = reveal ? "text" : "password";
      toggle.textContent = reveal ? "Hide" : "Show";
      toggle.setAttribute("aria-label", reveal ? "Hide password" : "Show password");
    });

    password.addEventListener("input", function () {
      var value = password.value;
      var score = 0;
      if (value.length >= 8) score += 1;
      if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score += 1;
      if (/\d/.test(value)) score += 1;
      if (/[^A-Za-z0-9]/.test(value)) score += 1;
      strengthBars.forEach(function (bar, index) {
        bar.classList.toggle("is-active", index < score);
      });
      strengthLabel.textContent = score < 2 ? "Needs strengthening" : score < 4 ? "Good" : "Strong";
    });
  }

  function showSelectedFile() {
    var file = fileInput && fileInput.files ? fileInput.files[0] : null;
    if (!file) {
      preview.hidden = true;
      return;
    }
    fileName.textContent = file.name;
    fileSize.textContent = (file.size / (1024 * 1024)).toFixed(1) + " MB - Encrypted document store";
    preview.hidden = false;
  }

  if (fileInput) fileInput.addEventListener("change", showSelectedFile);
  if (removeFile) removeFile.addEventListener("click", function () {
    fileInput.value = "";
    showSelectedFile();
  });
  if (uploadArea) {
    ["dragenter", "dragover"].forEach(function (eventName) {
      uploadArea.addEventListener(eventName, function () { uploadArea.classList.add("is-dragging"); });
    });
    ["dragleave", "drop"].forEach(function (eventName) {
      uploadArea.addEventListener(eventName, function () { uploadArea.classList.remove("is-dragging"); });
    });
  }
})();
