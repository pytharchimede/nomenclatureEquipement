// VERSION ULTRA-RAPIDE - Configuration des couleurs
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

// Variables globales optimisées
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

// NOUVELLE APPROCHE : Chargement ultra-rapide avec une seule requête
async function loadAllDataUltraFast() {
  try {
    console.log("🚀 Démarrage chargement ultra-rapide...");
    const startTime = performance.now();

    // 1. Charger TOUTES les données en une seule requête
    const response = await fetch(
      "request/stats_non_sap_ultra_fast.php?type=all_data"
    );
    const result = await response.json();

    if (result.success) {
      allData = result.data;

      // 2. Mise à jour immédiate de l'interface (pas d'attente)
      updateStatsInstantly();
      updateQuickPreviewInstantly();

      // 3. Créer tous les graphiques en parallèle
      createAllChartsInstantly();

      // 4. Charger les premiers lots de données détaillées en parallèle
      Promise.all([loadEquipementsBatchFast(), loadArticlesBatchFast()]);

      const endTime = performance.now();
      console.log(
        `✅ Chargement terminé en ${Math.round(endTime - startTime)}ms`
      );

      // 5. Finaliser l'interface
      finalizeFastLoading();
    } else {
      console.error("Erreur chargement données:", result.error);
    }
  } catch (error) {
    console.error("Erreur chargement ultra-rapide:", error);
  }
}

// Mise à jour instantanée des statistiques
function updateStatsInstantly() {
  const stats = allData.stats;

  // Supprimer les spinners et afficher les vraies valeurs
  document.getElementById("equipementsNonSAP").textContent =
    stats.equipements_non_sap || 0;
  document.getElementById("articlesNonSAP").textContent =
    stats.articles_non_sap || 0;
  document.getElementById("totalNonSAP").textContent =
    parseInt(stats.equipements_non_sap) + parseInt(stats.articles_non_sap) || 0;

  // Supprimer les classes de chargement
  document
    .getElementById("cardEquipements")
    .classList.remove("stat-card-loading");
  document.getElementById("cardArticles").classList.remove("stat-card-loading");

  console.log("📊 Stats mises à jour instantanément");
}

// Mise à jour instantanée de l'aperçu
function updateQuickPreviewInstantly() {
  // Calculer les métriques rapidement
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
  const totalEquipements = parseInt(allData.stats.equipements_non_sap) || 0;
  const totalArticles = parseInt(allData.stats.articles_non_sap) || 0;
  const totalElements = totalEquipements + totalArticles;

  // Calculs rapides
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

  // Mise à jour immédiate
  document.getElementById("pourcentageNonSAP").textContent = pourcentage + "%";
  document.getElementById("famillesPrincipales").textContent =
    famillesPrincipales;
  document.getElementById("sourcesActives").textContent = sourcesActives;
  document.getElementById("prioriteHaute").textContent = prioriteHaute;
}

// Création instantanée de tous les graphiques
function createAllChartsInstantly() {
  try {
    // Supprimer toutes les overlays de chargement
    hideAllLoadingOverlays();

    // Créer tous les graphiques en parallèle
    if (
      allData.equipements_par_famille &&
      allData.equipements_par_famille.length > 0
    ) {
      createEquipementsFamilleChartFast();
    }

    if (allData.articles_par_metier && allData.articles_par_metier.length > 0) {
      createArticlesMetierChartFast();
    }

    if (
      allData.equipements_par_source &&
      allData.equipements_par_source.length > 0
    ) {
      createEquipementsSourceChartFast();
    }

    if (allData.articles_par_source && allData.articles_par_source.length > 0) {
      createArticlesSourceChartFast();
    }

    // Masquer la section de préparation
    const chartPreparationSection = document.getElementById(
      "chartsPreparationSection"
    );
    if (chartPreparationSection) {
      chartPreparationSection.style.display = "none";
    }

    console.log("📈 Tous les graphiques créés instantanément");
  } catch (error) {
    console.error("Erreur création graphiques:", error);
  }
}

// Fonctions de création de graphiques optimisées
function createEquipementsFamilleChartFast() {
  const ctx = document.getElementById("equipementsFamilleChart");
  if (!ctx) return;

  const data = allData.equipements_par_famille;
  const labels = data.map((item) => item.label || "Non défini");
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
      animation: { duration: 500 }, // Animation rapide
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
      animation: { duration: 500 },
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
          backgroundColor: dangerColors[0],
          borderColor: dangerColors[1],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 500 },
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
          backgroundColor: dangerColors[2],
          borderColor: dangerColors[3],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 500 },
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } },
    },
  });
}

// Chargement rapide des équipements détaillés
async function loadEquipementsBatchFast() {
  if (equipementsData.loading) return;

  equipementsData.loading = true;

  try {
    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=equipements_details&offset=${equipementsData.currentOffset}&limit=50`
    );
    const data = await response.json();

    if (data.success) {
      equipementsData.items = [...equipementsData.items, ...data.data];
      equipementsData.currentOffset += data.data.length;
      equipementsData.totalCount = data.total_count;
      equipementsData.hasMore = data.has_more;

      updateEquipementsTableFast();
      updateEquipementsProgressFast();
    }
  } catch (error) {
    console.error("Erreur équipements rapide:", error);
  } finally {
    equipementsData.loading = false;
  }
}

// Chargement rapide des articles détaillés
async function loadArticlesBatchFast() {
  if (articlesData.loading) return;

  articlesData.loading = true;

  try {
    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=articles_details&offset=${articlesData.currentOffset}&limit=50`
    );
    const data = await response.json();

    if (data.success) {
      articlesData.items = [...articlesData.items, ...data.data];
      articlesData.currentOffset += data.data.length;
      articlesData.totalCount = data.total_count;
      articlesData.hasMore = data.has_more;

      updateArticlesTableFast();
      updateArticlesProgressFast();
    }
  } catch (error) {
    console.error("Erreur articles rapide:", error);
  } finally {
    articlesData.loading = false;
  }
}

// Mise à jour rapide des tables
function updateEquipementsTableFast() {
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

function updateArticlesTableFast() {
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

function updateEquipementsProgressFast() {
  const progressEl = document.getElementById("equipementsProgress");
  const statusEl = document.getElementById("equipementsStatus");
  const loadMoreBtn = document.getElementById("loadMoreEquipements");

  if (progressEl)
    progressEl.textContent = `${equipementsData.items.length}/${equipementsData.totalCount}`;
  if (statusEl)
    statusEl.textContent = `${equipementsData.items.length} équipements chargés`;
  if (loadMoreBtn)
    loadMoreBtn.style.display = equipementsData.hasMore
      ? "inline-block"
      : "none";
}

function updateArticlesProgressFast() {
  const progressEl = document.getElementById("articlesProgress");
  const statusEl = document.getElementById("articlesStatus");
  const loadMoreBtn = document.getElementById("loadMoreArticles");

  if (progressEl)
    progressEl.textContent = `${articlesData.items.length}/${articlesData.totalCount}`;
  if (statusEl)
    statusEl.textContent = `${articlesData.items.length} articles chargés`;
  if (loadMoreBtn)
    loadMoreBtn.style.display = articlesData.hasMore ? "inline-block" : "none";
}

// Supprimer toutes les overlays de chargement
function hideAllLoadingOverlays() {
  const loadingIds = [
    "loadingEquipFamille",
    "loadingArticlesMetier",
    "loadingEquipSource",
    "loadingArticlesSource",
  ];
  loadingIds.forEach((id) => {
    const element = document.getElementById(id);
    if (element) element.style.display = "none";
  });
}

// Finalisation du chargement rapide
function finalizeFastLoading() {
  // Masquer toutes les sections de chargement
  const loadingSection = document.getElementById("loadingStatusSection");
  if (loadingSection) loadingSection.style.display = "none";

  const progressContainer = document.getElementById("progressContainer");
  if (progressContainer) progressContainer.style.display = "none";

  console.log("🎉 Interface finalisée - Chargement ultra-rapide terminé !");
}

// Event listeners optimisés
document.addEventListener("DOMContentLoaded", function () {
  // Boutons pour charger plus
  const loadMoreEquipements = document.getElementById("loadMoreEquipements");
  const loadMoreArticles = document.getElementById("loadMoreArticles");

  if (loadMoreEquipements) {
    loadMoreEquipements.addEventListener("click", loadEquipementsBatchFast);
  }

  if (loadMoreArticles) {
    loadMoreArticles.addEventListener("click", loadArticlesBatchFast);
  }

  // DÉMARRAGE IMMÉDIAT - Pas d'attente !
  console.log("🚀 Démarrage immédiat du chargement ultra-rapide...");
  loadAllDataUltraFast();
});
