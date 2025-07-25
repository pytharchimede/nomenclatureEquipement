# 📋 **EquiNomTech** - Documentation Complète

## _Système de Gestion de Nomenclature d'Équipements Industriels_

---

## 📖 **Table des Matières**

1. [Présentation Générale](#presentation-generale)
2. [Architecture Technique](#architecture-technique)
3. [Guide Utilisateur](#guide-utilisateur)
4. [Guide Développeur](#guide-developeur)
5. [Fonctionnalités Détaillées](#fonctionnalites-detaillees)
6. [API et Exports](#api-et-exports)
7. [Installation et Configuration](#installation-et-configuration)
8. [Maintenance et Administration](#maintenance-et-administration)

---

## 🎯 **Présentation Générale**

### **Nom du Logiciel : EquiNomTech**

_Equipment Nomenclature Technology - Système de Gestion de Nomenclature d'Équipements_

### **Description**

EquiNomTech est une application web professionnelle dédiée à la gestion complète de la nomenclature d'équipements industriels. Elle permet de gérer les relations entre équipements, articles (pièces de rechange) et nomenclatures, avec des fonctionnalités avancées d'import/export, de reporting et d'analyse.

### **Objectifs**

- 📊 Centraliser la gestion des équipements industriels
- 🔗 Maintenir les relations équipements-articles
- 📈 Fournir des analyses et statistiques détaillées
- 🔄 Synchroniser les données entre différentes sources (SAP, RGM, Template SPL)
- 📤 Exporter les données dans différents formats
- 👥 Gérer les accès utilisateurs et droits

### **Public Cible**

- Gestionnaires de maintenance industrielle
- Responsables achats pièces de rechange
- Ingénieurs maintenance
- Administrateurs SAP
- Équipes logistiques

---

## 🏗️ **Architecture Technique**

### **Stack Technologique**

- **Backend** : PHP 7.4+
- **Base de données** : MySQL/MariaDB
- **Frontend** : Bootstrap 5, JavaScript ES6+, Chart.js
- **Import/Export** : PhpSpreadsheet, mPDF
- **Authentification** : Sessions PHP natives
- **APIs** : REST-like endpoints

### **Structure des Données**

#### **Tables Principales**

```sql
-- Équipements
equipements (
    id, code_equipement, designation_equipement, repere_equipement,
    fabricant, type_objet, designation_type, numero_serie_fabricant,
    numero_piece_fabricant, poste_technique, designation_poste_technique,
    poste_travail_principal, categorie_equipement, centre_de_couts,
    famille, date_creation
)

-- Articles (Pièces de rechange)
articles (
    id, code_article, designation_article, type_article,
    temsup_niv_mdt, ancien_num_article, uq_base, fabricant,
    numero_piece_fabricant, groupe_articles, groupe_marche_externe,
    document, description, date_creation, cree_par
)

-- Nomenclatures (Relations)
nomenclatures (
    id, repere_equipement, code_equipement, code_article,
    quantite, designation_equipement, designation_article,
    source, date_creation, cree_par
)

-- Gestion des utilisateurs
utilisateurs (
    id, nom_utilisateur, mot_de_passe, nom_complet,
    email, groupe_id, statut, date_creation
)

-- Gestion des droits
groupes_utilisateurs (
    id, nom_groupe, description
)

droits_utilisateur (
    id, groupe_id, ressource, droit
)
```

### **Modèle de Données**

```
Équipement (1) ←→ (N) Nomenclature (N) ←→ (1) Article
     ↓                      ↓                    ↓
  Famille              Source (SAP,           Métier
  Type                 RGM, Template)        Fabricant
  Fabricant            Quantité              Groupe
```

---

## 👤 **Guide Utilisateur**

### **1. Connexion et Authentification**

#### **Page de Login**

- Accès via `login.php`
- Authentification par nom d'utilisateur/mot de passe
- Gestion des sessions sécurisées
- Redirection automatique après connexion

### **2. Dashboard Principal**

#### **Vue d'Ensemble**

Le dashboard fournit une vue complète de l'état du système :

**Statistiques Principales :**

- 📊 Nombre total d'équipements
- 🔧 Nombre total d'articles
- 📋 Nombre total de nomenclatures
- 📈 Pourcentage d'équipements avec nomenclature
- 🎯 Pourcentage d'articles codifiés SAP

**Alertes Critiques :**

- ⚠️ Équipements sans nomenclature
- 🔴 Articles non codifiés SAP
- 📈 Évolution sur 30 jours

**Graphiques Analytiques :**

- 📊 Familles d'équipements (quantité vs diversité)
- 📈 Croissance des équipements (7 derniers mois)
- 🏭 Types d'équipements (pompes, compresseurs, etc.)
- 🔧 Métiers des articles (par domaine)

### **3. Gestion des Équipements**

#### **Fonctionnalités**

- 📋 Liste paginée avec défilement infini
- 🔍 Recherche avancée multi-critères
- 📊 Statistiques en temps réel
- 📤 Export Excel/PDF
- ➕ Ajout/modification/suppression
- 📥 Import Excel en masse

#### **Filtres Disponibles**

- Recherche textuelle globale
- Fabricant
- Type d'objet
- Catégorie d'équipement
- Source de données

#### **Import Excel**

- Drag & drop moderne
- Traitement par chunks (optimisé)
- Barre de progression temps réel
- Validation des données
- Gestion des doublons
- Rapport d'import détaillé

### **4. Gestion des Articles**

#### **Fonctionnalités**

- 📋 Liste paginée avec filtres avancés
- 🔍 Recherche multi-critères
- 📊 Classification par métier
- 📤 Export complet ou filtré
- ➕ CRUD complet
- 📥 Import Excel optimisé

#### **Classification par Métier**

```
1xxx - Mécanique
2xxx - Électrique
3xxx - Instrumentation
4xxx - Tuyauterie
5xxx - Chaudronnerie
6xxx - Civil/Structure
7xxx - Chimie/Process
8xxx - Sécurité
9xxx - Maintenance
```

#### **Filtres Spécialisés**

- Code article / Désignation
- Fabricant
- Type d'article
- Groupe d'articles
- Unité de base (UQ)
- Source de données

### **5. Gestion des Nomenclatures**

#### **Fonctionnalités Principales**

- 🔗 Liaison équipements ↔ articles
- 📊 Vue relationnelle complète
- 🔍 Recherche croisée
- 📈 Analyse de cohérence
- 📤 Export hiérarchique
- 🔄 Synchronisation multi-sources

#### **Sources de Données**

- **SAP** : Données officielles ERP
- **RGM** : Synthèse RGM
- **Template** : Imports Template SPL

#### **Gestion des Doublons**

- Détection automatique
- Interface de résolution
- Historique des modifications
- Validation manuelle/automatique

### **6. Module d'Exportation**

#### **Types d'Export Disponibles**

**Équipements :**

- Export complet
- Sans pièces de rechange
- Par famille
- Export personnalisé

**Articles :**

- Export complet
- Articles non liés
- Par fabricant
- Export personnalisé

**Nomenclatures :**

- Export complet
- Par équipement
- Vue hiérarchique
- Export personnalisé

**Formats Supportés :**

- 📊 Excel (.xlsx) avec mise en forme
- 📄 PDF avec graphiques
- 📋 CSV pour intégration

### **7. Analyse des Éléments Non SAP**

#### **Fonctionnalités**

- 📊 Dashboard dédié aux éléments non SAP
- 📈 Graphiques de répartition
- 📋 Listes détaillées progressives
- 📤 Export spécialisé
- 🔍 Analyse par famille/métier

#### **Visualisations**

- Répartition équipements par famille
- Articles par métier
- Sources actuelles
- Tendances d'évolution

### **8. Template SPL (Spare Parts List)**

#### **Fonctionnalités**

- 📥 Import Excel Template SPL
- ⚙️ Séparation automatique des équipements
- 📊 Traitement par chunks optimisé
- 📈 Suivi temps réel
- 🔄 Intégration automatique

#### **Structure Template**

```
Colonnes supportées :
A: N° SPL
B: Code SAP
D: Code Article (obligatoire)
E: Quantité
F: Désignation Article
G: Unité Base
H: Métier
I: N° Pièce Fabricant
J: Fabricant
K: Équipement (obligatoire)
```

### **9. Synthèse RGM**

#### **Fonctionnalités**

- 📊 Import données RGM
- 🔄 Synchronisation nomenclatures
- 📈 Analyse quantitative
- 📋 Rapports de synthèse

### **10. Gestion des Utilisateurs et Droits**

#### **Système de Droits**

- **Groupes d'utilisateurs** personnalisables
- **Droits granulaires** par ressource
- **Restriction d'accès** par fonctionnalité

#### **Ressources Contrôlées**

- Équipements (lecture, écriture, suppression)
- Articles (lecture, écriture, suppression)
- Nomenclatures (lecture, écriture, suppression)
- Exports (accès, types autorisés)
- Administration (gestion utilisateurs)

---

## 💻 **Guide Développeur**

### **1. Architecture MVC**

#### **Structure des Dossiers**

```
nomenclatureequipement/
├── includes/          # Authentification et utilitaires
├── model/            # Classes métier et base de données
├── request/          # APIs et endpoints
├── css/              # Styles CSS
├── js/               # Scripts JavaScript
├── plugins/          # Bibliothèques externes
├── uploads/          # Fichiers uploadés
├── tmp/              # Fichiers temporaires
└── vendor/           # Dépendances Composer
```

#### **Classes Principales**

```php
// Gestion base de données
Database::getConnection()

// Modèles métier
Equipement::getAll(), ::getPaginated(), ::getByRepere()
Article::getAll(), ::getPaginated(), ::getByCode()
Nomenclature::getAll(), ::getByRepereArticle()
Utilisateur::authenticate(), ::hasRight()

// Classes utilitaires
Quantitatif::getFamilleByRepere()
RgmSynthese::import()
TemplateSPL::process()
```

### **2. API Endpoints**

#### **Articles**

```php
GET  /request/articles_paginated.php          # Liste paginée
POST /request/article_add.php                 # Ajout
PUT  /request/article_update.php              # Modification
DEL  /request/article_delete.php              # Suppression
POST /request/article_import_optimized.php    # Import Excel
```

#### **Équipements**

```php
GET  /request/equipements_paginated.php       # Liste paginée
POST /request/equipement_add.php              # Ajout
PUT  /request/equipement_edit.php             # Modification
DEL  /request/equipement_delete.php           # Suppression
POST /request/equipement_import_optimized.php # Import Excel
```

#### **Nomenclatures**

```php
GET  /request/nomenclatures_paginated.php     # Liste paginée
POST /request/nomenclature_add.php            # Ajout
DEL  /request/nomenclature_delete.php         # Suppression
POST /request/nomenclature_import_optimized.php # Import Excel
```

#### **Exports**

```php
GET /request/export_equipements.php           # Export équipements
GET /request/export_articles.php              # Export articles
GET /request/export_nomenclatures.php         # Export nomenclatures
GET /request/export_articles_non_sap.php      # Export éléments non SAP
```

#### **Statistiques**

```php
GET /request/dashboard_stats_simple.php       # Stats dashboard
GET /request/stats_non_sap.php               # Stats éléments non SAP
GET /request/dashboard_alerts.php            # Alertes système
```

### **3. Frontend JavaScript**

#### **Modules Principaux**

```javascript
// Gestion articles
function_articles_updated.js -
  loadArticles(page, append) -
  createArticleRow(article) -
  handleAdvancedArticleImport() -
  deleteSelectedArticles();

// Gestion équipements
function_equipements_updated.js -
  loadEquipements(page, append) -
  handleEquipementImport() -
  exportEquipements(type);

// Gestion nomenclatures
function_nomenclatures_updated.js -
  loadNomenclatures(page, append) -
  handleExcelExport() -
  resolveAllDuplicates();
```

#### **Composants Réutilisables**

```javascript
// Pagination infinie
initInfiniteScroll();

// Filtres avancés
applyFilters(filters);

// Import/Export avec progression
showProgressModal();
updateProgress(percent);

// Alertes et notifications
showAlert(message, type);
```

### **4. Sécurité**

#### **Authentification**

```php
// Vérification session
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Vérification droits
requireDroit('equipements', 'lecture');
```

#### **Validation des Données**

```php
// Échappement XSS
htmlspecialchars($data, ENT_QUOTES, 'UTF-8')

// Requêtes préparées
$stmt = $pdo->prepare("SELECT * FROM equipements WHERE id = ?");
$stmt->execute([$id]);
```

#### **Protection CSRF**

```php
// Token CSRF dans les formulaires
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
```

### **5. Optimisations Performance**

#### **Base de Données**

```sql
-- Index optimisés
CREATE INDEX idx_equipements_repere ON equipements(repere_equipement);
CREATE INDEX idx_articles_code ON articles(code_article);
CREATE INDEX idx_nomenclatures_relation ON nomenclatures(repere_equipement, code_article);
CREATE INDEX idx_nomenclatures_source ON nomenclatures(source);
```

#### **PHP**

```php
// Pagination efficace
LIMIT $limit OFFSET $offset

// Requêtes optimisées avec JOIN
SELECT DISTINCT e.* FROM equipements e
INNER JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement

// Cache des résultats fréquents
if (!isset($_SESSION['stats_cache']) ||
    time() - $_SESSION['stats_cache']['time'] > 300) {
    // Recalculer les stats
}
```

#### **JavaScript**

```javascript
// Défilement infini optimisé
const observer = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting && hasMoreData && !isLoading) {
    loadMoreData();
  }
});

// Debounce pour les recherches
const debouncedSearch = debounce((term) => {
  performSearch(term);
}, 300);
```

### **6. Gestion des Erreurs**

#### **PHP**

```php
// Logging des erreurs
try {
    // Opération risquée
} catch (Exception $e) {
    error_log("Erreur équipements: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur interne']);
}
```

#### **JavaScript**

```javascript
// Gestion des erreurs AJAX
fetch(url)
  .then((response) => {
    if (!response.ok) {
      throw new Error("Erreur réseau");
    }
    return response.json();
  })
  .catch((error) => {
    console.error("Erreur:", error);
    showAlert("Une erreur est survenue", "danger");
  });
```

---

## 🎯 **Fonctionnalités Détaillées**

### **1. Dashboard Intelligence**

#### **Métriques Clés**

```php
// Calculs automatiques
$totalEquipements = count(Equipement::getAll());
$totalArticles = count(Article::getAll());
$totalNomenclatures = Nomenclature::countAll();

// Taux de couverture
$equipAvecNomenclature = // Équipements liés
$pourcentEquipAvecNomen = round($equipAvecNomenclature / $totalEquipements * 100, 1);

// Codification SAP
$articlesCodesSAP = // Articles dans SAP
$pourcentArticlesSAP = round($articlesCodesSAP / $totalArticles * 100, 1);
```

#### **Graphiques Dynamiques**

- **Chart.js** avec animations fluides
- **Gradients modernes** pour l'esthétique
- **Responsive design** pour mobile
- **Interactions utilisateur** (hover, click)

### **2. Import/Export Avancé**

#### **Import Excel Optimisé**

```php
// Traitement par chunks
public static function importFromExcel($filepath, $options = []) {
    $chunkSize = $options['chunkSize'] ?? 100;
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true);

    // Traitement progressif
    for ($startRow = 1; $startRow <= $maxRow; $startRow += $chunkSize) {
        $reader->setReadFilter(new ChunkReadFilter($startRow, $chunkSize));
        $spreadsheet = $reader->load($filepath);
        // Traitement du chunk
    }
}
```

#### **Export avec Mise en Forme**

```php
// Style professionnel Excel
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1976D2']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];

$sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);
```

### **3. Gestion des Doublons Intelligente**

#### **Détection Automatique**

```php
public static function detectDuplicates() {
    $sql = "
        SELECT repere_equipement, code_article, COUNT(*) as count
        FROM nomenclatures
        WHERE repere_equipement IS NOT NULL AND code_article IS NOT NULL
        GROUP BY repere_equipement, code_article
        HAVING count > 1
    ";
}
```

#### **Résolution Assistée**

- Interface graphique intuitive
- Comparaison côte à côte
- Fusion automatique ou manuelle
- Historique des résolutions

### **4. Analyse Prédictive**

#### **Tendances et Projections**

```javascript
// Analyse d'évolution
const evolutionData = {
  labels: last7Months,
  datasets: [
    {
      label: "Croissance équipements",
      data: monthlyGrowth,
      trend: calculateTrend(monthlyGrowth),
    },
  ],
};
```

#### **Alertes Intelligentes**

- Seuils configurable
- Notifications temps réel
- Escalade automatique
- Reporting automatisé

---

## 📊 **API et Exports**

### **1. API REST**

#### **Standards Respectés**

- **HTTP Status Codes** appropriés
- **JSON** comme format d'échange
- **CORS** configuré si nécessaire
- **Rate limiting** pour la protection

#### **Réponses Standardisées**

```json
// Succès
{
    "success": true,
    "data": [...],
    "pagination": {
        "page": 1,
        "limit": 50,
        "total": 1250,
        "hasMore": true
    }
}

// Erreur
{
    "success": false,
    "error": "Message d'erreur",
    "code": "ERROR_CODE"
}
```

### **2. Formats d'Export**

#### **Excel Professionnel**

- Mise en forme avancée
- Graphiques intégrés
- Métadonnées document
- Formules automatiques
- Protection feuilles

#### **PDF avec Charts**

- Mise en page professionnelle
- Graphiques vectoriels
- En-têtes/pieds de page
- Pagination automatique
- Marque-pages navigation

#### **CSV pour Intégration**

- Séparateurs configurables
- Encodage UTF-8
- Échappement automatique
- Headers optionnels

---

## ⚙️ **Installation et Configuration**

### **1. Prérequis Système**

#### **Serveur Web**

- Apache 2.4+ ou Nginx 1.14+
- PHP 7.4+ (recommandé 8.0+)
- MySQL 5.7+ ou MariaDB 10.3+
- Extensions PHP : PDO, JSON, ZIP, XML, GD

#### **Client**

- Navigateurs modernes (Chrome 80+, Firefox 75+, Safari 13+)
- JavaScript activé
- 2 Go RAM minimum
- 5 Go espace disque

### **2. Installation**

#### **Étape 1 : Fichiers**

```bash
# Clone ou copie des fichiers
git clone [repository] nomenclatureequipement/
cd nomenclatureequipement/

# Installation dépendances Composer
composer install
```

#### **Étape 2 : Base de Données**

```sql
-- Création base
CREATE DATABASE nomenclature_equipements CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import structure et données
mysql -u user -p nomenclature_equipements < database/schema.sql
mysql -u user -p nomenclature_equipements < database/initial_data.sql
```

#### **Étape 3 : Configuration**

```php
// model/Database.php
private static $host = 'localhost';
private static $dbname = 'nomenclature_equipements';
private static $username = 'your_username';
private static $password = 'your_password';
```

#### **Étape 4 : Permissions**

```bash
# Permissions dossiers
chmod 755 uploads/ tmp/ sessions/
chown -R www-data:www-data nomenclatureequipement/
```

### **3. Configuration Avancée**

#### **Performance**

```php
// php.ini optimisations
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 50M
post_max_size = 50M
max_input_vars = 10000
```

#### **Sécurité**

```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Protection fichiers sensibles
<Files "*.log">
    Deny from all
</Files>
```

---

## 🛠️ **Maintenance et Administration**

### **1. Monitoring Système**

#### **Métriques à Surveiller**

- Taille base de données
- Performances requêtes
- Utilisation mémoire PHP
- Espace disque uploads/
- Logs d'erreurs

#### **Outils de Monitoring**

```php
// Vérification santé système
function checkSystemHealth() {
    return [
        'database' => checkDatabaseConnection(),
        'disk_space' => getDiskSpaceInfo(),
        'memory_usage' => getMemoryUsage(),
        'error_logs' => getRecentErrors()
    ];
}
```

### **2. Sauvegarde et Restauration**

#### **Sauvegarde Automatique**

```bash
#!/bin/bash
# Sauvegarde quotidienne
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p nomenclature_equipements > backup_$DATE.sql
tar -czf uploads_backup_$DATE.tar.gz uploads/

# Nettoyage sauvegardes anciennes (> 30 jours)
find /backup/ -name "backup_*.sql" -mtime +30 -delete
```

#### **Restauration**

```bash
# Restauration base
mysql -u user -p nomenclature_equipements < backup_20241225_120000.sql

# Restauration fichiers
tar -xzf uploads_backup_20241225_120000.tar.gz
```

### **3. Optimisation Performances**

#### **Base de Données**

```sql
-- Optimisation tables
OPTIMIZE TABLE equipements, articles, nomenclatures;

-- Analyse performances
EXPLAIN SELECT * FROM nomenclatures
WHERE repere_equipement = 'MP1080A'
AND source = 'SAP';

-- Statistiques index
SHOW INDEX FROM nomenclatures;
```

#### **PHP**

```php
// Cache requêtes fréquentes
$cacheFile = 'tmp/stats_cache.json';
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
    $stats = json_decode(file_get_contents($cacheFile), true);
} else {
    $stats = calculateStats();
    file_put_contents($cacheFile, json_encode($stats));
}
```

### **4. Logs et Debugging**

#### **Système de Logs**

```php
// Logger personnalisé
class Logger {
    public static function log($level, $message, $context = []) {
        $logFile = 'logs/' . date('Y-m-d') . '.log';
        $entry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            json_encode($context)
        );
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
```

#### **Mode Debug**

```php
// Configuration debug
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Logs détaillés
    Logger::log('debug', 'Query executed', ['sql' => $sql, 'params' => $params]);
}
```

---

## 📈 **Évolutions Futures**

### **1. Améliorations Prévues**

#### **Interface Utilisateur**

- 🎨 Thèmes personnalisables
- 🌙 Mode sombre
- 📱 Application mobile dédiée
- 🔔 Notifications push

#### **Fonctionnalités Métier**

- 🤖 Intelligence artificielle pour détection anomalies
- 📊 Tableaux de bord configurables
- 🔄 Workflow validation automatisée
- 📧 Rapports automatiques par email

#### **Intégrations**

- 🔗 API SAP directe
- 📡 Connecteurs ERP tiers
- ☁️ Synchronisation cloud
- 🔄 Intégration GMAO

### **2. Roadmap Technique**

#### **Architecture**

- 🏗️ Microservices architecture
- 🐳 Containerisation Docker
- ☁️ Déploiement cloud-native
- 🔄 CI/CD pipeline

#### **Performance**

- ⚡ Cache Redis
- 📊 Optimisation requêtes avancée
- 🔄 Mise en cache intelligente
- 📈 Monitoring applicatif

---

## 📞 **Support et Contact**

### **Documentation Technique**

- 📚 Wiki interne : `/docs/`
- 🔧 API Documentation : `/api-docs/`
- 🐛 Bug Tracker : Issues GitHub
- 💬 Forum utilisateurs : Forum dédié

### **Formation**

- 📖 Guides utilisateur détaillés
- 🎥 Tutoriels vidéo
- 👨‍🏫 Sessions formation
- 📋 Certification utilisateurs

### **Assistance**

- 📧 Email support technique
- 📞 Hotline utilisateurs
- 💬 Chat en ligne
- 🆘 Support d'urgence 24/7

---

_Documentation rédigée le 25 juillet 2025_  
_Version EquiNomTech 2.1_  
_© 2025 - Système de Gestion de Nomenclature d'Équipements_
