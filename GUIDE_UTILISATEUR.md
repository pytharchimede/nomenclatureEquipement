# 👥 **EquiNomTech - Guide Utilisateur Détaillé**

## _Manuel d'Utilisation Complète_

---

## 📋 **Table des Matières**

1. [Premiers Pas](#premiers-pas)
2. [Navigation et Interface](#navigation-et-interface)
3. [Gestion des Équipements](#gestion-des-equipements)
4. [Gestion des Articles](#gestion-des-articles)
5. [Gestion des Nomenclatures](#gestion-des-nomenclatures)
6. [Imports et Exports](#imports-et-exports)
7. [Analyses et Rapports](#analyses-et-rapports)
8. [Administration](#administration)
9. [Conseils et Bonnes Pratiques](#conseils-et-bonnes-pratiques)
10. [FAQ et Résolution de Problèmes](#faq-et-resolution-de-problemes)

---

## 🚀 **Premiers Pas**

### **1. Connexion à l'Application**

#### **Accès Initial**

1. Ouvrir votre navigateur web
2. Saisir l'URL : `http://votre-serveur/nomenclatureequipement`
3. Vous êtes automatiquement redirigé vers la page de connexion

#### **Authentification**

1. **Nom d'utilisateur** : Saisissez votre identifiant
2. **Mot de passe** : Saisissez votre mot de passe
3. Cliquez sur **"Se connecter"**

> 💡 **Astuce** : En cas d'oubli de mot de passe, contactez votre administrateur système.

#### **Premier Démarrage**

Après votre première connexion, vous arrivez sur le **Dashboard principal** qui vous donne une vue d'ensemble de votre système.

### **2. Découverte de l'Interface**

#### **Éléments Principaux**

- **Barre de navigation gauche** : Accès aux différents modules
- **Zone centrale** : Contenu principal de la page active
- **Boutons d'action** : En haut à droite de chaque page
- **Alertes** : Notifications importantes en haut des pages

---

## 🧭 **Navigation et Interface**

### **1. Menu Principal**

#### **Structure du Menu**

```
🏠 Dashboard          → Vue d'ensemble et statistiques
🔧 Équipements        → Gestion des équipements industriels
📦 Articles           → Gestion des pièces de rechange
📋 Nomenclatures      → Relations équipements-articles
📤 Exportation       → Exports et téléchargements
⚠️ Doublons           → Gestion des doublons
🔴 Éléments Non SAP   → Analyse éléments non codifiés
✅ Validation Import  → Validation imports en attente
📂 Familles           → Classification par familles
📊 Quantitatif        → Import données quantitatives
📊 Synthèse RGM       → Données RGM
📋 Template SPL       → Gestion Template SPL
👥 Utilisateurs       → Gestion des utilisateurs
👥 Groupes            → Gestion des groupes d'accès
👤 Mon profil         → Profil utilisateur personnel
🚪 Déconnexion        → Sortie de l'application
```

### **2. Badges et Indicateurs**

#### **Système d'Alertes**

- 🔴 **Badge rouge** : Éléments nécessitant une attention immédiate
- 🟡 **Badge jaune** : Éléments en attente de traitement
- 🟢 **Badge vert** : Statut normal

**Exemples :**

- **Doublons (12)** : 12 doublons détectés à résoudre
- **Éléments Non SAP (45)** : 45 éléments non codifiés SAP
- **Validation Import (8)** : 8 imports en attente de validation

### **3. Interface Responsive**

#### **Adaptation Mobile**

- Menu hamburger sur mobile/tablette
- Tableaux défilants horizontalement
- Boutons d'action adaptés aux écrans tactiles
- Graphiques redimensionnés automatiquement

---

## 🔧 **Gestion des Équipements**

### **1. Vue d'Ensemble**

#### **Accès au Module**

- Cliquer sur **"Équipements"** dans le menu principal
- La page affiche la liste de tous vos équipements

#### **Informations Affichées**

```
Repère Équipement    : Identifiant unique (ex: MP1080A)
Désignation          : Description de l'équipement
Fabricant            : Constructeur de l'équipement
Type d'Objet         : Classification technique
Catégorie            : Groupe fonctionnel
Centre de Coûts      : Affectation comptable
Source               : Origine des données (SAP, RGM, etc.)
```

### **2. Recherche et Filtrage**

#### **Barre de Recherche Globale**

1. Tapez dans le champ **"Rechercher..."**
2. La recherche s'effectue en temps réel sur :
   - Repère équipement
   - Désignation
   - Fabricant

#### **Filtres Avancés**

- **Fabricant** : Sélection par constructeur
- **Type d'objet** : Filtrage par type technique
- **Catégorie** : Filtrage par groupe fonctionnel
- **Source** : Filtrage par origine des données

#### **Utilisation des Filtres**

1. Cliquez sur les listes déroulantes de filtres
2. Sélectionnez la valeur souhaitée
3. La liste se met à jour automatiquement
4. Bouton **"Réinitialiser"** pour effacer tous les filtres

### **3. Actions sur les Équipements**

#### **Ajout d'un Équipement**

1. Cliquer sur **"+ Ajouter"**
2. Remplir le formulaire :
   - **Repère** (obligatoire) : Identifiant unique
   - **Désignation** : Description complète
   - **Fabricant** : Nom du constructeur
   - **Type d'objet** : Classification
   - Autres champs optionnels
3. Cliquer **"Enregistrer"**

#### **Modification d'un Équipement**

1. Cliquer sur la ligne de l'équipement à modifier
2. Le formulaire d'édition s'ouvre
3. Modifier les champs nécessaires
4. Cliquer **"Sauvegarder"**

#### **Suppression d'Équipements**

1. Cocher les équipements à supprimer
2. Cliquer **"Supprimer sélection"**
3. Confirmer la suppression

> ⚠️ **Attention** : La suppression d'un équipement supprime aussi ses nomenclatures associées.

### **4. Import d'Équipements**

#### **Import Excel Simplifié**

1. Cliquer **"Import Excel"**
2. **Glisser-déposer** votre fichier Excel ou cliquer pour sélectionner
3. Le système affiche une barre de progression
4. Consulter le rapport d'import

#### **Format Fichier Excel**

```
Colonnes acceptées :
- Code Équipement / Repère Équipement (obligatoire)
- Désignation Équipement
- Fabricant
- Type d'Objet
- Catégorie Équipement
- Centre de Coûts
- Numéro Série
- Poste Technique
```

#### **Gestion des Erreurs**

- **Doublons** : Affichés en rouge dans le rapport
- **Données manquantes** : Signalées avec suggestions
- **Format incorrect** : Messages d'erreur explicites

### **5. Export d'Équipements**

#### **Types d'Export**

- **Export Complet** : Tous les équipements
- **Export Filtré** : Selon filtres actifs
- **Sans Pièces de Rechange** : Équipements sans nomenclature
- **Par Famille** : Groupé par classification

#### **Formats Disponibles**

- **Excel (.xlsx)** : Avec mise en forme
- **PDF** : Rapport professionnel
- **CSV** : Pour intégration tiers

---

## 📦 **Gestion des Articles**

### **1. Vue d'Ensemble**

#### **Accès au Module**

- Cliquer sur **"Articles"** dans le menu principal
- Affichage de la liste complète des articles/pièces de rechange

#### **Informations Affichées**

```
Code Article         : Identifiant unique (ex: 123456789)
Désignation          : Description de la pièce
Type d'Article       : Classification technique
Fabricant            : Fournisseur/Constructeur
N° Pièce Fabricant   : Référence constructeur
Groupe Articles      : Classification par famille
UQ Base              : Unité de quantité
Métier               : Domaine technique (automatique)
```

### **2. Classification Automatique par Métier**

#### **Logique de Classification**

Le système classe automatiquement les articles selon leur code :

```
1xxx → Mécanique      : Roulements, joints, visserie
2xxx → Électrique     : Moteurs, contacteurs, câbles
3xxx → Instrumentation: Capteurs, transmetteurs, vannes
4xxx → Tuyauterie     : Tubes, raccords, vannes
5xxx → Chaudronnerie  : Tôles, profilés, soudure
6xxx → Civil/Structure: Béton, ferraillage, étanchéité
7xxx → Chimie/Process : Produits chimiques, catalyseurs
8xxx → Sécurité       : EPI, détection, extinction
9xxx → Maintenance    : Outillage, lubrifiants, nettoyage
```

### **3. Recherche et Filtrage Avancé**

#### **Filtres Spécialisés**

- **Code/Désignation** : Recherche textuelle
- **Fabricant** : Par fournisseur
- **Type d'article** : Par classification
- **Groupe d'articles** : Par famille
- **UQ Base** : Par unité de mesure
- **Source** : Par origine des données
- **Métier** : Par domaine technique

#### **Recherche Intelligente**

- Recherche dans tous les champs simultanément
- Ignore la casse et les accents
- Suggestions automatiques
- Historique des recherches

### **4. Gestion des Articles**

#### **Ajout d'Article**

1. Cliquer **"+ Ajouter Article"**
2. Formulaire détaillé :
   - **Code Article** (obligatoire)
   - **Désignation** (obligatoire)
   - **Type d'article**
   - **Fabricant**
   - **N° Pièce Fabricant**
   - **Groupe Articles**
   - **UQ Base**
   - **Description complémentaire**
3. **Validation automatique** du code
4. **Classification métier** automatique

#### **Modification en Masse**

1. Sélectionner multiple articles (cases à cocher)
2. Cliquer **"Modifier sélection"**
3. Modifier les champs communs
4. Appliquer aux articles sélectionnés

#### **Import Excel d'Articles**

1. Préparer fichier Excel avec colonnes :
   ```
   Code Article (obligatoire)
   Désignation Article (obligatoire)
   Type d'article
   Fabricant
   N° pce fabricant
   Groupe articles
   UQ base
   Description
   ```
2. **Drag & Drop** sur zone d'import
3. **Validation en temps réel**
4. **Traitement par lots** (optimisé pour gros volumes)

### **5. Analyse des Articles**

#### **Statistiques Automatiques**

- Répartition par métier
- Top fabricants
- Articles les plus utilisés
- Articles orphelins (non liés à équipements)

#### **Détection d'Anomalies**

- Articles sans désignation
- Doublons potentiels
- Codes non conformes
- Fabricants incohérents

---

## 📋 **Gestion des Nomenclatures**

### **1. Concept de Nomenclature**

#### **Définition**

Une nomenclature établit la **relation entre un équipement et ses articles** (pièces de rechange). C'est le cœur du système qui permet de savoir quelles pièces sont nécessaires pour chaque équipement.

#### **Structure d'une Nomenclature**

```
Équipement (MP1080A) ←→ Article (123456789) + Quantité (2)
```

### **2. Sources de Données**

#### **Types de Sources**

- **SAP** : Données officielles ERP (référence)
- **RGM** : Données issues de la synthèse RGM
- **Template** : Données importées via Template SPL
- **Manuel** : Saisie manuelle utilisateur

#### **Hiérarchie des Sources**

1. **SAP** = Source de référence
2. **RGM** = Source secondaire fiable
3. **Template** = Source d'import
4. **Manuel** = Source utilisateur

### **3. Consultation des Nomenclatures**

#### **Vue Liste**

- **Repère Équipement** : Identifiant équipement
- **Code Article** : Identifiant pièce
- **Désignations** : Noms équipement et article
- **Quantité** : Nombre de pièces nécessaires
- **Source** : Origine de la donnée
- **Date Création** : Horodatage

#### **Filtres Spécialisés**

- **Par Équipement** : Toutes les pièces d'un équipement
- **Par Article** : Tous les équipements utilisant une pièce
- **Par Source** : Filtrage par origine
- **Par Date** : Période de création

### **4. Gestion des Doublons**

#### **Détection Automatique**

Le système détecte automatiquement les doublons :

- Même équipement + même article
- Sources différentes
- Quantités différentes

#### **Résolution des Doublons**

1. Accéder au module **"Doublons"**
2. Consulter la liste des conflits détectés
3. Pour chaque doublon :
   - **Comparer** les versions
   - **Choisir** la version à conserver
   - **Fusionner** ou **supprimer**

#### **Règles de Résolution Automatique**

1. **SAP** prime sur les autres sources
2. **Quantité la plus récente** en cas de conflit
3. **Données les plus complètes** privilégiées

### **5. Import de Nomenclatures**

#### **Import Excel Standard**

1. Préparer fichier avec colonnes :
   ```
   Repère Équipement (obligatoire)
   Code Article (obligatoire)
   Quantité
   Désignation Équipement
   Désignation Article
   Source
   ```
2. **Import avec validation** :
   - Vérification existence équipements/articles
   - Création automatique si nécessaire
   - Détection doublons

#### **Import Template SPL**

Module spécialisé pour les fichiers SPL (Spare Parts List) :

1. Format Excel spécifique avec feuille "SPL"
2. **Séparation automatique** des équipements multiples
3. **Traitement optimisé** par chunks
4. **Intégration intelligente** avec données existantes

---

## 📤 **Imports et Exports**

### **1. Centre d'Exportation**

#### **Accès au Module**

- Cliquer sur **"Exportation"** dans le menu principal
- Interface unifiée pour tous les exports

#### **Catégories d'Export**

**🔧 Équipements**

- Export Complet
- Sans Pièces de Rechange
- Par Famille
- Export Personnalisé

**📦 Articles**

- Export Complet
- Articles Non Liés (orphelins)
- Par Fabricant
- Export Personnalisé

**📋 Nomenclatures**

- Export Complet
- Par Équipement
- Vue Hiérarchique
- Export Personnalisé

**⚠️ Analyses Spécialisées**

- Doublons Détectés
- Éléments Non SAP
- Statistiques Complètes

### **2. Formats d'Export**

#### **Excel Professionnel (.xlsx)**

- **Mise en forme** : Headers colorés, cellules formatées
- **Graphiques intégrés** : Charts automatiques
- **Métadonnées** : Informations sur l'export
- **Feuilles multiples** : Données + analyses
- **Formules** : Calculs automatiques

#### **PDF Rapport**

- **Mise en page professionnelle**
- **Graphiques vectoriels** haute qualité
- **En-têtes/pieds** de page personnalisés
- **Table des matières** automatique
- **Pagination intelligente**

#### **CSV Données Brutes**

- **Séparateur** configurable (virgule, point-virgule)
- **Encodage UTF-8** avec BOM
- **Échappement** automatique caractères spéciaux
- **Headers** optionnels

### **3. Exports Personnalisés**

#### **Configuration Avancée**

1. Cliquer **"Export Personnalisé"**
2. **Sélection colonnes** : Choisir les champs à exporter
3. **Filtres avancés** : Critères de sélection
4. **Options format** : Personnalisation présentation
5. **Programmation** : Export récurrent optionnel

#### **Modèles d'Export**

- **Sauvegarde** de configurations fréquentes
- **Partage** entre utilisateurs
- **Modèles prédéfinis** pour usages courants

### **4. Import Intelligent**

#### **Fonctionnalités Avancées**

- **Drag & Drop** moderne sur toutes les pages
- **Validation temps réel** pendant l'upload
- **Barre de progression** détaillée
- **Traitement par chunks** pour gros fichiers
- **Gestion d'erreurs** avec suggestions

#### **Mapping Automatique**

Le système reconnaît automatiquement :

- **Colonnes standards** par nom
- **Variantes linguistiques** (français/anglais)
- **Synonymes** de colonnes
- **Formats de données** (dates, nombres)

#### **Gestion des Conflits**

- **Prévisualisation** avant import définitif
- **Résolution interactive** des conflits
- **Mode simulation** pour tests
- **Rollback** en cas de problème

---

## 📊 **Analyses et Rapports**

### **1. Dashboard Intelligence**

#### **Métriques Clés Temps Réel**

```
📊 Équipements Totaux    : 2,450 (+12 ce mois)
📦 Articles Totaux       : 8,750 (+45 ce mois)
📋 Nomenclatures         : 15,200 (+89 ce mois)
✅ Taux Couverture       : 94.2% équipements avec nomenclature
🎯 Codification SAP      : 87.5% articles codifiés
```

#### **Alertes Intelligentes**

- 🔴 **Critiques** : Nécessitent action immédiate
- 🟡 **Attention** : À surveiller
- 🟢 **Normal** : Situation stable
- 📈 **Tendances** : Évolutions significatives

### **2. Graphiques Analytiques**

#### **Famille d'Équipements**

- **Graphique combiné** : Barres (quantité) + Ligne (diversité articles)
- **Analyse** : Identification familles complexes
- **Actions** : Optimisation stock par famille

#### **Évolution Temporelle**

- **Croissance équipements** sur 7 mois
- **Nouveaux ajouts** vs cumul total
- **Tendances** et projections

#### **Répartition par Métiers**

- **Graphique circulaire** : Articles par domaine technique
- **Pourcentages** automatiques
- **Couleurs** distinctives par métier

#### **Sources de Données**

- **Comparaison** SAP vs RGM vs Template
- **Taux de couverture** par source
- **Évolutions** dans le temps

### **3. Module Éléments Non SAP**

#### **Vue d'Ensemble**

Analyse spécialisée pour identifier les éléments non intégrés dans SAP :

**📊 Statistiques Instantanées**

- Équipements non SAP par famille
- Articles non SAP par métier
- Sources alternatives disponibles
- Tendances d'évolution

**📈 Graphiques Spécialisés**

- Répartition équipements par famille
- Classification articles par métier
- Analyse des sources actuelles
- Évolution des codifications

#### **Actions Correctives**

- **Export spécialisé** pour équipes SAP
- **Listes détaillées** avec priorisations
- **Suivi progression** codifications
- **Rapports automatiques** périodiques

### **4. Rapports Automatisés**

#### **Rapports Prédéfinis**

- **Rapport Mensuel** : Synthèse activités
- **Rapport Qualité** : Anomalies et améliorations
- **Rapport SAP** : Suivi codifications
- **Rapport Stock** : Analyse nomenclatures

#### **Personnalisation Rapports**

1. **Sélection métriques** : Choisir indicateurs
2. **Périodicité** : Quotidien, hebdomadaire, mensuel
3. **Destinataires** : Listes email automatiques
4. **Format** : PDF, Excel, email HTML

---

## 👥 **Administration**

### **1. Gestion des Utilisateurs**

#### **Création d'Utilisateur**

1. Accéder à **"Utilisateurs"**
2. Cliquer **"+ Nouvel Utilisateur"**
3. Remplir les informations :
   ```
   Nom d'utilisateur : Identifiant unique
   Mot de passe      : Complexité requise
   Nom complet       : Prénom + Nom
   Email             : Pour notifications
   Groupe            : Droits d'accès
   Statut            : Actif/Inactif
   ```

#### **Gestion des Groupes**

**Groupes Prédéfinis :**

- **Administrateur** : Tous droits
- **Gestionnaire** : Lecture/écriture données
- **Utilisateur** : Lecture + exports
- **Consultant** : Lecture seule

**Droits Granulaires :**

```
Ressources          | Lecture | Écriture | Suppression | Export
--------------------|---------|----------|-------------|--------
Équipements         |    ✓    |    ✓     |      ✓      |   ✓
Articles            |    ✓    |    ✓     |      ✓      |   ✓
Nomenclatures       |    ✓    |    ✓     |      ✓      |   ✓
Utilisateurs        |    ✓    |    ✓     |      ✓      |   ✗
Rapports            |    ✓    |    ✗     |      ✗      |   ✓
```

### **2. Configuration Système**

#### **Paramètres Généraux**

- **Nom application** : Personnalisation titre
- **Logo entreprise** : Image header
- **Couleurs thème** : Charte graphique
- **Langue interface** : Français/Anglais

#### **Paramètres Techniques**

- **Taille maximale upload** : Limite fichiers
- **Timeout import** : Durée maximale traitement
- **Cache durée** : Performances
- **Logs niveau** : Debugging

### **3. Maintenance Système**

#### **Vérifications Santé**

- **Connexion base** : Test connectivité
- **Espace disque** : Surveillance stockage
- **Performance** : Temps de réponse
- **Intégrité données** : Cohérence base

#### **Nettoyage Automatique**

- **Fichiers temporaires** : Suppression auto
- **Logs anciens** : Archivage rotatif
- **Sessions expirées** : Nettoyage
- **Cache obsolète** : Rafraîchissement

---

## 💡 **Conseils et Bonnes Pratiques**

### **1. Optimisation Performance**

#### **Navigation Efficace**

- **Utiliser filtres** plutôt que scroll infini
- **Favoris recherches** fréquentes
- **Raccourcis clavier** pour actions courantes
- **Onglets multiples** pour comparaisons

#### **Gestion des Imports**

- **Fichiers préparés** : Validation préalable
- **Imports par lots** : Éviter surcharge
- **Heures creuses** : Planifier gros imports
- **Backup avant** : Sauvegarde sécurité

### **2. Qualité des Données**

#### **Saisie Cohérente**

- **Nomenclature standardisée** : Conventions de nommage
- **Codes unifiés** : Éviter doublons
- **Désignations complètes** : Information suffisante
- **Classifications correctes** : Respect taxonomie

#### **Validation Régulière**

- **Contrôles périodiques** : Cohérence données
- **Résolution doublons** : Traitement immédiat
- **Mise à jour sources** : Synchronisation SAP
- **Archivage obsolète** : Nettoyage régulier

### **3. Collaboration Équipe**

#### **Workflow Recommandé**

1. **Import initial** par administrateur
2. **Validation métier** par experts domaine
3. **Résolution conflits** collaborative
4. **Exports certifiés** pour production

#### **Communication**

- **Commentaires** sur modifications importantes
- **Notifications** changements critiques
- **Formations** régulières utilisateurs
- **Documentation** procédures spécifiques

---

## ❓ **FAQ et Résolution de Problèmes**

### **1. Problèmes Fréquents**

#### **"Échec de l'import Excel"**

**Causes possibles :**

- Format fichier non supporté (.xls au lieu .xlsx)
- Colonnes manquantes ou mal nommées
- Données corrompues ou caractères spéciaux
- Fichier trop volumineux

**Solutions :**

1. Vérifier format Excel (.xlsx uniquement)
2. Contrôler noms colonnes exactes
3. Nettoyer données avec caractères standard
4. Diviser gros fichiers en plusieurs parties

#### **"Page lente à charger"**

**Causes possibles :**

- Trop de données affichées simultanément
- Filtres non optimisés
- Connexion réseau lente
- Cache navigateur plein

**Solutions :**

1. Utiliser filtres pour réduire dataset
2. Vider cache navigateur (Ctrl+F5)
3. Fermer onglets inutiles
4. Contacter administrateur si persistant

#### **"Doublons non détectés"**

**Causes possibles :**

- Variations dans les codes (espaces, casse)
- Sources différentes non comparées
- Cache non rafraîchi

**Solutions :**

1. Normaliser les codes (suppression espaces)
2. Forcer détection manuelle
3. Rafraîchir page (F5)
4. Contacter support technique

### **2. Messages d'Erreur**

#### **"Accès refusé"**

- **Cause** : Droits insuffisants
- **Solution** : Contacter administrateur pour droits

#### **"Session expirée"**

- **Cause** : Inactivité prolongée
- **Solution** : Se reconnecter

#### **"Fichier corrompu"**

- **Cause** : Fichier Excel endommagé
- **Solution** : Récréer fichier proprement

### **3. Assistance**

#### **Support Niveaux**

1. **Documentation** : Guides et manuels
2. **FAQ interne** : Problèmes courants
3. **Support utilisateur** : Aide technique
4. **Support expert** : Problèmes complexes

#### **Informations Utiles**

Lors d'une demande de support, fournir :

- **Description précise** du problème
- **Étapes reproduction** si possible
- **Messages d'erreur** exacts
- **Navigateur/Version** utilisés
- **Captures d'écran** si pertinentes

---

## 📞 **Contacts et Ressources**

### **Support Technique**

- 📧 **Email** : support-equipnomtech@entreprise.com
- 📞 **Téléphone** : +33 1 23 45 67 89
- 💬 **Chat** : Disponible pendant heures ouvrées
- 🆘 **Urgence** : Hotline 24/7 pour incidents critiques

### **Formation**

- 📖 **Guides** : Documentation complète disponible
- 🎥 **Vidéos** : Tutoriels sur plateforme formation
- 👨‍🏫 **Sessions** : Formations organisées sur demande
- 📋 **Certification** : Programme certification utilisateurs

### **Communauté**

- 💬 **Forum** : Échanges entre utilisateurs
- 📝 **Wiki** : Base connaissance collaborative
- 🔄 **Retours** : Suggestions améliorations
- 📢 **Annonces** : Nouveautés et mises à jour

---

_Guide utilisateur rédigé le 25 juillet 2025_  
_Version EquiNomTech 2.1_  
_© 2025 - Système de Gestion de Nomenclature d'Équipements_
