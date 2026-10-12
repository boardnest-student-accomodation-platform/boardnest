(function () {
  var button = document.querySelector("[data-dashboard-menu]");
  var navigation = document.querySelector("[data-dashboard-nav]");
  if (!button || !navigation) return;
  button.addEventListener("click", function () {
    var open = navigation.classList.toggle("is-open");
    button.setAttribute("aria-expanded", open ? "true" : "false");
  });
  navigation.addEventListener("click", function () {
    navigation.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
  });

  var drawer = document.getElementById("student-profile");
  var profileToggles = document.querySelectorAll("[data-profile-toggle]");
  var profileClosers = document.querySelectorAll("[data-profile-close]");
  var backdrop = document.querySelector(".student-profile-backdrop");

  function setProfileOpen(open) {
    if (!drawer) return;
    drawer.classList.toggle("is-open", open);
    drawer.setAttribute("aria-hidden", open ? "false" : "true");
    document.body.classList.toggle("has-profile-drawer", open);
    profileToggles.forEach(function (control) {
      control.setAttribute("aria-expanded", open ? "true" : "false");
    });
    if (backdrop) backdrop.hidden = !open;
  }

  profileToggles.forEach(function (control) {
    control.addEventListener("click", function (event) {
      event.preventDefault();
      setProfileOpen(!drawer.classList.contains("is-open"));
    });
  });

  profileClosers.forEach(function (control) {
    control.addEventListener("click", function () { setProfileOpen(false); });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") setProfileOpen(false);
  });
})();
