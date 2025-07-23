/**
 * Gestion des articles avec pagination infinie et design moderne
 * Version adaptée du système équipements
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
 * Charge les articles avec pagination
 * @param {number} page - Page à charger
 * @param {boolean} append - Si true, ajoute à la liste existante, sinon remplace
 */
async function loadArticles(page = 1, append = false) {
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

    const response = await fetch(`request/articles_paginated.php?${params}`);
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || "Erreur lors du chargement");
    }

    const tbody = document.querySelector("#articles-table tbody");
    if (!tbody) return;

    if (!append) {
      tbody.innerHTML = "";
      currentPage = 1;
    }

    // Ajout des lignes d'articles
    result.data.forEach((article) => {
      const row = createArticleRow(article);
      tbody.appendChild(row);
    });

    // Mise à jour des variables de pagination
    hasMoreData = result.pagination.hasMore;
    currentPage = result.pagination.page;

    // Mise à jour des statistiques
    updateArticleStatistics(result.pagination.total);

    // Mise à jour du message de pagination
    updatePaginationInfo(result.pagination);
  } catch (error) {
    console.error("Erreur lors du chargement des articles:", error);
    showAlert("Erreur lors du chargement des articles", "danger");
  } finally {
    isLoading = false;
    hideLoadingIndicator();
  }
}

/**
 * Crée une ligne de tableau pour un article
 * @param {Object} article - Données de l'article
 * @returns {HTMLElement} - Ligne de tableau
 */
function createArticleRow(article) {
  const row = document.createElement("tr");
  row.innerHTML = `
        <td>
            <input type="checkbox" class="article-checkbox" value="${escapeHtml(
              article.id
            )}">
        </td>
        <td data-id="${escapeHtml(article.id)}">${escapeHtml(
    article.code_article || ""
  )}</td>
        <td>${escapeHtml(article.designation_article || "")}</td>
        <td>${escapeHtml(article.type_article || "")}</td>
        <td>${escapeHtml(article.uq_base || "")}</td>
        <td>${escapeHtml(article.fabricant || "")}</td>
        <td>${escapeHtml(article.numero_piece_fabricant || "")}</td>
        <td>${escapeHtml(article.groupe_articles || "")}</td>
        <td>${escapeHtml(article.document || "")}</td>
        <td>${escapeHtml(article.description || "")}</td>
        <td>${escapeHtml(article.date_creation || "")}</td>
        <td>${escapeHtml(article.cree_par || "")}</td>
    `;

  // Ajout des événements
  row.addEventListener("click", (e) => {
    if (e.target.type !== "checkbox") {
      handleRowClick(row, article.id);
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
  const groupeFilter = document.querySelector("#groupe-filter");
  const uqFilter = document.querySelector("#uq-filter");

  currentFilters = {};

  if (searchInput && searchInput.value.trim()) {
    currentFilters.search = searchInput.value.trim();
  }

  if (fabricantFilter && fabricantFilter.value) {
    currentFilters.fabricant = fabricantFilter.value;
  }

  if (typeFilter && typeFilter.value) {
    currentFilters.type_article = typeFilter.value;
  }

  if (groupeFilter && groupeFilter.value) {
    currentFilters.groupe_articles = groupeFilter.value;
  }

  if (uqFilter && uqFilter.value) {
    currentFilters.uq_base = uqFilter.value;
  }

  // Rechargement avec les nouveaux filtres
  hasMoreData = true;
  loadArticles(1, false);
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
    loadArticles(1, false);
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
        loadArticles(currentPage + 1, true);
      }
    }
  });
}

// ===============================
// Import et export
// ===============================

/**
 * Gestion de l'import Excel avec drag & drop et progression
 */
async function handleAdvancedArticleImport() {
  const fileInput = document.querySelector("#excel_file");
  const file = fileInput.files[0];

  if (!file) {
    showAlert("Veuillez sélectionner un fichier Excel", "warning");
    return;
  }

  const formData = new FormData();
  formData.append("excel_file", file);

  const progressContainer = document.querySelector("#importProgress");
  const progressBar = document.querySelector("#importProgress .progress-bar");
  const importResult = document.querySelector("#importLog");
  const startBtn = document.querySelector("#importBtn");

  try {
    // Affichage de la barre de progression
    progressContainer.classList.remove("d-none");
    startBtn.disabled = true;
    importResult.innerHTML = '<div class="text-info">Import en cours...</div>';

    // Simulation de progression
    let progress = 0;
    const progressInterval = setInterval(() => {
      progress += Math.random() * 15;
      if (progress > 90) progress = 90;
      progressBar.style.width = progress + "%";
      progressBar.textContent = Math.round(progress) + "%";
    }, 500);

    const response = await fetch("request/article_import_optimized.php", {
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
          • ${result.imported || 0} articles importés<br>
          • ${result.duplicates || 0} doublons ignorés<br>
          • ${result.errors || 0} erreurs<br>
          • Temps de traitement : ${result.execution_time || "N/A"}
        </div>
      `;

      // Recharger la liste après un délai
      setTimeout(() => {
        hasMoreData = true;
        loadArticles(1, false);

        // Fermer le modal après 2 secondes
        setTimeout(() => {
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("importArticleModal")
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
 * Met à jour les statistiques affichées
 * @param {number} total - Nombre total d'articles
 */
function updateArticleStatistics(total) {
  const totalElement = document.querySelector('[data-stat="total-articles"]');
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

    paginationInfo.textContent = `Affichage de ${start} à ${end} sur ${pagination.total} articles`;
  }
}

/**
 * Met à jour la visibilité des boutons basés sur la sélection
 */
function updateSelectionButtons() {
  const checkboxes = document.querySelectorAll(".article-checkbox:checked");
  const deleteBtn = document.querySelector("#deleteSelectedBtn");
  const exportBtn = document.querySelector("#exportFilteredBtn");

  if (deleteBtn) {
    deleteBtn.style.display = checkboxes.length > 0 ? "inline-block" : "none";
  }

  if (exportBtn) {
    exportBtn.style.display = checkboxes.length > 0 ? "inline-block" : "none";
  }
}

/**
 * Gère le clic sur une ligne d'article
 * @param {HTMLElement} row - Ligne cliquée
 * @param {string} id - ID de l'article
 */
function handleRowClick(row, id) {
  // Supprime la sélection précédente
  document.querySelectorAll("tr.table-warning").forEach((r) => {
    r.classList.remove("table-warning");
  });

  // Sélectionne la ligne actuelle
  row.classList.add("table-warning");

  // Charge les détails de l'article pour édition
  loadArticleDetails(id);
}

/**
 * Charge les détails d'un article pour édition
 * @param {string} id - ID de l'article
 */
async function loadArticleDetails(id) {
  try {
    const response = await fetch(
      `request/article_get.php?id=${encodeURIComponent(id)}`
    );
    const article = await response.json();

    if (article.success === false) {
      throw new Error(article.message || "Article non trouvé");
    }

    // Afficher le modal d'édition (réutiliser le modal existant)
    const editModal = new bootstrap.Modal(
      document.getElementById("editArticleModal")
    );
    editModal.show();
  } catch (error) {
    console.error("Erreur lors du chargement des détails:", error);
    showAlert("Erreur lors du chargement des détails de l'article", "danger");
  }
}

// ===============================
// Initialisation
// ===============================

/**
 * Initialise tous les événements et fonctionnalités
 */
function initArticlesPage() {
  // Chargement initial des données
  loadArticles(1, false);

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
    "#groupe-filter",
    "#uq-filter",
  ];
  filters.forEach((selector) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener("change", applyFilters);
    }
  });

  // Bouton de réinitialisation des filtres
  const resetFiltersBtn = document.querySelector("#reset-filters");
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
      // Réinitialise tous les filtres
      const searchInput = document.querySelector("#search-input");
      const fabricantFilter = document.querySelector("#fabricant-filter");
      const typeFilter = document.querySelector("#type-filter");
      const groupeFilter = document.querySelector("#groupe-filter");
      const uqFilter = document.querySelector("#uq-filter");

      if (searchInput) searchInput.value = "";
      if (fabricantFilter) fabricantFilter.value = "";
      if (typeFilter) typeFilter.value = "";
      if (groupeFilter) groupeFilter.value = "";
      if (uqFilter) uqFilter.value = "";

      currentFilters = {};
      hasMoreData = true;
      loadArticles(1, false);
    });
  }

  // Sélection/déselection de tous les articles
  const selectAllCheckbox = document.querySelector("#select-all-articles");
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", (e) => {
      const checkboxes = document.querySelectorAll(".article-checkbox");
      checkboxes.forEach((cb) => (cb.checked = e.target.checked));
      updateSelectionButtons();
    });
  }

  // Observer les changements de checkbox pour mettre à jour les boutons
  document.addEventListener("change", (e) => {
    if (e.target.classList.contains("article-checkbox")) {
      updateSelectionButtons();
    }
  });

  // Gestion de l'importation
  const importForm = document.querySelector("#importArticleForm");
  if (importForm) {
    importForm.addEventListener("submit", (e) => {
      e.preventDefault();
      handleAdvancedArticleImport();
    });
  }

  // Gestion du drag & drop pour l'importation
  const dropZone = document.querySelector("#dropZone");
  const fileInput = document.querySelector("#excel_file");

  if (dropZone && fileInput) {
    dropZone.addEventListener("click", () => fileInput.click());

    dropZone.addEventListener("dragover", (e) => {
      e.preventDefault();
      dropZone.classList.add("dragover");
    });

    dropZone.addEventListener("dragleave", (e) => {
      e.preventDefault();
      dropZone.classList.remove("dragover");
    });

    dropZone.addEventListener("drop", (e) => {
      e.preventDefault();
      dropZone.classList.remove("dragover");
      if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        const fileName = e.dataTransfer.files[0].name;
        const textDiv = dropZone.querySelector(".mt-2");
        if (textDiv) textDiv.textContent = fileName;
      }
    });

    fileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        const fileName = e.target.files[0].name;
        const textDiv = dropZone.querySelector(".mt-2");
        if (textDiv) textDiv.textContent = fileName;
      }
    });
  }
}

// ===============================
// Gestion des exports
// ===============================

/**
 * Affichage du loader d'export avec progression temps réel
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
  progressText.textContent = "Initialisation de l'export...";

  // Afficher le bouton "Fermer" après 5 secondes automatiquement
  const autoShowCloseTimeout = setTimeout(() => {
    closeBtn.style.display = "";
    closeBtn.onclick = function () {
      modal.hide();
    };
  }, 5000);

  return {
    updateProgress: function (percentage, message, processed = 0, total = 0) {
      progressBar.style.width = percentage + "%";
      progressBar.textContent = Math.round(percentage) + "%";

      if (processed > 0 && total > 0) {
        progressText.textContent = `${message} (${processed.toLocaleString()}/${total.toLocaleString()})`;
      } else {
        progressText.textContent = message;
      }
    },
    showCloseButton: function () {
      clearTimeout(autoShowCloseTimeout); // Annuler le timeout automatique
      closeBtn.style.display = "";
      closeBtn.onclick = function () {
        modal.hide();
      };
    },
    hide: function () {
      clearTimeout(autoShowCloseTimeout);
      modal.hide();
    },
  };
}

/**
 * Export avec suivi temps réel
 */
function startProgressiveExport(type) {
  const loader = showExportLoader();
  const sessionId = Date.now().toString();

  // Construction de l'URL avec les filtres actuels
  const params = new URLSearchParams(currentFilters);
  params.append("type", type);
  params.append("session", sessionId);

  const eventSource = new EventSource(
    `request/export_articles_progressive.php?${params}`
  );

  let exportCompleted = false;

  eventSource.onmessage = function (event) {
    try {
      const data = JSON.parse(event.data);

      if (data.error) {
        loader.updateProgress(0, "ERREUR: " + data.message);
        loader.showCloseButton();
        eventSource.close();
        return;
      }

      loader.updateProgress(
        data.percentage,
        data.message,
        data.processed,
        data.total
      );

      if (data.completed && data.downloadUrl) {
        exportCompleted = true;

        // Déclencher le téléchargement
        const link = document.createElement("a");
        link.href = data.downloadUrl;
        link.download = data.filename;
        link.style.display = "none";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Afficher le bouton fermer après téléchargement
        setTimeout(() => {
          loader.showCloseButton();
          eventSource.close();
        }, 1000);
      }
    } catch (e) {
      console.error("Erreur parsing JSON:", e);
      loader.updateProgress(0, "Erreur de communication");
      loader.showCloseButton();
      eventSource.close();
    }
  };

  eventSource.onerror = function (event) {
    console.error("Erreur EventSource:", event);
    loader.updateProgress(0, "Erreur de connexion");
    loader.showCloseButton();
    eventSource.close();
  };

  // Timeout de sécurité - si pas de réponse après 10 secondes, afficher le bouton fermer
  setTimeout(() => {
    if (!exportCompleted && eventSource.readyState !== EventSource.CLOSED) {
      // Si l'export n'est pas terminé mais prend trop de temps, permettre de fermer
      if (loader.showCloseButton) {
        loader.showCloseButton();
      }
    }
  }, 10000);

  // Timeout final de sécurité
  setTimeout(() => {
    if (eventSource.readyState !== EventSource.CLOSED) {
      eventSource.close();
      loader.updateProgress(0, "Timeout - export interrompu");
      loader.showCloseButton();
    }
  }, 300000); // 5 minutes max
}

/**
 * Gestion de l'export Excel avec suivi temps réel
 */
function handleExcelExport() {
  // Essayer d'abord l'export progressif, avec fallback sur l'export simple
  if (typeof EventSource !== "undefined") {
    startProgressiveExport("excel");
  } else {
    // Fallback pour navigateurs qui ne supportent pas EventSource
    const loader = showExportLoader();

    // Utiliser une iframe invisible pour le téléchargement
    let iframe = document.getElementById("downloadFrame");
    if (!iframe) {
      iframe = document.createElement("iframe");
      iframe.id = "downloadFrame";
      iframe.style.display = "none";
      document.body.appendChild(iframe);
    }

    const params = new URLSearchParams(currentFilters);
    params.append("type", "excel");
    iframe.src = `request/export_articles.php?${params}`;
  }
}

/**
 * Gestion de l'export PDF avec suivi temps réel
 */
function handlePdfExport() {
  // Essayer d'abord l'export progressif, avec fallback sur l'export simple
  if (typeof EventSource !== "undefined") {
    startProgressiveExport("pdf");
  } else {
    // Fallback pour navigateurs qui ne supportent pas EventSource
    const loader = showExportLoader();

    // Utiliser une iframe invisible pour le téléchargement
    let iframe = document.getElementById("downloadFrame");
    if (!iframe) {
      iframe = document.createElement("iframe");
      iframe.id = "downloadFrame";
      iframe.style.display = "none";
      document.body.appendChild(iframe);
    }

    const params = new URLSearchParams(currentFilters);
    params.append("type", "pdf");
    iframe.src = `request/export_articles.php?${params}`;
  }
}

/**
 * Export de la sélection filtrée avec choix du format
 */
function handleFilteredExport() {
  const checkboxes = document.querySelectorAll(".article-checkbox:checked");

  if (checkboxes.length === 0) {
    showAlert("Aucun article sélectionné pour l'export", "warning");
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
            <p>Exporter ${checkboxes.length} articles sélectionnés :</p>
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
 * Exporte la sélection dans le format choisi
 */
function exportSelected(format) {
  const checkboxes = document.querySelectorAll(".article-checkbox:checked");
  const codes = Array.from(checkboxes).map((cb) => cb.value);

  // Créer un formulaire pour envoyer les codes sélectionnés
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "request/export_articles.php";
  form.style.display = "none";

  // Ajouter les codes sélectionnés
  const codeInput = document.createElement("input");
  codeInput.type = "hidden";
  codeInput.name = "selected_codes";
  codeInput.value = JSON.stringify(codes);
  form.appendChild(codeInput);

  // Type d'export
  const typeInput = document.createElement("input");
  typeInput.type = "hidden";
  typeInput.name = "type";
  typeInput.value = format;
  form.appendChild(typeInput);

  document.body.appendChild(form);
  showExportLoader();
  form.submit();
  document.body.removeChild(form);

  // Fermer le modal
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("exportFormatModal")
  );
  if (modal) modal.hide();
}

// Initialisation automatique au chargement de la page
document.addEventListener("DOMContentLoaded", initArticlesPage);
