// ===============================
// Gestion du loader d'export
// ===============================
function showExportLoader() {
  const modal = new bootstrap.Modal(
    document.getElementById("exportLoadingModal")
  );
  modal.show();

  let progress = 0;
  const progressBar = document.getElementById("exportProgressBar");
  const progressText = document.getElementById("exportProgressText");
  const closeBtn = document.getElementById("closeExportModalBtn");
  closeBtn.style.display = "none";
  progressBar.style.width = "0%";
  progressBar.textContent = "0%";
  progressText.textContent = "Préparation de l’export, veuillez patienter...";

  // Animation de la barre de progression
  const interval = setInterval(() => {
    progress += Math.floor(Math.random() * 10) + 5;
    if (progress > 100) progress = 100;
    progressBar.style.width = progress + "%";
    progressBar.textContent = progress + "%";
    if (progress >= 100) {
      clearInterval(interval);
      progressText.textContent = "Téléchargement en cours...";
    }
  }, 400);

  // Affiche le bouton "Fermer" après 10 secondes
  setTimeout(() => {
    closeBtn.style.display = "";
  }, 10000);

  closeBtn.onclick = function () {
    modal.hide();
  };
}

// ===============================
// Filtrage du tableau et MAJ des graphiques
// ===============================
function filterTable() {
  const table = document.querySelector("table.table");
  if (!table) return;
  const tbody = table.querySelector("tbody");
  const filterRow = document.getElementById("filter-row");
  const filterInputs = filterRow.querySelectorAll("input, select");
  const totalDiv = document.querySelector(
    '.mini-card div > div[style*="font-size:1.3rem"][style*="font-weight:700;"]'
  );
  const miniPie = Chart.getChart("miniPie");
  const miniBar = Chart.getChart("miniBar");
  const exportFilteredBtn = document.getElementById("exportFilteredBtn");

  let total = 0;
  let catCounts = {};
  tbody.querySelectorAll("tr").forEach((tr) => {
    let show = true;
    filterInputs.forEach((input, idx) => {
      let val = input.value.trim().toLowerCase();
      let cell = tr.children[idx];
      if (!cell) return;
      if (input.type === "select-one") {
        if (val && cell.textContent.trim().toLowerCase() !== val) show = false;
      } else if (input.type === "date") {
        if (val && cell.textContent.trim().substr(0, 10) !== val) show = false;
      } else {
        if (val && !cell.textContent.toLowerCase().includes(val)) show = false;
      }
    });
    tr.style.display = show ? "" : "none";
    if (show) {
      total++;
      let cat = tr.children[6].textContent.trim();
      catCounts[cat] = (catCounts[cat] || 0) + 1;
    }
  });
  // Mise à jour du total affiché
  if (totalDiv) totalDiv.textContent = total;

  // Mise à jour des graphiques
  if (miniPie && miniBar) {
    miniPie.data.datasets[0].data = miniPie.data.labels.map(
      (lab) => catCounts[lab] || 0
    );
    miniPie.update();
    miniBar.data.datasets[0].data = miniBar.data.labels.map(
      (lab) => catCounts[lab] || 0
    );
    miniBar.update();
  }

  // Affichage du bouton d'export filtré
  if (exportFilteredBtn) {
    exportFilteredBtn.style.display = total > 0 ? "" : "none";
  }
}

// ===============================
// Export des données filtrées
// ===============================
function exportFilteredData() {
  const rows = Array.from(
    document.querySelectorAll("table.table tbody tr")
  ).filter((tr) => tr.style.display !== "none");
  if (rows.length === 0) {
    alert("Aucune donnée à exporter !");
    return;
  }
  const data = rows.map((tr) =>
    Array.from(tr.children).map((td) => td.textContent.trim())
  );

  // Récupération des critères de filtre non vides
  const filterRow = document.getElementById("filter-row");
  const filterInputs = filterRow.querySelectorAll("input, select");
  const columnLabels = [
    "Code Equipement",
    "Désignation équipement",
    "Repère équipement",
    "Fabricant",
    "Type d'objet",
    "N° série fabricant",
    "Catégorie équipement",
    "Date création",
  ];
  const filters = [];
  filterInputs.forEach((input, idx) => {
    if (input.value && input.value.trim() !== "") {
      filters.push({
        col: idx,
        label: columnLabels[idx],
        value: input.value,
      });
    }
  });

  // Création et soumission du formulaire POST
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "request/export_equipements.php?type=excel&filtered=1";
  form.style.display = "none";

  const inputData = document.createElement("input");
  inputData.type = "hidden";
  inputData.name = "filtered_data";
  inputData.value = JSON.stringify(data);
  form.appendChild(inputData);

  const inputFilters = document.createElement("input");
  inputFilters.type = "hidden";
  inputFilters.name = "filters";
  inputFilters.value = JSON.stringify(filters);
  form.appendChild(inputFilters);

  document.body.appendChild(form);
  form.submit();
}

// ===============================
// Gestion de la sélection et suppression multiple
// ===============================
function updateDeleteBtn() {
  const deleteBtn = document.getElementById("deleteSelectedBtn");
  const checked = document.querySelectorAll(".equip-checkbox:checked");
  if (!deleteBtn) return;
  deleteBtn.style.display = checked.length > 0 ? "" : "none";
  if (checked.length > 0) {
    deleteBtn.innerHTML = `<span class="material-icons">delete</span>Supprimer la sélection (${checked.length})`;
  }
}

function handleSelectAll() {
  const selectAll = document.getElementById("selectAllEquip");
  const checkboxes = document.querySelectorAll(".equip-checkbox");
  if (!selectAll) return;
  selectAll.addEventListener("change", function () {
    checkboxes.forEach((cb) => {
      if (cb.closest("tr").style.display !== "none") {
        cb.checked = selectAll.checked;
      }
    });
    updateDeleteBtn();
  });
}

function handleCheckboxChange() {
  document.querySelectorAll(".equip-checkbox").forEach((cb) => {
    cb.addEventListener("change", updateDeleteBtn);
  });
}

function handleDeleteSelected() {
  const deleteBtn = document.getElementById("deleteSelectedBtn");
  if (!deleteBtn) return;
  deleteBtn.addEventListener("click", function () {
    const checked = Array.from(
      document.querySelectorAll(".equip-checkbox:checked")
    );
    if (checked.length === 0) return;
    if (
      !confirm(
        `Voulez-vous vraiment supprimer ${checked.length} équipement(s) ? Cette action est irréversible.`
      )
    )
      return;

    // Récupère les repères à supprimer
    const reperes = checked.map((cb) => cb.value);

    // Envoi AJAX vers le script PHP de suppression
    fetch("request/equipement_delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ reperes }),
    })
      .then((r) => r.json())
      .then((res) => {
        if (res.success) {
          // Retire les lignes supprimées du DOM
          checked.forEach((cb) => cb.closest("tr").remove());
          updateDeleteBtn();
          alert(res.message || "Suppression réussie !");
        } else {
          alert(res.message || "Erreur lors de la suppression.");
        }
      })
      .catch(() => alert("Erreur réseau lors de la suppression."));
  });
}

// Ajoute l'écouteur sur chaque ligne du tableau pour ouvrir le formulaire de modification
function handleRowClick() {
  const tbody = document.querySelector("table.table tbody");
  if (!tbody) return;
  tbody.querySelectorAll("tr").forEach((tr) => {
    tr.addEventListener("click", function (e) {
      // Ignore le clic sur la case à cocher
      if (e.target.classList.contains("equip-checkbox")) return;

      // Récupère les cellules de la ligne
      const tds = tr.querySelectorAll("td");
      if (tds.length < 9) return;

      // Ouvre le modal d'ajout/modification
      const modal = new bootstrap.Modal(
        document.getElementById("addEquipModal")
      );
      modal.show();

      // Change le titre et le bouton
      document.getElementById("addEquipModalLabel").textContent =
        "Modifier un équipement";
      const form = document.querySelector("#addEquipModal form");
      form.action = "request/equipement_edit.php"; // À adapter selon votre script de modification
      form.querySelector('button[type="submit"]').textContent = "Enregistrer";

      // Remplit les champs du formulaire avec les valeurs de la ligne
      form.code_equipement.value = tds[1].textContent.trim();
      form.designation_equipement.value = tds[2].textContent.trim();
      form.repere_equipement.value = tds[3].textContent.trim();
      form.fabricant.value = tds[4].textContent.trim();
      form.type_objet.value = tds[5].textContent.trim();
      // form.designation_type.value = ... // à adapter si colonne présente
      form.numero_serie_fabricant.value = tds[6].textContent.trim();
      // form.numero_piece_fabricant.value = ... // à adapter si colonne présente
      // form.poste_technique.value = ... // à adapter si colonne présente
      // form.designation_poste_technique.value = ... // à adapter si colonne présente
      // form.poste_travail_principal.value = ... // à adapter si colonne présente
      form.categorie_equipement.value = tds[7].textContent.trim();
      // form.centre_de_couts.value = ... // à adapter si colonne présente
      form.date_creation.value = tds[8].textContent.trim();

      // Ajoutez un champ caché pour l'identifiant si besoin
      if (!form.querySelector('input[name="repere_original"]')) {
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "repere_original";
        form.appendChild(hidden);
      }
      form.repere_original.value = tds[3].textContent.trim();
    });
  });
}

// ===============================
// Gestion de l'import Excel (clic & drag & drop + import AJAX)
// ===============================
function handleExcelImport() {
  const dropArea = document.getElementById("drop-area");
  const fileInput = document.getElementById("excelFileInput");
  const fileNameDiv = document.getElementById("fileName");
  const importBtn = document.getElementById("startImportBtn");
  const progressBarContainer = document.getElementById(
    "importProgressBarContainer"
  );
  const progressBar = document.getElementById("importProgressBar");
  const importResult = document.getElementById("importResult");

  if (!dropArea || !fileInput || !fileNameDiv || !importBtn) return;

  // Clic sur la zone = clic sur l'input file
  dropArea.addEventListener("click", function () {
    fileInput.click();
  });

  // Affichage du nom du fichier et activation du bouton
  fileInput.addEventListener("change", function () {
    if (fileInput.files.length > 0) {
      fileNameDiv.textContent = fileInput.files[0].name;
      importBtn.disabled = false;
    } else {
      fileNameDiv.textContent = "";
      importBtn.disabled = true;
    }
  });

  // Drag & drop
  dropArea.addEventListener("dragover", function (e) {
    e.preventDefault();
    dropArea.classList.add("border-primary");
  });
  dropArea.addEventListener("dragleave", function (e) {
    e.preventDefault();
    dropArea.classList.remove("border-primary");
  });
  dropArea.addEventListener("drop", function (e) {
    e.preventDefault();
    dropArea.classList.remove("border-primary");
    if (e.dataTransfer.files.length > 0) {
      fileInput.files = e.dataTransfer.files;
      fileInput.dispatchEvent(new Event("change"));
    }
  });

  // Clic sur le bouton Importer
  importBtn.addEventListener("click", function () {
    if (fileInput.files.length === 0) return;

    // Prépare l'affichage du chargement
    if (progressBarContainer) progressBarContainer.style.display = "";
    if (progressBar) {
      progressBar.style.width = "0%";
      progressBar.textContent = "0%";
    }
    if (importResult) importResult.innerHTML = "";

    const formData = new FormData();
    formData.append("excel_file", fileInput.files[0]);

    // Animation de la barre de progression (simulée)
    let progress = 0;
    const interval = setInterval(() => {
      progress += Math.floor(Math.random() * 15) + 10;
      if (progress > 90) progress = 90;
      if (progressBar) {
        progressBar.style.width = progress + "%";
        progressBar.textContent = progress + "%";
      }
    }, 300);

    fetch("request/equipement_import.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((res) => {
        clearInterval(interval);
        if (progressBar) {
          progressBar.style.width = "100%";
          progressBar.textContent = "100%";
        }
        if (importResult) {
          if (res.success) {
            importResult.innerHTML = `<div class="alert alert-success">${res.message}</div>`;
          } else {
            importResult.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
          }
          // Affiche le détail du log si présent
          if (res.log && Array.isArray(res.log)) {
            importResult.innerHTML +=
              "<ul style='max-height:200px;overflow:auto;font-size:0.95em;'>";
            res.log.forEach((l) => {
              importResult.innerHTML += `<li>${l.message}</li>`;
            });
            importResult.innerHTML += "</ul>";
          }
        }
        // Optionnel : reset le formulaire après import
        fileInput.value = "";
        fileNameDiv.textContent = "";
        importBtn.disabled = true;
      })
      .catch(() => {
        clearInterval(interval);
        if (importResult) {
          importResult.innerHTML = `<div class="alert alert-danger">Erreur réseau lors de l'import.</div>`;
        }
      });
  });
}

// ===============================
// Initialisation globale au chargement du DOM
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  // ...autres initialisations...
  handleRowClick();
  handleSelectAll();
  handleCheckboxChange();
  handleDeleteSelected();
  handleExcelImport(); // <-- Ajout de l'import Excel
  // ...autres initialisations...
});
