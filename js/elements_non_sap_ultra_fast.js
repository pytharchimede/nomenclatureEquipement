// VERSION ULTRA-OPTIMISÉE pour chargement immédiat des données
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
let allData = {};
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

// 🚀 CHARGEMENT ULTRA-RAPIDE avec affichage immédiat
async function loadAllDataUltraFast() {
  try {
    console.log("🚀 Démarrage chargement ultra-rapide...");
    const startTime = performance.now();

    // 1. AFFICHAGE IMMÉDIAT (0ms) - Plus d'écran blanc !
    showImmediatePreview();
    updateProgressStatus(10);

    // 2. Charger les stats de base uniquement (très rapide)
    const statsPromise = loadBasicStats();
    const statsResult = await statsPromise;

    if (statsResult.success) {
      updateStatsInstantly(statsResult.data);
      updateProgressStatus(30);
    }

    // 3. Charger les données détaillées en arrière-plan
    loadDetailedDataInBackground().then((result) => {
      if (result.success) {
        allData = result.data;
        updateQuickPreviewInstantly();
        updateProgressStatus(60);

        // Créer les graphiques rapidement
        setTimeout(() => {
          createAllChartsInstantly();
          updateProgressStatus(80);
        }, 100);

        // Charger les tableaux
        setTimeout(() => {
          Promise.all([
            loadEquipementsBatchFast(),
            loadArticlesBatchFast(),
          ]).then(() => {
            updateProgressStatus(100);
            finalizeFastLoading();
          });
        }, 200);
      }
    });

    const endTime = performance.now();
    console.log(
      `✅ Interface responsive en ${Math.round(endTime - startTime)}ms`
    );
  } catch (error) {
    console.error("Erreur chargement ultra-rapide:", error);
    showErrorState();
  }
}

// Affichage immédiat pour éviter l'écran blanc
function showImmediatePreview() {
  // Masquer les spinners immédiatement
  hideLoadingSpinners();

  // Afficher des valeurs temporaires
  document.getElementById("equipementsNonSAP").innerHTML =
    '<span class="text-muted">Calcul...</span>';
  document.getElementById("articlesNonSAP").innerHTML =
    '<span class="text-muted">Calcul...</span>';
  document.getElementById("totalNonSAP").innerHTML =
    '<span class="text-muted">Analyse...</span>';

  // Afficher la barre de progression immédiatement
  const progressContainer = document.getElementById("progressContainer");
  if (progressContainer) progressContainer.style.display = "block";
}

// Chargement des stats de base uniquement (ultra-rapide)
async function loadBasicStats() {
  try {
    const response = await fetch(
      "request/stats_non_sap_ultra_fast.php?type=basic_stats&fast=1",
      {
        cache: "no-cache",
      }
    );
    return await response.json();
  } catch (error) {
    console.error("Erreur stats de base:", error);
    return { success: false };
  }
}

// Chargement détaillé en arrière-plan
async function loadDetailedDataInBackground() {
  try {
    const response = await fetch(
      "request/stats_non_sap_ultra_fast.php?type=all_data",
      {
        cache: "no-cache",
      }
    );
    return await response.json();
  } catch (error) {
    console.error("Erreur données détaillées:", error);
    return { success: false };
  }
}

// Mise à jour instantanée des statistiques
function updateStatsInstantly(data = null) {
  const stats = data ? data.stats : allData.stats;

  if (stats) {
    document.getElementById("equipementsNonSAP").textContent =
      stats.equipements_non_sap || 0;
    document.getElementById("articlesNonSAP").textContent =
      stats.articles_non_sap || 0;
    document.getElementById("totalNonSAP").textContent =
      parseInt(stats.equipements_non_sap) + parseInt(stats.articles_non_sap) ||
      0;

    // Supprimer les classes de chargement
    document
      .getElementById("cardEquipements")
      .classList.remove("stat-card-loading");
    document
      .getElementById("cardArticles")
      .classList.remove("stat-card-loading");

    console.log("📊 Stats mises à jour instantanément");
  }
}

// Masquer les spinners pour un affichage plus rapide
function hideLoadingSpinners() {
  const spinners = document.querySelectorAll(".spinner-border");
  spinners.forEach((spinner) => {
    if (!spinner.closest("#progressContainer")) {
      spinner.style.display = "none";
    }
  });
}

// Mise à jour de la barre de progression globale
function updateProgressStatus(percentage) {
  const progressBar = document.getElementById("progressBar");
  const progressRing = document.getElementById("progressRingCircle");
  const globalProgress = document.getElementById("globalProgress");

  if (progressBar) progressBar.style.width = percentage + "%";

  if (progressRing) {
    const circumference = 314;
    const offset = circumference - (percentage / 100) * circumference;
    progressRing.style.strokeDashoffset = offset;
  }

  if (globalProgress) globalProgress.textContent = percentage + "%";

  // Mise à jour des statuts de chargement
  updateLoadingStatuses(percentage);
}

// Mise à jour des statuts des étapes
function updateLoadingStatuses(percentage) {
  const steps = [
    { id: "statusStats", threshold: 30, name: "Statistiques générales" },
    { id: "statusEquipements", threshold: 60, name: "Équipements détaillés" },
    { id: "statusArticles", threshold: 80, name: "Articles détaillés" },
    { id: "statusCharts", threshold: 100, name: "Graphiques d'analyse" },
  ];

  steps.forEach((step) => {
    const spinner = document.getElementById(step.id);
    const icon = document.getElementById(step.id + "Icon");
    const text = document.getElementById(step.id + "Text");

    if (percentage >= step.threshold) {
      if (spinner) spinner.style.display = "none";
      if (icon) {
        icon.style.display = "inline";
        icon.className = "material-icons ms-auto text-success";
        icon.textContent = "check_circle";
      }
      if (text) {
        text.className = "text-success";
        text.textContent = step.name + " ✓";
      }
    } else if (percentage >= step.threshold - 20) {
      if (spinner) {
        spinner.style.display = "inline-block";
        spinner.className =
          "spinner-border spinner-border-sm text-primary me-2";
      }
      if (text) {
        text.className = "text-primary";
        text.textContent = step.name + " (en cours...)";
      }
    }
  });
}

// Mise à jour instantanée de l'aperçu
function updateQuickPreviewInstantly() {
  if (!allData || !allData.equipements_par_famille) return;

  const famillesCount = allData.equipements_par_famille
    ? allData.equipements_par_famille.length
    : 0;
  const metiersCount = allData.articles_par_metier
    ? allData.articles_par_metier.length
    : 0;
  const sourcesEquip = allData.equipements_par_source
    ? allData.equipements_par_source.length
    : 0;
  const sourcesArt = allData.articles_par_source
    ? allData.articles_par_source.length
    : 0;
  const sourcesTotales = Math.max(sourcesEquip, sourcesArt);

  // Mise à jour immédiate
  document.getElementById("previewFamilles").textContent = famillesCount;
  document.getElementById("previewMetiers").textContent = metiersCount;
  document.getElementById("previewSources").textContent = sourcesTotales;
  document.getElementById("previewStatus").innerHTML =
    '<span class="material-icons text-success">check_circle</span>';
  document.getElementById("previewStatusText").textContent =
    "Analyse terminée !";

  // Statistiques avancées
  updateAdvancedStatsInstantly();
  console.log("👁️ Aperçu mis à jour instantanément");
}

// Mise à jour instantanée des statistiques avancées
function updateAdvancedStatsInstantly() {
  if (!allData.stats) return;

  const totalEquipements = parseInt(allData.stats.equipements_non_sap) || 0;
  const totalArticles = parseInt(allData.stats.articles_non_sap) || 0;
  const totalElements = totalEquipements + totalArticles;

  const pourcentage =
    totalElements > 0
      ? Math.round((totalElements / (totalElements * 2)) * 100)
      : 0;
  const famillesPrincipales = allData.equipements_par_famille
    ? allData.equipements_par_famille.length
    : 0;
  const sourcesActives = Math.max(
    allData.equipements_par_source ? allData.equipements_par_source.length : 0,
    allData.articles_par_source ? allData.articles_par_source.length : 0
  );
  const prioriteHaute = Math.round(totalElements * 0.3);

  document.getElementById("pourcentageNonSAP").textContent = pourcentage + "%";
  document.getElementById("famillesPrincipales").textContent =
    famillesPrincipales;
  document.getElementById("sourcesActives").textContent = sourcesActives;
  document.getElementById("prioriteHaute").textContent = prioriteHaute;
}

// Création instantanée de tous les graphiques
function createAllChartsInstantly() {
  try {
    // Vérifier si les éléments canvas existent dans le DOM
    const equipementsFamilleCanvas = document.getElementById(
      "equipementsFamilleChart"
    );
    const articlesMetierCanvas = document.getElementById("articlesMetierChart");
    const equipementsSourceCanvas = document.getElementById(
      "equipementsSourceChart"
    );
    const articlesSourceCanvas = document.getElementById("articlesSourceChart");

    // Si aucun canvas n'est trouvé, les graphiques sont désactivés
    if (
      !equipementsFamilleCanvas &&
      !articlesMetierCanvas &&
      !equipementsSourceCanvas &&
      !articlesSourceCanvas
    ) {
      console.log(
        "📊 Graphiques désactivés - éléments canvas non trouvés dans le DOM"
      );

      // Masquer la section de préparation des graphiques
      const chartPreparationSection = document.getElementById(
        "chartsPreparationSection"
      );
      if (chartPreparationSection) {
        chartPreparationSection.style.display = "none";
      }

      return;
    }

    // Supprimer toutes les overlays de chargement
    hideAllLoadingOverlays();

    // Créer seulement les graphiques dont les canvas existent
    if (
      equipementsFamilleCanvas &&
      allData.equipements_par_famille &&
      allData.equipements_par_famille.length > 0
    ) {
      createEquipementsFamilleChartFast();
    }

    if (
      articlesMetierCanvas &&
      allData.articles_par_metier &&
      allData.articles_par_metier.length > 0
    ) {
      createArticlesMetierChartFast();
    }

    if (
      equipementsSourceCanvas &&
      allData.equipements_par_source &&
      allData.equipements_par_source.length > 0
    ) {
      createEquipementsSourceChartFast();
    }

    if (
      articlesSourceCanvas &&
      allData.articles_par_source &&
      allData.articles_par_source.length > 0
    ) {
      createArticlesSourceChartFast();
    }

    // Masquer la section de préparation
    const chartPreparationSection = document.getElementById(
      "chartsPreparationSection"
    );
    if (chartPreparationSection) {
      chartPreparationSection.style.display = "none";
    }

    console.log("📈 Graphiques créés pour les canvas disponibles");
  } catch (error) {
    console.error("Erreur création graphiques:", error);
  }
}

// Fonctions de création de graphiques
function createEquipementsFamilleChartFast() {
  const ctx = document.getElementById("equipementsFamilleChart");
  if (!ctx) return;

  const data = allData.equipements_par_famille;
  const labels = data.map((item) => item.label || "Non définie");
  const values = data.map((item) => parseInt(item.count));

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
      animation: { duration: 300 },
      plugins: {
        legend: {
          position: "bottom",
          labels: { padding: 15, usePointStyle: true },
        },
      },
    },
  });
}

function createArticlesMetierChartFast() {
  const ctx = document.getElementById("articlesMetierChart");
  if (!ctx) return;

  const data = allData.articles_par_metier;
  const labels = data.map((item) => item.label || "Non défini");
  const values = data.map((item) => parseInt(item.count));

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
      animation: { duration: 300 },
      plugins: {
        legend: {
          position: "bottom",
          labels: { padding: 15, usePointStyle: true },
        },
      },
    },
  });
}

function createEquipementsSourceChartFast() {
  const ctx = document.getElementById("equipementsSourceChart");
  if (!ctx) return;

  const data = allData.equipements_par_source;
  const labels = data.map((item) => item.label || "Inconnue");
  const values = data.map((item) => parseInt(item.count));

  charts.equipementsSource = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: dangerColors.slice(0, labels.length),
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 300 },
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } },
    },
  });
}

function createArticlesSourceChartFast() {
  const ctx = document.getElementById("articlesSourceChart");
  if (!ctx) return;

  const data = allData.articles_par_source;
  const labels = data.map((item) => item.label || "Inconnue");
  const values = data.map((item) => parseInt(item.count));

  charts.articlesSource = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: dangerColors.slice(0, labels.length),
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 300 },
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } },
    },
  });
}

// Chargement ultra-rapide des équipements (par batch)
async function loadEquipementsBatchFast() {
  try {
    const tableBody = document.getElementById("equipementsTableBody");
    const progress = document.getElementById("equipementsProgress");
    const loading = document.getElementById("equipementsLoading");
    const status = document.getElementById("equipementsStatus");

    if (loading) loading.style.display = "inline-block";
    if (status) status.textContent = "Chargement des premiers équipements...";

    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=equipements_details&offset=0&limit=20&fast=1`
    );
    const result = await response.json();

    if (result.success && result.data && result.data.length > 0) {
      if (tableBody) {
        tableBody.innerHTML = result.data
          .map(
            (item) => `
          <tr>
            <td><span class="badge bg-secondary">${
              item.repere || "N/A"
            }</span></td>
            <td><span class="text-truncate">${
              item.famille || "Non définie"
            }</span></td>
            <td><span class="badge bg-outline-secondary">${
              item.source || "N/A"
            }</span></td>
          </tr>
        `
          )
          .join("");
      }

      if (progress) progress.textContent = `20/${result.total || 20}`;
      if (status) status.textContent = `20 équipements chargés`;

      equipementsData.items = result.data;
      equipementsData.currentOffset = 20;
      equipementsData.totalCount = result.total || 20;
      equipementsData.hasMore = result.data.length === 20;

      const loadMoreBtn = document.getElementById("loadMoreEquipements");
      if (loadMoreBtn && equipementsData.hasMore) {
        loadMoreBtn.style.display = "inline-block";
        loadMoreBtn.onclick = () => loadMoreEquipements();
      }
    } else {
      if (tableBody) {
        tableBody.innerHTML =
          '<tr><td colspan="3" class="text-center text-muted">Aucun équipement non SAP trouvé</td></tr>';
      }
    }

    if (loading) loading.style.display = "none";
    console.log("⚙️ Équipements chargés rapidement");
  } catch (error) {
    console.error("Erreur chargement équipements:", error);
  }
}

// Chargement ultra-rapide des articles (par batch)
async function loadArticlesBatchFast() {
  try {
    const tableBody = document.getElementById("articlesTableBody");
    const progress = document.getElementById("articlesProgress");
    const loading = document.getElementById("articlesLoading");
    const status = document.getElementById("articlesStatus");

    if (loading) loading.style.display = "inline-block";
    if (status) status.textContent = "Chargement des premiers articles...";

    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=articles_details&offset=0&limit=20&fast=1`
    );
    const result = await response.json();

    if (result.success && result.data && result.data.length > 0) {
      if (tableBody) {
        tableBody.innerHTML = result.data
          .map(
            (item) => `
          <tr>
            <td><span class="badge bg-secondary">${
              item.code_article || "N/A"
            }</span></td>
            <td><span class="text-truncate">${
              item.metier || "Non défini"
            }</span></td>
            <td><span class="badge bg-outline-secondary">${
              item.source || "N/A"
            }</span></td>
          </tr>
        `
          )
          .join("");
      }

      if (progress) progress.textContent = `20/${result.total || 20}`;
      if (status) status.textContent = `20 articles chargés`;

      articlesData.items = result.data;
      articlesData.currentOffset = 20;
      articlesData.totalCount = result.total || 20;
      articlesData.hasMore = result.data.length === 20;

      const loadMoreBtn = document.getElementById("loadMoreArticles");
      if (loadMoreBtn && articlesData.hasMore) {
        loadMoreBtn.style.display = "inline-block";
        loadMoreBtn.onclick = () => loadMoreArticles();
      }
    } else {
      if (tableBody) {
        tableBody.innerHTML =
          '<tr><td colspan="3" class="text-center text-muted">Aucun article non SAP trouvé</td></tr>';
      }
    }

    if (loading) loading.style.display = "none";
    console.log("📦 Articles chargés rapidement");
  } catch (error) {
    console.error("Erreur chargement articles:", error);
  }
}

// Fonction pour charger plus d'équipements
async function loadMoreEquipements() {
  if (equipementsData.loading || !equipementsData.hasMore) return;

  equipementsData.loading = true;
  const loadMoreBtn = document.getElementById("loadMoreEquipements");
  const tableBody = document.getElementById("equipementsTableBody");

  if (loadMoreBtn) {
    loadMoreBtn.disabled = true;
    loadMoreBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';
  }

  try {
    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=equipements_details&offset=${equipementsData.currentOffset}&limit=50`
    );
    const result = await response.json();

    if (result.success && result.data) {
      result.data.forEach((item) => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td><span class="badge bg-secondary">${
            item.repere || "N/A"
          }</span></td>
          <td><span class="text-truncate">${
            item.famille || "Non définie"
          }</span></td>
          <td><span class="badge bg-outline-secondary">${
            item.source || "N/A"
          }</span></td>
        `;
        if (tableBody) tableBody.appendChild(row);
      });

      equipementsData.items = [...equipementsData.items, ...result.data];
      equipementsData.currentOffset += result.data.length;
      equipementsData.hasMore = result.data.length === 50;

      const progress = document.getElementById("equipementsProgress");
      if (progress)
        progress.textContent = `${equipementsData.items.length}/${equipementsData.totalCount}`;
    }
  } catch (error) {
    console.error("Erreur chargement plus d'équipements:", error);
  }

  if (loadMoreBtn) {
    if (equipementsData.hasMore) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.innerHTML =
        '<span class="material-icons me-1" style="font-size: 16px;">add</span>Charger plus (50)';
    } else {
      loadMoreBtn.style.display = "none";
    }
  }

  equipementsData.loading = false;
}

// Fonction pour charger plus d'articles
async function loadMoreArticles() {
  if (articlesData.loading || !articlesData.hasMore) return;

  articlesData.loading = true;
  const loadMoreBtn = document.getElementById("loadMoreArticles");
  const tableBody = document.getElementById("articlesTableBody");

  if (loadMoreBtn) {
    loadMoreBtn.disabled = true;
    loadMoreBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';
  }

  try {
    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=articles_details&offset=${articlesData.currentOffset}&limit=50`
    );
    const result = await response.json();

    if (result.success && result.data) {
      result.data.forEach((item) => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td><span class="badge bg-secondary">${
            item.code_article || "N/A"
          }</span></td>
          <td><span class="text-truncate">${
            item.metier || "Non défini"
          }</span></td>
          <td><span class="badge bg-outline-secondary">${
            item.source || "N/A"
          }</span></td>
        `;
        if (tableBody) tableBody.appendChild(row);
      });

      articlesData.items = [...articlesData.items, ...result.data];
      articlesData.currentOffset += result.data.length;
      articlesData.hasMore = result.data.length === 50;

      const progress = document.getElementById("articlesProgress");
      if (progress)
        progress.textContent = `${articlesData.items.length}/${articlesData.totalCount}`;
    }
  } catch (error) {
    console.error("Erreur chargement plus d'articles:", error);
  }

  if (loadMoreBtn) {
    if (articlesData.hasMore) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.innerHTML =
        '<span class="material-icons me-1" style="font-size: 16px;">add</span>Charger plus (50)';
    } else {
      loadMoreBtn.style.display = "none";
    }
  }

  articlesData.loading = false;
}

// Masquer toutes les overlays de chargement
function hideAllLoadingOverlays() {
  const overlays = document.querySelectorAll(".loading-overlay");
  overlays.forEach((overlay) => {
    overlay.style.display = "none";
  });
}

// Affichage d'état d'erreur
function showErrorState() {
  document.getElementById("equipementsNonSAP").innerHTML =
    '<span class="text-danger">Erreur</span>';
  document.getElementById("articlesNonSAP").innerHTML =
    '<span class="text-danger">Erreur</span>';
  document.getElementById("totalNonSAP").innerHTML =
    '<span class="text-danger">Erreur de chargement</span>';
  updateProgressStatus(0);
}

// Finalisation du chargement ultra-rapide
function finalizeFastLoading() {
  // Masquer la barre de progression après un court délai
  const progressContainer = document.getElementById("progressContainer");
  if (progressContainer) {
    setTimeout(() => {
      progressContainer.style.display = "none";
    }, 1500);
  }

  // Masquer la section de statut de chargement
  const loadingStatusSection = document.getElementById("loadingStatusSection");
  if (loadingStatusSection) {
    setTimeout(() => {
      loadingStatusSection.style.display = "none";
    }, 3000);
  }

  console.log("🎉 Interface finalisée - Chargement ultra-rapide terminé !");
}

// 🚀 DÉMARRAGE IMMÉDIAT au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  console.log("🚀 Démarrage immédiat du chargement ultra-rapide...");

  // Afficher immédiatement la barre de progression
  const progressContainer = document.getElementById("progressContainer");
  if (progressContainer) {
    progressContainer.style.display = "block";
  }

  // LANCEMENT IMMÉDIAT
  loadAllDataUltraFast();
});
