// Configuration des couleurs
const dangerColors = [
  "#f44336",
  "#e53935",
  "#d32f2f",
  "#c62828",
  "#b71c1c",
  "#ff5722",
  "#f4511e",
  "#e64a19",
  "#d84315",
  "#bf360c",
];

// Variables globales
let charts = {};
let loadedData = {
  equipements_par_famille: [],
  articles_par_metier: [],
  equipements_par_source: [],
  articles_par_source: [],
};

// Variables de pagination
let equipementsData = {
  items: [],
  currentOffset: 0,
  totalCount: 0,
  hasMore: true,
  loading: false,
};

let articlesData = {
  items: [],
  currentOffset: 0,
  totalCount: 0,
  hasMore: true,
  loading: false,
};

// Variables de statistiques
let statsCalculated = false;
let globalProgressSteps = 0;
let totalSteps = 8;

// Fonctions utilitaires
function showProgressBar() {
  const progressContainer = document.getElementById("progressContainer");
  if (progressContainer) {
    progressContainer.style.display = "block";
  }
}

function hideProgressBar() {
  const progressContainer = document.getElementById("progressContainer");
  if (progressContainer) {
    progressContainer.style.display = "none";
  }
}

function updateProgressMessage(message) {
  const progressText = document.getElementById("progressText");
  if (progressText) {
    progressText.textContent = message;
  }
}

function updateGlobalProgress(step) {
  globalProgressSteps = step;
  const percentage = Math.round((step / totalSteps) * 100);

  // Mettre à jour le texte de progression
  const globalProgress = document.getElementById("globalProgress");
  if (globalProgress) {
    globalProgress.textContent = percentage + "%";
  }

  // Mettre à jour la barre de progression circulaire
  const progressRing = document.getElementById("progressRingCircle");
  if (progressRing) {
    const circumference = 2 * Math.PI * 50; // rayon = 50
    const offset = circumference - (percentage / 100) * circumference;
    progressRing.style.strokeDashoffset = offset;
  }

  // Mettre à jour la barre de progression horizontale
  const progressBar = document.getElementById("progressBar");
  if (progressBar) {
    progressBar.style.width = percentage + "%";
  }
}

function updateLoadingStatus(type, status) {
  const statusElement = document.getElementById(
    `status${type.charAt(0).toUpperCase() + type.slice(1)}`
  );
  const textElement = document.getElementById(
    `status${type.charAt(0).toUpperCase() + type.slice(1)}Text`
  );
  const iconElement = document.getElementById(
    `status${type.charAt(0).toUpperCase() + type.slice(1)}Icon`
  );

  if (status === "loading") {
    if (statusElement) {
      statusElement.style.display = "inline-block";
      statusElement.className =
        "spinner-border spinner-border-sm text-primary me-2";
    }
    if (textElement) {
      textElement.className = "text-primary";
    }
    if (iconElement) {
      iconElement.style.display = "none";
    }
  } else if (status === "complete") {
    if (statusElement) {
      statusElement.style.display = "none";
    }
    if (textElement) {
      textElement.className = "text-success";
    }
    if (iconElement) {
      iconElement.style.display = "inline-block";
      iconElement.className = "material-icons ms-auto text-success";
    }
  }
}

function hideLoadingStatus() {
  const section = document.getElementById("loadingStatusSection");
  if (section) {
    section.style.display = "none";
  }
}

function showError(message) {
  console.error(message);
  // Vous pouvez ajouter ici une notification d'erreur visuelle
}

// Fonction principale de chargement progressif
async function loadDataProgressively() {
  showProgressBar();
  updateGlobalProgress(0);

  try {
    // Étape 1: Charger les statistiques générales
    updateProgressMessage("Chargement des statistiques générales...");
    updateLoadingStatus("stats", "loading");
    await loadGeneralStats();
    updateLoadingStatus("stats", "complete");
    updateGlobalProgress(1);

    // Mise à jour immédiate de l'aperçu
    updateQuickPreview();

    // Étape 2: Charger les équipements (premier lot)
    updateProgressMessage("Chargement des équipements (premier lot)...");
    updateLoadingStatus("equipements", "loading");
    await loadEquipementsBatch();
    updateLoadingStatus("equipements", "complete");
    updateGlobalProgress(2);

    // Étape 3: Charger les articles (premier lot)
    updateProgressMessage("Chargement des articles (premier lot)...");
    updateLoadingStatus("articles", "loading");
    await loadArticlesBatch();
    updateLoadingStatus("articles", "complete");
    updateGlobalProgress(3);

    // Étape 4: Charger les graphiques famille
    updateProgressMessage("Analyse des familles d'équipements...");
    updateLoadingStatus("charts", "loading");
    await loadEquipementsFamilleChart();
    updateGlobalProgress(4);

    // Étape 5: Charger les graphiques métier
    updateProgressMessage("Classification des articles par métier...");
    await loadArticlesMetierChart();
    updateGlobalProgress(5);

    // Étape 6: Charger les graphiques équipements par source
    updateProgressMessage("Analyse des sources d'équipements...");
    await loadEquipementsSourceChart();
    updateGlobalProgress(6);

    // Étape 7: Charger les graphiques articles par source
    updateProgressMessage("Analyse des sources d'articles...");
    await loadArticlesSourceChart();
    updateGlobalProgress(7);

    // Étape 8: Calcul des statistiques avancées
    updateProgressMessage("Finalisation des analyses...");
    calculateAdvancedStats();
    updateLoadingStatus("charts", "complete");
    updateGlobalProgress(8);

    // Finalisation
    setTimeout(() => {
      hideProgressBar();
      hideLoadingStatus();
      showAllCharts();
      updateFinalStatus();
    }, 1000);
  } catch (error) {
    console.error("Erreur lors du chargement:", error);
    hideProgressBar();
    showError("Erreur lors du chargement des données");
  }
}

// Fonctions de chargement des données
async function loadGeneralStats() {
  try {
    const response = await fetch(
      "request/stats_non_sap_paginated.php?type=general_stats"
    );
    const data = await response.json();

    if (data.success) {
      // Mettre à jour les statistiques générales
      document.getElementById("equipementsNonSAP").textContent =
        data.stats.equipements_non_sap || 0;
      document.getElementById("articlesNonSAP").textContent =
        data.stats.articles_non_sap || 0;
      document.getElementById("totalNonSAP").textContent =
        data.stats.equipements_non_sap + data.stats.articles_non_sap || 0;

      // Supprimer les classes de chargement
      document
        .getElementById("cardEquipements")
        .classList.remove("stat-card-loading");
      document
        .getElementById("cardArticles")
        .classList.remove("stat-card-loading");
    }
  } catch (error) {
    console.error("Erreur stats générales:", error);
    throw error;
  }
}

async function loadEquipementsBatch() {
  if (equipementsData.loading || !equipementsData.hasMore) return;

  equipementsData.loading = true;
  document.getElementById("equipementsLoading").style.display = "inline-block";

  try {
    const response = await fetch(
      `request/stats_non_sap_paginated.php?type=equipements_details&offset=${equipementsData.currentOffset}&limit=50`
    );
    const data = await response.json();

    if (data.success) {
      equipementsData.items = [...equipementsData.items, ...data.data];
      equipementsData.currentOffset += data.data.length;
      equipementsData.totalCount = data.total_count;
      equipementsData.hasMore = data.has_more;

      updateEquipementsTable();
      updateEquipementsProgress();
    }
  } catch (error) {
    console.error("Erreur équipements:", error);
    throw error;
  } finally {
    equipementsData.loading = false;
    document.getElementById("equipementsLoading").style.display = "none";
  }
}

async function loadArticlesBatch() {
  if (articlesData.loading || !articlesData.hasMore) return;

  articlesData.loading = true;
  document.getElementById("articlesLoading").style.display = "inline-block";

  try {
    const response = await fetch(
      `request/stats_non_sap_paginated.php?type=articles_details&offset=${articlesData.currentOffset}&limit=50`
    );
    const data = await response.json();

    if (data.success) {
      articlesData.items = [...articlesData.items, ...data.data];
      articlesData.currentOffset += data.data.length;
      articlesData.totalCount = data.total_count;
      articlesData.hasMore = data.has_more;

      updateArticlesTable();
      updateArticlesProgress();
    }
  } catch (error) {
    console.error("Erreur articles:", error);
    throw error;
  } finally {
    articlesData.loading = false;
    document.getElementById("articlesLoading").style.display = "none";
  }
}

function updateEquipementsTable() {
  const tbody = document.getElementById("equipementsTableBody");
  if (!tbody) return;

  tbody.innerHTML = "";

  equipementsData.items.forEach((item) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td><strong>${item.repere_equipement || "N/A"}</strong></td>
            <td><span class="badge bg-info">${
              item.famille || "Non défini"
            }</span></td>
            <td><span class="badge bg-secondary">${
              item.source_actuelle || "Inconnue"
            }</span></td>
        `;
    tbody.appendChild(row);
  });
}

function updateArticlesTable() {
  const tbody = document.getElementById("articlesTableBody");
  if (!tbody) return;

  tbody.innerHTML = "";

  articlesData.items.forEach((item) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td><strong>${item.code_article || "N/A"}</strong></td>
            <td><span class="badge bg-warning">${
              item.metier || "Non défini"
            }</span></td>
            <td><span class="badge bg-secondary">${
              item.source_actuelle || "Inconnue"
            }</span></td>
        `;
    tbody.appendChild(row);
  });
}

function updateEquipementsProgress() {
  const progressEl = document.getElementById("equipementsProgress");
  const statusEl = document.getElementById("equipementsStatus");
  const loadMoreBtn = document.getElementById("loadMoreEquipements");

  if (progressEl) {
    progressEl.textContent = `${equipementsData.items.length}/${equipementsData.totalCount}`;
  }

  if (statusEl) {
    statusEl.textContent = `${equipementsData.items.length} équipements chargés`;
  }

  if (loadMoreBtn) {
    loadMoreBtn.style.display = equipementsData.hasMore
      ? "inline-block"
      : "none";
  }
}

function updateArticlesProgress() {
  const progressEl = document.getElementById("articlesProgress");
  const statusEl = document.getElementById("articlesStatus");
  const loadMoreBtn = document.getElementById("loadMoreArticles");

  if (progressEl) {
    progressEl.textContent = `${articlesData.items.length}/${articlesData.totalCount}`;
  }

  if (statusEl) {
    statusEl.textContent = `${articlesData.items.length} articles chargés`;
  }

  if (loadMoreBtn) {
    loadMoreBtn.style.display = articlesData.hasMore ? "inline-block" : "none";
  }
}

// Fonctions de chargement des graphiques
async function loadEquipementsFamilleChart() {
  try {
    const response = await fetch(
      "request/stats_non_sap_paginated.php?type=equipements_par_famille"
    );
    const data = await response.json();

    if (data.success) {
      loadedData.equipements_par_famille = data.data;
      createEquipementsFamilleChart();
      hideChartLoading("loadingEquipFamille");
    }
  } catch (error) {
    console.error("Erreur graphique familles:", error);
  }
}

async function loadArticlesMetierChart() {
  try {
    const response = await fetch(
      "request/stats_non_sap_paginated.php?type=articles_par_metier"
    );
    const data = await response.json();

    if (data.success) {
      loadedData.articles_par_metier = data.data;
      createArticlesMetierChart();
      hideChartLoading("loadingArticlesMetier");
    }
  } catch (error) {
    console.error("Erreur graphique métiers:", error);
  }
}

async function loadEquipementsSourceChart() {
  try {
    const response = await fetch(
      "request/stats_non_sap_paginated.php?type=equipements_par_source"
    );
    const data = await response.json();

    if (data.success) {
      loadedData.equipements_par_source = data.data;
      createEquipementsSourceChart();
      hideChartLoading("loadingEquipSource");
    } else {
      console.error("Erreur API equipements_par_source:", data.error);
    }
  } catch (error) {
    console.error("Erreur graphique sources équipements:", error);
  }
}

async function loadArticlesSourceChart() {
  try {
    const response = await fetch(
      "request/stats_non_sap_paginated.php?type=articles_par_source"
    );
    const data = await response.json();

    if (data.success) {
      loadedData.articles_par_source = data.data;
      createArticlesSourceChart();
      hideChartLoading("loadingArticlesSource");
    } else {
      console.error("Erreur API articles_par_source:", data.error);
    }
  } catch (error) {
    console.error("Erreur graphique sources articles:", error);
  }
}

function hideChartLoading(loadingId) {
  const loadingEl = document.getElementById(loadingId);
  if (loadingEl) {
    loadingEl.style.display = "none";
  }
}

// Fonctions de création des graphiques
function createEquipementsFamilleChart() {
  const ctx = document.getElementById("equipementsFamilleChart");
  if (!ctx || loadedData.equipements_par_famille.length === 0) return;

  const data = loadedData.equipements_par_famille;
  const labels = data.map((item) => item.famille || "Non défini");
  const values = data.map((item) => item.count);

  charts.equipementsFamille = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: dangerColors.slice(0, labels.length),
          borderWidth: 2,
          borderColor: "#fff",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 20,
            usePointStyle: true,
          },
        },
      },
    },
  });
}

function createArticlesMetierChart() {
  const ctx = document.getElementById("articlesMetierChart");
  if (!ctx || loadedData.articles_par_metier.length === 0) return;

  const data = loadedData.articles_par_metier;
  const labels = data.map((item) => item.metier || "Non défini");
  const values = data.map((item) => item.count);

  charts.articlesMetier = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: dangerColors.slice(0, labels.length),
          borderWidth: 2,
          borderColor: "#fff",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 20,
            usePointStyle: true,
          },
        },
      },
    },
  });
}

function createEquipementsSourceChart() {
  const ctx = document.getElementById("equipementsSourceChart");
  if (!ctx || loadedData.equipements_par_source.length === 0) return;

  const data = loadedData.equipements_par_source;
  const labels = data.map((item) => item.source_actuelle || "Inconnue");
  const values = data.map((item) => item.count);

  charts.equipementsSource = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: dangerColors[0],
          borderColor: dangerColors[1],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

function createArticlesSourceChart() {
  const ctx = document.getElementById("articlesSourceChart");
  if (!ctx || loadedData.articles_par_source.length === 0) return;

  const data = loadedData.articles_par_source;
  const labels = data.map((item) => item.source_actuelle || "Inconnue");
  const values = data.map((item) => item.count);

  charts.articlesSource = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: dangerColors[2],
          borderColor: dangerColors[3],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

function showAllCharts() {
  const chartPreparationSection = document.getElementById(
    "chartsPreparationSection"
  );
  if (chartPreparationSection) {
    chartPreparationSection.style.display = "none";
  }
}

function updateQuickPreview() {
  // Mise à jour de l'aperçu rapide basé sur les données chargées
  const familles = new Set();
  const metiers = new Set();
  const sources = new Set();

  if (
    loadedData.equipements_par_famille &&
    loadedData.equipements_par_famille.length > 0
  ) {
    loadedData.equipements_par_famille.forEach((item) =>
      familles.add(item.famille)
    );
  }
  if (
    loadedData.articles_par_metier &&
    loadedData.articles_par_metier.length > 0
  ) {
    loadedData.articles_par_metier.forEach((item) => metiers.add(item.metier));
  }
  if (
    loadedData.equipements_par_source &&
    loadedData.equipements_par_source.length > 0
  ) {
    loadedData.equipements_par_source.forEach((item) =>
      sources.add(item.source_actuelle)
    );
  }
  if (
    loadedData.articles_par_source &&
    loadedData.articles_par_source.length > 0
  ) {
    loadedData.articles_par_source.forEach((item) =>
      sources.add(item.source_actuelle)
    );
  }

  // Mettre à jour seulement si on a des données
  if (familles.size > 0) {
    document.getElementById("previewFamilles").textContent = familles.size;
  }
  if (metiers.size > 0) {
    document.getElementById("previewMetiers").textContent = metiers.size;
  }
  if (sources.size > 0) {
    document.getElementById("previewSources").textContent = sources.size;
  }

  document.getElementById("previewStatus").innerHTML =
    '<span class="material-icons">trending_up</span>';
  document.getElementById("previewStatusText").textContent =
    "Analyse en cours...";
}

function calculateAdvancedStats() {
  if (statsCalculated) return;

  const totalEquipements =
    parseInt(document.getElementById("equipementsNonSAP").textContent) || 0;
  const totalArticles =
    parseInt(document.getElementById("articlesNonSAP").textContent) || 0;
  const totalElements = totalEquipements + totalArticles;

  // Calcul du pourcentage (estimation)
  const estimatedTotalElements = totalElements * 2; // Estimation basée sur le ratio habituel
  const pourcentage = Math.round(
    (totalElements / estimatedTotalElements) * 100
  );

  document.getElementById("pourcentageNonSAP").textContent = pourcentage + "%";
  document.getElementById("famillesPrincipales").textContent =
    loadedData.equipements_par_famille.length;
  document.getElementById("sourcesActives").textContent = new Set([
    ...loadedData.equipements_par_source.map((item) => item.source_actuelle),
    ...loadedData.articles_par_source.map((item) => item.source_actuelle),
  ]).size;
  document.getElementById("prioriteHaute").textContent = Math.round(
    totalElements * 0.3
  ); // 30% considérés comme priorité haute

  statsCalculated = true;
}

function updateFinalStatus() {
  document.getElementById("previewStatus").innerHTML =
    '<span class="material-icons">check_circle</span>';
  document.getElementById("previewStatusText").textContent = "Analyse terminée";
}

// Event listeners
document.addEventListener("DOMContentLoaded", function () {
  // Bouton "Charger plus" pour les équipements
  document
    .getElementById("loadMoreEquipements")
    .addEventListener("click", loadEquipementsBatch);

  // Bouton "Charger plus" pour les articles
  document
    .getElementById("loadMoreArticles")
    .addEventListener("click", loadArticlesBatch);

  // Démarrer le chargement progressif
  setTimeout(() => {
    loadDataProgressively();
  }, 500);
});
