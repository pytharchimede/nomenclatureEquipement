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
}

// Initialisation automatique au chargement de la page
document.addEventListener("DOMContentLoaded", initArticlesPage);
