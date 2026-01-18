# Guide Détaillé de la Base de Données - Gestion Menuiserie

## 📋 Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture générale](#architecture-générale)
3. [Dictionnaire des tables](#dictionnaire-des-tables)
4. [Relations entre tables](#relations-entre-tables)
5. [Flux métier](#flux-métier)
6. [Considérations de performance](#considérations-de-performance)

---

## 🎯 Vue d'Ensemble

Cette base de données gère un système complet de **gestion de menuiserie** incluant :
- **Gestion des clients** (particuliers et professionnels)
- **Gestion des devis** avec lignes détaillées
- **Gestion des commandes** liées aux devis
- **Gestion des factures** pour le suivi des paiements
- **Gestion du stock** d'articles avec mouvements
- **Gestion des dépenses** professionnelles
- **Système d'authentification** et de permissions

**Moteur** : MySQL/MariaDB avec charset UTF-8 Unicode

---

## 🏗️ Architecture Générale

### Couches Fonctionnelles

```
┌─────────────────────────────────────┐
│     GESTION CLIENTS                 │
│  (clients, type_client, statut)     │
└──────────────┬──────────────────────┘
               │
     ┌─────────┴──────────┐
     │                    │
┌────▼─────────────┐  ┌───▼──────────────────┐
│   DEVIS          │  │  COMMANDES           │
│ (lignes_devis)   │  │ (articles_commande)  │
└────┬─────────────┘  └───┬──────────────────┘
     │                    │
     └─────────┬──────────┘
               │
         ┌─────▼──────────┐
         │   FACTURES     │
         │(articles_facture)
         └────────────────┘

┌─────────────────────────────────────┐
│     GESTION STOCK                   │
│  (articles, mouvements)             │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│     GESTION FINANCIÈRE              │
│  (dépenses, paiements)              │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  AUTHENTIFICATION & PERMISSIONS      │
│  (users, roles, permissions)        │
└─────────────────────────────────────┘
```

---

## 📊 Dictionnaire des Tables

### 1. **USERS** - Utilisateurs du Système
**Description** : Gère les comptes utilisateurs pour l'authentification

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `name` | varchar(255) | NOT NULL | Nom complet de l'utilisateur |
| `email` | varchar(255) | NOT NULL, UNIQUE | Email unique pour authentification |
| `email_verified_at` | timestamp | NULL | Date de vérification email |
| `password` | varchar(255) | NOT NULL | Mot de passe hashé |
| `remember_token` | varchar(100) | NULL | Token pour "se souvenir de moi" |
| `created_at` | timestamp | NULL | Date de création du compte |
| `updated_at` | timestamp | NULL | Date de dernière modification |

**Cas d'usage** :
- Authentification système
- Gestion des droits d'accès
- Traçabilité des modifications

---

### 2. **CLIENTS** - Gestion des Clients
**Description** : Stocke les informations complètes des clients (particuliers et professionnels)

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique du client |
| `nom` | varchar(255) | NOT NULL | Nom du client |
| `prenom` | varchar(255) | NOT NULL | Prénom du client |
| `telephone` | varchar(255) | NOT NULL | Numéro de téléphone |
| `email` | varchar(255) | NULL | Adresse email (optionnelle) |
| `adresse` | text | NOT NULL | Adresse complète (rue, n°) |
| `ville` | varchar(255) | NOT NULL | Ville |
| `code_postal` | varchar(255) | NULL | Code postal |
| `type_client` | enum('Particulier','Professionnel') | NOT NULL, DEFAULT 'Particulier' | Classification du client |
| `date_inscription` | date | NOT NULL | Date d'enregistrement |
| `nombre_commandes` | int(11) | NOT NULL, DEFAULT 0 | Nombre total de commandes |
| `total_achats` | decimal(15,2) | NOT NULL, DEFAULT 0.00 | Montant total des achats |
| `derniere_commande` | date | NULL | Date de la dernière commande |
| `statut` | enum('Actif','Inactif','VIP') | NOT NULL, DEFAULT 'Actif' | Statut du client |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |
| `deleted_at` | timestamp | NULL | Soft delete (suppression logique) |

**Indices** : Aucun indice spécifique (optimisation possible)

**Cas d'usage** :
- Afficher les données du client lors d'une commande
- Filtrer par statut (Actif/Inactif/VIP)
- Calculer le montant total des achats pour fidélisation
- Historique des clients

---

### 3. **ARTICLES** - Gestion du Stock
**Description** : Catalogue des articles/matériaux disponibles pour la menuiserie

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `nom` | varchar(255) | NOT NULL | Nom de l'article |
| `reference` | varchar(255) | NOT NULL, UNIQUE | Code de référence unique |
| `categorie` | varchar(255) | NOT NULL | Catégorie (fenêtres, portes, etc.) |
| `quantite` | int(11) | NOT NULL, DEFAULT 0 | Quantité actuelle en stock |
| `unite` | varchar(255) | NOT NULL | Unité de mesure (m, m², kg, pcs, etc.) |
| `seuil_alerte` | int(11) | NOT NULL, DEFAULT 10 | Quantité minimale avant alerte |
| `prix_achat` | decimal(15,2) | NULL | Prix unitaire d'achat |
| `fournisseur` | varchar(255) | NULL | Nom du fournisseur |
| `emplacement` | varchar(255) | NULL | Localisation physique en magasin |
| `derniere_entree` | date | NULL | Date de la dernière entrée stock |
| `derniere_sortie` | date | NULL | Date de la dernière sortie stock |
| `deleted_at` | timestamp | NULL | Soft delete |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

**Indices** : Clé unique sur `reference`

**Cas d'usage** :
- Consulter la disponibilité d'articles
- Générer des alertes de stock bas
- Tracer les mouvements d'entrée/sortie
- Coût des matériaux pour devis

---

### 4. **MOUVEMENTS** & **MOUVEMENTS_STOCK** - Historique des Stocks
**Description** : Traçabilité complète des entrées/sorties de stock

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `article_id` | bigint(20) | FK → articles | Article concerné |
| `type` | enum('entree','sortie') | NOT NULL | Type de mouvement |
| `quantite` | int(11) | NOT NULL | Quantité mouvementée |
| `quantite_avant` | int(11) | NOT NULL | Stock avant le mouvement |
| `quantite_apres` | int(11) | NOT NULL | Stock après le mouvement |
| `motif` | varchar(255) | NULL | Raison du mouvement |
| `commentaire` | text | NULL | Commentaire additionnel |
| `date_mouvement` | date | NOT NULL | Date du mouvement |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

**Indices** : FK sur `article_id`

⚠️ **Note** : Deux tables identiques `mouvements` et `mouvements_stock` - **À fusionner pour éviter la duplication**

**Cas d'usage** :
- Audit complet des mouvements de stock
- Vérification des incohérences
- Rapports d'entrée/sortie par période
- Traçabilité des stocks négatifs

---

### 5. **DEVIS** - Devis Clients
**Description** : Gestion des devis émis aux clients

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `client_id` | bigint(20) | FK → clients | Client associé |
| `date_emission` | date | NOT NULL | Date de création du devis |
| `validite` | int(11) | NOT NULL, DEFAULT 30 | Durée de validité (jours) |
| `date_validite` | date | NOT NULL | Date d'expiration du devis |
| `remise` | decimal(8,2) | NOT NULL, DEFAULT 0.00 | Pourcentage de remise |
| `acompte` | decimal(8,2) | NOT NULL, DEFAULT 0.00 | Pourcentage d'acompte demandé |
| `delai_livraison` | varchar(255) | NULL | Délai estimé (ex: "14 jours") |
| `conditions_paiement` | varchar(255) | NULL | Conditions (ex: "30 jours") |
| `sous_total` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Total HT avant remise |
| `montant_remise` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Montant de remise appliqué |
| `total_ht` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Total HT après remise |
| `total_ttc` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Total TTC (HT + TVA) |
| `montant_acompte` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Montant d'acompte demandé |
| `notes` | text | NULL | Notes additionnelles |
| `statut` | enum('brouillon','envoye','accepte','refuse','expire') | NOT NULL, DEFAULT 'brouillon' | État du devis |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |
| `deleted_at` | timestamp | NULL | Soft delete |

**Indices** : FK sur `client_id`

**Statuts possibles** :
- `brouillon` : En cours de création
- `envoye` : Envoyé au client
- `accepte` : Client a accepté
- `refuse` : Client a refusé
- `expire` : Dépassé sa date de validité

**Cas d'usage** :
- Créer des propositions commerciales
- Suivre l'état des devis
- Convertir en commande si accepté
- Calculer automatiquement TVA et remises

---

### 6. **LIGNES_DEVIS** - Détails des Devis
**Description** : Chaque ligne correspond à un produit/service dans un devis

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `devis_id` | bigint(20) | FK → devis | Devis parent |
| `produit` | varchar(255) | NOT NULL | Nom du produit |
| `categorie` | varchar(255) | NULL | Catégorie (fenêtre, porte, etc.) |
| `description` | text | NULL | Description détaillée |
| `largeur` | decimal(8,2) | NULL | Largeur du produit (cm ou m) |
| `hauteur` | decimal(8,2) | NULL | Hauteur du produit (cm ou m) |
| `quantite` | int(11) | NOT NULL, DEFAULT 1 | Quantité |
| `aluminium` | varchar(255) | NULL | Type d'aluminium (couleur, profil) |
| `vitrage` | varchar(255) | NULL | Type de vitrage (simple, double, teinté) |
| `prix_unitaire` | decimal(12,2) | NOT NULL | Prix unitaire HT |
| `sous_total` | decimal(12,2) | NOT NULL | Quantité × Prix unitaire |
| `ordre` | int(11) | NOT NULL, DEFAULT 0 | Ordre d'affichage dans le devis |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

**Indices** : FK sur `devis_id`, permet ON DELETE CASCADE

**Cas d'usage** :
- Détail des articles proposés dans le devis
- Affichage du devis au client
- Calcul du sous-total du devis
- Conversion en commande avec les mêmes lignes

---

### 7. **COMMANDES** - Gestion des Commandes
**Description** : Commandes fermes du client (généralement basées sur un devis accepté)

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `numero_commande` | varchar(255) | NOT NULL, UNIQUE | Numéro de commande unique |
| `client_id` | bigint(20) | FK → clients | Client |
| `devis_id` | bigint(20) | FK → devis (NULL OK) | Devis d'origine (optionnel) |
| `date_commande` | date | NOT NULL | Date de passation |
| `date_livraison` | date | NULL | Date de livraison prévue |
| `statut` | enum('En attente','En production','Prête','Livrée','Annulée') | NOT NULL, DEFAULT 'En attente' | État de la commande |
| `montant_ht` | decimal(12,2) | NOT NULL | Montant HT total |
| `montant_ttc` | decimal(12,2) | NOT NULL | Montant TTC total |
| `notes` | text | NULL | Remarques spéciales |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |
| `deleted_at` | timestamp | NULL | Soft delete |

**Indices** : FK sur `client_id` et `devis_id`, clé unique sur `numero_commande`

**Statuts possibles** :
- `En attente` : Reçue, en attente de confirmation
- `En production` : Commande en cours de fabrication
- `Prête` : Finalisée, en attente d'enlèvement/livraison
- `Livrée` : Client a reçu la commande
- `Annulée` : Commande annulée

**Cas d'usage** :
- Suivi de la production
- Génération de bon de commande
- Lien avec facturation
- Mise à jour du nombre de commandes du client

---

### 8. **ARTICLES_COMMANDE** - Détails des Commandes
**Description** : Chaque ligne représente un article dans la commande

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `commande_id` | bigint(20) | FK → commandes | Commande parent |
| `produit` | varchar(255) | NOT NULL | Nom du produit |
| `quantite` | int(11) | NOT NULL | Quantité commandée |
| `dimensions` | varchar(255) | NULL | Dimensions (ex: "100x200 cm") |
| `prix` | decimal(12,2) | NOT NULL | Prix unitaire HT |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

**Indices** : FK sur `commande_id`

**Cas d'usage** :
- Détails de chaque article commandé
- Bon de production/livraison
- Suivi des stocks à réserver

---

### 9. **FACTURES** - Gestion des Factures
**Description** : Factures émises aux clients pour suivi des paiements

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `numero_facture` | varchar(255) | NOT NULL, UNIQUE | Numéro de facture unique |
| `commande_id` | bigint(20) | FK → commandes | Commande associée |
| `client_id` | bigint(20) | FK → clients | Client facturé |
| `date_emission` | date | NOT NULL | Date de facturation |
| `date_echeance` | date | NOT NULL | Date limite de paiement |
| `montant_ht` | decimal(12,2) | NOT NULL | Total HT |
| `tva` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Montant TVA |
| `montant_ttc` | decimal(12,2) | NOT NULL | Total TTC |
| `montant_paye` | decimal(12,2) | NOT NULL, DEFAULT 0.00 | Montant déjà payé |
| `statut` | enum('Non payée','Payée','En attente','En retard') | NOT NULL, DEFAULT 'Non payée' | État du paiement |
| `mode_paiement` | varchar(255) | NULL | Mode (chèque, virement, etc.) |
| `date_paiement` | date | NULL | Date du paiement |
| `notes` | text | NULL | Remarques |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |
| `deleted_at` | timestamp | NULL | Soft delete |

**Indices** : FK sur `commande_id` et `client_id`, clé unique sur `numero_facture`

**Statuts possibles** :
- `Non payée` : En attente de paiement
- `Payée` : Client a payé intégralement
- `En attente` : Paiement partiel reçu
- `En retard` : Dépassée la date d'échéance

**Cas d'usage** :
- Suivi des paiements clients
- Rappels de factures en retard
- Rapports de trésorerie
- Réconciliation comptable

---

### 10. **ARTICLES_FACTURE** - Détails des Factures
**Description** : Détail de chaque article facturé

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `facture_id` | bigint(20) | FK → factures | Facture parent |
| `designation` | varchar(255) | NOT NULL | Désignation du service/produit |
| `quantite` | int(11) | NOT NULL | Quantité facturée |
| `prix_unitaire` | decimal(12,2) | NOT NULL | Prix unitaire HT |
| `total` | decimal(12,2) | NOT NULL | Montant HT (quantité × prix unitaire) |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

**Indices** : FK sur `facture_id`

---

### 11. **DEPENSES** - Gestion des Dépenses
**Description** : Suivi des dépenses professionnelles pour comptabilité

| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `categorie` | enum('Achat matériaux','Transport','Électricité','Maintenance','Autre') | NOT NULL | Type de dépense |
| `description` | varchar(255) | NOT NULL | Détails de la dépense |
| `montant` | decimal(15,2) | NOT NULL | Montant TTC |
| `date` | date | NOT NULL | Date de la dépense |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |
| `deleted_at` | timestamp | NULL | Soft delete |

**Cas d'usage** :
- Tableau de bord financier
- Rapports d'exploitation
- Calculs de coûts par catégorie
- Analyse de rentabilité

---

### 12. **ROLES** & **PERMISSIONS** - Gestion d'Accès
**Description** : Système RBAC (Role-Based Access Control) utilisant Spatie

#### ROLES
| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `name` | varchar(255) | NOT NULL, UNIQUE (with guard) | Nom du rôle |
| `guard_name` | varchar(255) | NOT NULL | Guard (web, api) |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

#### PERMISSIONS
| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `id` | bigint(20) | PK, AUTO_INCREMENT | Identifiant unique |
| `name` | varchar(255) | NOT NULL, UNIQUE (with guard) | Nom de la permission |
| `guard_name` | varchar(255) | NOT NULL | Guard (web, api) |
| `created_at` | timestamp | NULL | Date de création |
| `updated_at` | timestamp | NULL | Date de modification |

**Exemples de rôles** :
- `admin` - Accès total
- `responsable_commercial` - Gestion devis/commandes
- `responsable_production` - Suivi production
- `responsable_finances` - Gestion factures/paiements
- `responsable_stock` - Gestion du stock

**Exemples de permissions** :
- `create_devis`, `edit_devis`, `delete_devis`, `view_devis`
- `create_commande`, `edit_commande`, `view_commandes`
- `manage_users`, `manage_permissions`, `view_reports`

---

### 13. **MODEL_HAS_PERMISSIONS** - Assignation de Permissions
| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `permission_id` | bigint(20) | FK → permissions | Permission |
| `model_id` | bigint(20) | | ID de l'utilisateur/modèle |
| `model_type` | varchar(255) | | Type de modèle (User) |

Permet d'assigner des permissions directement à un utilisateur.

---

### 14. **ROLE_HAS_PERMISSIONS** - Assignation Rôle-Permission
| Colonne | Type | Constraint | Description |
|---------|------|-----------|-------------|
| `permission_id` | bigint(20) | FK → permissions | Permission |
| `role_id` | bigint(20) | FK → roles | Rôle |

Lie les permissions à un rôle.

---

### 15. Tables d'Infrastructure

#### **PERSONAL_ACCESS_TOKENS** - API Authentication
Gère les tokens Sanctum pour l'authentification API
- Tokens avec durée d'expiration
- Suivi du dernier utilisation
- Capacités/permissions associées

#### **SESSIONS** - Gestion des Sessions
Stocke les sessions utilisateur web
- IP address pour sécurité
- User agent (navigateur)
- Payload des données de session

#### **PASSWORD_RESET_TOKENS** & **PASSWORD_RESET_CODES**
- Tokens classiques pour réinitialisation password
- Codes (6 chiffres) avec expiration

#### **MIGRATIONS**
Suivi des migrations de schéma appliquées

---

## 🔗 Relations entre Tables

### Schéma Entité-Relation (ERD)

```
┌─────────┐
│ USERS   │
└────┬────┘
     │ (Owns)
     │
┌────▼──────────┐
│ CLIENTS        │ ────── [type_client, statut]
└────┬──────────┘
     │
     ├─→ [1:N] DEVIS
     │         └─→ [1:N] LIGNES_DEVIS (avec dimensions, matériaux)
     │
     ├─→ [1:N] COMMANDES
     │         └─→ [1:N] ARTICLES_COMMANDE
     │
     └─→ [1:N] FACTURES
             └─→ [1:N] ARTICLES_FACTURE

┌──────────┐
│ ARTICLES │ (Catalogue stock)
└────┬─────┘
     │
     └─→ [1:N] MOUVEMENTS / MOUVEMENTS_STOCK
             (Traçabilité entrée/sortie)

┌─────────────┐
│ DEPENSES    │ (Financier)
└─────────────┘

┌─────────────────────────────────┐
│ ROLES ←──── ROLE_HAS_PERMISSIONS │
└─────────────────────────────────┘
         ↑
         │
    PERMISSIONS
         ↑
         │
┌─────────────────────────────────────┐
│ USERS ←──── MODEL_HAS_PERMISSIONS   │
└─────────────────────────────────────┘
```

### Cascade et Contraintes

| Relation | Comportement | Raison |
|----------|-------------|--------|
| Client → Devis | ON DELETE CASCADE | Un client supprimé = devis supprimés |
| Client → Commandes | ON DELETE CASCADE | Un client supprimé = commandes supprimées |
| Client → Factures | ON DELETE CASCADE | Un client supprimé = factures supprimées |
| Commande → Articles_Commande | ON DELETE CASCADE | Suppression commande = lignes supprimées |
| Devis → Lignes_Devis | ON DELETE CASCADE | Suppression devis = lignes supprimées |
| Devis → Commande | ON DELETE SET NULL | Devis supprimé mais commande conservée |
| Facture → Articles_Facture | ON DELETE CASCADE | Suppression facture = lignes supprimées |
| Article → Mouvements | ON DELETE CASCADE | Article supprimé = mouvements supprimés |

---

## 📈 Flux Métier

### 1️⃣ Cycle Vente

```
CLIENT POTENTIEL
      ↓
[1. CRÉER CLIENT]
      ↓
[2. CRÉER DEVIS]
   ├─ Ajout de lignes_devis (produits avec dimensions)
   ├─ Calcul automatique (TVA, remise, acompte)
   └─ Statut: "brouillon"
      ↓
[3. ENVOYER DEVIS]
   └─ Statut: "envoye"
      ↓
      ├─→ ACCEPTÉ? ──→ [4A. CONVERTIR EN COMMANDE]
      │                  └─ Copier lignes devis → articles_commande
      │                  └─ Créer COMMANDE (statut: "En attente")
      │                  └─ Devis statut: "accepte"
      │
      └─→ REFUSÉ? ──→ [4B. MARQUER COMME REFUSÉ]
                      └─ Devis statut: "refuse"

[5. GESTION PRODUCTION]
   └─ Commande: "En attente" → "En production" → "Prête"

[6. CRÉER FACTURE]
   ├─ Depuis commande
   ├─ Copier articles_commande → articles_facture
   ├─ Statut: "Non payée"
   └─ Date d'échéance: date_emission + délai paiement

[7. SUIVI PAIEMENT]
   ├─ Réception paiement partiel → Statut: "En attente"
   ├─ Réception paiement complet → Statut: "Payée"
   └─ Retard → Statut: "En retard" (relance client)

[8. LIVRAISON]
   └─ Commande statut: "Livrée"
   └─ Mettre à jour: clients.nombre_commandes, clients.total_achats
```

### 2️⃣ Cycle Stock

```
[ACHETER MATÉRIAUX]
      ↓
[CRÉER ARTICLE dans articles]
   ├─ nom, reference (unique)
   ├─ categorie, unite
   ├─ seuil_alerte
   └─ prix_achat, fournisseur
      ↓
[RÉCEPTION STOCK]
   ├─ Augmenter articles.quantite
   ├─ Créer MOUVEMENT (type: "entree")
   ├─ Enregistrer quantite_avant, quantite_apres
   └─ derniere_entree = aujourd'hui
      ↓
[RÉSERVATION/UTILISATION]
   ├─ Diminuer articles.quantite
   ├─ Créer MOUVEMENT (type: "sortie")
   ├─ Motif: "Commande #123"
   └─ derniere_sortie = aujourd'hui
      ↓
[VÉRIFIER SEUIL]
   └─ Si quantite < seuil_alerte → ALERTE de réapprovisionnement

[SUPPRESSION ARTICLE]
   └─ Soft delete (deleted_at != NULL)
   └─ Les mouvements restent pour audit
```

### 3️⃣ Cycle Financier

```
[DÉPENSES RÉELLES]
   ├─ Achat matériaux
   ├─ Transport
   ├─ Électricité
   └─ Enregistrer dans DEPENSES
      ↓
[ANALYSE RENTABILITÉ]
   ├─ Sommes par catégorie
   ├─ Total dépenses vs Chiffre d'affaires
   └─ Marge brute = CA - Dépenses matériaux - Transport
      ↓
[RAPPORTS FINANCIERS]
   └─ Dashboard avec KPIs
```

---

## 🚀 Considérations de Performance

### Index Recommandés

#### ✅ Déjà présents
```sql
-- Clés uniques
ALTER TABLE articles ADD UNIQUE(reference);
ALTER TABLE commandes ADD UNIQUE(numero_commande);
ALTER TABLE factures ADD UNIQUE(numero_facture);
ALTER TABLE users ADD UNIQUE(email);
ALTER TABLE roles ADD UNIQUE(name, guard_name);
ALTER TABLE permissions ADD UNIQUE(name, guard_name);
```

#### ⚠️ À ajouter pour optimiser les recherches

```sql
-- Clients
ALTER TABLE clients ADD INDEX idx_statut (statut);
ALTER TABLE clients ADD INDEX idx_type_client (type_client);
ALTER TABLE clients ADD INDEX idx_email (email);

-- Devis
ALTER TABLE devis ADD INDEX idx_client_id (client_id);
ALTER TABLE devis ADD INDEX idx_statut (statut);
ALTER TABLE devis ADD INDEX idx_date_emission (date_emission);

-- Commandes
ALTER TABLE commandes ADD INDEX idx_client_id (client_id);
ALTER TABLE commandes ADD INDEX idx_devis_id (devis_id);
ALTER TABLE commandes ADD INDEX idx_statut (statut);
ALTER TABLE commandes ADD INDEX idx_date_commande (date_commande);

-- Factures
ALTER TABLE factures ADD INDEX idx_client_id (client_id);
ALTER TABLE factures ADD INDEX idx_commande_id (commande_id);
ALTER TABLE factures ADD INDEX idx_statut (statut);
ALTER TABLE factures ADD INDEX idx_date_emission (date_emission);

-- Mouvements
ALTER TABLE mouvements ADD INDEX idx_article_id (article_id);
ALTER TABLE mouvements ADD INDEX idx_type (type);
ALTER TABLE mouvements ADD INDEX idx_date (date_mouvement);

-- Stock
ALTER TABLE articles ADD INDEX idx_reference (reference);
ALTER TABLE articles ADD INDEX idx_categorie (categorie);
```

### Problèmes Identifiés

#### 🔴 Haute Priorité

1. **Tables MOUVEMENTS et MOUVEMENTS_STOCK dupliquées**
   - Fusionner en une seule table
   - Problème : Maintenance difficile, données potentiellement incohérentes

2. **Manque d'INDEX sur clés étrangères**
   - Les FK doivent toujours être indexées
   - Impact: Lenteur sur recherches et JOINs

3. **Pas d'INDEX sur les colonnes de date**
   - Recherches par plage de dates inefficaces
   - Impact: Lenteur des rapports mensuels/annuels

#### 🟡 Priorité Moyenne

4. **Calculs dans DEVIS pas atomiques**
   - sous_total, montant_remise, total_ht, total_ttc calculés manuellement
   - Risque: Incohérence si le calcul est mal fait
   - Solution: Utiliser des triggers ou calculer côté application

5. **Pas de versioning des devis/commandes**
   - Impossible de voir l'historique des modifications
   - Solution: Implémenter l'audit avec nova-audit ou similar

6. **Champ `statut` en plusieurs tables**
   - Pas de table de référence ENUM
   - Risque: Typos ou valeurs mal synchronisées

---

## 📋 Exemples de Requêtes Courantes

### Afficher les 5 clients les plus importants
```sql
SELECT id, nom, prenom, total_achats, nombre_commandes
FROM clients
WHERE statut = 'Actif'
ORDER BY total_achats DESC
LIMIT 5;
```

### Devis en attente d'expiration
```sql
SELECT id, numero_devis, client_id, date_validite
FROM devis
WHERE statut = 'envoye' 
  AND date_validite < CURDATE();
```

### Articles sous le seuil d'alerte
```sql
SELECT id, nom, reference, quantite, seuil_alerte
FROM articles
WHERE quantite < seuil_alerte
  AND deleted_at IS NULL;
```

### Factures impayées en retard
```sql
SELECT f.id, f.numero_facture, f.montant_ttc, f.date_echeance, c.email
FROM factures f
JOIN clients c ON f.client_id = c.id
WHERE f.statut IN ('Non payée', 'En retard')
  AND f.date_echeance < CURDATE()
  AND f.deleted_at IS NULL;
```

### Revenu mensuel total
```sql
SELECT 
  DATE_FORMAT(f.date_emission, '%Y-%m') as mois,
  SUM(f.montant_ttc) as revenu_ttc,
  SUM(f.montant_ttc - f.tva) as revenu_ht
FROM factures f
WHERE f.statut = 'Payée'
GROUP BY DATE_FORMAT(f.date_emission, '%Y-%m')
ORDER BY mois DESC;
```

### Historique stock d'un article
```sql
SELECT 
  date_mouvement, type, quantite, 
  quantite_avant, quantite_apres, motif
FROM mouvements
WHERE article_id = ? 
ORDER BY date_mouvement DESC;
```

---

## 🔐 Sécurité et Bonnes Pratiques

1. **Soft Deletes** : Utilisés pour articles, clients, commandes, factures, devis, dépenses
   - Toujours filtrer par `deleted_at IS NULL` en lecture
   - Permet la traçabilité

2. **Timestamps Automatiques** : `created_at`, `updated_at` sur toutes les tables
   - Audit de qui a créé/modifié

3. **Intégrité Référentielle** : FK avec CASCADE / SET NULL
   - Garantit la cohérence des données

4. **Unicité** : Nombres de documents (commande, facture, devis)
   - Évite les doublons

5. **Énumérés** : Pour statuts et catégories
   - Limite les valeurs invalides

---

## 📞 Questions Fréquentes

**Q: Comment créer une commande depuis un devis?**
A: Copier les `lignes_devis` vers `articles_commande`, créer la `commande` avec même montant, et mettre le devis en statut "accepte".

**Q: Comment gérer les paiements partiels?**
A: `montant_paye` dans factures peut être < `montant_ttc`. Statut = "En attente" jusqu'à paiement complet.

**Q: Peut-on créer une commande sans devis?**
A: Oui, `devis_id` est nullable dans commandes. On peut créer directement des articles_commande.

**Q: Comment savoir si un article est en stock?**
A: Vérifier `articles.quantite > 0` et `deleted_at IS NULL`.

**Q: Comment auditer les modifications?**
A: Consulter `mouvements` pour stock, et `updated_at` pour autres tables. Idéalement implémenter l'audit Laravel.

---

## 📚 Ressources

- **Framework** : Laravel 11
- **Authentification** : Sanctum (API) + Sessions web
- **Permissions** : Spatie Laravel Permissions
- **Base de Données** : MySQL 8.0+ / MariaDB 10.5+

---

**Dernière mise à jour** : 18 Janvier 2026
**Version du Schéma** : 1.0
**Auteur** : Équipe Développement
