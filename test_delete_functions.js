/**
 * Script de test pour les fonctionnalités de suppression d'articles
 * À exécuter dans la console du navigateur sur la page articles.php
 */

// Test 1: Vérifier que les fonctions existent
console.log("=== Test des fonctions de suppression ===");

if (typeof deleteSelectedArticles === "function") {
  console.log("✅ deleteSelectedArticles() est définie");
} else {
  console.log("❌ deleteSelectedArticles() manquante");
}

if (typeof deleteFilteredArticles === "function") {
  console.log("✅ deleteFilteredArticles() est définie");
} else {
  console.log("❌ deleteFilteredArticles() manquante");
}

if (typeof updateSelectionButtons === "function") {
  console.log("✅ updateSelectionButtons() est définie");
} else {
  console.log("❌ updateSelectionButtons() manquante");
}

// Test 2: Vérifier les boutons dans le DOM
console.log("\n=== Test des boutons dans le DOM ===");

const deleteSelectedBtn = document.querySelector("#deleteSelectedBtn");
const deleteFilteredBtn = document.querySelector("#deleteFilteredBtn");
const exportFilteredBtn = document.querySelector("#exportFilteredBtn");

if (deleteSelectedBtn) {
  console.log("✅ Bouton #deleteSelectedBtn trouvé", deleteSelectedBtn);
} else {
  console.log("❌ Bouton #deleteSelectedBtn manquant");
}

if (deleteFilteredBtn) {
  console.log("✅ Bouton #deleteFilteredBtn trouvé", deleteFilteredBtn);
} else {
  console.log("❌ Bouton #deleteFilteredBtn manquant");
}

if (exportFilteredBtn) {
  console.log("✅ Bouton #exportFilteredBtn trouvé", exportFilteredBtn);
} else {
  console.log("❌ Bouton #exportFilteredBtn manquant");
}

// Test 3: Vérifier les event listeners
console.log("\n=== Test des event listeners ===");

// Simuler un changement de checkbox pour voir si updateSelectionButtons() est appelée
const firstCheckbox = document.querySelector(".article-checkbox");
if (firstCheckbox) {
  console.log("✅ Checkbox d'article trouvée, test de sélection...");

  // Sauvegarder l'état initial
  const initialState = firstCheckbox.checked;

  // Changer l'état
  firstCheckbox.checked = !initialState;
  firstCheckbox.dispatchEvent(new Event("change", { bubbles: true }));

  // Vérifier la visibilité des boutons
  setTimeout(() => {
    const isDeleteBtnVisible =
      deleteSelectedBtn && deleteSelectedBtn.style.display !== "none";
    const isExportBtnVisible =
      exportFilteredBtn && exportFilteredBtn.style.display !== "none";

    if (isDeleteBtnVisible) {
      console.log("✅ Bouton de suppression devient visible après sélection");
    } else {
      console.log("❌ Bouton de suppression ne devient pas visible");
    }

    // Remettre dans l'état initial
    firstCheckbox.checked = initialState;
    firstCheckbox.dispatchEvent(new Event("change", { bubbles: true }));
  }, 100);
} else {
  console.log("❌ Aucune checkbox d'article trouvée");
}

// Test 4: Vérifier les filtres actuels
console.log("\n=== Test des filtres ===");

if (typeof currentFilters !== "undefined") {
  console.log("✅ Variable currentFilters existe:", currentFilters);

  const hasActiveFilters = Object.values(currentFilters).some(
    (value) => value && value.trim() !== ""
  );
  if (hasActiveFilters) {
    console.log("✅ Des filtres sont actifs");

    // Vérifier si le bouton de suppression filtrée devrait être visible
    setTimeout(() => {
      updateSelectionButtons();
      const isFilteredDeleteVisible =
        deleteFilteredBtn && deleteFilteredBtn.style.display !== "none";
      if (isFilteredDeleteVisible) {
        console.log("✅ Bouton de suppression filtrée est visible");
      } else {
        console.log(
          "❌ Bouton de suppression filtrée n'est pas visible malgré les filtres actifs"
        );
      }
    }, 100);
  } else {
    console.log("ℹ️ Aucun filtre actif actuellement");
  }
} else {
  console.log("❌ Variable currentFilters manquante");
}

// Test 5: Test simulé de l'API (sans vraiment supprimer)
console.log("\n=== Test de l'API (simulation) ===");

fetch("api/delete_articles.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    type: "test", // Type non supporté pour le test
    test: true,
  }),
})
  .then((response) => response.json())
  .then((result) => {
    if (result.success === false && result.message) {
      console.log(
        "✅ API répond correctement aux requêtes invalides:",
        result.message
      );
    } else {
      console.log("⚠️ Réponse API inattendue:", result);
    }
  })
  .catch((error) => {
    console.log("❌ Erreur lors de la communication avec l'API:", error);
  });

console.log("\n=== Tests terminés ===");
console.log("Pour tester manuellement:");
console.log("1. Sélectionnez des articles avec les checkboxes");
console.log("2. Appliquez des filtres");
console.log("3. Vérifiez que les boutons apparaissent/disparaissent");
console.log(
  "4. Testez les fonctions deleteSelectedArticles() et deleteFilteredArticles()"
);
