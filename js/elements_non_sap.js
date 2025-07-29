// Configuration des couleurs
const dangerColors = [
    '#f44336', '#e53935', '#d32f2f', '#c62828', '#b71c1c',
    '#ff5722', '#f4511e', '#e64a19', '#d84315', '#bf360c'
];

// Variables globales
let charts = {};
let loadedData = {
    equipements_par_famille: [],
    articles_par_metier: [],
    equipements_par_source: [],
    articles_par_source: []
};

// Variables de pagination
let equipementsData = {
    items: [],
    currentOffset: 0,
    totalCount: 0,
    hasMore: true,
    loading: false
};

let articlesData = {
    items: [],
    currentOffset: 0,
    totalCount: 0,
    hasMore: true,
    loading: false
};

// Variables de statistiques
let statsCalculated = false;
let globalProgressSteps = 0;
let totalSteps = 8;

// Fonction principale de chargement progressif
async function loadDataProgressively() {
    showProgressBar();
    updateGlobalProgress(0);

    try {
        // Étape 1: Charger les statistiques générales
        updateProgressMessage("Chargement des statistiques générales...");
        updateLoadingStatus('stats', 'loading');
        await loadGeneralStats();
        updateLoadingStatus('stats', 'complete');
        updateGlobalProgress(1);

        // Mise à jour immédiate de l'aperçu
        updateQuickPreview();

        // Étape 2: Charger les équipements (premier lot)
        updateProgressMessage("Chargement des équipements (premier lot)...");
        updateLoadingStatus('equipements', 'loading');
        await loadEquipementsBatch();
        updateLoadingStatus('equipements', 'complete');
        updateGlobalProgress(2);

        // Étape 3: Charger les articles (premier lot)  
        updateProgressMessage("Chargement des articles (premier lot)...");
        updateLoadingStatus('articles', 'loading');
        await loadArticlesBatch();
        updateLoadingStatus('articles', 'complete');
        updateGlobalProgress(3);

        // Étape 4: Charger les graphiques famille
        updateProgressMessage("Analyse des familles d'équipements...");
        updateLoadingStatus('charts', 'loading');
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
        updateLoadingStatus('charts', 'complete');
        updateGlobalProgress(8);

        // Finalisation
        setTimeout(() => {
            hideProgressBar();
            hideLoadingStatus();
            showAllCharts();
            updateFinalStatus();
        }, 1000);

    } catch (error) {
        console.error('Erreur lors du chargement:', error);
        hideProgressBar();
        showError('Erreur lors du chargement des données');
    }
}

// Reste du code JavaScript...
// (Copiez tout le JavaScript restant du fichier PHP ici)

// Démarrer le chargement progressif quand la page est prête
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        loadDataProgressively();
    }, 500);
});