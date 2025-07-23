/**
 * JavaScript moderne pour la gestion des nomenclatures
 * Système complet avec pagination, filtres, export temps réel, import optimisé
 * Compatible avec de gros volumes (26 531+ lignes)
 */

// Variables globales
let currentPage = 1;
let hasMoreData = true;
let isLoading = false;
let currentFilters = {};
let searchTimeout = null;

// ===============================
// Fonctions utilitaires
// ===============================

/**
 * Affiche une alerte dans le conteneur dédié
 * @param {string} message - Message à afficher
 * @param {string} type - Type d'alerte (success, danger, warning, info)
 */
function showAlert(message, type = "info") {
  const container =
    document.getElementById("alerts-container") || createAlertsContainer();

  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;

  container.appendChild(alertDiv);

  // Auto-suppression après 5 secondes
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

/**
 * Crée le conteneur d'alertes s'il n'existe pas
 */
function createAlertsContainer() {
  const container = document.createElement("div");
  container.id = "alerts-container";
  container.className = "position-fixed top-0 end-0 p-3";
  container.style.zIndex = "1055";
  document.body.appendChild(container);
  return container;
}

/**
 * Affiche/Cache l'indicateur de chargement
 */
function showLoadingIndicator() {
  const indicator = document.getElementById("loading-indicator");
  if (indicator) indicator.style.display = "block";
}

function hideLoadingIndicator() {
  const indicator = document.getElementById("loading-indicator");
  if (indicator) indicator.style.display = "none";
}

// ===============================
// Gestion du chargement des données
// ===============================

/**
 * Charge les nomenclatures avec pagination et filtres
 * @param {number} page - Numéro de page
 * @param {boolean} append - Ajouter aux données existantes ou remplacer
 */
async function loadNomenclatures(page = 1, append = false) {
  if (isLoading) return;

  isLoading = true;
  showLoadingIndicator();

  try {
    // Construction des paramètres
    const params = new URLSearchParams({
      page: page,
      limit: 100, // Lots de 100 pour de meilleures performances
      ...currentFilters,
    });

    const response = await fetch(
      `request/nomenclatures_paginated.php?${params}`
    );

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }

    const result = await response.json();

    if (!result.success) {
      throw new Error(
        result.message || "Erreur lors du chargement des données"
      );
    }

    const tbody = document.querySelector("#nomenclatures-table tbody");
    if (!tbody) {
      throw new Error("Tableau des nomenclatures introuvable");
    }

    // Vider le tableau si c'est une nouvelle recherche
    if (!append) {
      tbody.innerHTML = "";
      currentPage = 1;
    }

    // Ajout des lignes de nomenclatures
    result.data.forEach((nomenclature) => {
      const row = createNomenclatureRow(nomenclature);
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
    console.error("Erreur lors du chargement des nomenclatures:", error);
    showAlert(
      "Erreur lors du chargement des nomenclatures: " + error.message,
      "danger"
    );
  } finally {
    isLoading = false;
    hideLoadingIndicator();
  }
}

/**
 * Crée une ligne de tableau pour une nomenclature
 * @param {Object} nomenclature - Données de la nomenclature
 * @returns {HTMLElement} - Ligne de tableau
 */
function createNomenclatureRow(nomenclature) {
  const row = document.createElement("tr");
  row.innerHTML = `
    <td>
      <input type="checkbox" class="nomenclature-checkbox" value="${escapeHtml(
        nomenclature.id
      )}">
    </td>
    <td>${escapeHtml(nomenclature.code_equipement || "")}</td>
    <td>${escapeHtml(nomenclature.code_article || "")}</td>
    <td><strong>${escapeHtml(
      nomenclature.repere_equipement || ""
    )}</strong></td>
    <td>${escapeHtml(nomenclature.designation_equipement || "")}</td>
    <td>${escapeHtml(nomenclature.fabricant || "")}</td>
    <td>${escapeHtml(nomenclature.type || "")}</td>
    <td>${escapeHtml(nomenclature.numero_serie_fabricant || "")}</td>
    <td>${escapeHtml(nomenclature.designation_article || "")}</td>
    <td>${escapeHtml(nomenclature.numero_poste || "")}</td>
    <td>${escapeHtml(nomenclature.quantite || "")}</td>
    <td>${escapeHtml(nomenclature.unite || "")}</td>
    <td>${escapeHtml(nomenclature.poste_technique || "")}</td>
    <td>${escapeHtml(nomenclature.metier || "")}</td>
    <td>${escapeHtml(nomenclature.date_creation || "")}</td>
    <td>${escapeHtml(nomenclature.source || "")}</td>
  `;

  // Ajout des événements
  row.addEventListener("click", (e) => {
    if (e.target.type !== "checkbox") {
      handleRowClick(row, nomenclature.id);
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
  const codeEquipementFilter = document.querySelector(
    "#code-equipement-filter"
  );
  const codeArticleFilter = document.querySelector("#code-article-filter");
  const repereEquipementFilter = document.querySelector(
    "#repere-equipement-filter"
  );
  const fabricantFilter = document.querySelector("#fabricant-filter");
  const typeFilter = document.querySelector("#type-filter");
  const designationArticleFilter = document.querySelector(
    "#designation-article-filter"
  );
  const uniteFilter = document.querySelector("#unite-filter");
  const posteTechniqueFilter = document.querySelector(
    "#poste-technique-filter"
  );
  const metierFilter = document.querySelector("#metier-filter");
  const sourceFilter = document.querySelector("#source-filter");

  currentFilters = {};

  if (searchInput && searchInput.value.trim()) {
    currentFilters.search = searchInput.value.trim();
  }

  if (codeEquipementFilter && codeEquipementFilter.value) {
    currentFilters.code_equipement = codeEquipementFilter.value;
  }

  if (codeArticleFilter && codeArticleFilter.value) {
    currentFilters.code_article = codeArticleFilter.value;
  }

  if (repereEquipementFilter && repereEquipementFilter.value) {
    currentFilters.repere_equipement = repereEquipementFilter.value;
  }

  if (fabricantFilter && fabricantFilter.value) {
    currentFilters.fabricant = fabricantFilter.value;
  }

  if (typeFilter && typeFilter.value) {
    currentFilters.type = typeFilter.value;
  }

  if (designationArticleFilter && designationArticleFilter.value) {
    currentFilters.designation_article = designationArticleFilter.value;
  }

  if (uniteFilter && uniteFilter.value) {
    currentFilters.unite = uniteFilter.value;
  }

  if (posteTechniqueFilter && posteTechniqueFilter.value) {
    currentFilters.poste_technique = posteTechniqueFilter.value;
  }

  if (metierFilter && metierFilter.value) {
    currentFilters.metier = metierFilter.value;
  }

  if (sourceFilter && sourceFilter.value) {
    currentFilters.source = sourceFilter.value;
  }

  // Rechargement avec les nouveaux filtres
  hasMoreData = true;
  loadNomenclatures(1, false);
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
    loadNomenclatures(1, false);
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
        loadNomenclatures(currentPage + 1, true);
      }
    }
  });
}

// ===============================
// Gestion des exports temps réel
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
  iframe.src = `request/export_nomenclatures.php?${params}`;
  document.body.appendChild(iframe);

  // Supprimer l'iframe après un délai
  setTimeout(() => {
    document.body.removeChild(iframe);
  }, 5000);
}

/**
 * Export des nomenclatures filtrées avec suivi temps réel
 */
function handleFilteredExcelExport() {
  showExportLoader();
  startProgressiveExport("excel", "filtered");
}

/**
 * Démarre l'export progressif avec Server-Sent Events
 */
function startProgressiveExport(type, exportType) {
  const params = new URLSearchParams(currentFilters);
  params.append("type", type);
  params.append("export_type", exportType);

  // Récupération des nomenclatures sélectionnées si nécessaire
  if (exportType === "selected") {
    const checkboxes = document.querySelectorAll(
      ".nomenclature-checkbox:checked"
    );
    const selectedIds = Array.from(checkboxes).map((cb) => cb.value);
    if (selectedIds.length === 0) {
      showAlert("Aucune nomenclature sélectionnée", "warning");
      return;
    }
    params.append("selected_ids", selectedIds.join(","));
  }

  const url = `request/export_nomenclatures_progressive.php?${params}`;

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
        "export_nomenclatures_progressive.php",
        "export_nomenclatures.php"
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
      "export_nomenclatures_progressive.php",
      "export_nomenclatures.php"
    );
    document.body.appendChild(iframe);

    setTimeout(() => {
      document.body.removeChild(iframe);
    }, 5000);
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
              ? data.details.total_processed + " nomenclatures exportées"
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
// Gestion des événements
// ===============================

/**
 * Gère le clic sur une ligne de nomenclature
 * @param {HTMLElement} row - Ligne cliquée
 * @param {string} id - ID de la nomenclature
 */
function handleRowClick(row, id) {
  // Supprime la sélection précédente
  document.querySelectorAll("tr.table-warning").forEach((r) => {
    r.classList.remove("table-warning");
  });

  // Sélectionne la ligne actuelle
  row.classList.add("table-warning");

  // Charge les détails de la nomenclature pour édition
  loadNomenclatureDetails(id);
}

/**
 * Charge les détails d'une nomenclature pour édition
 * @param {string} id - ID de la nomenclature
 */
async function loadNomenclatureDetails(id) {
  try {
    const response = await fetch(
      `request/nomenclature_get.php?id=${encodeURIComponent(id)}`
    );
    const nomenclature = await response.json();

    if (nomenclature.success === false) {
      throw new Error(nomenclature.message || "Nomenclature non trouvée");
    }

    // Remplir le formulaire d'édition
    fillEditForm(nomenclature);

    // Afficher le modal d'édition
    const editModal = new bootstrap.Modal(
      document.getElementById("editNomenclatureModal")
    );
    editModal.show();
  } catch (error) {
    console.error("Erreur lors du chargement des détails:", error);
    showAlert(
      "Erreur lors du chargement des détails de la nomenclature",
      "danger"
    );
  }
}

/**
 * Remplit le formulaire d'édition avec les données de la nomenclature
 * @param {Object} nomenclature - Données de la nomenclature
 */
function fillEditForm(nomenclature) {
  const form = document.getElementById("editNomenclatureForm");
  if (!form) return;

  // Mapping des champs
  const fields = [
    "code_equipement",
    "code_article",
    "repere_equipement",
    "designation_equipement",
    "fabricant",
    "type",
    "numero_serie_fabricant",
    "designation_article",
    "numero_poste",
    "quantite",
    "unite",
    "poste_technique",
    "metier",
    "date_creation",
    "source",
  ];

  fields.forEach((field) => {
    const input = form.querySelector(`[name="${field}"]`);
    if (input) {
      input.value = nomenclature[field] || "";
    }
  });

  // Stocker l'ID pour la mise à jour
  const idInput = form.querySelector('[name="id"]');
  if (idInput) {
    idInput.value = nomenclature.id || "";
  }
}

// ===============================
// Mise à jour des statistiques
// ===============================

/**
 * Met à jour les statistiques basiques
 * @param {number} total - Nombre total de nomenclatures
 */
function updateStatistics(total) {
  const totalElement = document.querySelector(
    '[data-stat="total-nomenclatures"]'
  );
  if (totalElement) {
    totalElement.textContent = total.toLocaleString();
  }
}

/**
 * Met à jour les statistiques détaillées
 */
async function updateDetailedStats() {
  try {
    const response = await fetch("request/nomenclatures_stats.php");
    const stats = await response.json();

    if (stats.success) {
      // Mise à jour des statistiques par famille
      const topFamilleElement = document.querySelector(
        '[data-stat="top-famille"]'
      );
      if (topFamilleElement && stats.top_famille) {
        topFamilleElement.textContent = stats.top_famille;
      }

      const maxFamilleElement = document.querySelector(
        '[data-stat="max-famille"]'
      );
      if (maxFamilleElement && stats.max_famille) {
        maxFamilleElement.textContent = stats.max_famille.toLocaleString();
      }

      // Mise à jour des graphiques si nécessaire
      updateCharts(stats);
    }
  } catch (error) {
    console.error("Erreur lors du chargement des statistiques:", error);
  }
}

/**
 * Met à jour les graphiques avec de nouvelles données
 * @param {Object} stats - Statistiques
 */
function updateCharts(stats) {
  // Cette fonction peut être étendue pour mettre à jour les graphiques dynamiquement
  // Pour l'instant, on laisse les graphiques statiques générés côté serveur
}

/**
 * Met à jour les informations de pagination
 * @param {Object} pagination - Informations de pagination
 */
function updatePaginationInfo(pagination) {
  const infoElement = document.getElementById("pagination-info");
  if (infoElement) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    infoElement.textContent = `${start}-${end} sur ${pagination.total.toLocaleString()} nomenclatures`;
  }
}

// ===============================
// Gestion de la sélection
// ===============================

/**
 * Met à jour la visibilité des boutons basés sur la sélection
 */
function updateSelectionButtons() {
  const checkboxes = document.querySelectorAll(
    ".nomenclature-checkbox:checked"
  );
  const deleteBtn = document.querySelector("#deleteSelectedBtn");
  const exportBtn = document.querySelector("#exportFilteredBtn");

  if (deleteBtn) {
    deleteBtn.style.display = checkboxes.length > 0 ? "inline-block" : "none";
  }

  if (exportBtn) {
    exportBtn.style.display = checkboxes.length > 0 ? "inline-block" : "none";
  }
}

// ===============================
// Initialisation
// ===============================

/**
 * Initialise tous les événements et fonctionnalités
 */
function initNomenclaturesPage() {
  // Chargement initial des données
  loadNomenclatures(1, false);

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
    "#code-equipement-filter",
    "#code-article-filter",
    "#repere-equipement-filter",
    "#fabricant-filter",
    "#type-filter",
    "#designation-article-filter",
    "#unite-filter",
    "#poste-technique-filter",
    "#metier-filter",
    "#source-filter",
  ];
  filters.forEach((selector) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener("change", applyFilters);
    }
  });

  // Événements pour les boutons d'export
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

  const exportFilteredExcelBtn = document.querySelector(
    "#export-filtered-excel-btn"
  );
  if (exportFilteredExcelBtn) {
    exportFilteredExcelBtn.addEventListener("click", (e) => {
      e.preventDefault();
      handleFilteredExcelExport();
    });
  }

  // Bouton de réinitialisation des filtres
  const resetFiltersBtn = document.querySelector("#reset-filters");
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
      // Réinitialise tous les filtres
      document.querySelectorAll(".filter-input").forEach((input) => {
        input.value = "";
      });

      currentFilters = {};
      hasMoreData = true;
      loadNomenclatures(1, false);
    });
  }

  // Sélection/déselection de toutes les nomenclatures
  const selectAllCheckbox = document.querySelector("#select-all-nomenclatures");
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", (e) => {
      const checkboxes = document.querySelectorAll(".nomenclature-checkbox");
      checkboxes.forEach((cb) => (cb.checked = e.target.checked));
      updateSelectionButtons();
    });
  }

  // Observer les changements de checkbox pour mettre à jour les boutons
  document.addEventListener("change", (e) => {
    if (e.target.classList.contains("nomenclature-checkbox")) {
      updateSelectionButtons();
    }
  });
}

// Initialisation automatique au chargement de la page
document.addEventListener("DOMContentLoaded", initNomenclaturesPage);
