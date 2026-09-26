document.addEventListener("DOMContentLoaded", function () {
  /*
   * ==========================================
   * ADMIN NOTIFICATIONS
   * ==========================================
   */

  const adminNotificationButton = document.getElementById(
    "admin-notification-button",
  );

  if (adminNotificationButton) {
    adminNotificationButton.addEventListener("click", function () {
      alert("You have new admin notifications.");
    });
  }

  /*
   * ==========================================
   * ADMIN VIEW ALL BUTTONS
   * ==========================================
   */

  const adminViewButtons = document.querySelectorAll("[data-admin-action]");

  adminViewButtons.forEach(function (adminButton) {
    adminButton.addEventListener("click", function () {
      const adminAction = adminButton.getAttribute("data-admin-action");

      if (adminAction === "registrations") {
        alert("Registration Approvals page will open here.");
      }

      if (adminAction === "listings") {
        alert("Listing Approvals page will open here.");
      }

      if (adminAction === "complaints") {
        alert("Complaint Moderation page will open here.");
      }
    });
  });

  /*
   * ==========================================
   * ADMIN REVIEW BUTTONS
   * ==========================================
   */

  const adminReviewButtons = document.querySelectorAll("[data-admin-review]");

  adminReviewButtons.forEach(function (adminButton) {
    adminButton.addEventListener("click", function () {
      const adminItem = adminButton.getAttribute("data-admin-review");

      alert("Review page for " + adminItem + " will open here.");
    });
  });

  /*
   * ==========================================
   * ADMIN ASSIGN BUTTONS
   * ==========================================
   */

  const adminAssignButtons = document.querySelectorAll("[data-admin-assign]");

  adminAssignButtons.forEach(function (adminButton) {
    adminButton.addEventListener("click", function () {
      const adminItem = adminButton.getAttribute("data-admin-assign");

      alert("Field Agent assignment for " + adminItem + " will open here.");
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  /*
    |--------------------------------------------------------------------------
    | Mobile sidebar
    |--------------------------------------------------------------------------
    */

  const mobileMenuButton = document.getElementById("adminMobileMenuButton");
  const sidebar = document.querySelector(".admin-sidebar");

  if (mobileMenuButton && sidebar) {
    mobileMenuButton.addEventListener("click", function () {
      sidebar.classList.toggle("admin-sidebar--open");
    });
  }

  /*
    |--------------------------------------------------------------------------
    | Modal helpers
    |--------------------------------------------------------------------------
    */

  function openModal(modal) {
    if (!modal) {
      return;
    }

    modal.classList.add("admin-modal--open");
    modal.setAttribute("aria-hidden", "false");

    document.body.classList.add("admin-modal-open");
  }

  function closeModal(modal) {
    if (!modal) {
      return;
    }

    modal.classList.remove("admin-modal--open");
    modal.setAttribute("aria-hidden", "true");

    const openModals = document.querySelectorAll(
      ".admin-modal.admin-modal--open",
    );

    if (openModals.length === 0) {
      document.body.classList.remove("admin-modal-open");
    }
  }

  /*
    |--------------------------------------------------------------------------
    | View complaint modal
    |--------------------------------------------------------------------------
    */

  const detailsModal = document.getElementById("complaintDetailsModal");

  const viewButtons = document.querySelectorAll(".admin-view-complaint");

  viewButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const complaintId = button.dataset.complaintId;
      const complainant = button.dataset.complainant;
      const landlord = button.dataset.landlord;
      const listing = button.dataset.listing;
      const category = button.dataset.category;
      const description = button.dataset.description;

      const complaintIdElement = document.getElementById("detailsComplaintId");

      const categoryElement = document.getElementById("detailsCategory");

      const complainantElement = document.getElementById("detailsComplainant");

      const landlordElement = document.getElementById("detailsLandlord");

      const listingElement = document.getElementById("detailsListing");

      const descriptionElement = document.getElementById("detailsDescription");

      if (complaintIdElement) {
        complaintIdElement.textContent = complaintId;
      }

      if (categoryElement) {
        categoryElement.textContent = category;
      }

      if (complainantElement) {
        complainantElement.textContent = complainant;
      }

      if (landlordElement) {
        landlordElement.textContent = landlord;
      }

      if (listingElement) {
        listingElement.textContent = listing;
      }

      if (descriptionElement) {
        descriptionElement.textContent = description;
      }

      openModal(detailsModal);
    });
  });

  /*
    |--------------------------------------------------------------------------
    | Assign complaint modal
    |--------------------------------------------------------------------------
    */

  const assignModal = document.getElementById("assignComplaintModal");

  const assignButtons = document.querySelectorAll(".admin-assign-complaint");

  assignButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const complaintId = button.dataset.complaintId;
      const description = button.dataset.description;

      const complaintIdInput = document.getElementById("assignComplaintId");

      const descriptionInput = document.getElementById(
        "assignComplaintDescription",
      );

      const agentSelect = document.getElementById("fieldAgent");

      const notesInput = document.getElementById("adminNotes");

      if (complaintIdInput) {
        complaintIdInput.value = complaintId;
      }

      if (descriptionInput) {
        descriptionInput.value = description;
      }

      if (agentSelect) {
        agentSelect.value = "";
      }

      if (notesInput) {
        notesInput.value = "";
      }

      openModal(assignModal);
    });
  });

  /*
    |--------------------------------------------------------------------------
    | Close buttons
    |--------------------------------------------------------------------------
    */

  const closeButtons = document.querySelectorAll("[data-close-modal]");

  closeButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const modal = button.closest(".admin-modal");

      closeModal(modal);
    });
  });

  /*
    |--------------------------------------------------------------------------
    | Close modal with Escape
    |--------------------------------------------------------------------------
    */

  document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") {
      return;
    }

    const openModalElement = document.querySelector(
      ".admin-modal.admin-modal--open",
    );

    if (openModalElement) {
      closeModal(openModalElement);
    }
  });

  /*
    |--------------------------------------------------------------------------
    | Confirm assignment
    |--------------------------------------------------------------------------
    |
    | Assignment logic is intentionally NOT implemented yet.
    |
    */

  const confirmAssignmentButton = document.getElementById(
    "confirmAssignmentButton",
  );

  if (confirmAssignmentButton) {
    confirmAssignmentButton.addEventListener("click", function () {
      const agentSelect = document.getElementById("fieldAgent");

      if (!agentSelect || !agentSelect.value) {
        alert("Please select a field agent.");

        return;
      }

      alert("Assignment functionality will be implemented later.");
    });
  }
});

/* ============================================================
   FIELD AGENTS
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {
  const filterButton = document.getElementById("fieldAgentFilterBtn");

  const filterPanel = document.getElementById("fieldAgentFilterPanel");

  const statusFilter = document.getElementById("agentStatusFilter");

  const modeFilter = document.getElementById("agentModeFilter");

  const cityFilter = document.getElementById("agentCityFilter");

  const table = document.getElementById("fieldAgentsTable");

  const resultCount = document.getElementById("fieldAgentResultCount");

  if (!table) {
    return;
  }

  /* ------------------------------------------------------------
       Show / hide filter panel
       ------------------------------------------------------------ */

  if (filterButton && filterPanel) {
    filterButton.addEventListener("click", function () {
      filterPanel.hidden = !filterPanel.hidden;
    });
  }

  /* ------------------------------------------------------------
       Apply filters
       ------------------------------------------------------------ */

  function applyAgentFilters() {
    const rows = table.querySelectorAll("tbody tr.admin-agent-row");

    const selectedStatus = statusFilter ? statusFilter.value : "all";

    const selectedMode = modeFilter ? modeFilter.value : "all";

    const selectedCity = cityFilter
      ? cityFilter.value.trim().toLowerCase()
      : "";

    let visibleCount = 0;

    rows.forEach(function (row) {
      const rowStatus = row.dataset.status || "";

      const rowMode = row.dataset.mode || "";

      const rowCity = row.dataset.city || "";

      const statusMatches =
        selectedStatus === "all" || rowStatus === selectedStatus;

      const modeMatches = selectedMode === "all" || rowMode === selectedMode;

      const cityMatches = selectedCity === "" || rowCity.includes(selectedCity);

      const shouldShow = statusMatches && modeMatches && cityMatches;

      row.style.display = shouldShow ? "" : "none";

      if (shouldShow) {
        visibleCount++;
      }
    });

    if (resultCount) {
      resultCount.textContent = "Showing " + visibleCount + " agents";
    }
  }

  if (statusFilter) {
    statusFilter.addEventListener("change", applyAgentFilters);
  }

  if (modeFilter) {
    modeFilter.addEventListener("change", applyAgentFilters);
  }

  if (cityFilter) {
    cityFilter.addEventListener("input", applyAgentFilters);
  }

  /* ------------------------------------------------------------
       Export
       ------------------------------------------------------------ */

  const exportButton = document.getElementById("fieldAgentExportBtn");

  if (exportButton) {
    exportButton.addEventListener("click", function () {
      const rows = table.querySelectorAll("tbody tr.admin-agent-row");

      let csv = "";

      csv +=
        "Agent Name,Assigned City,Status,Mode," +
        "Verifications,Rating,Last Active\n";

      rows.forEach(function (row) {
        if (row.style.display === "none") {
          return;
        }

        const cells = row.querySelectorAll("td");

        if (cells.length < 8) {
          return;
        }

        const name = cells[0].querySelector(".admin-agent-name__text");

        const nameText = name ? name.textContent.trim() : "";

        const city = cells[1].textContent.trim();

        const status = cells[2].textContent.trim();

        const mode = cells[3].textContent.trim();

        const verifications = cells[4].textContent.trim();

        const rating = cells[6].textContent.trim();

        const lastActive = cells[7].textContent.trim();

        csv +=
          '"' +
          nameText +
          '",' +
          '"' +
          city +
          '",' +
          '"' +
          status +
          '",' +
          '"' +
          mode +
          '",' +
          '"' +
          verifications +
          '",' +
          '"' +
          rating +
          '",' +
          '"' +
          lastActive +
          '"\n';
      });

      const blob = new Blob([csv], {
        type: "text/csv;charset=utf-8;",
      });

      const url = URL.createObjectURL(blob);

      const link = document.createElement("a");

      link.href = url;
      link.download = "boardnest-field-agents.csv";

      document.body.appendChild(link);

      link.click();

      document.body.removeChild(link);

      URL.revokeObjectURL(url);
    });
  }
});

/* =========================================================
   AREA PROFILES
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  const panel = document.getElementById("editPanel");

  const form = document.getElementById("areaProfileForm");

  const panelTitle = document.getElementById("panelTitle");

  const cityIdInput = document.getElementById("areaCityId");

  const citySelect = document.getElementById("areaCity");

  const safetySelect = document.getElementById("safetySelect");

  const descriptionInput = document.getElementById("areaDescription");

  const transportCheckboxes = document.querySelectorAll(
    "[data-transport-option]",
  );

  const amenityCheckboxes = document.querySelectorAll("[data-amenity-option]");

  const editButtons = document.querySelectorAll("[data-area-edit]");

  const addButton = document.querySelector("[data-area-add]");

  const closeButton = document.querySelector("[data-area-close]");

  /*
    |--------------------------------------------------------------------------
    | Open Panel
    |--------------------------------------------------------------------------
    */

  function openAreaPanel(data) {
    if (!panel) {
      return;
    }

    panel.classList.add("admin-area-panel-open");

    if (data.cityId) {
      cityIdInput.value = data.cityId;

      citySelect.value = data.cityId;

      citySelect.disabled = true;
    } else {
      cityIdInput.value = "";

      citySelect.value = "";

      citySelect.disabled = false;
    }

    panelTitle.textContent = data.cityName
      ? "Edit " + data.cityName
      : "Add Area Profile";

    safetySelect.value = data.status || "Under Review";

    descriptionInput.value = data.description || "";

    /*
        |--------------------------------------------------------------------------
        | Reset Transport Checkboxes
        |--------------------------------------------------------------------------
        */

    transportCheckboxes.forEach(function (checkbox) {
      checkbox.checked = false;
    });

    /*
        |--------------------------------------------------------------------------
        | Set Transport Checkboxes
        |--------------------------------------------------------------------------
        */

    const transports = (data.transport || "")
      .split(",")
      .map(function (item) {
        return item.trim();
      })
      .filter(function (item) {
        return item !== "";
      });

    transportCheckboxes.forEach(function (checkbox) {
      checkbox.checked = transports.includes(checkbox.value);
    });

    /*
        |--------------------------------------------------------------------------
        | Reset Amenity Checkboxes
        |--------------------------------------------------------------------------
        */

    amenityCheckboxes.forEach(function (checkbox) {
      checkbox.checked = false;
    });

    /*
        |--------------------------------------------------------------------------
        | Set Amenity Checkboxes
        |--------------------------------------------------------------------------
        */

    const amenities = (data.amenities || "")
      .split(",")
      .map(function (item) {
        return item.trim();
      })
      .filter(function (item) {
        return item !== "";
      });

    amenityCheckboxes.forEach(function (checkbox) {
      checkbox.checked = amenities.includes(checkbox.value);
    });
  }

  /*
    |--------------------------------------------------------------------------
    | Close Panel
    |--------------------------------------------------------------------------
    */

  function closeAreaPanel() {
    if (!panel) {
      return;
    }

    panel.classList.remove("admin-area-panel-open");
  }

  /*
    |--------------------------------------------------------------------------
    | Edit Buttons
    |--------------------------------------------------------------------------
    */

  editButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      openAreaPanel({
        cityId: button.dataset.cityId,

        cityName: button.dataset.cityName,

        status: button.dataset.status,

        transport: button.dataset.transport,

        amenities: button.dataset.amenities,

        description: button.dataset.description,
      });
    });
  });

  /*
    |--------------------------------------------------------------------------
    | Add New Area
    |--------------------------------------------------------------------------
    */

  if (addButton) {
    addButton.addEventListener("click", function () {
      openAreaPanel({
        cityId: "",

        cityName: "",

        status: "Under Review",

        transport: "",

        amenities: "",

        description: "",
      });
    });
  }

  /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */

  if (closeButton) {
    closeButton.addEventListener("click", closeAreaPanel);
  }

  /*
    |--------------------------------------------------------------------------
    | ESC Key
    |--------------------------------------------------------------------------
    */

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeAreaPanel();
    }
  });

  /*
    |--------------------------------------------------------------------------
    | Form Submit
    |--------------------------------------------------------------------------
    |
    | The city select is disabled when editing.
    | Disabled fields are not submitted by browsers.
    |
    | Therefore the hidden city_id field remains enabled.
    |
    */

  if (form) {
    form.addEventListener("submit", function () {
      if (citySelect.disabled) {
        cityIdInput.value = citySelect.value;
      }
    });
  }
});
