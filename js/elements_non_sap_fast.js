// VERSION ULTRA-LÉGÈRE - PERFORMANCE MAXIMALE
// Suppression de tout ce qui n'est pas essentiel

// Variables globales minimales
let allData = {};

// CHARGEMENT ULTRA-SIMPLE - Juste les données essentielles
async function loadDataLight() {
  try {
    console.log("⚡ Chargement ultra-léger...");

    // UNE SEULE requête simple
    const response = await fetch(
      "request/stats_non_sap_ultra_fast.php?type=everything&limit=100"
    );
    const result = await response.json();

    if (result.success) {
      // Mise à jour IMMÉDIATE des stats principales
      updateStatsLight(result.stats);

      // Affichage simple des tableaux
      displayEquipementsLight(result.equipements || []);
      displayArticlesLight(result.articles || []);

      console.log("✅ Données chargées ultra-rapidement");
    } else {
      showErrorLight();
    }
  } catch (error) {
    console.error("Erreur:", error);
    showErrorLight();
  }
}

// Mise à jour ultra-simple des stats
function updateStatsLight(stats) {
  if (stats) {
    document.getElementById("equipementsNonSAP").textContent =
      stats.equipements_non_sap || 0;
    document.getElementById("articlesNonSAP").textContent =
      stats.articles_non_sap || 0;
    document.getElementById("totalNonSAP").textContent =
      parseInt(stats.equipements_non_sap) + parseInt(stats.articles_non_sap) ||
      0;
  }
}

// Affichage ultra-simple des équipements
function displayEquipementsLight(equipements) {
  const tableBody = document.getElementById("equipementsTableBody");
  if (!tableBody) return;

  if (equipements.length > 0) {
    tableBody.innerHTML = equipements
      .slice(0, 100)
      .map(
        (item) => `
      <tr>
        <td>${item.repere || "N/A"}</td>
        <td>${item.famille || "Non définie"}</td>
        <td>${item.source || "N/A"}</td>
      </tr>
    `
      )
      .join("");
  } else {
    tableBody.innerHTML =
      '<tr><td colspan="3" class="text-center">Aucun équipement</td></tr>';
  }
}

// Affichage ultra-simple des articles
function displayArticlesLight(articles) {
  const tableBody = document.getElementById("articlesTableBody");
  if (!tableBody) return;

  if (articles.length > 0) {
    tableBody.innerHTML = articles
      .slice(0, 100)
      .map(
        (item) => `
      <tr>
        <td>${item.code_article || "N/A"}</td>
        <td>${item.metier || "Non défini"}</td>
        <td>${item.source || "N/A"}</td>
      </tr>
    `
      )
      .join("");
  } else {
    tableBody.innerHTML =
      '<tr><td colspan="3" class="text-center">Aucun article</td></tr>';
  }
}

// Gestion d'erreur simple
function showErrorLight() {
  document.getElementById("equipementsNonSAP").textContent = "Erreur";
  document.getElementById("articlesNonSAP").textContent = "Erreur";
  document.getElementById("totalNonSAP").textContent = "Erreur";
}

// Démarrage immédiat et simple
document.addEventListener("DOMContentLoaded", function () {
  loadDataLight();
});

// Affichage immédiat pour éviter l'écran blanc
function showImmediatePreview() {
  // Afficher immédiatement des valeurs temporaires
  document.getElementById("equipementsNonSAP").innerHTML =
    '<span class="text-muted">...</span>';
  document.getElementById("articlesNonSAP").innerHTML =
    '<span class="text-muted">...</span>';
  document.getElementById("totalNonSAP").innerHTML =
    '<span class="text-muted">Calcul...</span>';

  // Masquer les spinners de chargement
  hideLoadingSpinners();

  updateProgressStatus(10);
}

// Affichage INSTANTANÉ des équipements
function displayEquipementsInstantly(equipements) {
  const tableBody = document.getElementById("equipementsTableBody");
  const progress = document.getElementById("equipementsProgress");
  const status = document.getElementById("equipementsStatus");

  if (tableBody && equipements.length > 0) {
    // Limiter à 500 pour performance
    const displayEquipements = equipements.slice(0, 500);

    tableBody.innerHTML = displayEquipements
      .map(
        (item) => `
      <tr>
        <td><span class="badge bg-secondary">${item.repere || "N/A"}</span></td>
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

    // Mise à jour du progress
    if (progress)
      progress.textContent = `${displayEquipements.length}/${equipements.length}`;
    if (status)
      status.textContent = `${displayEquipements.length} équipements affichés${
        equipements.length > 500
          ? " (sur " + equipements.length + " total)"
          : ""
      }`;

    // Sauvegarder toutes les données
    equipementsData.items = equipements;
    equipementsData.totalCount = equipements.length;
    equipementsData.hasMore = false;

    // Bouton pour voir plus si nécessaire
    if (equipements.length > 500) {
      addShowAllButton("equipements", equipements.length);
    }
  } else {
    if (tableBody) {
      tableBody.innerHTML =
        '<tr><td colspan="3" class="text-center text-muted">Aucun équipement non SAP trouvé</td></tr>';
    }
  }

  console.log(`⚙️ ${equipements.length} équipements affichés instantanément`);
}

// Affichage INSTANTANÉ des articles
function displayArticlesInstantly(articles) {
  const tableBody = document.getElementById("articlesTableBody");
  const progress = document.getElementById("articlesProgress");
  const status = document.getElementById("articlesStatus");

  if (tableBody && articles.length > 0) {
    // Limiter à 500 pour performance
    const displayArticles = articles.slice(0, 500);

    tableBody.innerHTML = displayArticles
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

    // Mise à jour du progress
    if (progress)
      progress.textContent = `${displayArticles.length}/${articles.length}`;
    if (status)
      status.textContent = `${displayArticles.length} articles affichés${
        articles.length > 500 ? " (sur " + articles.length + " total)" : ""
      }`;

    // Sauvegarder toutes les données
    articlesData.items = articles;
    articlesData.totalCount = articles.length;
    articlesData.hasMore = false;

    // Bouton pour voir plus si nécessaire
    if (articles.length > 500) {
      addShowAllButton("articles", articles.length);
    }
  } else {
    if (tableBody) {
      tableBody.innerHTML =
        '<tr><td colspan="3" class="text-center text-muted">Aucun article non SAP trouvé</td></tr>';
    }
  }

  console.log(`📦 ${articles.length} articles affichés instantanément`);
}

// Ajouter bouton "Voir tout" si nécessaire
function addShowAllButton(type, total) {
  const statusId =
    type === "equipements" ? "#equipementsStatus" : "#articlesStatus";
  const statusDiv = document.querySelector(statusId).parentNode;

  if (statusDiv && !statusDiv.querySelector(".btn-show-all")) {
    const showAllBtn = document.createElement("button");
    showAllBtn.className = "btn btn-primary btn-sm ms-2 btn-show-all";
    showAllBtn.innerHTML = `<span class="material-icons me-1" style="font-size: 16px;">visibility</span>Voir tout (${total})`;
    showAllBtn.onclick = () => showAllData(type);
    statusDiv.appendChild(showAllBtn);
  }
}

// Afficher TOUTES les données d'un type
function showAllData(type) {
  if (type === "equipements") {
    displayAllEquipements();
  } else if (type === "articles") {
    displayAllArticles();
  }
}

// Afficher TOUS les équipements
function displayAllEquipements() {
  const tableBody = document.getElementById("equipementsTableBody");
  const progress = document.getElementById("equipementsProgress");
  const status = document.getElementById("equipementsStatus");

  if (tableBody && equipementsData.items.length > 0) {
    tableBody.innerHTML = equipementsData.items
      .map(
        (item) => `
      <tr>
        <td><span class="badge bg-secondary">${item.repere || "N/A"}</span></td>
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

    if (progress)
      progress.textContent = `${equipementsData.items.length}/${equipementsData.items.length}`;
    if (status)
      status.textContent = `Tous les équipements affichés (${equipementsData.items.length} total)`;

    // Masquer le bouton "Voir tout"
    const showAllBtn = document.querySelector(".btn-show-all");
    if (showAllBtn) showAllBtn.style.display = "none";
  }
}

// Afficher TOUS les articles
function displayAllArticles() {
  const tableBody = document.getElementById("articlesTableBody");
  const progress = document.getElementById("articlesProgress");
  const status = document.getElementById("articlesStatus");

  if (tableBody && articlesData.items.length > 0) {
    tableBody.innerHTML = articlesData.items
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

    if (progress)
      progress.textContent = `${articlesData.items.length}/${articlesData.items.length}`;
    if (status)
      status.textContent = `Tous les articles affichés (${articlesData.items.length} total)`;

    // Masquer le bouton "Voir tout"
    const showAllBtn = document.querySelector(".btn-show-all");
    if (showAllBtn) showAllBtn.style.display = "none";
  }
}

// Mise à jour instantanée des statistiques
function updateStatsInstantly(data = null) {
  const stats = data ? data.stats : allData.stats;

  if (stats) {
    // Supprimer les spinners et afficher les vraies valeurs
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

  if (progressBar) {
    progressBar.style.width = percentage + "%";
  }

  if (progressRing) {
    const circumference = 314;
    const offset = circumference - (percentage / 100) * circumference;
    progressRing.style.strokeDashoffset = offset;
  }

  if (globalProgress) {
    globalProgress.textContent = percentage + "%";
  }

  // Mise à jour des statuts de chargement
  updateLoadingStatuses(percentage);
}

// Mise à jour des statuts des étapes
function updateLoadingStatuses(percentage) {
  const steps = [
    { id: "statusStats", threshold: 25, name: "Statistiques générales" },
    { id: "statusEquipements", threshold: 50, name: "Équipements détaillés" },
    { id: "statusArticles", threshold: 75, name: "Articles détaillés" },
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
    } else if (percentage >= step.threshold - 25) {
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
      animation: { duration: 300 }, // Animation rapide
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

// Fonctions de création de graphiques optimisées
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
async function loadEquipementsBatchFast() {
  try {
    const tableBody = document.getElementById("equipementsTableBody");
    const progress = document.getElementById("equipementsProgress");
    const loading = document.getElementById("equipementsLoading");
    const status = document.getElementById("equipementsStatus");

    // Affichage immédiat de l'état de chargement
    if (loading) loading.style.display = "inline-block";
    if (status) status.textContent = "Chargement des premiers équipements...";

    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=equipements_details&offset=0&limit=100&fast=1`
    );
    const result = await response.json();

    if (result.success && result.data && result.data.length > 0) {
      // Vider le tableau et ajouter les premières données
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

      // Mettre à jour le progress
      if (progress) progress.textContent = `100/${result.total || 100}`;
      if (status)
        status.textContent = `100 équipements chargés (sur ${
          result.total || 0
        } total)`;

      // Configuration pour le "charger plus"
      equipementsData.items = result.data;
      equipementsData.currentOffset = 100;
      equipementsData.totalCount = result.total || 100;
      equipementsData.hasMore = result.data.length === 100;

      // Activer le bouton "charger plus" si nécessaire
      const loadMoreBtn = document.getElementById("loadMoreEquipements");
      if (loadMoreBtn && equipementsData.hasMore) {
        loadMoreBtn.style.display = "inline-block";
        loadMoreBtn.onclick = () => loadMoreEquipements();
      }

      // Ajouter un bouton "Tout charger" si il y a plus de données
      if (result.total && result.total > 100) {
        const loadAllBtn = document.createElement("button");
        loadAllBtn.className = "btn btn-primary btn-sm ms-2";
        loadAllBtn.innerHTML =
          '<span class="material-icons me-1" style="font-size: 16px;">visibility</span>Voir tout (' +
          result.total +
          ")";
        loadAllBtn.onclick = () => loadAllEquipements();

        const statusDiv =
          document.querySelector("#equipementsStatus").parentNode;
        if (statusDiv && !statusDiv.querySelector(".btn-primary")) {
          statusDiv.appendChild(loadAllBtn);
        }
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
    const tableBody = document.getElementById("equipementsTableBody");
    if (tableBody) {
      tableBody.innerHTML =
        '<tr><td colspan="3" class="text-center text-danger">Erreur de chargement</td></tr>';
    }
  }
}

// Chargement ultra-rapide des articles (par batch)
async function loadArticlesBatchFast() {
  try {
    const tableBody = document.getElementById("articlesTableBody");
    const progress = document.getElementById("articlesProgress");
    const loading = document.getElementById("articlesLoading");
    const status = document.getElementById("articlesStatus");

    // Affichage immédiat de l'état de chargement
    if (loading) loading.style.display = "inline-block";
    if (status) status.textContent = "Chargement des premiers articles...";

    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=articles_details&offset=0&limit=100&fast=1`
    );
    const result = await response.json();

    if (result.success && result.data && result.data.length > 0) {
      // Vider le tableau et ajouter les premières données
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

      // Mettre à jour le progress
      if (progress) progress.textContent = `100/${result.total || 100}`;
      if (status)
        status.textContent = `100 articles chargés (sur ${
          result.total || 0
        } total)`;

      // Configuration pour le "charger plus"
      articlesData.items = result.data;
      articlesData.currentOffset = 100;
      articlesData.totalCount = result.total || 100;
      articlesData.hasMore = result.data.length === 100;

      // Activer le bouton "charger plus" si nécessaire
      const loadMoreBtn = document.getElementById("loadMoreArticles");
      if (loadMoreBtn && articlesData.hasMore) {
        loadMoreBtn.style.display = "inline-block";
        loadMoreBtn.onclick = () => loadMoreArticles();
      }

      // Ajouter un bouton "Tout charger" si il y a plus de données
      if (result.total && result.total > 100) {
        const loadAllBtn = document.createElement("button");
        loadAllBtn.className = "btn btn-primary btn-sm ms-2";
        loadAllBtn.innerHTML =
          '<span class="material-icons me-1" style="font-size: 16px;">visibility</span>Voir tout (' +
          result.total +
          ")";
        loadAllBtn.onclick = () => loadAllArticles();

        const statusDiv = document.querySelector("#articlesStatus").parentNode;
        if (statusDiv && !statusDiv.querySelector(".btn-primary")) {
          statusDiv.appendChild(loadAllBtn);
        }
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
    const tableBody = document.getElementById("articlesTableBody");
    if (tableBody) {
      tableBody.innerHTML =
        '<tr><td colspan="3" class="text-center text-danger">Erreur de chargement</td></tr>';
    }
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
      // Ajouter les nouvelles lignes
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

      // Mettre à jour le progress
      const progress = document.getElementById("equipementsProgress");
      if (progress)
        progress.textContent = `${equipementsData.items.length}/${equipementsData.totalCount}`;
    }
  } catch (error) {
    console.error("Erreur chargement plus d'équipements:", error);
  }

  // Restaurer le bouton
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

// Fonction pour charger TOUS les équipements
async function loadAllEquipements() {
  if (equipementsData.loading) return;

  equipementsData.loading = true;
  const tableBody = document.getElementById("equipementsTableBody");
  const status = document.getElementById("equipementsStatus");

  if (status) status.textContent = "Chargement de tous les équipements...";

  try {
    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=equipements_details&offset=0&limit=10000`
    );
    const result = await response.json();

    if (result.success && result.data) {
      // Remplacer tout le contenu du tableau
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

      equipementsData.items = result.data;
      equipementsData.currentOffset = result.data.length;
      equipementsData.hasMore = false;

      // Mettre à jour le progress
      const progress = document.getElementById("equipementsProgress");
      if (progress)
        progress.textContent = `${result.data.length}/${result.data.length}`;
      if (status)
        status.textContent = `Tous les équipements chargés (${result.data.length} total)`;

      // Masquer les boutons "charger plus"
      const loadMoreBtn = document.getElementById("loadMoreEquipements");
      if (loadMoreBtn) loadMoreBtn.style.display = "none";

      // Masquer le bouton "Tout charger"
      const loadAllBtns = document.querySelectorAll(".btn-primary");
      loadAllBtns.forEach((btn) => {
        if (btn.textContent.includes("Voir tout")) {
          btn.style.display = "none";
        }
      });
    }
  } catch (error) {
    console.error("Erreur chargement tous équipements:", error);
    if (status) status.textContent = "Erreur lors du chargement complet";
  }

  equipementsData.loading = false;
}

// Fonction pour charger TOUS les articles
async function loadAllArticles() {
  if (articlesData.loading) return;

  articlesData.loading = true;
  const tableBody = document.getElementById("articlesTableBody");
  const status = document.getElementById("articlesStatus");

  if (status) status.textContent = "Chargement de tous les articles...";

  try {
    const response = await fetch(
      `request/stats_non_sap_ultra_fast.php?type=articles_details&offset=0&limit=10000`
    );
    const result = await response.json();

    if (result.success && result.data) {
      // Remplacer tout le contenu du tableau
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

      articlesData.items = result.data;
      articlesData.currentOffset = result.data.length;
      articlesData.hasMore = false;

      // Mettre à jour le progress
      const progress = document.getElementById("articlesProgress");
      if (progress)
        progress.textContent = `${result.data.length}/${result.data.length}`;
      if (status)
        status.textContent = `Tous les articles chargés (${result.data.length} total)`;

      // Masquer les boutons "charger plus"
      const loadMoreBtn = document.getElementById("loadMoreArticles");
      if (loadMoreBtn) loadMoreBtn.style.display = "none";

      // Masquer le bouton "Tout charger"
      const loadAllBtns = document.querySelectorAll(".btn-primary");
      loadAllBtns.forEach((btn) => {
        if (btn.textContent.includes("Voir tout")) {
          btn.style.display = "none";
        }
      });
    }
  } catch (error) {
    console.error("Erreur chargement tous articles:", error);
    if (status) status.textContent = "Erreur lors du chargement complet";
  }

  articlesData.loading = false;
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
      // Ajouter les nouvelles lignes
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

      // Mettre à jour le progress
      const progress = document.getElementById("articlesProgress");
      if (progress)
        progress.textContent = `${articlesData.items.length}/${articlesData.totalCount}`;
    }
  } catch (error) {
    console.error("Erreur chargement plus d'articles:", error);
  }

  // Restaurer le bouton
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
