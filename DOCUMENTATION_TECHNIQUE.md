# 🔧 **Documentation Technique - EquiNomTech**

## _Guide Développeur Complet_

---

## 📋 **Table des Matières**

1. [Architecture Générale](#architecture-générale)
2. [Base de Données](#base-de-données)
3. [Structure des Fichiers](#structure-des-fichiers)
4. [Modèles de Données (Models)](#modèles-de-données)
5. [Contrôleurs et API](#contrôleurs-et-api)
6. [Pages Principales](#pages-principales)
7. [Système d'Import/Export](#système-dimportexport)
8. [Gestion des Doublons](#gestion-des-doublons)
9. [Authentification et Sécurité](#authentification-et-sécurité)
10. [APIs et Services](#apis-et-services)
11. [Configuration et Déploiement](#configuration-et-déploiement)
12. [Maintenance et Monitoring](#maintenance-et-monitoring)

---

## 🏗️ **Architecture Générale**

### **Stack Technologique**

- **Backend** : PHP 7.4+ (Vanilla PHP, pas de framework)
- **Base de données** : MySQL 8.0+
- **Frontend** : HTML5, CSS3, JavaScript ES6+
- **UI Framework** : Bootstrap 5.3
- **Icônes** : Material Icons
- **Charts** : Chart.js
- **Export** : TCPDF, PhpSpreadsheet
- **Import** : PhpSpreadsheet

### **Pattern Architectural**

```
Application MVC Simple
├── Models/          → Classes métier et accès données
├── Views/           → Pages PHP avec HTML embarqué
├── Controllers/     → Logique métier (dans request/)
├── API/             → Services REST/Ajax
└── Assets/          → CSS, JS, Images
```

### **Principe de Fonctionnement**

1. **Routage** : Pas de routeur, accès direct aux fichiers PHP
2. **Session** : Gestion native PHP des sessions utilisateur
3. **Sécurité** : Authentification par session + contrôle d'accès
4. **Ajax** : Appels asynchrones pour pagination et filtres
5. **Exports** : Génération à la volée de fichiers Excel/PDF

---

## 🗄️ **Base de Données**

### **Structure Principale**

```sql
-- Table principale : ÉQUIPEMENTS
CREATE TABLE equipements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_equipement VARCHAR(50) UNIQUE,
    designation_equipement TEXT,
    repere_equipement VARCHAR(100) UNIQUE,
    fabricant VARCHAR(255),
    type_objet VARCHAR(100),
    designation_type VARCHAR(255),
    numero_serie_fabricant VARCHAR(100),
    numero_piece_fabricant VARCHAR(100),
    poste_technique VARCHAR(100),
    designation_poste_technique TEXT,
    poste_travail_principal VARCHAR(100),
    categorie_equipement VARCHAR(100),
    centre_de_couts VARCHAR(50),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table principale : ARTICLES
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_article VARCHAR(50) UNIQUE,
    designation_article TEXT,
    type_article VARCHAR(100),
    temsup_niv_mdt VARCHAR(50),
    ancien_num_article VARCHAR(50),
    uq_base VARCHAR(10),
    fabricant VARCHAR(255),
    numero_piece_fabricant VARCHAR(100),
    groupe_articles VARCHAR(100),
    groupe_marche_externe VARCHAR(100),
    document VARCHAR(255),
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cree_par VARCHAR(50)
);

-- Table de liaison : NOMENCLATURES
CREATE TABLE nomenclatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_equipement VARCHAR(50),
    code_article VARCHAR(50),
    repere_equipement VARCHAR(100),
    designation_equipement TEXT,
    fabricant VARCHAR(255),
    type VARCHAR(100),
    numero_serie_fabricant VARCHAR(100),
    designation_article TEXT,
    numero_poste INT,
    quantite DECIMAL(10,3),
    unite VARCHAR(20),
    poste_technique VARCHAR(100),
    metier VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    source VARCHAR(50),
    UNIQUE KEY unique_repere_article (repere_equipement, code_article)
);
```

### **Tables Système**

```sql
-- Gestion des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) UNIQUE,
    mot_de_passe VARCHAR(255),
    email VARCHAR(100),
    telephone VARCHAR(20),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gestion des droits
CREATE TABLE droits_utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    module VARCHAR(50),
    permission VARCHAR(20),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id)
);

-- Doublons d'import
CREATE TABLE nomenclatures_doublons_import (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repere_equipement VARCHAR(100),
    code_article VARCHAR(50),
    quantite_existante DECIMAL(10,3),
    quantite_nouvelle DECIMAL(10,3),
    statut ENUM('en_attente', 'approuve', 'rejete'),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **Relations et Contraintes**

- **Équipement ↔ Nomenclature** : 1:N via `repere_equipement`
- **Article ↔ Nomenclature** : 1:N via `code_article`
- **Contrainte unique** : `(repere_equipement, code_article)` dans nomenclatures
- **Indexation** : Index sur tous les champs de recherche fréquents

---

## 📁 **Structure des Fichiers**

```
nomenclatureequipement/
├── 📄 index.php                    → Redirection vers dashboard
├── 📄 login.php                    → Page de connexion
├── 📄 logout.php                   → Déconnexion
├── 📄 menu.php                     → Menu latéral commun
├── 📄 dashboard.php                → Tableau de bord principal
├── 📄 equipements.php              → Gestion équipements
├── 📄 articles.php                 → Gestion articles
├── 📄 nomenclatures.php            → Gestion nomenclatures
├── 📄 exportations.php             → Centre d'export
├── 📄 gestion_doublons_nomenclature.php → Gestion doublons
├── 📄 elements_non_sap.php         → Éléments non SAP
├── 📄 validation_doublons_import.php → Validation imports
├── 📄 familles.php                 → Gestion familles
├── 📄 import_quantitatif.php       → Import quantitatif
├── 📄 rgm_synthese.php             → Synthèse RGM
├── 📄 compilation_template.php     → Template SPL
├── 📄 utilisateurs.php             → Gestion utilisateurs
├── 📄 groupes.php                  → Gestion groupes
├── 📄 profil.php                   → Profil utilisateur
├──📁 model/                        → Classes métier
│   ├── Database.php                → Connexion BDD
│   ├── Equipement.php              → Modèle équipement
│   ├── Article.php                 → Modèle article
│   ├── Nomenclature.php            → Modèle nomenclature
│   ├── Utilisateur.php             → Modèle utilisateur
│   ├── Famille.php                 → Modèle famille
│   ├── Quantitatif.php             → Modèle quantitatif
│   ├── RgmSynthese.php             → Modèle RGM
│   └── TemplateSPL.php             → Modèle Template SPL
├──📁 request/                      → Contrôleurs/API
│   ├── 📄 *_paginated.php          → APIs pagination
│   ├── 📄 *_add.php                → Ajout d'entités
│   ├── 📄 *_update.php             → Mise à jour
│   ├── 📄 *_delete.php             → Suppression
│   ├── 📄 export_*.php             → Exports
│   └── 📄 import_*.php             → Imports
├──📁 api/                          → Services REST
│   ├── 📄 export_data.php          → Export général
│   ├── 📄 import_*.php             → Services import
│   └── 📄 *_stats.php              → Statistiques
├──📁 includes/                     → Utilitaires
│   └── auth.php                    → Contrôle authentification
├──📁 css/                          → Styles
├──📁 js/                           → JavaScript
├──📁 plugins/                      → Librairies tierces
├──📁 uploads/                      → Fichiers téléchargés
├──📁 tmp/                          → Fichiers temporaires
└──📁 vendor/                       → Dépendances Composer
```

---

## 🎯 **Modèles de Données**

### **Model/Database.php**

```php
<?php
class Database
{
    private static $pdo;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=fidestci_nomenclatureequipement_db',
                    'fidestci_ulrich',
                    '@Succes2019'
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
```

**Fonctionnalités** :

- Singleton pour la connexion PDO
- Gestion d'erreur avec exception
- Configuration MySQL centralisée

### **Model/Equipement.php**

**Propriétés principales** :

```php
public $id, $code_equipement, $designation_equipement, $repere_equipement,
       $fabricant, $type_objet, $designation_type, $numero_serie_fabricant,
       $numero_piece_fabricant, $poste_technique, $designation_poste_technique,
       $poste_travail_principal, $categorie_equipement, $centre_de_couts;
```

**Méthodes clés** :

- `getAll()` : Récupère tous les équipements
- `getPaginated($page, $limit, $filters)` : Pagination avec filtres
- `getByRepere($repere)` : Recherche par repère (clé métier)
- `getByCode($code)` : Recherche par code
- `create($data)` : Création avec validation
- `update($id, $data)` : Mise à jour
- `delete($id)` : Suppression (avec vérification référentielle)
- `getDistinctValues($column)` : Valeurs pour filtres
- `countAddedLast30Days()` : Statistiques

### **Model/Article.php**

**Propriétés principales** :

```php
public $id, $code_article, $designation_article, $type_article,
       $temsup_niv_mdt, $ancien_num_article, $uq_base, $fabricant,
       $numero_piece_fabricant, $groupe_articles, $groupe_marche_externe,
       $document, $description;
```

**Méthodes spécifiques** :

- API de pagination avancée avec filtres multiples
- Gestion des groupes d'articles
- Export Excel optimisé
- Statistiques par type et fabricant

### **Model/Nomenclature.php**

**Logique métier complexe** :

```php
// Synchronisation automatique repère/code
private static function syncRepereCode(&$data);

// Contrôle de doublon métier
public static function existsByRepereArticle($repere, $code_article);

// Recherche de doublons
public static function findDuplicates();
```

**Fonctionnalités avancées** :

- Gestion des doublons avec résolution
- Import en masse avec validation
- Export progressif pour gros volumes
- Statistiques par source (SAP, RGM, Template SPL)

---

## 🎮 **Pages Principales**

### **1. Dashboard.php**

**Objectif** : Vue d'ensemble avec statistiques temps réel

**Fonctionnalités** :

- Compteurs animés (équipements, articles, nomenclatures)
- Graphiques Chart.js (familles, évolution, types, métiers)
- Alertes critiques (doublons, éléments non SAP)
- Boutons d'action rapide

**Technologies** :

```javascript
// Animation des compteurs
function animateCounter(elementClass, finalValue, duration = 2000)

// Chargement asynchrone des données
fetch('request/dashboard_stats_simple.php')
```

**API utilisée** : `request/dashboard_stats_simple.php`

### **2. Equipements.php**

**Objectif** : CRUD complet des équipements avec recherche avancée

**Fonctionnalités** :

- Tableau paginé avec tri et filtres
- Modal d'ajout/édition avec validation
- Import Excel via drag & drop
- Export Excel progressif
- Recherche full-text

**Technologies** :

```javascript
// Pagination asynchrone
function loadEquipements(page = 1, filters = {})

// Modal dynamique
function openEquipementModal(id = null)
```

**APIs utilisées** :

- `request/equipements_paginated.php` → Données paginées
- `request/equipement_add.php` → Ajout
- `request/equipement_update.php` → Mise à jour
- `request/equipement_import.php` → Import Excel

### **3. Articles.php**

**Objectif** : Gestion des pièces de rechange et articles

**Fonctionnalités similaires aux équipements** :

- CRUD avec validation métier
- Filtres par fabricant, type, groupe
- Gestion des unités de mesure (UQ_BASE)
- Export avec colonnes personnalisables

### **4. Nomenclatures.php**

**Objectif** : Gestion des relations équipement-article

**Complexité métier** :

- Validation de l'existence équipement + article
- Contrôle des doublons (repère + code article)
- Synchronisation repère ↔ code équipement
- Gestion des quantités et unités

**Validation front-end** :

```javascript
// Validation existence équipement
async function validateRepereEquipement(repere)

// Validation existence article
async function validateCodeArticle(code)

// Contrôle doublon avant ajout
async function checkDuplicate(repere, codeArticle)
```

### **5. Exportations.php**

**Objectif** : Centre de téléchargement unifié

**Types d'export** :

- **Excel** : Données tabulaires avec formatage
- **PDF** : Rapports formatés avec graphiques
- **CSV** : Format interopérable
- **Export progressif** : Pour gros volumes (>10k lignes)

**Architecture** :

```php
// Export synchrone (< 5000 lignes)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// Export asynchrone (> 5000 lignes)
$_SESSION['export_progress'] = ['status' => 'processing', 'percent' => 0];
```

### **6. Gestion_doublons_nomenclature.php**

**Objectif** : Détection et résolution des doublons

**Algorithme de détection** :

```php
// Groupement par clé métier
$key = $equipement['repere_equipement'] . '|' . $article['code_article'];

// Détection des conflits
foreach ($nomenclatures as $nom) {
    if (isset($seen[$key])) {
        $dups[$key][] = $nom;
    }
}
```

**Interface de résolution** :

- Tableau des conflits avec détails
- Actions : Conserver/Fusionner/Supprimer
- Validation en lot
- Historique des résolutions

---

## 📤 **Système d'Import/Export**

### **Architecture Import**

```php
// 1. Upload et validation fichier
if ($_FILES['file']['type'] !== 'application/vnd.openxmlformats...') {
    throw new Exception('Format non supporté');
}

// 2. Lecture PhpSpreadsheet
$reader = IOFactory::createReader('Xlsx');
$spreadsheet = $reader->load($_FILES['file']['tmp_name']);

// 3. Validation colonnes
$expectedColumns = ['repere_equipement', 'code_article', 'quantite'];
$headerRow = $worksheet->getRowIterator(1, 1)->current();

// 4. Traitement par batch
for ($row = 2; $row <= $highestRow; $row += 100) {
    $batch = $worksheet->rangeToArray("A{$row}:Z{$min($row+99, $highestRow)}");
    processImportBatch($batch);
}
```

### **Gestion des Doublons d'Import**

**Table temporaire** : `nomenclatures_doublons_import`

**Workflow** :

1. **Détection** : Import identifie les doublons existants
2. **Temporisation** : Stockage en table temporaire avec statut `en_attente`
3. **Validation** : Interface utilisateur pour résolution
4. **Application** : Mise à jour selon décision utilisateur

```php
// Détection doublon à l'import
$existing = Nomenclature::getByRepereArticle($repere, $codeArticle);
if ($existing) {
    DoublonImport::create([
        'repere_equipement' => $repere,
        'code_article' => $codeArticle,
        'quantite_existante' => $existing['quantite'],
        'quantite_nouvelle' => $newQuantite,
        'statut' => 'en_attente'
    ]);
}
```

### **Export Progressif**

**Problématique** : Export de gros volumes (>100k lignes) sans timeout

**Solution** :

```php
// 1. Calcul du nombre total
$total = Nomenclature::count($filters);

// 2. Export par chunks
$chunkSize = 5000;
for ($offset = 0; $offset < $total; $offset += $chunkSize) {
    $data = Nomenclature::getChunk($offset, $chunkSize, $filters);
    $worksheet->fromArray($data, null, "A" . ($offset + 2));

    // Mise à jour progression
    $_SESSION['export_progress'] = [
        'percent' => round(($offset / $total) * 100),
        'status' => 'processing'
    ];
}
```

---

## 🔍 **Gestion des Doublons**

### **Types de Doublons**

1. **Doublons de Nomenclature**

   - Même `(repere_equipement, code_article)`
   - Quantités ou unités différentes
   - Sources différentes (SAP vs Template SPL)

2. **Doublons d'Import**

   - Détectés lors d'import Excel
   - Nécessitent validation utilisateur
   - Stockage temporaire pour résolution

3. **Doublons d'Équipement**
   - Même repère, codes différents
   - Même code, repères différents
   - Inconsistances référentielles

### **Interface de Résolution**

**Page** : `gestion_doublons_nomenclature.php`

**Fonctionnalités** :

```javascript
// Affichage des conflits
function displayConflicts(conflicts) {
    conflicts.forEach(conflict => {
        renderConflictRow(conflict);
    });
}

// Actions de résolution
function resolveConflict(conflictId, action) {
    // action: 'keep_first', 'keep_last', 'merge', 'delete_all'
}

// Validation en lot
function bulkResolve(selectedConflicts, action)
```

**Algorithme de fusion** :

```php
public static function mergeNomenclatures($nomenclatures) {
    // Priorité : SAP > Template SPL > RGM > Autre
    usort($nomenclatures, function($a, $b) {
        $priority = ['SAP' => 4, 'Template SPL' => 3, 'RGM' => 2];
        return ($priority[$b['source']] ?? 1) - ($priority[$a['source']] ?? 1);
    });

    $master = $nomenclatures[0];

    // Fusion des quantités
    $totalQuantite = array_sum(array_column($nomenclatures, 'quantite'));
    $master['quantite'] = $totalQuantite;

    return $master;
}
```

---

## 🔐 **Authentification et Sécurité**

### **includes/auth.php**

**Contrôle d'accès global** :

```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérification timeout session
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > 3600)) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();
```

### **Gestion des Utilisateurs**

**Model/Utilisateur.php** :

```php
public static function authenticate($username, $password) {
    $user = self::getByUsername($username);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        return $user;
    }

    return false;
}

public static function createUser($data) {
    $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
    // ...
}
```

### **Système de Droits**

**Table** : `droits_utilisateur`

```sql
INSERT INTO droits_utilisateur VALUES
(1, 1, 'equipements', 'read'),
(2, 1, 'equipements', 'write'),
(3, 1, 'articles', 'read'),
(4, 2, 'dashboard', 'read'); -- Utilisateur en lecture seule
```

**Contrôle** :

```php
function hasPermission($userId, $module, $permission) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM droits_utilisateur
        WHERE id_utilisateur = ? AND module = ? AND permission = ?
    ");
    $stmt->execute([$userId, $module, $permission]);
    return $stmt->fetchColumn() > 0;
}
```

---

## 🔌 **APIs et Services**

### **Structure API REST**

**Pattern** : `api/{entity}_{action}.php`

**Exemples** :

- `api/export_data.php` → Export général
- `api/import_rgm.php` → Import données RGM
- `api/quantitatif_stats.php` → Statistiques quantitatif
- `api/template_spl_realtime.php` → Données temps réel Template SPL

### **Request Layer**

**Pattern** : `request/{entity}_{action}.php`

**Types d'endpoints** :

1. **Pagination** : `*_paginated.php`
2. **CRUD** : `*_add.php`, `*_update.php`, `*_delete.php`
3. **Import** : `*_import.php`
4. **Export** : `export_*.php`
5. **Stats** : `*_stats.php`

### **Exemple API Pagination**

**request/equipements_paginated.php** :

```php
header('Content-Type: application/json');

$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 50);
$filters = [
    'search' => $_GET['search'] ?? '',
    'fabricant' => $_GET['fabricant'] ?? '',
    'source' => $_GET['source'] ?? ''
];

try {
    $result = Equipement::getPaginated($page, $limit, $filters);

    echo json_encode([
        'success' => true,
        'data' => $result['data'],
        'pagination' => [
            'page' => $result['page'],
            'total' => $result['total'],
            'hasMore' => $result['hasMore']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### **Gestion des Erreurs API**

**Format standard** :

```json
{
  "success": false,
  "error": "Message d'erreur explicite",
  "code": "VALIDATION_ERROR",
  "details": {
    "field": "repere_equipement",
    "message": "Le repère est obligatoire"
  }
}
```

---

## ⚙️ **Configuration et Déploiement**

### **Requirements**

**Serveur** :

- PHP 7.4+ avec extensions : PDO MySQL, Zip, GD, MBString
- MySQL 8.0+
- Apache/Nginx avec mod_rewrite
- 512MB RAM minimum, 2GB recommandé
- 50GB espace disque (pour uploads/exports)

**Composer Dependencies** :

```json
{
  "require": {
    "phpoffice/phpspreadsheet": "^1.29",
    "tecnickcom/tcpdf": "^6.6",
    "setasign/fpdi": "^2.3"
  }
}
```

### **Installation**

1. **Clone/Upload** du code source
2. **Composer install** pour les dépendances
3. **Configuration BDD** dans `model/Database.php`
4. **Import SQL** du schéma de base
5. **Permissions** : `uploads/`, `tmp/` en écriture
6. **Création utilisateur admin** initial

### **Configuration Apache**

**.htaccess** :

```apache
RewriteEngine On

# Redirection index -> dashboard
RewriteRule ^index\.php$ dashboard.php [R=301,L]

# Protection des dossiers sensibles
<FilesMatch "\.(php|json|sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Uploads publics
<Directory "uploads/">
    Order allow,deny
    Allow from all
</Directory>

# Sécurité uploads
<Directory "uploads/">
    <FilesMatch "\.(php|phtml|php3|php4|php5|pl|cgi)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
</Directory>
```

### **Variables d'Environnement**

**Recommandation** : Externaliser la config DB

```php
// config/database.php
return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'nomenclature_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? ''
];
```

---

## 📊 **Maintenance et Monitoring**

### **Logs et Debug**

**Activation logs** :

```php
// En tête des pages critiques
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log personnalisé
function logError($message, $context = []) {
    $logEntry = date('Y-m-d H:i:s') . " - " . $message . " - " . json_encode($context);
    file_put_contents('logs/app.log', $logEntry . PHP_EOL, FILE_APPEND);
}
```

### **Monitoring Performance**

**Métriques à surveiller** :

- Temps de réponse API pagination (< 2s)
- Taille des exports Excel (< 100MB)
- Connexions simultanées BDD (< 50)
- Espace disque uploads/ et tmp/

**Optimisations BDD** :

```sql
-- Index pour recherches fréquentes
CREATE INDEX idx_nomenclatures_repere ON nomenclatures(repere_equipement);
CREATE INDEX idx_nomenclatures_article ON nomenclatures(code_article);
CREATE INDEX idx_nomenclatures_source ON nomenclatures(source);

-- Partitioning par date pour gros volumes
ALTER TABLE nomenclatures
PARTITION BY RANGE (YEAR(date_creation)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026)
);
```

### **Scripts de Maintenance**

**Nettoyage automatique** :

```php
// scripts/cleanup.php
// Suppression fichiers temporaires > 7 jours
$files = glob('tmp/*');
foreach ($files as $file) {
    if (is_file($file) && time() - filemtime($file) > 7 * 24 * 3600) {
        unlink($file);
    }
}

// Suppression doublons d'import résolus > 30 jours
$pdo->query("
    DELETE FROM nomenclatures_doublons_import
    WHERE statut IN ('approuve', 'rejete')
    AND date_creation < DATE_SUB(NOW(), INTERVAL 30 DAY)
");
```

**Backup automatique** :

```bash
#!/bin/bash
# scripts/backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backups/db_$DATE.sql
tar -czf backups/files_$DATE.tar.gz uploads/ css/ js/

# Retention 30 jours
find backups/ -name "*.sql" -mtime +30 -delete
find backups/ -name "*.tar.gz" -mtime +30 -delete
```

---

## 🔧 **Outils de Développement**

### **Debug et Profiling**

**Xdebug recommandé** pour développement :

```ini
; php.ini
xdebug.mode=debug
xdebug.start_with_request=trigger
xdebug.client_port=9003
```

**Query profiling** :

```php
// model/Database.php
public static function profileQuery($sql, $params = []) {
    $start = microtime(true);
    $stmt = self::$pdo->prepare($sql);
    $stmt->execute($params);
    $duration = microtime(true) - $start;

    if ($duration > 1.0) { // Log requêtes lentes
        error_log("Slow query ({$duration}s): $sql");
    }

    return $stmt;
}
```

### **Tests Unitaires**

**Structure recommandée** :

```
tests/
├── Models/
│   ├── EquipementTest.php
│   ├── ArticleTest.php
│   └── NomenclatureTest.php
├── API/
│   ├── PaginationTest.php
│   └── ExportTest.php
└── Integration/
    ├── ImportTest.php
    └── DoublonsTest.php
```

**Exemple test** :

```php
// tests/Models/EquipementTest.php
class EquipementTest extends PHPUnit\Framework\TestCase {

    public function testGetPaginated() {
        $result = Equipement::getPaginated(1, 10, ['search' => 'pompe']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertLessThanOrEqual(10, count($result['data']));
    }

    public function testCreateValidation() {
        $this->expectException(Exception::class);

        Equipement::create([
            'repere_equipement' => '', // Repère vide doit échouer
            'designation_equipement' => 'Test'
        ]);
    }
}
```

---

## 📝 **Conventions de Code**

### **Nommage**

- **Classes** : PascalCase (`EquipementController`)
- **Méthodes** : camelCase (`getPaginated`)
- **Variables** : snake_case (`$repere_equipement`)
- **Constantes** : UPPER_CASE (`MAX_IMPORT_SIZE`)
- **Fichiers** : snake_case (`equipements_paginated.php`)

### **Documentation**

**PHPDoc obligatoire** :

```php
/**
 * Récupère les équipements avec pagination et filtres
 *
 * @param int $page Page actuelle (1-based)
 * @param int $limit Nombre d'éléments par page
 * @param array $filters Filtres à appliquer ['search' => string, 'fabricant' => string]
 * @return array ['data' => array, 'total' => int, 'hasMore' => bool]
 * @throws Exception Si les paramètres sont invalides
 */
public static function getPaginated($page = 1, $limit = 50, $filters = [])
```

### **Gestion d'Erreurs**

**Try-catch systematique** :

```php
try {
    $result = Equipement::create($data);
    echo json_encode(['success' => true, 'id' => $result]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'CREATION_FAILED'
    ]);
}
```

---

## 🚀 **Évolutions Futures**

### **Améliorations Techniques**

1. **Migration Framework** : Symfony/Laravel pour structure plus robuste
2. **API REST complète** : OpenAPI/Swagger documentation
3. **Cache Redis** : Pour requêtes statistiques fréquentes
4. **Queue System** : Pour imports/exports asynchrones (RabbitMQ)
5. **Docker** : Containerisation pour déploiement
6. **CI/CD** : Pipeline automatisé avec tests

### **Nouvelles Fonctionnalités**

1. **Workflow d'approbation** : Validation multiniveau des imports
2. **Audit Trail** : Historique détaillé des modifications
3. **Notifications** : Email/SMS pour événements critiques
4. **Dashboard mobile** : Interface responsive avancée
5. **Export API** : Intégration avec systèmes externes
6. **Machine Learning** : Détection automatique des anomalies

### **Optimisations Performance**

1. **Lazy Loading** : Chargement à la demande des relations
2. **Elasticsearch** : Recherche full-text performante
3. **CDN** : Assets statiques (CSS/JS/Images)
4. **Clustering BDD** : Réplication maître-esclave
5. **Micro-services** : Séparation import/export/stats

---

## 📋 **Détail des Fonctionnalités par Page**

### **Pages de Gestion CRUD**

#### **1. Équipements.php**

- **Objectif** : Gestion complète du référentiel équipements
- **Fonctionnalités** :
  - Pagination Ajax avec filtres (fabricant, type, catégorie, source)
  - Import Excel avec validation des colonnes
  - Export Excel/PDF avec sélection personnalisée
  - Statistiques par famille avec graphiques Chart.js
  - Recherche full-text multi-colonnes
  - Modal d'édition avec validation temps réel

**API Endpoints** :

```
GET  /request/equipements_paginated.php    → Données paginées
POST /request/equipement_add.php           → Ajout
PUT  /request/equipement_update.php        → Mise à jour
DEL  /request/equipement_delete.php        → Suppression
POST /request/equipement_import.php        → Import Excel
GET  /request/export_equipements.php       → Export Excel/PDF
```

#### **2. Articles.php**

- **Similaire aux équipements** avec spécificités :
  - Gestion des groupes d'articles
  - Validation du format code article
  - Unités de mesure (UQ_BASE)
  - Lien avec numéros de pièces fabricant

#### **3. Nomenclatures.php**

- **Logique métier complexe** :
  - Validation existante équipement + article
  - Contrôle unicité (repère + code article)
  - Synchronisation automatique repère ↔ code équipement
  - Gestion quantités avec unités
  - Traçabilité par source (SAP, RGM, Template SPL)

**Validation JavaScript** :

```javascript
async function validateNomenclature(data) {
  // Vérif existence équipement
  const equipementExists = await checkEquipement(data.repere_equipement);

  // Vérif existence article
  const articleExists = await checkArticle(data.code_article);

  // Vérif pas de doublon
  const isDuplicate = await checkDuplicate(
    data.repere_equipement,
    data.code_article
  );

  return { valid: equipementExists && articleExists && !isDuplicate };
}
```

### **Pages d'Analyse et Contrôle**

#### **4. Gestion_doublons_nomenclature.php**

- **Détection automatique des doublons**
- **Interface de résolution par lot**
- **Algorithme de fusion intelligent** :

```php
// Détection doublons par clé métier
$sql = "SELECT repere_equipement, code_article, COUNT(*) as count
        FROM nomenclatures
        GROUP BY repere_equipement, code_article
        HAVING count > 1";

// Fusion avec priorité par source
$priority = ['SAP' => 4, 'Template SPL' => 3, 'RGM' => 2, 'Autre' => 1];
```

#### **5. Elements_non_sap.php**

- **Identification des éléments non codifiés SAP**
- **Export Excel pour codification**
- **Statistiques avec graphiques temps réel**

**Requête de détection** :

```sql
-- Équipements sans nomenclature SAP
SELECT DISTINCT e.repere_equipement
FROM equipements e
WHERE e.repere_equipement NOT IN (
    SELECT DISTINCT n.repere_equipement
    FROM nomenclatures n
    WHERE n.source = 'SAP'
);

-- Articles sans référence SAP
SELECT DISTINCT a.code_article
FROM articles a
WHERE a.code_article NOT IN (
    SELECT DISTINCT n.code_article
    FROM nomenclatures n
    WHERE n.source = 'SAP'
);
```

#### **6. Validation_doublons_import.php**

- **Validation des conflits d'import**
- **Interface de résolution conflit par conflit**
- **Actions** : Conserver existant, Remplacer, Fusionner quantités

**Workflow de validation** :

```javascript
function resolveConflict(conflictId, action) {
  switch (action) {
    case "keep_existing":
      // Rejeter l'import, conserver l'existant
      break;
    case "replace":
      // Remplacer par les nouvelles valeurs
      break;
    case "merge_quantities":
      // Additionner les quantités
      break;
  }
}
```

### **Pages d'Import et Traitement de Masse**

#### **7. Import_quantitatif.php**

- **Import de données quantitatives Excel**
- **Traitement par batch de 1000 lignes**
- **Validation et rapprochement automatique**

**Algorithme d'import** :

```php
// 1. Lecture fichier Excel
$reader = IOFactory::createReader('Xlsx');
$spreadsheet = $reader->load($file);

// 2. Validation colonnes obligatoires
$requiredColumns = ['repere', 'famille', 'quantite', 'unite'];

// 3. Traitement par batch pour éviter timeout
for ($row = 2; $row <= $highestRow; $row += 1000) {
    $batch = $worksheet->rangeToArray("A{$row}:Z{$min($row+999, $highestRow)}");
    processQuantitatifBatch($batch);
}

// 4. Rapprochement avec équipements existants
function matchWithEquipements($quantitatifData) {
    // Fuzzy matching sur repères équipement
    // Mise à jour automatique des familles
}
```

#### **8. RGM_synthese.php**

- **Import données RGM (Référentiel Général Métier)**
- **Synchronisation avec nomenclatures**
- **Traçabilité des modifications**

**API de synchronisation** :

```php
// api/sync_rgm_nomenclature.php
function syncRgmToNomenclature($rgmData) {
    foreach ($rgmData as $rgm) {
        $existing = Nomenclature::getByRepereArticle(
            $rgm['repere_equipement'],
            $rgm['code_article']
        );

        if ($existing) {
            // Mise à jour avec contrôle de source
            if ($existing['source'] !== 'SAP') {
                Nomenclature::updateFromRgm($existing['id'], $rgm);
            }
        } else {
            // Création nouvelle nomenclature
            Nomenclature::createFromRgm($rgm);
        }
    }
}
```

#### **9. Compilation_template.php**

- **Compilation de templates SPL (Spare Parts List)**
- **Import Excel avec validation métier**
- **Export consolidé multi-format**

**Validation Template SPL** :

```javascript
function validateSplTemplate(data) {
  const required = ["metier", "code_article", "quantite", "unite_base"];
  const errors = [];

  required.forEach((field) => {
    if (!data[field] || data[field].trim() === "") {
      errors.push(`Le champ ${field} est obligatoire`);
    }
  });

  // Validation format quantité
  if (data.quantite && !isNumeric(data.quantite)) {
    errors.push("La quantité doit être numérique");
  }

  return errors;
}
```

### **Pages d'Administration**

#### **10. Utilisateurs.php**

- **Gestion CRUD des utilisateurs**
- **Hashage sécurisé des mots de passe**
- **Activation/désactivation des comptes**

#### **11. Groupes.php**

- **Gestion des groupes utilisateurs**
- **Attribution des droits par module**
- **Héritage des permissions**

#### **12. Profil.php**

- **Modification profil utilisateur**
- **Changement mot de passe sécurisé**
- **Historique des connexions**

### **Centre d'Export Unifié**

#### **13. Exportations.php**

- **Hub central pour tous les exports**
- **Support multi-format** : Excel, PDF, CSV
- **Export progressif pour gros volumes**

**Architecture Export** :

```php
class ExportManager {
    public static function export($type, $format, $filters = []) {
        switch($format) {
            case 'excel':
                return self::exportExcel($type, $filters);
            case 'pdf':
                return self::exportPdf($type, $filters);
            case 'csv':
                return self::exportCsv($type, $filters);
        }
    }

    private static function exportExcel($type, $filters) {
        $spreadsheet = new Spreadsheet();
        // Configuration styles, en-têtes, données

        if ($this->isLargeDataset($filters)) {
            return $this->exportProgressive($spreadsheet, $filters);
        } else {
            return $this->exportDirect($spreadsheet, $filters);
        }
    }
}
```

---

## 🔧 **APIs et Services Détaillés**

### **API Pagination (Pattern Commun)**

**Structure standard** pour tous les modules :

```php
// request/{module}_paginated.php
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));

$filters = array_filter([
    'search' => $_GET['search'] ?? '',
    'fabricant' => $_GET['fabricant'] ?? '',
    // ... autres filtres
]);

$result = Module::getPaginated($page, $limit, $filters);

echo json_encode([
    'success' => true,
    'data' => $result['data'],
    'pagination' => [
        'page' => $result['page'],
        'total' => $result['total'],
        'hasMore' => $result['hasMore'],
        'totalPages' => ceil($result['total'] / $result['limit'])
    ]
]);
```

### **API Statistiques Temps Réel**

#### **dashboard_stats_simple.php**

```php
// Statistiques optimisées pour dashboard
$data = [
    'familles' => getFamillesStats(),           // Top 10 familles
    'evolution' => getEvolutionStats(),         // 7 derniers mois
    'types_equipements' => getTypesStats(),     // Répartition par type
    'metiers' => getMetiersStats()              // Métiers articles
];

// Cache Redis recommandé pour optimisation
if (extension_loaded('redis')) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->setex('dashboard_stats', 300, json_encode($data)); // 5min cache
}
```

#### **quantitatif_stats.php**

```php
// Statistiques quantitatif avec agrégations
$stats = [
    'total_elements' => getQuantitatifCount(),
    'total_familles' => getDistinctFamilles(),
    'top_familles' => getTopFamilles(10),
    'quantites' => [
        'total' => getSumQuantites(),
        'moyenne' => getAvgQuantites(),
        'max' => getMaxQuantites()
    ]
];
```

### **Services d'Import Avancés**

#### **Import avec Validation Métier**

```php
class ImportValidator {
    public static function validateEquipement($data) {
        $errors = [];

        // Validation repère unique
        if (Equipement::existsByRepere($data['repere_equipement'])) {
            $errors[] = "Le repère {$data['repere_equipement']} existe déjà";
        }

        // Validation format code
        if (!preg_match('/^[A-Z0-9-]+$/', $data['code_equipement'])) {
            $errors[] = "Format code équipement invalide";
        }

        return $errors;
    }
}
```

#### **Import Progressif avec WebSocket (Recommandé)**

```javascript
// Frontend - Suivi progression import
function startImport(file) {
  const formData = new FormData();
  formData.append("file", file);

  // Upload avec progression
  const xhr = new XMLHttpRequest();

  xhr.upload.addEventListener("progress", (e) => {
    const percent = (e.loaded / e.total) * 100;
    updateProgressBar(percent);
  });

  xhr.onload = function () {
    if (xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);
      showImportResults(response);
    }
  };

  xhr.open("POST", "request/equipement_import.php");
  xhr.send(formData);
}
```

---

## 🛡️ **Sécurité et Validation**

### **Validation Input Global**

```php
class SecurityValidator {
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }

        // Nettoyage XSS
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        // Nettoyage SQL injection
        $input = trim($input);

        return $input;
    }

    public static function validateFileUpload($file) {
        $allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ];

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé');
        }

        if ($file['size'] > 50 * 1024 * 1024) { // 50MB max
            throw new Exception('Fichier trop volumineux');
        }

        return true;
    }
}
```

### **Protection CSRF**

```php
// Génération token CSRF
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validation token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}
```

---

## 📊 **Optimisations Performance**

### **Cache Strategy**

```php
class CacheManager {
    private static $redis;

    public static function get($key) {
        if (!self::$redis) {
            self::$redis = new Redis();
            self::$redis->connect('127.0.0.1', 6379);
        }

        return self::$redis->get($key);
    }

    public static function set($key, $value, $ttl = 300) {
        if (!self::$redis) return false;

        return self::$redis->setex($key, $ttl, serialize($value));
    }

    // Cache statistiques dashboard
    public static function getDashboardStats() {
        $cacheKey = 'dashboard_stats_' . date('Y-m-d-H');
        $cached = self::get($cacheKey);

        if ($cached) {
            return unserialize($cached);
        }

        $stats = DashboardController::generateStats();
        self::set($cacheKey, $stats, 3600); // 1h cache

        return $stats;
    }
}
```

### **Requêtes Optimisées**

```sql
-- Index composites pour recherches fréquentes
CREATE INDEX idx_nomenclatures_repere_article ON nomenclatures(repere_equipement, code_article);
CREATE INDEX idx_nomenclatures_source_date ON nomenclatures(source, date_creation);
CREATE INDEX idx_equipements_search ON equipements(repere_equipement, designation_equipement, fabricant);

-- Requête optimisée pour dashboard
SELECT
    (SELECT COUNT(*) FROM equipements) as total_equipements,
    (SELECT COUNT(*) FROM articles) as total_articles,
    (SELECT COUNT(*) FROM nomenclatures) as total_nomenclatures,
    (SELECT COUNT(DISTINCT repere_equipement) FROM nomenclatures WHERE source = 'SAP') as equipements_sap;
```

---

## 🚀 **Évolutions et Recommandations**

### **Architecture Cible**

1. **Microservices** : Séparer Import/Export/Stats en services indépendants
2. **Queue System** : RabbitMQ pour traitement asynchrone des imports
3. **API Gateway** : Nginx avec rate limiting et authentification centralisée
4. **Monitoring** : Prometheus + Grafana pour métriques applicatives

### **Migrations Recommandées**

```php
// Structure cible avec framework moderne
app/
├── Controllers/
│   ├── EquipementController.php
│   ├── ArticleController.php
│   └── NomenclatureController.php
├── Services/
│   ├── ImportService.php
│   ├── ExportService.php
│   └── ValidationService.php
├── Repositories/
│   ├── EquipementRepository.php
│   └── ArticleRepository.php
└── Events/
    ├── EquipementCreated.php
    └── ImportCompleted.php
```

### **Tests et Qualité**

```php
// Exemple tests unitaires recommandés
class EquipementServiceTest extends TestCase {
    public function testCreateEquipementWithValidData() {
        $data = [
            'repere_equipement' => 'TEST-001',
            'designation_equipement' => 'Pompe de test',
            'fabricant' => 'TestCorp'
        ];

        $result = EquipementService::create($data);

        $this->assertInstanceOf(Equipement::class, $result);
        $this->assertEquals('TEST-001', $result->repere_equipement);
    }

    public function testCreateEquipementWithDuplicateRepere() {
        $this->expectException(DuplicateRepereException::class);

        EquipementService::create(['repere_equipement' => 'EXISTING-001']);
    }
}
```

---

Cette documentation technique fournit tous les éléments nécessaires pour comprendre, maintenir et faire évoluer l'application EquiNomTech. Elle constitue la référence complète pour les développeurs intervenant sur le projet.
