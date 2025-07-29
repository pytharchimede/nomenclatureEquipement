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
