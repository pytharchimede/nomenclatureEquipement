// ===============================
// Gestion de l'import Excel SPL (clic & drag & drop + import AJAX)
// ===============================
function handleExcelImportSPL() {
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

    fetch("request/import_template_spl.php", {
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
        // Recharge la page pour afficher les nouvelles données
        setTimeout(() => window.location.reload(), 1200);
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
// Filtrage du tableau SPL
// ===============================
function filterTableSPL() {
  const table = document.querySelector("table.table");
  if (!table) return;
  const tbody = table.querySelector("tbody");
  const filterRow = document.getElementById("filter-row");
  const filterInputs = filterRow.querySelectorAll("input, select");

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
  });
}

// ===============================
// Initialisation globale au chargement du DOM
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  handleExcelImportSPL();

  // Filtres
  const filterRow = document.getElementById("filter-row");
  if (filterRow) {
    filterRow.querySelectorAll("input, select").forEach((input) => {
      input.addEventListener("input", filterTableSPL);
      input.addEventListener("change", filterTableSPL);
    });
  }
});
