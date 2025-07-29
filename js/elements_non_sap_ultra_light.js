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
      showErrorLight("Erreur de données");
    }
  } catch (error) {
    console.error("❌ Erreur réseau:", error);
    showErrorLight("Erreur réseau");
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

  if (equipEl) equipEl.textContent = "Erreur";
  if (artEl) artEl.textContent = "Erreur";
  if (totalEl) totalEl.textContent = "Erreur";
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
});

console.log("🎯 Script ultra-léger chargé");
