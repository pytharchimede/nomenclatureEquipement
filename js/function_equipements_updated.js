/**
 * Gestion des équipements avec pagination infinie et repère comme clé primaire
 * Version mise à jour pour assurer la cohérence du système
 */

// ===============================
// Variables globales
// ===============================
let currentPage = 1;
let isLoading = false;
let hasMoreData = true;
let currentFilters = {};
let searchTimeout = null;

// ===============================
// Gestion de la pagination infinie
// ===============================

/**
 * Charge les équipements avec pagination
 * @param {number} page - Page à charger
 * @param {boolean} append - Si true, ajoute à la liste existante, sinon remplace
 */
async function loadEquipements(page = 1, append = false) {
  if (isLoading) return;

  isLoading = true;
  showLoadingIndicator();

  try {
    // Construction de l'URL avec paramètres
    const params = new URLSearchParams({
      page: page,
      limit: 50,
      ...currentFilters,
    });

    const response = await fetch(`request/equipements_paginated.php?${params}`);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || "Erreur lors du chargement");
    }

    const tbody = document.querySelector("#equipements-table tbody");
    if (!tbody) return;

    if (!append) {
      tbody.innerHTML = "";
      currentPage = 1;
    }

    // Ajout des lignes d'équipements
    result.data.forEach((equipement) => {
      const row = createEquipementRow(equipement);
      tbody.appendChild(row);
    });

    // Mise à jour des variables de pagination
    hasMoreData = result.pagination.hasMore;
    currentPage = result.pagination.page;

    // Mise à jour des statistiques
    updateStatistics(result.pagination.total);

    // Mise à jour des statistiques détaillées si c'est une nouvelle recherche
    if (!append) {
      updateDetailedStats();
    }

    // Mise à jour du message de pagination
    updatePaginationInfo(result.pagination);
  } catch (error) {
    console.error("Erreur lors du chargement des équipements:", error);
    showAlert("Erreur lors du chargement des équipements", "danger");
  } finally {
    isLoading = false;
    hideLoadingIndicator();
  }
}

/**
 * Crée une ligne de tableau pour un équipement
 * @param {Object} equipement - Données de l'équipement
 * @returns {HTMLElement} - Ligne de tableau
 */
function createEquipementRow(equipement) {
  const row = document.createElement("tr");
  row.innerHTML = `
        <td>
            <input type="checkbox" class="equip-checkbox" value="${escapeHtml(
              equipement.repere_equipement
            )}">
        </td>
        <td data-repere="${escapeHtml(
          equipement.repere_equipement
        )}">${escapeHtml(equipement.code_equipement || "")}</td>
        <td>${escapeHtml(equipement.designation_equipement || "")}</td>
        <td><strong>${escapeHtml(
          equipement.repere_equipement || ""
        )}</strong></td>
        <td>${escapeHtml(equipement.fabricant || "")}</td>
        <td>${escapeHtml(equipement.type_objet || "")}</td>
        <td>${escapeHtml(equipement.numero_serie_fabricant || "")}</td>
        <td>${escapeHtml(equipement.categorie_equipement || "")}</td>
        <td>${escapeHtml(equipement.date_creation || "")}</td>
    `;

  // Ajout des événements
  row.addEventListener("click", (e) => {
    if (e.target.type !== "checkbox") {
      handleRowClick(row, equipement.repere_equipement);
    }
  });

  return row;
}

/**
 * Échappe les caractères HTML pour éviter les injections XSS
 * @param {string} text - Texte à échapper
 * @returns {string} - Texte échappé
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// ===============================
// Gestion du filtrage
// ===============================

/**
 * Applique les filtres de recherche
 */
function applyFilters() {
  // Récupération des valeurs de filtres
  const searchInput = document.querySelector("#search-input");
  const fabricantFilter = document.querySelector("#fabricant-filter");
  const typeFilter = document.querySelector("#type-filter");
  const categorieFilter = document.querySelector("#categorie-filter");
  const sourceFilter = document.querySelector("#source-filter");

  currentFilters = {};

  if (searchInput && searchInput.value.trim()) {
    currentFilters.search = searchInput.value.trim();
  }

  if (fabricantFilter && fabricantFilter.value) {
    currentFilters.fabricant = fabricantFilter.value;
  }

  if (typeFilter && typeFilter.value) {
    currentFilters.type_objet = typeFilter.value;
  }

  if (categorieFilter && categorieFilter.value) {
    currentFilters.categorie_equipement = categorieFilter.value;
  }

  if (sourceFilter && sourceFilter.value) {
    currentFilters.source = sourceFilter.value;
  }

  // Rechargement avec les nouveaux filtres
  hasMoreData = true;
  loadEquipements(1, false);
}

/**
 * Recherche avec délai (debounce)
 * @param {string} searchTerm - Terme de recherche
 */
function debouncedSearch(searchTerm) {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    currentFilters.search = searchTerm;
    hasMoreData = true;
    loadEquipements(1, false);
  }, 300);
}

// ===============================
// Gestion du défilement infini
// ===============================

/**
 * Initialise le défilement infini
 */
function initInfiniteScroll() {
  const container = document.querySelector(".table-responsive");
  if (!container) return;

  container.addEventListener("scroll", () => {
    // Vérifier si on approche du bas du conteneur
    if (
      container.scrollTop + container.clientHeight >=
      container.scrollHeight - 100
    ) {
      if (hasMoreData && !isLoading) {
        loadEquipements(currentPage + 1, true);
      }
    }
  });
}

// ===============================
// Gestion des événements
// ===============================

/**
 * Gère le clic sur une ligne d'équipement
 * @param {HTMLElement} row - Ligne cliquée
 * @param {string} repere - Repère de l'équipement
 */
function handleRowClick(row, repere) {
  // Supprime la sélection précédente
  document.querySelectorAll("tr.table-warning").forEach((r) => {
    r.classList.remove("table-warning");
  });

  // Sélectionne la ligne actuelle
  row.classList.add("table-warning");

  // Charge les détails de l'équipement pour édition
  loadEquipementDetails(repere);
}

/**
 * Charge les détails d'un équipement pour édition
 * @param {string} repere - Repère de l'équipement
 */
async function loadEquipementDetails(repere) {
  try {
    const response = await fetch(
      `request/equipement_get.php?repere=${encodeURIComponent(repere)}`
    );
    const equipement = await response.json();

    if (equipement.success === false) {
      throw new Error(equipement.message || "Équipement non trouvé");
    }

    // Remplir le formulaire d'édition
    fillEditForm(equipement);

    // Afficher le modal d'édition
    const editModal = new bootstrap.Modal(document.getElementById("editModal"));
    editModal.show();
  } catch (error) {
    console.error("Erreur lors du chargement des détails:", error);
    showAlert(
      "Erreur lors du chargement des détails de l'équipement",
      "danger"
    );
  }
}

/**
 * Remplit le formulaire d'édition avec les données de l'équipement
 * @param {Object} equipement - Données de l'équipement
 */
function fillEditForm(equipement) {
  const form = document.getElementById("editForm");
  if (!form) return;

  // Mapping des champs
  const fields = [
    "code_equipement",
    "designation_equipement",
    "repere_equipement",
    "fabricant",
    "type_objet",
    "designation_type",
    "numero_serie_fabricant",
    "numero_piece_fabricant",
    "poste_technique",
    "designation_poste_technique",
    "poste_travail_principal",
    "categorie_equipement",
    "centre_de_couts",
    "date_creation",
  ];

  fields.forEach((field) => {
    const input = form.querySelector(`[name="${field}"]`);
    if (input) {
      input.value = equipement[field] || "";
    }
  });

  // Stocker le repère original pour la mise à jour
  const originalRepereInput = form.querySelector('[name="repere_original"]');
  if (originalRepereInput) {
    originalRepereInput.value = equipement.repere_equipement || "";
  }
}

// ===============================
// Gestion des exports et imports
// ===============================

/**
 * Gestion de l'export Excel avec suivi temps réel
 */
function handleExcelExport() {
  showExportLoader();
  startProgressiveExport("excel", "all");
}

/**
 * Gestion de l'export PDF avec filtres
 */
function handlePdfExport() {
  showExportLoader();

  // Construction de l'URL avec les filtres actuels
  const params = new URLSearchParams(currentFilters);
  params.append("type", "pdf");

  // Utilisation de l'iframe pour éviter la redirection
  const iframe = document.createElement("iframe");
  iframe.style.display = "none";
  iframe.src = `request/export_equipements.php?${params}`;
  document.body.appendChild(iframe);

  // Supprimer l'iframe après un délai
  setTimeout(() => {
    document.body.removeChild(iframe);
  }, 5000);
}

/**
 * Démarre l'export progressif avec Server-Sent Events
 */
function startProgressiveExport(type, exportType) {
  const params = new URLSearchParams(currentFilters);
  params.append("type", type);
  params.append("export_type", exportType);

  // Récupération des équipements sélectionnés si nécessaire
  if (exportType === "selected") {
    const checkboxes = document.querySelectorAll(".equip-checkbox:checked");
    const selectedReperes = Array.from(checkboxes).map((cb) => cb.value);
    if (selectedReperes.length === 0) {
      showAlert("Aucun équipement sélectionné", "warning");
      return;
    }
    params.append("selected_reperes", selectedReperes.join(","));
  }

  const url = `request/export_equipements_progressive.php?${params}`;

  // Utilisation d'EventSource pour le suivi temps réel
  if (typeof EventSource !== "undefined") {
    const eventSource = new EventSource(url);

    eventSource.onmessage = function (event) {
      try {
        const data = JSON.parse(event.data);
        updateExportProgress(data);

        if (data.step === "complete") {
          eventSource.close();
          // Démarrer le téléchargement
          if (data.details && data.details.download_url) {
            const downloadLink = document.createElement("a");
            downloadLink.href = data.details.download_url;
            downloadLink.download = data.details.filename;
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
          }
        } else if (data.step === "error") {
          eventSource.close();
          showAlert("Erreur lors de l'export: " + data.message, "danger");
        }
      } catch (e) {
        console.error("Erreur parsing SSE data:", e);
      }
    };

    eventSource.onerror = function (event) {
      console.error("Erreur EventSource:", event);
      eventSource.close();

      // Fallback: téléchargement direct
      const iframe = document.createElement("iframe");
      iframe.style.display = "none";
      iframe.src = url.replace(
        "export_equipements_progressive.php",
        "export_equipements.php"
      );
      document.body.appendChild(iframe);

      setTimeout(() => {
        document.body.removeChild(iframe);
      }, 5000);
    };
  } else {
    // Fallback pour les navigateurs sans EventSource
    const iframe = document.createElement("iframe");
    iframe.style.display = "none";
    iframe.src = url.replace(
      "export_equipements_progressive.php",
      "export_equipements.php"
    );
    document.body.appendChild(iframe);

    setTimeout(() => {
      document.body.removeChild(iframe);
    }, 5000);
  }
}

/**
 * Export de la sélection filtrée avec choix du format
 */
function handleFilteredExport() {
  const checkboxes = document.querySelectorAll(".equip-checkbox:checked");

  if (checkboxes.length === 0) {
    showAlert("Aucun équipement sélectionné pour l'export", "warning");
    return;
  }

  // Afficher un modal de choix du format
  const modalHtml = `
    <div class="modal fade" id="exportFormatModal" tabindex="-1">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Choisir le format d'export</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <p>Exporter ${checkboxes.length} équipements sélectionnés :</p>
            <div class="d-grid gap-2">
              <button class="btn btn-success" onclick="exportSelected('excel')">
                <span class="material-icons">file_download</span> Excel
              </button>
              <button class="btn btn-danger" onclick="exportSelected('pdf')">
                <span class="material-icons">picture_as_pdf</span> PDF
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  // Ajouter le modal au DOM s'il n'existe pas
  if (!document.getElementById("exportFormatModal")) {
    document.body.insertAdjacentHTML("beforeend", modalHtml);
  }

  const modal = new bootstrap.Modal(
    document.getElementById("exportFormatModal")
  );
  modal.show();
}

/**
 * Exporte la sélection dans le format choisi avec suivi temps réel
 */
function exportSelected(format) {
  const checkboxes = document.querySelectorAll(".equip-checkbox:checked");

  if (checkboxes.length === 0) {
    showAlert("Aucun équipement sélectionné", "warning");
    return;
  }

  // Fermer le modal de choix de format
  const formatModal = bootstrap.Modal.getInstance(
    document.getElementById("exportFormatModal")
  );
  if (formatModal) formatModal.hide();

  // Démarrer l'export avec le système temps réel
  showExportLoader();

  if (format === "excel") {
    startProgressiveExport("excel", "selected");
  } else {
    // Pour PDF, utiliser l'ancien système car pas encore implémenté
    const reperes = Array.from(checkboxes).map((cb) => cb.value);

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "request/export_equipements.php";
    form.style.display = "none";

    const repereInput = document.createElement("input");
    repereInput.type = "hidden";
    repereInput.name = "selected_reperes";
    repereInput.value = JSON.stringify(reperes);
    form.appendChild(repereInput);

    const typeInput = document.createElement("input");
    typeInput.type = "hidden";
    typeInput.name = "type";
    typeInput.value = format;
    form.appendChild(typeInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }
}

/**
 * Gestion de l'import Excel avec drag & drop et progression
 */
async function handleAdvancedExcelImport() {
  const fileInput = document.querySelector("#excelFileInput");
  const file = fileInput.files[0];

  if (!file) {
    showAlert("Veuillez sélectionner un fichier Excel", "warning");
    return;
  }

  const formData = new FormData();
  formData.append("excel_file", file);

  const progressBarContainer = document.querySelector(
    "#importProgressBarContainer"
  );
  const progressBar = document.querySelector("#importProgressBar");
  const importResult = document.querySelector("#importResult");
  const startBtn = document.querySelector("#startImportBtn");

  try {
    // Affichage de la barre de progression
    progressBarContainer.style.display = "block";
    startBtn.disabled = true;
    importResult.innerHTML = '<div class="text-info">Import en cours...</div>';

    // Simulation de progression (car l'import peut être long)
    let progress = 0;
    const progressInterval = setInterval(() => {
      progress += Math.random() * 15;
      if (progress > 90) progress = 90;
      progressBar.style.width = progress + "%";
      progressBar.textContent = Math.round(progress) + "%";
    }, 500);

    const response = await fetch("request/equipement_import_optimized.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    // Arrêter la simulation et compléter la barre
    clearInterval(progressInterval);
    progressBar.style.width = "100%";
    progressBar.textContent = "100%";

    if (result.success) {
      importResult.innerHTML = `
        <div class="alert alert-success">
          <strong>Import réussi !</strong><br>
          • ${result.imported || 0} équipements importés<br>
          • ${result.updated || 0} équipements mis à jour<br>
          • ${result.duplicates || 0} doublons ignorés<br>
          • Temps de traitement : ${result.execution_time || "N/A"}
        </div>
      `;

      // Recharger la liste après un délai
      setTimeout(() => {
        hasMoreData = true;
        loadEquipements(1, false);

        // Fermer le modal après 2 secondes
        setTimeout(() => {
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("importModal")
          );
          if (modal) modal.hide();
        }, 2000);
      }, 1000);
    } else {
      throw new Error(result.message || "Erreur lors de l'import");
    }
  } catch (error) {
    console.error("Erreur lors de l'import:", error);
    importResult.innerHTML = `
      <div class="alert alert-danger">
        <strong>Erreur d'import :</strong><br>
        ${error.message}
      </div>
    `;
  } finally {
    startBtn.disabled = false;
  }
}

/**
 * Gestion de l'import Excel (version simple pour compatibilité)
 * @param {Event} event - Événement de changement de fichier
 */
async function handleExcelImport(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Mettre à jour l'affichage du nom de fichier
  const fileName = document.querySelector("#fileName");
  if (fileName) {
    fileName.textContent = `Fichier sélectionné : ${file.name}`;
  }

  // Activer le bouton d'import
  const startBtn = document.querySelector("#startImportBtn");
  if (startBtn) {
    startBtn.disabled = false;
  }
}

/**
 * Suppression des équipements sélectionnés
 */
async function handleDeleteSelected() {
  const checkboxes = document.querySelectorAll(".equip-checkbox:checked");

  if (checkboxes.length === 0) {
    showAlert("Aucun équipement sélectionné", "warning");
    return;
  }

  if (
    !confirm(
      `Êtes-vous sûr de vouloir supprimer ${checkboxes.length} équipement(s) ?`
    )
  ) {
    return;
  }

  const reperes = Array.from(checkboxes).map((cb) => cb.value);

  try {
    showLoadingIndicator();

    const response = await fetch("request/equipement_delete.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ reperes: reperes }),
    });

    const result = await response.json();

    if (result.success) {
      showAlert(
        `${reperes.length} équipement(s) supprimé(s) avec succès`,
        "success"
      );

      // Recharger la liste
      hasMoreData = true;
      loadEquipements(1, false);
    } else {
      throw new Error(result.message || "Erreur lors de la suppression");
    }
  } catch (error) {
    console.error("Erreur lors de la suppression:", error);
    showAlert("Erreur lors de la suppression des équipements", "danger");
  } finally {
    hideLoadingIndicator();
  }
}

// ===============================
// Fonctions utilitaires
// ===============================

/**
 * Affiche un indicateur de chargement
 */
function showLoadingIndicator() {
  const indicator = document.getElementById("loading-indicator");
  if (indicator) {
    indicator.style.display = "block";
  }
}

/**
 * Masque l'indicateur de chargement
 */
function hideLoadingIndicator() {
  const indicator = document.getElementById("loading-indicator");
  if (indicator) {
    indicator.style.display = "none";
  }
}

/**
 * Affiche une alerte
 * @param {string} message - Message à afficher
 * @param {string} type - Type d'alerte (success, warning, danger, info)
 */
function showAlert(message, type = "info") {
  const alertsContainer = document.getElementById("alerts-container");
  if (!alertsContainer) return;

  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  alertsContainer.appendChild(alertDiv);

  // Suppression automatique après 5 secondes
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

/**
 * Met à jour les statistiques détaillées avec les filtres appliqués
 */
async function updateDetailedStats() {
  try {
    // Construction de l'URL avec les mêmes filtres que la recherche
    const params = new URLSearchParams(currentFilters);

    const response = await fetch(`request/equipements_stats.php?${params}`);
    const result = await response.json();

    if (result.success) {
      const stats = result.stats;

      // Mise à jour des cartes de statistiques
      updateStatCard("total-equipements", stats.total, "Total équipements");
      updateStatCard(
        "top-famille",
        stats.topFamille || "Aucune",
        "Famille la + présente"
      );
      updateStatCard("max-famille", stats.maxFamille, "Max dans une famille");
      updateStatCard(
        "non-affectes",
        stats.nonAffectes,
        "Non affectés à une famille"
      );

      // Mise à jour du graphique en secteurs si disponible
      if (window.miniPieChart && stats.familleLabels.length > 0) {
        window.miniPieChart.data.labels = stats.familleLabels.slice(0, 5); // Top 5
        window.miniPieChart.data.datasets[0].data = stats.familleData.slice(
          0,
          5
        );
        window.miniPieChart.update();
      }
    }
  } catch (error) {
    console.error("Erreur lors de la mise à jour des statistiques:", error);
  }
}

/**
 * Met à jour une carte de statistique
 * @param {string} cardId - ID de la carte (utilise data-stat attribute)
 * @param {string|number} value - Valeur à afficher
 * @param {string} label - Label de la statistique
 */
function updateStatCard(cardId, value, label) {
  // Recherche par data-stat ou structure de carte
  let valueElement = document.querySelector(`[data-stat="${cardId}"]`);

  if (!valueElement) {
    // Fallback : recherche par structure de carte
    const cards = document.querySelectorAll(".mini-card");
    cards.forEach((card) => {
      const labelElement = card.querySelector(".text-muted");
      if (
        labelElement &&
        labelElement.textContent.includes(label.split(" ")[0])
      ) {
        valueElement = card.querySelector("div:first-child div:first-child");
      }
    });
  }

  if (valueElement) {
    valueElement.textContent =
      typeof value === "number" ? value.toLocaleString() : value;
  }
}

/**
 * Met à jour les statistiques affichées
 * @param {number} total - Nombre total d'équipements
 */
function updateStatistics(total) {
  const totalElement = document.querySelector(
    '[data-stat="total-equipements"]'
  );
  if (totalElement) {
    totalElement.textContent = total.toLocaleString();
  }
}

/**
 * Met à jour les informations de pagination
 * @param {Object} pagination - Informations de pagination
 */
function updatePaginationInfo(pagination) {
  const paginationInfo = document.getElementById("pagination-info");
  if (paginationInfo) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(start + pagination.limit - 1, pagination.total);

    paginationInfo.textContent = `Affichage de ${start} à ${end} sur ${pagination.total} équipements`;
  }
}

/**
 * Gestion du loader d'export avec support temps réel
 */
function showExportLoader() {
  const modal = new bootstrap.Modal(
    document.getElementById("exportLoadingModal")
  );
  modal.show();

  const progressBar = document.getElementById("exportProgressBar");
  const progressText = document.getElementById("exportProgressText");
  const closeBtn = document.getElementById("closeExportModalBtn");

  closeBtn.style.display = "none";
  progressBar.style.width = "0%";
  progressBar.textContent = "0%";
  progressText.textContent = "Préparation de l'export, veuillez patienter...";

  // Affiche automatiquement le bouton "Fermer" après 5 secondes
  let autoShowCloseTimeout = setTimeout(() => {
    closeBtn.style.display = "";
  }, 5000);

  closeBtn.onclick = function () {
    if (autoShowCloseTimeout) {
      clearTimeout(autoShowCloseTimeout);
    }
    modal.hide();
  };
}

/**
 * Met à jour le progrès de l'export en temps réel
 */
function updateExportProgress(data) {
  const progressBar = document.getElementById("exportProgressBar");
  const progressText = document.getElementById("exportProgressText");
  const closeBtn = document.getElementById("closeExportModalBtn");

  if (!progressBar || !progressText) return;

  // Mise à jour de la barre de progression
  if (data.percent !== null && data.percent !== undefined) {
    progressBar.style.width = data.percent + "%";
    progressBar.textContent = Math.round(data.percent) + "%";
  }

  // Mise à jour du texte
  let message = data.message;
  if (data.details && typeof data.details === "string") {
    message += ` - ${data.details}`;
  } else if (data.details && data.details.total_processed) {
    message += ` (${data.details.total_processed} traités)`;
  }

  // Ajout du timestamp pour plus de transparence
  if (data.timestamp) {
    message += ` [${data.timestamp}]`;
  }

  progressText.textContent = message;

  // Affichage du bouton fermer quand terminé
  if (data.step === "complete" || data.step === "error") {
    closeBtn.style.display = "";

    if (data.step === "complete") {
      progressText.innerHTML = `
        <div class="text-success">
          <strong>✓ Export terminé avec succès !</strong><br>
          ${
            data.details && data.details.total_processed
              ? data.details.total_processed + " équipements exportés"
              : ""
          }
          ${
            data.details && data.details.filename
              ? "<br>Fichier: " + data.details.filename
              : ""
          }
        </div>
      `;
    } else if (data.step === "error") {
      progressText.innerHTML = `
        <div class="text-danger">
          <strong>✗ Erreur lors de l'export</strong><br>
          ${data.message}
        </div>
      `;
    }
  }
}

// ===============================
// Initialisation
// ===============================

/**
 * Initialise tous les événements et fonctionnalités
 */
function initEquipementsPage() {
  // Chargement initial des données
  loadEquipements(1, false);

  // Initialisation du défilement infini
  initInfiniteScroll();

  // Événements de filtrage
  const searchInput = document.querySelector("#search-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      debouncedSearch(e.target.value.trim());
    });
  }

  // Événements pour les filtres select
  const filters = [
    "#fabricant-filter",
    "#type-filter",
    "#categorie-filter",
    "#source-filter",
  ];
  filters.forEach((selector) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener("change", applyFilters);
    }
  });

  // Événements pour les boutons d'action
  const exportExcelBtn = document.querySelector("#export-excel-btn");
  if (exportExcelBtn) {
    exportExcelBtn.addEventListener("click", (e) => {
      e.preventDefault();
      handleExcelExport();
    });
  }

  const exportPdfBtn = document.querySelector("#export-pdf-btn");
  if (exportPdfBtn) {
    exportPdfBtn.addEventListener("click", (e) => {
      e.preventDefault();
      handlePdfExport();
    });
  }

  const exportFilteredBtn = document.querySelector("#exportFilteredBtn");
  if (exportFilteredBtn) {
    exportFilteredBtn.addEventListener("click", handleFilteredExport);
  }

  const importInput = document.querySelector("#excelFileInput");
  if (importInput) {
    importInput.addEventListener("change", handleExcelImport);
  }

  // Gestion du bouton d'import
  const startImportBtn = document.querySelector("#startImportBtn");
  if (startImportBtn) {
    startImportBtn.addEventListener("click", handleAdvancedExcelImport);
  }

  // Gestion du drag & drop pour l'import
  const dropArea = document.querySelector("#drop-area");
  if (dropArea && importInput) {
    // Clic sur la zone de drop ouvre le sélecteur
    dropArea.addEventListener("click", () => {
      importInput.click();
    });

    // Gestion des événements de drag & drop
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ["dragenter", "dragover"].forEach((eventName) => {
      dropArea.addEventListener(
        eventName,
        () => {
          dropArea.classList.add("border-success");
          dropArea.style.backgroundColor = "#e8f5e8";
        },
        false
      );
    });

    ["dragleave", "drop"].forEach((eventName) => {
      dropArea.addEventListener(
        eventName,
        () => {
          dropArea.classList.remove("border-success");
          dropArea.style.backgroundColor = "#f8fafd";
        },
        false
      );
    });

    dropArea.addEventListener(
      "drop",
      (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
          importInput.files = files;
          const event = new Event("change", { bubbles: true });
          importInput.dispatchEvent(event);
        }
      },
      false
    );
  }

  const deleteSelectedBtn = document.querySelector("#deleteSelectedBtn");
  if (deleteSelectedBtn) {
    deleteSelectedBtn.addEventListener("click", handleDeleteSelected);
  }

  // Bouton de réinitialisation des filtres
  const resetFiltersBtn = document.querySelector("#reset-filters");
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
      // Réinitialise tous les filtres
      const searchInput = document.querySelector("#search-input");
      const fabricantFilter = document.querySelector("#fabricant-filter");
      const typeFilter = document.querySelector("#type-filter");
      const categorieFilter = document.querySelector("#categorie-filter");
      const sourceFilter = document.querySelector("#source-filter");

      if (searchInput) searchInput.value = "";
      if (fabricantFilter) fabricantFilter.value = "";
      if (typeFilter) typeFilter.value = "";
      if (categorieFilter) categorieFilter.value = "";
      if (sourceFilter) sourceFilter.value = "";

      currentFilters = {};
      hasMoreData = true;
      loadEquipements(1, false);
    });
  }

  // Sélection/déselection de tous les équipements
  const selectAllCheckbox = document.querySelector("#select-all-equipements");
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", (e) => {
      const checkboxes = document.querySelectorAll(".equip-checkbox");
      checkboxes.forEach((cb) => (cb.checked = e.target.checked));
      updateSelectionButtons();
    });
  }

  // Observer les changements de checkbox pour mettre à jour les boutons
  document.addEventListener("change", (e) => {
    if (e.target.classList.contains("equip-checkbox")) {
      updateSelectionButtons();
    }
  });
}

/**
 * Met à jour la visibilité des boutons basés sur la sélection
 */
function updateSelectionButtons() {
  const checkboxes = document.querySelectorAll(".equip-checkbox:checked");
  const deleteBtn = document.querySelector("#deleteSelectedBtn");
  const exportBtn = document.querySelector("#exportFilteredBtn");

  if (deleteBtn) {
    deleteBtn.style.display = checkboxes.length > 0 ? "inline-block" : "none";
  }

  if (exportBtn) {
    exportBtn.style.display = checkboxes.length > 0 ? "inline-block" : "none";
  }
}

// Initialisation automatique au chargement de la page
document.addEventListener("DOMContentLoaded", initEquipementsPage);
