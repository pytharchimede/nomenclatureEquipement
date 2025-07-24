// Variables globales
let currentPage = 1;
let isLoading = false;
let hasMoreData = true;
let currentFilters = {};
let pieChart = null;
let barChart = null;
let lineChart = null;

// Initialisation de la page
document.addEventListener("DOMContentLoaded", function () {
  loadStats();
  loadData(1, true);
  initializeFilters();
  initializeExport();
  initializeImport();

  // Défilement infini
  window.addEventListener("scroll", handleInfiniteScroll);
});

// Chargement des statistiques
async function loadStats() {
  try {
    const response = await fetch("api/template_spl_stats.php");
    const data = await response.json();

    if (data.success) {
      updateMiniCards(data.stats);
      updateCharts(data);
    }
  } catch (error) {
    console.error("Erreur lors du chargement des statistiques:", error);
  }
}

// Mise à jour des mini-cards
function updateMiniCards(stats) {
  document.getElementById("totalLignes").textContent = stats.total_lignes || 0;
  document.getElementById("totalArticles").textContent =
    stats.total_articles_uniques || 0;
  document.getElementById("totalMetiers").textContent =
    stats.total_metiers || 0;
  document.getElementById("totalFabricants").textContent =
    stats.total_fabricants || 0;
}

// Mise à jour des graphiques
function updateCharts(data) {
  // Graphique métiers (secteur)
  updatePieChart(data.metiers);

  // Graphique unités (barres)
  updateBarChart(data.unites);

  // Évolution des imports (ligne)
  updateLineChart(data.evolution_imports);
}

// Graphique secteur métiers
function updatePieChart(metiers) {
  const ctx = document.getElementById("metiersChart");
  if (!ctx) return;

  if (pieChart) {
    pieChart.destroy();
  }

  const labels = metiers.map((m) => m.metier);
  const values = metiers.map((m) => m.total);
  const colors = [
    "#1976D2",
    "#43A047",
    "#FF8F00",
    "#E53935",
    "#8E24AA",
    "#00ACC1",
    "#FB8C00",
    "#C0CA33",
    "#F06292",
    "#26A69A",
  ];

  pieChart = new Chart(ctx, {
    type: "pie",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: colors.slice(0, labels.length),
          borderWidth: 2,
          borderColor: "#fff",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1.5,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

// Graphique barres unités
function updateBarChart(unites) {
  const ctx = document.getElementById("unitesChart");
  if (!ctx) return;

  if (barChart) {
    barChart.destroy();
  }

  const labels = unites.map((u) => u.unite_base);
  const values = unites.map((u) => u.total);

  barChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Nombre d'utilisations",
          data: values,
          backgroundColor: "#1976D2",
          borderColor: "#1565C0",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1.5,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// Graphique évolution
function updateLineChart(evolution) {
  const ctx = document.getElementById("evolutionChart");
  if (!ctx) return;

  if (lineChart) {
    lineChart.destroy();
  }

  const labels = evolution.map((e) => {
    const date = new Date(e.mois + "-01");
    return date.toLocaleDateString("fr-FR", {
      month: "short",
      year: "numeric",
    });
  });
  const imports = evolution.map((e) => e.total_imports);
  const articles = evolution.map((e) => e.nouveaux_articles);

  lineChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total imports",
          data: imports,
          borderColor: "#1976D2",
          backgroundColor: "rgba(25, 118, 210, 0.1)",
          tension: 0.4,
          fill: true,
        },
        {
          label: "Nouveaux articles",
          data: articles,
          borderColor: "#43A047",
          backgroundColor: "rgba(67, 160, 71, 0.1)",
          tension: 0.4,
          fill: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1.5,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// Chargement des données avec pagination
async function loadData(page = 1, reset = false) {
  if (isLoading) return;

  isLoading = true;
  const loadingIndicator = document.getElementById("loadingIndicator");
  if (loadingIndicator) loadingIndicator.style.display = "block";

  try {
    const params = new URLSearchParams({
      page: page,
      limit: 50,
      ...currentFilters,
    });

    const response = await fetch(`api/template_spl_data.php?${params}`);
    const data = await response.json();

    if (data.success) {
      if (reset) {
        document.getElementById("templatesTableBody").innerHTML = "";
        currentPage = 1;
        hasMoreData = true;
      }

      displayData(data.data);
      updatePaginationInfo(data.pagination);

      hasMoreData = data.pagination.has_next;
      currentPage = data.pagination.current_page;
    }
  } catch (error) {
    console.error("Erreur lors du chargement des données:", error);
    showNotification("Erreur lors du chargement des données", "error");
  } finally {
    isLoading = false;
    if (loadingIndicator) loadingIndicator.style.display = "none";
  }
}

// Affichage des données dans le tableau
function displayData(templates) {
  const tbody = document.getElementById("templatesTableBody");

  templates.forEach((tpl) => {
    const row = document.createElement("tr");
    row.className = "fade-in";
    row.innerHTML = `
            <td><span class="badge badge-modern bg-secondary">${escapeHtml(
              tpl.numero || ""
            )}</span></td>
            <td><code class="text-muted">${escapeHtml(
              tpl.code_sap || ""
            )}</code></td>
            <td><span class="badge badge-modern bg-primary">${escapeHtml(
              tpl.code_article
            )}</span></td>
            <td class="text-center"><strong class="text-info">${escapeHtml(
              tpl.quantite
            )}</strong></td>
            <td class="text-truncate" style="max-width: 200px;" title="${escapeHtml(
              tpl.designation_article
            )}">${escapeHtml(tpl.designation_article)}</td>
            <td><span class="badge badge-modern bg-secondary">${escapeHtml(
              tpl.unite_base
            )}</span></td>
            <td><span class="badge badge-modern bg-info">${escapeHtml(
              tpl.metier
            )}</span></td>
            <td><small class="text-muted">${escapeHtml(
              tpl.numero_piece_fabricant || "-"
            )}</small></td>
            <td><span class="badge badge-modern bg-warning text-dark">${escapeHtml(
              tpl.fabricant || "-"
            )}</span></td>
            <td><span class="badge badge-modern bg-success">${escapeHtml(
              tpl.equipement
            )}</span></td>
            <td><small class="text-muted">${formatDate(
              tpl.date_import
            )}</small></td>
        `;
    tbody.appendChild(row);
  });
}

// Initialisation des filtres
function initializeFilters() {
  const filterInputs = document.querySelectorAll(
    "#filterRow input, #filterRow select"
  );

  filterInputs.forEach((input) => {
    input.addEventListener(
      "input",
      debounce(() => {
        applyFilters();
      }, 300)
    );
  });

  // Bouton reset filtres
  document
    .getElementById("resetFilters")
    ?.addEventListener("click", resetFilters);
}

// Application des filtres
function applyFilters() {
  const filterRow = document.getElementById("filterRow");
  const inputs = filterRow.querySelectorAll("input, select");

  currentFilters = {};
  inputs.forEach((input) => {
    if (input.value.trim() !== "") {
      currentFilters[input.name] = input.value.trim();
    }
  });

  loadData(1, true);
}

// Reset des filtres
function resetFilters() {
  const filterInputs = document.querySelectorAll(
    "#filterRow input, #filterRow select"
  );
  filterInputs.forEach((input) => (input.value = ""));
  currentFilters = {};
  loadData(1, true);
}

// Défilement infini
function handleInfiniteScroll() {
  if (isLoading || !hasMoreData) return;

  const scrollPosition = window.innerHeight + window.scrollY;
  const documentHeight = document.documentElement.offsetHeight;

  if (scrollPosition >= documentHeight - 1000) {
    loadData(currentPage + 1, false);
  }
}

// Initialisation de l'export
function initializeExport() {
  document.getElementById("exportExcel")?.addEventListener("click", () => {
    const params = new URLSearchParams(currentFilters);
    window.open(`api/export_template_spl.php?${params}`, "_blank");
  });
}

// Initialisation de l'import
function initializeImport() {
  const dropArea = document.getElementById("dropArea");
  const fileInput = document.getElementById("excelFileInput");
  const importBtn = document.getElementById("startImportBtn");

  // Drag & Drop
  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropArea?.addEventListener(eventName, preventDefaults, false);
  });

  ["dragenter", "dragover"].forEach((eventName) => {
    dropArea?.addEventListener(eventName, highlight, false);
  });

  ["dragleave", "drop"].forEach((eventName) => {
    dropArea?.addEventListener(eventName, unhighlight, false);
  });

  dropArea?.addEventListener("drop", handleDrop, false);
  dropArea?.addEventListener("click", () => fileInput?.click());

  fileInput?.addEventListener("change", handleFileSelect);
  importBtn?.addEventListener("click", startImport);
}

// Gestion du drag & drop
function preventDefaults(e) {
  e.preventDefault();
  e.stopPropagation();
}

function highlight(e) {
  e.currentTarget.classList.add("highlight");
}

function unhighlight(e) {
  e.currentTarget.classList.remove("highlight");
}

function handleDrop(e) {
  const dt = e.dataTransfer;
  const files = dt.files;
  handleFiles(files);
}

function handleFileSelect(e) {
  const files = e.target.files;
  handleFiles(files);
}

function handleFiles(files) {
  if (files.length > 0) {
    const file = files[0];
    document.getElementById("fileName").textContent = file.name;
    document.getElementById("startImportBtn").disabled = false;
  }
}

// Import Excel avec suivi de progression
async function startImport() {
  const fileInput = document.getElementById("excelFileInput");
  const progressContainer = document.getElementById(
    "importProgressBarContainer"
  );
  const progressBar = document.getElementById("importProgressBar");
  const resultDiv = document.getElementById("importResult");
  const importBtn = document.getElementById("startImportBtn");

  if (!fileInput.files.length) {
    showNotification("Veuillez sélectionner un fichier", "error");
    return;
  }

  const formData = new FormData();
  formData.append("excel_file", fileInput.files[0]);

  // Affichage de la progression
  progressContainer.style.display = "block";
  progressBar.style.width = "0%";
  progressBar.querySelector("span").textContent = "0%";
  importBtn.disabled = true;
  importBtn.innerHTML =
    '<span class="material-icons">hourglass_empty</span>Import en cours...';

  // Affichage du message d\'attente pour gros fichiers
  resultDiv.innerHTML = `
        <div class="alert alert-info">
            <span class="material-icons me-2">info</span>
            <strong>Import en cours...</strong><br>
            Traitement du fichier Excel en cours. Cela peut prendre quelques minutes pour les gros fichiers.
        </div>
    `;

  try {
    // Animation de progression réaliste
    let progress = 0;
    const interval = setInterval(() => {
      progress += Math.random() * 8 + 2; // Plus lent et plus réaliste
      if (progress > 85) progress = 85; // S'arrêter à 85% en attendant la réponse
      progressBar.style.width = progress + "%";
      progressBar.querySelector("span").textContent =
        Math.round(progress) + "%";
    }, 500);

    // Requête avec timeout augmenté
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutes timeout

    const response = await fetch("api/import_template_spl.php", {
      method: "POST",
      body: formData,
      signal: controller.signal,
    });

    clearTimeout(timeoutId);
    clearInterval(interval);

    // Compléter la progression
    progressBar.style.width = "100%";
    progressBar.querySelector("span").textContent = "100%";

    const result = await response.json();

    if (result.success) {
      resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <span class="material-icons me-2">check_circle</span>
                    <strong>Import réussi !</strong><br>
                    <div class="row mt-3">
                        <div class="col-lg-3 col-6">
                            <div class="text-center">
                                <i class="material-icons text-success" style="font-size: 1.5rem;">add_circle</i><br>
                                <strong class="text-success">${
                                  result.stats.imported
                                }</strong><br>
                                <small>Template SPL</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="text-center">
                                <i class="material-icons text-primary" style="font-size: 1.5rem;">inventory</i><br>
                                <strong class="text-primary">${
                                  result.stats.imported_nomenclatures || 0
                                }</strong><br>
                                <small>Nomenclatures</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="text-center">
                                <i class="material-icons text-warning" style="font-size: 1.5rem;">content_copy</i><br>
                                <strong class="text-warning">${
                                  result.stats.duplicates +
                                  (result.stats.duplicates_nomenclatures || 0)
                                }</strong><br>
                                <small>Doublons ignorés</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="text-center">
                                <i class="material-icons ${
                                  result.stats.errors > 0
                                    ? "text-danger"
                                    : "text-success"
                                }" style="font-size: 1.5rem;">${
        result.stats.errors > 0 ? "error" : "check"
      }</i><br>
                                <strong class="${
                                  result.stats.errors > 0
                                    ? "text-danger"
                                    : "text-success"
                                }">${result.stats.errors}</strong><br>
                                <small>Erreurs</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-light rounded">
                        <small class="text-muted">
                            <i class="material-icons" style="font-size: 1rem;">info</i>
                            <strong>Détail :</strong> ${
                              result.stats.total_processed
                            } lignes traitées. 
                            Les données SPL ont été importées dans Template SPL ET dans Nomenclatures avec source "SPL".
                        </small>
                    </div>
                </div>
            `; // Afficher les erreurs si présentes
      if (result.errors && result.errors.length > 0) {
        resultDiv.innerHTML += `
                    <div class="alert alert-warning mt-2">
                        <strong>Détail des erreurs:</strong>
                        <ul class="mb-0 mt-2">
                            ${result.errors
                              .map(
                                (error) => `<li><small>${error}</small></li>`
                              )
                              .join("")}
                        </ul>
                    </div>
                `;
      }

      // Recharger les données et stats
      setTimeout(async () => {
        await loadStats();
        await loadData(1, true);
        showNotification("Import terminé avec succès!", "success");
      }, 1000);

      // Fermer le modal après 3 secondes
      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("importModal")
        );
        modal?.hide();
        resetImportForm();
      }, 3000);
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    clearInterval(interval);

    if (error.name === "AbortError") {
      resultDiv.innerHTML = `
                <div class="alert alert-warning">
                    <span class="material-icons me-2">warning</span>
                    <strong>Timeout :</strong> Le fichier est trop volumineux ou le traitement prend trop de temps. 
                    Essayez de diviser votre fichier en plus petites parties.
                </div>
            `;
    } else {
      resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <span class="material-icons me-2">error</span>
                    <strong>Erreur :</strong> ${error.message}
                </div>
            `;
    }
  } finally {
    // Remettre le bouton en état
    importBtn.disabled = false;
    importBtn.innerHTML = '<span class="material-icons">upload</span>Importer';
  }
}

// Reset du formulaire d'import
function resetImportForm() {
  document.getElementById("excelFileInput").value = "";
  document.getElementById("fileName").textContent = "";
  document.getElementById("startImportBtn").disabled = true;
  document.getElementById("importProgressBarContainer").style.display = "none";
  document.getElementById("importResult").innerHTML = "";
}

// Fonctions utilitaires
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return (
    date.toLocaleDateString("fr-FR") +
    " " +
    date.toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" })
  );
}

function updatePaginationInfo(pagination) {
  const info = document.getElementById("paginationInfo");
  if (info) {
    info.textContent = `Page ${pagination.current_page} sur ${pagination.total_pages} (${pagination.total_records} éléments)`;
  }
}

function showNotification(message, type = "info") {
  // Création d'une notification toast
  const toast = document.createElement("div");
  toast.className = `alert alert-${
    type === "error" ? "danger" : type
  } position-fixed`;
  toast.style.cssText =
    "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
  toast.textContent = message;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 5000);
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Toggle sidebar pour mobile
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  sidebar?.classList.toggle("show");
}
