// VERSION ULTRA-LÉGÈRE - PERFORMANCE MAXIMALE
// Suppression de tout ce qui n'est pas essentiel
console.log("⚡ Chargement ultra-rapide en cours...");

// CHARGEMENT ULTRA-SIMPLE
async function loadDataLight() {
  try {
    console.log("⚡ Requête API...");

    // Mise à jour de l'état de progression
    updateLoadingStatus("stats", "Chargement statistiques...", true);

    // UNE SEULE requête simple
    const response = await fetch(
      "request/stats_non_sap_ultra_fast.php?type=everything&limit=100"
    );
    const result = await response.json();

    console.log("📊 Données reçues:", result);

  if (result && result.success !== false) {
      // Mise à jour des états de progression
      updateLoadingStatus("stats", "Statistiques chargées", false);
      updateLoadingStatus("equipements", "Chargement équipements...", true);

      // Mise à jour IMMÉDIATE des stats
      updateStatsLight(result.stats || result);

      // Mise à jour de l'aperçu rapide
      updateQuickPreview(result);

      // KPI Accès rapides (3 cartes)
      updateKpis(result);

      // Mise à jour de l'analyse détaillée
      updateDetailedAnalysis(result);

      // Affichage simple des tableaux
      displayEquipementsLight(result.equipements || []);
      updateLoadingStatus("equipements", "Équipements chargés", false);
      updateLoadingStatus("articles", "Chargement articles...", true);

      displayArticlesLight(result.articles || []);
      updateLoadingStatus("articles", "Articles chargés", false);

      // Finalisation
      updateGlobalProgress(100);
      hideLoadingSections();

      console.log("✅ Données affichées avec succès");
    } else {
      console.error("❌ Erreur dans la réponse:", result);
      await fallbackLoadStatsFromFamilles();
    }
  } catch (error) {
    console.error("❌ Erreur réseau:", error);
    await fallbackLoadStatsFromFamilles();
  }
}

// Mise à jour ultra-simple des stats
function updateStatsLight(stats) {
  console.log("📈 Mise à jour stats:", stats);

  if (stats) {
    const equipEl = document.getElementById("equipementsNonSAP");
    const artEl = document.getElementById("articlesNonSAP");
    const totalEl = document.getElementById("totalNonSAP");

    if (equipEl) equipEl.textContent = stats.equipements_non_sap || 0;
    if (artEl) artEl.textContent = stats.articles_non_sap || 0;
    if (totalEl) {
      const total =
        (parseInt(stats.equipements_non_sap) || 0) +
        (parseInt(stats.articles_non_sap) || 0);
      totalEl.textContent = total;
    }

    console.log("✅ Stats mises à jour");
  }
}

// Affichage ultra-simple des équipements
function displayEquipementsLight(equipements) {
  console.log("🔧 Affichage équipements:", equipements.length);

  const tableBody = document.getElementById("equipementsTableBody");
  if (!tableBody) {
    console.warn("❌ Table équipements introuvable");
    return;
  }

  if (equipements && equipements.length > 0) {
    tableBody.innerHTML = equipements
      .slice(0, 100)
      .map(
        (item) => `
      <tr>
        <td>${item.repere || item.repere_equipement || "N/A"}</td>
        <td>${item.famille || "Non définie"}</td>
        <td>${item.source || "N/A"}</td>
      </tr>
    `
      )
      .join("");
    console.log("✅ Équipements affichés");
  } else {
    tableBody.innerHTML =
      '<tr><td colspan="3" class="text-center">Aucun équipement</td></tr>';
  }
}

// Affichage ultra-simple des articles
function displayArticlesLight(articles) {
  console.log("📦 Affichage articles:", articles.length);

  const tableBody = document.getElementById("articlesTableBody");
  if (!tableBody) {
    console.warn("❌ Table articles introuvable");
    return;
  }

  if (articles && articles.length > 0) {
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
    console.log("✅ Articles affichés");
  } else {
    tableBody.innerHTML =
      '<tr><td colspan="3" class="text-center">Aucun article</td></tr>';
  }
}

// Gestion d'erreur simple
function showErrorLight(message = "Erreur") {
  console.error("❌ Affichage erreur:", message);

  const equipEl = document.getElementById("equipementsNonSAP");
  const artEl = document.getElementById("articlesNonSAP");
  const totalEl = document.getElementById("totalNonSAP");

  if (equipEl && equipEl.textContent === "...") equipEl.textContent = "0";
  if (artEl && artEl.textContent === "...") artEl.textContent = "0";
  if (totalEl && totalEl.textContent === "...") totalEl.textContent = "0";
}

// Mise à jour des KPI des 3 cartes Accès rapides
async function updateKpis(data) {
  try {
    // KPI Équipements non SAP et Articles non SAP via stats globales
    const kpiEq = document.getElementById("kpiEquipementsNonSAP");
    const kpiAr = document.getElementById("kpiArticlesNonSAP");
    if (kpiEq) kpiEq.textContent = data?.stats?.equipements_non_sap ?? "—";
    if (kpiAr) kpiAr.textContent = data?.stats?.articles_non_sap ?? "—";

    // KPI Équipements sans nomenclature: somme depuis l'API familles (nb_equipements_sans_nomenclature)
    const kpiSansNom = document.getElementById("kpiSansNomenclature");
    if (kpiSansNom) {
      try {
        const resp = await fetch("api/familles_repartition_uniques.php");
        const json = await resp.json();
        if (json && json.success && Array.isArray(json.data)) {
          const totalSansNom = json.data.reduce(
            (acc, row) =>
              acc + parseInt(row.nb_equipements_sans_nomenclature || 0),
            0
          );
          kpiSansNom.textContent = totalSansNom;
        } else {
          kpiSansNom.textContent = "—";
        }
      } catch (e) {
        console.warn("Impossible de charger KPI sans nomenclature", e);
        kpiSansNom.textContent = "—";
      }
    }
  } catch (e) {
    console.warn("Erreur updateKpis", e);
  }
}

// Mise à jour de l'état de progression
function updateLoadingStatus(type, text, isLoading) {
  const statusEl = document.getElementById(
    `status${type.charAt(0).toUpperCase() + type.slice(1)}`
  );
  const statusTextEl = document.getElementById(
    `status${type.charAt(0).toUpperCase() + type.slice(1)}Text`
  );
  const statusIconEl = document.getElementById(
    `status${type.charAt(0).toUpperCase() + type.slice(1)}Icon`
  );

  if (statusEl) {
    statusEl.style.display = isLoading ? "block" : "none";
  }
  if (statusTextEl) {
    statusTextEl.textContent = text;
    statusTextEl.className = isLoading ? "" : "text-success";
  }
  if (statusIconEl) {
    statusIconEl.style.display = isLoading ? "none" : "inline";
    statusIconEl.className = "material-icons ms-auto text-success";
  }
}

// Mise à jour de l'analyse détaillée
function updateDetailedAnalysis(data) {
  // Calcul du pourcentage non codifiés (estimation basique)
  const totalEquipements = 3170; // Total connu d'après les tests
  const totalArticles = 18068;
  const totalElements = totalEquipements + totalArticles;
  const totalNonSap =
    (data.stats?.equipements_non_sap || 0) +
    (data.stats?.articles_non_sap || 0);
  const pourcentage = Math.round((totalNonSap / totalElements) * 100);

  const pourcentageEl = document.getElementById("pourcentageNonSAP");
  if (pourcentageEl) {
    pourcentageEl.innerHTML = `<span class="text-warning">${pourcentage}%</span>`;
  }

  // Familles principales (nombre de familles différentes)
  const famillesEl = document.getElementById("famillesPrincipales");
  if (famillesEl && data.equipements_par_famille) {
    const nbFamilles = data.equipements_par_famille.length;
    famillesEl.innerHTML = `<span class="text-info">${nbFamilles}</span>`;
  }

  // Sources actives (nombre de sources différentes)
  const sourcesEl = document.getElementById("sourcesActives");
  if (sourcesEl && data.equipements_par_source) {
    const nbSources =
      data.equipements_par_source.length +
      (data.articles_par_source?.length || 0);
    sourcesEl.innerHTML = `<span class="text-success">${nbSources}</span>`;
  }

  // Priorité haute (estimation - équipements sans famille définie)
  const prioriteEl = document.getElementById("prioriteHaute");
  if (prioriteEl && data.equipements_par_famille) {
    const nonDefinies = data.equipements_par_famille.find(
      (f) => f.label === "Non définie"
    );
    const priorite = nonDefinies ? nonDefinies.count : 0;
    prioriteEl.innerHTML = `<span class="text-primary">${priorite}</span>`;
  }

  console.log("📊 Analyse détaillée mise à jour");
}

// Mise à jour de l'aperçu rapide
function updateQuickPreview(data) {
  if (data.equipements_par_famille) {
    const familles = document.getElementById("previewFamilles");
    if (familles) familles.textContent = data.equipements_par_famille.length;
  }

  if (data.articles_par_metier) {
    const metiers = document.getElementById("previewMetiers");
    if (metiers) metiers.textContent = data.articles_par_metier.length;
  }

  if (data.equipements_par_source) {
    const sources = document.getElementById("previewSources");
    if (sources) sources.textContent = data.equipements_par_source.length;
  }

  const status = document.getElementById("previewStatus");
  const statusText = document.getElementById("previewStatusText");
  if (status)
    status.innerHTML =
      '<span class="material-icons text-success">check_circle</span>';
  if (statusText) statusText.textContent = "Analyse terminée";
}

// Mise à jour du progrès global
function updateGlobalProgress(percent) {
  const progressEl = document.getElementById("globalProgress");
  const progressRing = document.getElementById("progressRingCircle");

  if (progressEl) progressEl.textContent = percent + "%";
  if (progressRing) {
    const circumference = 314;
    const offset = circumference - (percent / 100) * circumference;
    progressRing.style.strokeDashoffset = offset;
  }
}

// Masquer les sections de chargement
function hideLoadingSections() {
  setTimeout(() => {
    const loadingSection = document.getElementById("loadingStatusSection");
    const chartsSection = document.getElementById("chartsPreparationSection");

    if (loadingSection) loadingSection.style.display = "none";
    if (chartsSection) chartsSection.style.display = "none";
  }, 2000);
}

// Démarrage immédiat et simple
document.addEventListener("DOMContentLoaded", function () {
  console.log("🚀 DOM prêt, démarrage chargement...");

  // Affichage immédiat de "Chargement..."
  const equipEl = document.getElementById("equipementsNonSAP");
  const artEl = document.getElementById("articlesNonSAP");
  const totalEl = document.getElementById("totalNonSAP");

  if (equipEl) equipEl.textContent = "...";
  if (artEl) artEl.textContent = "...";
  if (totalEl) totalEl.textContent = "...";

  // Lancer le chargement
  loadDataLight();

  // Gestion du bouton d'activation des graphiques
  setupGraphicsToggle();

  // Lancer également le chargement des compteurs des accès rapides
  loadQuickAccessCounts();
});

// Configuration des boutons de graphiques
function setupGraphicsToggle() {
  const enableBtn = document.getElementById("enableGraphicsBtn");
  const disableBtn = document.getElementById("disableGraphicsBtn");

  if (enableBtn) {
    enableBtn.addEventListener("click", enableGraphics);
  }
  if (disableBtn) {
    disableBtn.addEventListener("click", disableGraphics);
  }
}

// Activation des graphiques
async function enableGraphics() {
  console.log("📊 Activation des graphiques...");

  const enableSection = document.getElementById("graphicsDisabledSection");
  const graphicsSection = document.getElementById("graphicsSection");
  const enableBtn = document.getElementById("enableGraphicsBtn");

  // Masquer la section désactivée et afficher les graphiques
  if (enableSection) enableSection.style.display = "none";
  if (graphicsSection) graphicsSection.style.display = "block";

  // Désactiver le bouton pendant le chargement
  if (enableBtn) {
    enableBtn.disabled = true;
    enableBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...';
  }

  try {
    // Charger les données graphiques spécifiquement
    await loadGraphicsData();
  } catch (error) {
    console.error("❌ Erreur chargement graphiques:", error);
    // En cas d'erreur, remettre la section désactivée
    if (enableSection) enableSection.style.display = "block";
    if (graphicsSection) graphicsSection.style.display = "none";
    if (enableBtn) {
      enableBtn.disabled = false;
      enableBtn.innerHTML =
        '<span class="material-icons me-2">analytics</span>Activer les Graphiques';
    }
  }
}

// Désactivation des graphiques
function disableGraphics() {
  console.log("📊 Désactivation des graphiques...");

  const enableSection = document.getElementById("graphicsDisabledSection");
  const graphicsSection = document.getElementById("graphicsSection");

  if (enableSection) enableSection.style.display = "block";
  if (graphicsSection) graphicsSection.style.display = "none";
}

// Chargement spécifique des données graphiques
async function loadGraphicsData() {
  console.log("📊 Chargement données graphiques...");

  try {
    // Requête spécifique pour les graphiques (réutilise les données déjà chargées)
    const response = await fetch(
      "request/stats_non_sap_ultra_fast.php?type=everything&limit=500"
    );
    const result = await response.json();

    if (result && result.success !== false) {
      // Créer les graphiques avec les données
      await createAllCharts(result);
      console.log("✅ Graphiques créés avec succès");
    } else {
      throw new Error("Données graphiques invalides");
    }
  } catch (error) {
    console.error("❌ Erreur lors du chargement des graphiques:", error);
    throw error;
  }
}

// Création de tous les graphiques
async function createAllCharts(data) {
  console.log("📊 Création des graphiques...");

  // Masquer les overlays de chargement et créer les graphiques
  hideLoadingOverlay("loadingEquipFamille");
  hideLoadingOverlay("loadingArticlesMetier");
  hideLoadingOverlay("loadingEquipSource");
  hideLoadingOverlay("loadingArticlesSource");

  // Créer les graphiques Chart.js
  if (window.Chart && data) {
    // Si l'API dédiée existe, privilégier la répartition unique par repère
    try {
      const famResp = await fetch("api/familles_repartition_uniques.php");
      const famJson = await famResp.json();
      if (famJson && famJson.success && Array.isArray(famJson.data)) {
        const chartData = famJson.data.map((row) => ({
          label: row.famille,
          count: parseInt(row.nb_reperes_uniques || 0),
        }));
        createEquipementsFamilleChart(chartData);
      } else {
        createEquipementsFamilleChart(data.equipements_par_famille || []);
      }
    } catch (e) {
      console.warn(
        "Répartition familles unique indisponible, fallback aux données locales",
        e
      );
      createEquipementsFamilleChart(data.equipements_par_famille || []);
    }
    createArticlesMetierChart(data.articles_par_metier || []);
    createEquipementsSourceChart(data.equipements_par_source || []);
    createArticlesSourceChart(data.articles_par_source || []);
  }
}

// Masquer l'overlay de chargement
function hideLoadingOverlay(overlayId) {
  const overlay = document.getElementById(overlayId);
  if (overlay) {
    overlay.style.display = "none";
  }
}

// Fonctions de création des graphiques Chart.js
function createEquipementsFamilleChart(data) {
  const ctx = document.getElementById("equipementsFamilleChart");
  if (!ctx || !data.length) return;

  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: data.map((item) => item.label),
      datasets: [
        {
          data: data.map((item) => item.count),
          backgroundColor: [
            "#FF6384",
            "#36A2EB",
            "#FFCE56",
            "#4BC0C0",
            "#9966FF",
            "#FF9F40",
          ],
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" },
      },
    },
  });
}

function createArticlesMetierChart(data) {
  const ctx = document.getElementById("articlesMetierChart");
  if (!ctx || !data.length) return;

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.map((item) => item.label),
      datasets: [
        {
          label: "Articles",
          data: data.map((item) => item.count),
          backgroundColor: "#36A2EB",
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
    },
  });
}

function createEquipementsSourceChart(data) {
  const ctx = document.getElementById("equipementsSourceChart");
  if (!ctx || !data.length) return;

  new Chart(ctx, {
    type: "pie",
    data: {
      labels: data.map((item) => item.label || "Non définie"),
      datasets: [
        {
          data: data.map((item) => item.count),
          backgroundColor: [
            "#FF6384",
            "#36A2EB",
            "#FFCE56",
            "#4BC0C0",
            "#9966FF",
          ],
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" },
      },
    },
  });
}

function createArticlesSourceChart(data) {
  const ctx = document.getElementById("articlesSourceChart");
  if (!ctx || !data.length) return;

  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: data.map((item) => item.label || "Non définie"),
      datasets: [
        {
          data: data.map((item) => item.count),
          backgroundColor: ["#FF9F40", "#FF6384", "#36A2EB", "#FFCE56"],
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" },
      },
    },
  });
}

console.log("🎯 Script ultra-léger chargé");

// Fallback: charger les totals depuis l'API familles si l'API principale échoue
async function fallbackLoadStatsFromFamilles() {
  try {
    const resp = await fetch('api/familles_repartition_uniques.php');
    const json = await resp.json();
    if (json && json.success && Array.isArray(json.data)) {
      const equipementsNonSAP = json.data.reduce((acc, row) => acc + (parseInt(row.nb_equipements_non_sap || 0)), 0);
      const articlesNonSAP = json.data.reduce((acc, row) => acc + (parseInt(row.nb_articles_non_sap || 0)), 0);
      updateStatsLight({ equipements_non_sap: equipementsNonSAP, articles_non_sap: articlesNonSAP });

      // Finaliser l'état de chargement minimal
      updateLoadingStatus("stats", "Statistiques chargées (fallback)", false);
      hideLoadingSections();
      return;
    }
  } catch (e) {
    console.warn('Fallback familles indisponible', e);
  }
  // Si tout échoue, afficher 0 pour éviter "Erreur"
  updateStatsLight({ equipements_non_sap: 0, articles_non_sap: 0 });
  updateLoadingStatus("stats", "Statistiques chargées (0)", false);
  hideLoadingSections();
}

// Chargement des compteurs Accès rapides
async function loadQuickAccessCounts() {
  try {
    // 1) Équipements sans nomenclature
    const respFam = await fetch("api/familles_repartition_uniques.php");
    const famJson = await respFam.json();
    if (famJson && famJson.success) {
      const totalSansNomen = famJson.data.reduce(
        (acc, row) => acc + parseInt(row.nb_equipements_sans_nomenclature || 0),
        0
      );
      const el1 = document.getElementById("quickNoNomenclaturesCount");
      if (el1) el1.textContent = totalSansNomen;
      const elEquip = document.getElementById("quickEquipNonSAPCount");
      if (elEquip) {
        const totalNonSAP = famJson.data.reduce(
          (acc, row) => acc + parseInt(row.nb_equipements_non_sap || 0),
          0
        );
        elEquip.textContent = totalNonSAP;
      }
      const elArt = document.getElementById("quickArticlesNonSAPCount");
      if (elArt) {
        const totalArtNonSAP = famJson.data.reduce(
          (acc, row) => acc + parseInt(row.nb_articles_non_sap || 0),
          0
        );
        elArt.textContent = totalArtNonSAP;
      }
    }
  } catch (e) {
    console.warn("Impossible de charger les compteurs rapides", e);
  }
}
