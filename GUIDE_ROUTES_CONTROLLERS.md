# Guide Complet - Routes et Contrôleurs | Gestion Menuiserie

## 📋 Table des Matières
1. [Architecture générale](#architecture-générale)
2. [Authentification](#authentification)
3. [Endpoints par ressource](#endpoints-par-ressource)
4. [Contrôleurs détaillés](#contrôleurs-détaillés)
5. [Requêtes validées (Requests)](#requêtes-validées-requests)
6. [Ressources (Resources)](#ressources-resources)
7. [Flux de travail complets](#flux-de-travail-complets)
8. [Bonnes pratiques](#bonnes-pratiques)

---

## 🏗️ Architecture Générale

### Patterns Utilisés

```
API REST avec Laravel
├── Authentication (Sanctum)
├── Controllers (Logique métier)
├── Requests (Validation)
├── Resources (Transformation JSON)
├── Models (ORM Eloquent)
└── Services (Calculs complexes)
```

### Structure des Fichiers

```
app/Http/
├── Controllers/
│   ├── Controller.php (classe parente)
│   ├── Auth/
│   │   └── AuthController.php
│   ├── ClientController.php
│   ├── DevisController.php
│   ├── CommandeController.php
│   ├── FactureController.php
│   ├── ArticleController.php
│   ├── DepenseController.php
│   ├── MouvementController.php
│   └── DashboardController.php
├── Requests/
│   ├── Auth/
│   ├── StoreClientRequest.php
│   ├── UpdateClientRequest.php
│   ├── StoreDevisRequest.php
│   ├── UpdateDevisRequest.php
│   └── ...
└── Resources/
    ├── ClientResource.php
    ├── DevisResource.php
    ├── CommandeResource.php
    ├── FactureResource.php
    └── ...

routes/
├── api.php (API REST)
└── web.php (Frontend)
```

---

## 🔐 Authentification

### 1️⃣ AuthController - Gestion des Utilisateurs

**Fichier** : [app/Http/Controllers/Auth/AuthController.php](app/Http/Controllers/Auth/AuthController.php)

#### Endpoints Publics

| Méthode | Route | Description |
|---------|-------|-------------|
| `POST` | `/api/auth/login` | Authentification utilisateur |
| `POST` | `/api/auth/forgot-password` | Demander réinitialisation password |
| `POST` | `/api/auth/reset-password` | Réinitialiser password avec code |

#### Endpoints Protégés (auth:sanctum)

| Méthode | Route | Description |
|---------|-------|-------------|
| `POST` | `/api/auth/logout` | Déconnexion utilisateur |
| `GET` | `/api/auth/me` | Récupérer profil actuel |

### Exemple d'utilisation

**Login** :
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

**Réponse** :
```json
{
  "message": "Authentifié avec succès",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com"
  }
}
```

**Utiliser le token** :
```bash
curl -X GET http://localhost/api/clients \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

---

## 📊 Endpoints par Ressource

### 📍 CLIENTS

**URL Base** : `/api/clients`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/clients` | Index (liste paginée) | `ClientController@index` |
| `POST` | `/api/clients` | Créer client | `ClientController@store` |
| `GET` | `/api/clients/{id}` | Afficher client | `ClientController@show` |
| `PUT` | `/api/clients/{id}` | Mettre à jour client | `ClientController@update` |
| `DELETE` | `/api/clients/{id}` | Supprimer client | `ClientController@destroy` |
| `PATCH` | `/api/clients/{id}/statut` | Changer statut | `ClientController@updateStatut` |
| `GET` | `/api/clients/stats` | Statistiques clients | `ClientController@stats` |

#### Exemple : Créer un client
```bash
POST /api/clients
Content-Type: application/json
Authorization: Bearer {token}

{
  "nom": "Dupont",
  "prenom": "Jean",
  "telephone": "0612345678",
  "email": "jean@example.com",
  "adresse": "123 rue de la Paix",
  "ville": "Paris",
  "code_postal": "75000",
  "type_client": "Particulier",
  "date_inscription": "2026-01-18"
}
```

#### Exemple : Chercher des clients
```bash
GET /api/clients?search=Dupont&statut=Actif&type_client=Particulier&sort_by=created_at&sort_order=desc&per_page=20
```

#### Paramètres de recherche
| Paramètre | Type | Description |
|-----------|------|-------------|
| `search` | string | Recherche par nom/prénom/téléphone |
| `statut` | enum | Actif, Inactif, VIP |
| `type_client` | enum | Particulier, Professionnel |
| `sort_by` | string | Colonne pour tri (défaut: created_at) |
| `sort_order` | string | asc ou desc (défaut: desc) |
| `per_page` | int | Éléments par page (défaut: 15) |

---

### 💼 DEVIS

**URL Base** : `/api/devis`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/devis` | Index (liste paginée) | `DevisController@index` |
| `POST` | `/api/devis` | Créer devis | `DevisController@store` |
| `GET` | `/api/devis/{id}` | Afficher devis | `DevisController@show` |
| `PUT` | `/api/devis/{id}` | Mettre à jour devis | `DevisController@update` |
| `DELETE` | `/api/devis/{id}` | Supprimer devis | `DevisController@destroy` |
| `POST` | `/api/devis/{id}/valider` | Valider & créer commande | `DevisController@validerEtFacturer` |

#### Exemple : Créer un devis

```bash
POST /api/devis
Content-Type: application/json
Authorization: Bearer {token}

{
  "client_id": 1,
  "date_emission": "2026-01-18",
  "validite": 30,
  "date_validite": "2026-02-17",
  "remise": 10.00,
  "acompte": 30.00,
  "delai_livraison": "14 jours",
  "conditions_paiement": "30 jours",
  "notes": "Devis standard",
  "lignes": [
    {
      "produit": "Fenêtre PVC double vitrage",
      "categorie": "Fenêtres",
      "description": "Fenêtre PVC blanc 1000x1200",
      "largeur": 1.0,
      "hauteur": 1.2,
      "quantite": 2,
      "aluminium": "Profil standard",
      "vitrage": "Double vitrage 4-16-4",
      "prix_unitaire": 350.00
    }
  ]
}
```

#### Cycle de vie du devis

```
BROUILLON → ENVOYE → ACCEPTE → (Commande créée)
         ↘                    ↗ REFUSE
          → EXPIRE
```

**Statuts** :
- `brouillon` : En cours de création (modifiable)
- `envoye` : Envoyé au client (non modifiable)
- `accepte` : Client a accepté (conversion en commande possible)
- `refuse` : Client a refusé
- `expire` : Dépassé la date de validité

#### Valider un devis
```bash
POST /api/devis/1/valider
Authorization: Bearer {token}
```

**Réponse** :
```json
{
  "message": "Devis validé. Commande et facture créées.",
  "devis": { ... },
  "commande_id": 5,
  "facture_id": 10
}
```

---

### 📦 COMMANDES

**URL Base** : `/api/commandes`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/commandes` | Index (liste) | `CommandeController@index` |
| `POST` | `/api/commandes` | Créer commande | `CommandeController@store` |
| `GET` | `/api/commandes/{id}` | Afficher commande | `CommandeController@show` |
| `PUT` | `/api/commandes/{id}` | Mettre à jour | `CommandeController@update` |
| `DELETE` | `/api/commandes/{id}` | Supprimer | `CommandeController@destroy` |
| `POST` | `/api/commandes/{id}/statut` | Changer statut | `CommandeController@updateStatut` |
| `GET` | `/api/commandes/stats` | Statistiques | `CommandeController@stats` |

#### Exemple : Créer une commande directement

```bash
POST /api/commandes
Authorization: Bearer {token}

{
  "client_id": 1,
  "devis_id": 5,
  "date_commande": "2026-01-18",
  "date_livraison": "2026-02-01",
  "montant_ht": 1400.00,
  "montant_ttc": 1680.00,
  "notes": "Commande directe",
  "articles": [
    {
      "produit": "Fenêtre PVC",
      "quantite": 2,
      "dimensions": "1.0m x 1.2m",
      "prix": 700.00
    }
  ]
}
```

#### Statuts de commande

```
EN ATTENTE → EN PRODUCTION → PRETE → LIVREE
         ↘                          ↗
          → ANNULEE
```

| Statut | Signification |
|--------|---------------|
| `En attente` | Reçue, confirmée |
| `En production` | Fabrication en cours |
| `Prête` | Finalisée, prête à livrer |
| `Livrée` | Remise au client |
| `Annulée` | Commande annulée |

#### Changer le statut
```bash
POST /api/commandes/1/statut
Authorization: Bearer {token}

{
  "statut": "En production"
}
```

---

### 📄 FACTURES

**URL Base** : `/api/factures`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/factures` | Index (liste) | `FactureController@index` |
| `POST` | `/api/factures` | Créer facture | `FactureController@store` |
| `GET` | `/api/factures/{id}` | Afficher facture | `FactureController@show` |
| `PUT` | `/api/factures/{id}` | Mettre à jour | `FactureController@update` |
| `DELETE` | `/api/factures/{id}` | Supprimer | `FactureController@destroy` |
| `POST` | `/api/factures/{id}/payer` | Marquer comme payée | `FactureController@marquerPayee` |
| `GET` | `/api/factures/{id}/telecharger-pdf` | Télécharger PDF | `FactureController@telechargerPDF` |
| `GET` | `/api/factures/stats` | Statistiques | `FactureController@stats` |

#### Exemple : Créer une facture

```bash
POST /api/factures
Authorization: Bearer {token}

{
  "commande_id": 5,
  "client_id": 1,
  "date_emission": "2026-01-18",
  "date_echeance": "2026-02-17",
  "montant_ht": 1400.00,
  "tva": 280.00,
  "montant_ttc": 1680.00,
  "mode_paiement": "Virement",
  "notes": "Conditions 30 jours"
}
```

#### Statuts de facture

| Statut | Signification |
|--------|---------------|
| `Non payée` | En attente de paiement |
| `Payée` | Totalement payée |
| `En attente` | Paiement partiel reçu |
| `En retard` | Dépassée la date d'échéance |

#### Enregistrer un paiement
```bash
POST /api/factures/10/payer
Authorization: Bearer {token}

{
  "montant_paye": 500.00,
  "mode_paiement": "Chèque",
  "date_paiement": "2026-01-20"
}
```

---

### 📦 ARTICLES (Stock)

**URL Base** : `/api/articles`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/articles` | Index (liste) | `ArticleController@index` |
| `POST` | `/api/articles` | Créer article | `ArticleController@store` |
| `GET` | `/api/articles/{id}` | Afficher article | `ArticleController@show` |
| `PUT` | `/api/articles/{id}` | Mettre à jour | `ArticleController@update` |
| `DELETE` | `/api/articles/{id}` | Supprimer | `ArticleController@destroy` |
| `POST` | `/api/articles/{id}/ajuster-stock` | Ajuster stock | `ArticleController@ajusterStock` |
| `GET` | `/api/articles/alertes` | Articles en alerte | `ArticleController@alertes` |
| `GET` | `/api/articles/stats` | Statistiques stock | `ArticleController@stats` |

#### Exemple : Créer un article

```bash
POST /api/articles
Authorization: Bearer {token}

{
  "nom": "Fenêtre PVC 100x120",
  "reference": "FENETRE-PVC-100x120",
  "categorie": "Fenêtres",
  "quantite": 5,
  "unite": "pcs",
  "seuil_alerte": 2,
  "prix_achat": 350.00,
  "fournisseur": "ABC Menuiserie",
  "emplacement": "Rayonnage A1"
}
```

#### Ajuster le stock

```bash
POST /api/articles/1/ajuster-stock
Authorization: Bearer {token}

{
  "quantite": 10,
  "type": "entree",
  "motif": "Réapprovisionnement",
  "commentaire": "Livraison fournisseur"
}
```

---

### 📊 MOUVEMENTS (Historique Stock)

**URL Base** : `/api/mouvement`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/mouvement` | Index (liste) | `MouvementController@index` |
| `POST` | `/api/mouvement` | Créer mouvement | `MouvementController@store` |
| `GET` | `/api/mouvement/{id}` | Afficher mouvement | `MouvementController@show` |
| `PUT` | `/api/mouvement/{id}` | Mettre à jour | `MouvementController@update` |
| `GET` | `/api/articles/{id}/historique-mouvement` | Historique article | `MouvementController@historique` |
| `GET` | `/api/mouvement/stats` | Statistiques | `MouvementController@stats` |

#### Exemple : Créer un mouvement

```bash
POST /api/mouvement
Authorization: Bearer {token}

{
  "article_id": 1,
  "type": "sortie",
  "quantite": 2,
  "motif": "Commande #COM001",
  "commentaire": "Utilisation pour commande",
  "date_mouvement": "2026-01-18"
}
```

#### Historique d'un article

```bash
GET /api/articles/1/historique-mouvement?mois=01&annee=2026
```

---

### 💰 DÉPENSES

**URL Base** : `/api/depenses`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/depenses` | Index (liste) | `DepenseController@index` |
| `POST` | `/api/depenses` | Créer dépense | `DepenseController@store` |
| `GET` | `/api/depenses/{id}` | Afficher dépense | `DepenseController@show` |
| `PUT` | `/api/depenses/{id}` | Mettre à jour | `DepenseController@update` |
| `DELETE` | `/api/depenses/{id}` | Supprimer | `DepenseController@destroy` |
| `GET` | `/api/depenses/stats` | Statistiques | `DepenseController@stats` |

#### Exemple : Créer une dépense

```bash
POST /api/depenses
Authorization: Bearer {token}

{
  "categorie": "Achat matériaux",
  "description": "Achat de PVC pour fenêtres",
  "montant": 2500.00,
  "date": "2026-01-18"
}
```

#### Catégories de dépenses

- `Achat matériaux` - Achats de matières premières
- `Transport` - Frais de transport
- `Électricité` - Consommation électrique
- `Maintenance` - Entretien équipements
- `Autre` - Autres dépenses

---

### 📈 DASHBOARD

**URL Base** : `/api/dashboard`

| Méthode | Route | Action | Controller |
|---------|-------|--------|-----------|
| `GET` | `/api/dashboard` | Index général | `DashboardController@index` |
| `GET` | `/api/dashboard/chart-data` | Données pour graphiques | `DashboardController@chartData` |

#### Paramètres de période

```bash
GET /api/dashboard?periode=mois
```

| Période | Description |
|---------|-------------|
| `mois` | Mois en cours (défaut) |
| `semaine` | Semaine en cours |
| `trimestre` | Trimestre en cours |
| `annee` | Année en cours |

#### Réponse Dashboard

```json
{
  "stats": {
    "commandes": 15,
    "revenus": 25000.00,
    "clients_actifs": 8,
    "produits": 42
  },
  "details_commandes": {
    "total": 15,
    "en_attente": 3,
    "en_production": 5,
    "prete": 4,
    "livrees": 2,
    "annulees": 1
  },
  "commandes_recentes": [
    {
      "id": 5,
      "numero_commande": "COM001",
      "client": "Jean Dupont",
      "montant_ttc": 1680.00,
      "statut": "En production",
      "date_commande": "18/01/2026"
    }
  ],
  "top_articles": [
    {
      "nom": "Fenêtre PVC",
      "reference": "FENETRE-PVC",
      "quantite_sortie": 10
    }
  ],
  "alertes": {
    "stock_faible": 3,
    "stock_critique": 1,
    "devis_en_attente": 2,
    "factures_impayees": 4,
    "livraisons_du_jour": 1
  }
}
```

---

## 🔍 Contrôleurs Détaillés

### ClientController

**Localisation** : [app/Http/Controllers/ClientController.php](app/Http/Controllers/ClientController.php)

#### Méthodes

**index()** - Liste paginée des clients
```php
public function index(Request $request): AnonymousResourceCollection
```
- Supports : `search`, `statut`, `type_client`, `sort_by`, `sort_order`, `per_page`
- Retourne : Paginated ClientResource

**store()** - Créer un client
```php
public function store(StoreClientRequest $request): JsonResponse
```
- Validation via `StoreClientRequest`
- Crée le client avec les données validées
- Retourne : Message + ClientResource

**show()** - Afficher un client
```php
public function show(Client $client): JsonResponse
```
- Retourne : ClientResource d'un client unique

**update()** - Mettre à jour un client
```php
public function update(UpdateClientRequest $request, Client $client)
```
- Validation via `UpdateClientRequest`
- Met à jour les données du client
- Retourne : Message + ClientResource

**destroy()** - Supprimer un client (soft delete)
```php
public function destroy(Client $client): JsonResponse
```
- Soft delete (enregistrement conservé dans la DB)
- Retourne : Message de confirmation

**stats()** - Statistiques des clients
```php
public function stats(): JsonResponse
```
- Retourne :
  - `total_clients` - Nombre total
  - `clients_vip` - Clients VIP
  - `clients_actifs` - Clients avec statut Actif
  - `total_commandes` - Somme des commandes
  - `total_achats` - Montant total des achats
  - `newClientsMonth` - Nouveaux clients ce mois

**updateStatut()** - Changer le statut d'un client
```php
public function updateStatut(Request $request, Client $client): JsonResponse
```
- Valide : `statut` ∈ [Actif, Inactif, VIP]
- Retourne : Message + ClientResource

---

### DevisController

**Localisation** : [app/Http/Controllers/DevisController.php](app/Http/Controllers/DevisController.php)

#### Méthodes principales

**store()** - Créer un devis en brouillon
```php
public function store(StoreDevisRequest $request): JsonResponse
```

**Processus** :
1. Crée le devis avec `statut = 'brouillon'`
2. Crée les lignes_devis associées
3. Calcule les totaux (sous_total, TVA, remises)
4. Transaction DB (rollback en cas d'erreur)

**Calcul du prix** :
- Utilise `PricingService::calculerPrixUnitaire()`
- Basé sur : produit, largeur, hauteur

**Validation** : `StoreDevisRequest`

**update()** - Modifier un devis (brouillon seulement)
```php
public function update(UpdateDevisRequest $request, Devis $devis): JsonResponse
```

**Restrictions** :
- Erreur 400 si devis n'est pas en `brouillon`
- Supprime et recrée les lignes (on ne les modifie pas)
- Recalcule les totaux

**destroy()** - Supprimer un devis (brouillon seulement)
```php
public function destroy(Devis $devis): JsonResponse
```

**validerEtFacturer()** - Validation du devis
```php
public function validerEtFacturer(Devis $devis): JsonResponse
```

**Processus** :
1. Vérifie que devis est en `brouillon`
2. Passe le devis à `accepte`
3. **Crée une COMMANDE** :
   - Copie les lignes devis → articles_commande
   - Date livraison = date_validite du devis
4. **Crée une FACTURE** :
   - Copie articles_commande → articles_facture
   - Statut initial : "Non payée"
   - Date échéance : date_emission + 30 jours

**Transaction DB** : Rollback si erreur

**Retourne** :
```json
{
  "message": "Devis validé. Commande et facture créées.",
  "devis": {...},
  "commande_id": 5,
  "facture_id": 10
}
```

---

### CommandeController

**Localisation** : [app/Http/Controllers/CommandeController.php](app/Http/Controllers/CommandeController.php)

#### Méthodes

**index()** - Liste des commandes avec filtres
**show()** - Afficher une commande
**store()** - Créer une commande (directement ou depuis devis)
**update()** - Mettre à jour une commande
**destroy()** - Supprimer une commande (soft delete)
**updateStatut()** - Changer le statut de la commande
**stats()** - Statistiques des commandes

**Fonctionnalités principales** :
- Filtre par statut, client, date
- Tri par plusieurs colonnes
- Incluant les articles associés
- Mise à jour du client après validation

---

### FactureController

**Localisation** : [app/Http/Controllers/FactureController.php](app/Http/Controllers/FactureController.php)

#### Méthodes principales

**index()** - Liste des factures (avec filtres)
**show()** - Afficher une facture
**store()** - Créer une facture
**update()** - Mettre à jour une facture
**destroy()** - Supprimer une facture (soft delete)

**marquerPayee()** - Enregistrer un paiement
```php
public function marquerPayee(Request $request, Facture $facture): JsonResponse
```

**Logique** :
1. Valide : `montant_paye`, `mode_paiement`, `date_paiement`
2. Ajoute au `montant_paye` existant
3. Détermine le statut :
   - `montant_paye >= montant_ttc` → "Payée"
   - `montant_paye > 0` → "En attente"
   - Sinon → "Non payée"

**telechargerPDF()** - Générer PDF facture
```php
public function telechargerPDF(Facture $facture)
```

**stats()** - Statistiques des factures
- Factures payées / Non payées
- Montants totaux
- Factures en retard

---

### ArticleController

**Localisation** : [app/Http/Controllers/ArticleController.php](app/Http/Controllers/ArticleController.php)

#### Méthodes principales

**index()** - Liste des articles (stock)
- Filtres : `categorie`, `recherche`, `statut alerte`
- Tri : par quantité, prix, etc.

**store()** - Créer un article
```php
public function store(ArticleRequest $request): JsonResponse
```

**update()** - Mettre à jour un article
```php
public function update(UpdateArticleRequest $request, Article $article): JsonResponse
```

**ajusterStock()** - Ajouter ou retirer du stock
```php
public function ajusterStock(Request $request, Article $article): JsonResponse
```

**Logique** :
1. Valide : `quantite`, `type` (entree/sortie), `motif`
2. Récalcule : `quantite_apres = quantite_avant ± quantite`
3. Crée un MOUVEMENT pour audit
4. Mets à jour l'article

**alertes()** - Articles en alerte stock
```php
public function alertes(): JsonResponse
```

**Retourne** :
- Articles où `quantite <= seuil_alerte`
- Articles où `quantite = 0` (critique)

**stats()** - Statistiques stock
- Nombre total d'articles
- Articles en alerte
- Valeur totale du stock
- Top articles les plus chers

---

### MouvementController

**Localisation** : [app/Http/Controllers/MouvementController.php](app/Http/Controllers/MouvementController.php)

#### Méthodes

**index()** - Historique des mouvements
- Filtre : `article_id`, `type` (entree/sortie), `date`
- Tri : par date descendante (plus récent en premier)

**store()** - Créer un mouvement
```php
public function store(MouvementRequest $request): JsonResponse
```

**historique()** - Historique complet d'un article
```php
public function historique(Article $article): JsonResponse
```

**Retourne** :
```json
{
  "article": {...},
  "mouvements": [
    {
      "date": "2026-01-18",
      "type": "sortie",
      "quantite": 2,
      "avant": 10,
      "apres": 8,
      "motif": "Commande #COM001"
    }
  ]
}
```

**stats()** - Statistiques mouvements
- Entrées totales (mois/année)
- Sorties totales
- Articles avec plus de mouvements

---

### DashboardController

**Localisation** : [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php)

#### index()

```php
public function index(Request $request): JsonResponse
```

**Paramètres** :
- `periode` : mois | semaine | trimestre | annee

**Retourne** :
```json
{
  "stats": {
    "commandes": 15,
    "revenus": 25000.00,
    "clients_actifs": 8,
    "produits": 42
  },
  "details_commandes": {
    "total": 15,
    "en_attente": 3,
    "en_production": 5,
    "prete": 4,
    "livrees": 2,
    "annulees": 1
  },
  "commandes_recentes": [...],
  "top_articles": [...],
  "alertes": {
    "stock_faible": 3,
    "stock_critique": 1,
    "devis_en_attente": 2,
    "factures_impayees": 4,
    "livraisons_du_jour": 1
  }
}
```

#### chartData()

```php
public function chartData(Request $request): JsonResponse
```

**Retourne** :
- Évolution des commandes par jour (graphe en ligne)
- Répartition par statut (graphe en camembert)

---

## ✅ Requêtes Validées (Requests)

### StoreClientRequest

**Règles de validation** :
```php
'nom' => 'required|string|max:255',
'prenom' => 'required|string|max:255',
'telephone' => 'required|string|max:255',
'email' => 'nullable|email|unique:clients',
'adresse' => 'required|string',
'ville' => 'required|string|max:255',
'code_postal' => 'nullable|string|max:255',
'type_client' => 'required|in:Particulier,Professionnel',
'date_inscription' => 'required|date',
'statut' => 'required|in:Actif,Inactif,VIP'
```

### UpdateClientRequest

Similar to StoreClientRequest but some fields are nullable/optional.

### StoreDevisRequest

```php
'client_id' => 'required|exists:clients,id',
'date_emission' => 'required|date',
'validite' => 'required|integer|min:1',
'date_validite' => 'required|date|after:date_emission',
'remise' => 'nullable|numeric|min:0|max:100',
'acompte' => 'nullable|numeric|min:0|max:100',
'lignes' => 'required|array|min:1',
'lignes.*.produit' => 'required|string',
'lignes.*.quantite' => 'required|integer|min:1',
'lignes.*.largeur' => 'nullable|numeric',
'lignes.*.hauteur' => 'nullable|numeric',
'lignes.*.prix_unitaire' => 'required|numeric|min:0'
```

### ArticleRequest

```php
'nom' => 'required|string|max:255',
'reference' => 'required|string|unique:articles',
'categorie' => 'required|string|max:255',
'quantite' => 'required|integer|min:0',
'unite' => 'required|string|max:255',
'seuil_alerte' => 'required|integer|min:0',
'prix_achat' => 'nullable|numeric|min:0',
'fournisseur' => 'nullable|string|max:255',
'emplacement' => 'nullable|string|max:255'
```

### MouvementRequest

```php
'article_id' => 'required|exists:articles,id',
'type' => 'required|in:entree,sortie',
'quantite' => 'required|integer|min:1',
'motif' => 'nullable|string|max:255',
'commentaire' => 'nullable|string',
'date_mouvement' => 'required|date'
```

---

## 📤 Ressources (Resources)

Les **Resources** transforment les modèles Eloquent en JSON structuré.

### ClientResource

```php
class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'code_postal' => $this->code_postal,
            'type_client' => $this->type_client,
            'date_inscription' => $this->date_inscription,
            'nombre_commandes' => $this->nombre_commandes,
            'total_achats' => (float) $this->total_achats,
            'derniere_commande' => $this->derniere_commande,
            'statut' => $this->statut,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### DevisResource

```php
class DevisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'date_emission' => $this->date_emission,
            'date_validite' => $this->date_validite,
            'validite' => $this->validite,
            'remise' => (float) $this->remise,
            'acompte' => (float) $this->acompte,
            'delai_livraison' => $this->delai_livraison,
            'conditions_paiement' => $this->conditions_paiement,
            'sous_total' => (float) $this->sous_total,
            'montant_remise' => (float) $this->montant_remise,
            'total_ht' => (float) $this->total_ht,
            'total_ttc' => (float) $this->total_ttc,
            'montant_acompte' => (float) $this->montant_acompte,
            'notes' => $this->notes,
            'statut' => $this->statut,
            'lignes' => LigneDevisResource::collection($this->whenLoaded('lignes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### CommandeResource, FactureResource, etc.

Suivent le même pattern : transformation des modèles en JSON structuré.

---

## 🔄 Flux de Travail Complets

### 1️⃣ Flux Vente Complet

```
┌─ ÉTAPE 1 : CRÉER CLIENT
│
├─ POST /api/clients
│   └─ Créer un nouveau client
│
├─ ÉTAPE 2 : CRÉER DEVIS
│
├─ POST /api/devis
│   ├─ Statut: "brouillon"
│   ├─ Ajouter les lignes (produits avec dimensions)
│   └─ Calculer totaux (HT, remises, TVA, TTC)
│
├─ ÉTAPE 3 : ENVOYER/VALIDER DEVIS
│
├─ POST /api/devis/{id}/valider
│   ├─ Passe le devis à "accepte"
│   ├─ CRÉE une COMMANDE
│   │   ├─ Copie les lignes devis → articles_commande
│   │   └─ Statut: "En attente"
│   └─ CRÉE une FACTURE
│       ├─ Copie articles_commande → articles_facture
│       └─ Statut: "Non payée"
│
├─ ÉTAPE 4 : SUIVI PRODUCTION
│
├─ POST /api/commandes/{id}/statut
│   ├─ En attente → En production
│   ├─ En production → Prête
│   └─ Prête → Livrée
│
├─ ÉTAPE 5 : ENREGISTRER PAIEMENT
│
├─ POST /api/factures/{id}/payer
│   ├─ Montant versé
│   ├─ Mode de paiement
│   └─ Facture statut: "Payée"
│
└─ FIN : Client satisfait, facture payée ✅
```

### Exemple Complet en cURL

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost/api/auth/login \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq -r '.token')

# 2. Créer client
CLIENT=$(curl -s -X POST http://localhost/api/clients \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nom":"Dupont", "prenom":"Jean",
    "telephone":"0612345678", "email":"jean@dupont.fr",
    "adresse":"123 rue de Paris", "ville":"Lyon",
    "type_client":"Particulier", "date_inscription":"2026-01-18"
  }' | jq '.data.id')

# 3. Créer devis
DEVIS=$(curl -s -X POST http://localhost/api/devis \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "client_id":'$CLIENT',
    "date_emission":"2026-01-18",
    "validite":30, "date_validite":"2026-02-17",
    "remise":10, "acompte":30,
    "lignes":[{"produit":"Fenêtre PVC","largeur":1,"hauteur":1.2,"quantite":2,"prix_unitaire":350}]
  }' | jq '.data.id')

# 4. Valider devis → crée commande + facture
curl -s -X POST http://localhost/api/devis/$DEVIS/valider \
  -H "Authorization: Bearer $TOKEN" | jq .
```

---

### 2️⃣ Flux Stock Complet

```
┌─ ÉTAPE 1 : AJOUTER UN ARTICLE AU CATALOGUE
│
├─ POST /api/articles
│   └─ Créer article avec références uniques
│
├─ ÉTAPE 2 : RÉCEPTION STOCK
│
├─ POST /api/articles/{id}/ajuster-stock
│   ├─ type: "entree"
│   ├─ quantite: 10
│   └─ Crée un MOUVEMENT (type: "entree")
│
├─ ÉTAPE 3 : VÉRIFIER ALERTES
│
├─ GET /api/articles/alertes
│   ├─ Articles sous seuil_alerte
│   └─ Articles stock critique (= 0)
│
├─ ÉTAPE 4 : UTILISATION EN COMMANDE
│
├─ POST /api/articles/{id}/ajuster-stock
│   ├─ type: "sortie"
│   ├─ quantite: 2
│   ├─ motif: "Commande #COM001"
│   └─ Crée un MOUVEMENT (type: "sortie")
│
├─ ÉTAPE 5 : AUDIT HISTORIQUE
│
└─ GET /api/articles/{id}/historique-mouvement
    └─ Voir tous les mouvements (entrées + sorties)
```

---

### 3️⃣ Flux Financier

```
┌─ ÉTAPE 1 : ENREGISTRER DÉPENSES
│
├─ POST /api/depenses
│   ├─ categorie: "Achat matériaux"
│   ├─ montant: 2500
│   └─ date: "2026-01-18"
│
├─ ÉTAPE 2 : ANALYSER RENTABILITÉ
│
├─ GET /api/dashboard?periode=mois
│   ├─ Revenus du mois
│   ├─ Dépenses du mois
│   └─ Marge brute = Revenus - Dépenses
│
└─ GET /api/depenses/stats
    └─ Breakdown par catégorie
```

---

## 🏆 Bonnes Pratiques

### 1. Authentification
- ✅ Toutes les routes protégées ont `middleware('auth:sanctum')`
- ✅ Token JWT unique par utilisateur
- ✅ Tokens expirables

### 2. Validation
- ✅ Utiliser les **Requests** pour validation centralisée
- ✅ Messages d'erreur en français
- ✅ Validation côté serveur obligatoire

### 3. Transactions
- ✅ Les opérations complexes utilisent `DB::transaction()`
- ✅ Rollback automatique en cas d'erreur
- ✅ Exemple : `validerEtFacturer()` crée commande + facture atomiquement

### 4. Soft Deletes
- ✅ Utilisés sur clients, articles, commandes, factures
- ✅ Toujours filter par `whereNull('deleted_at')`
- ✅ Conserve la traçabilité

### 5. Ressources
- ✅ Transforment les modèles en JSON structuré
- ✅ Masquent les données sensibles
- ✅ Castent les types (int, float, string)

### 6. Erreurs
- ✅ Statuts HTTP corrects : 201 (create), 400 (validation), 404 (not found), 500 (server)
- ✅ Messages d'erreur explicites en JSON
- ✅ Rollback des transactions en cas d'erreur

### 7. Pagination
- ✅ `per_page` par défaut = 15
- ✅ Supporte custom : `per_page=50`
- ✅ Retourne `pagination` avec total, current_page, etc.

### 8. Filtres et Tri
- ✅ `sort_by` : colonne pour tri
- ✅ `sort_order` : asc ou desc
- ✅ `search` : recherche texte
- ✅ `statut`, `type_client` : filtres spécifiques

### 9. Calculs de Prix
- ✅ Utiliser `PricingService::calculerPrixUnitaire()`
- ✅ NE PAS accepter le prix du frontend
- ✅ Toujours recalculer côté serveur

### 10. Montants
- ✅ Stocker avec 2 décimales : `decimal(12,2)`
- ✅ Castage float en JSON : `(float) $value`
- ✅ Jamais de calculs flottants, utiliser `BCMath` si nécessaire

---

## 📋 Checklist Développement

- [ ] Middleware `auth:sanctum` sur les routes protégées
- [ ] Validation avec Requests
- [ ] Transactions DB pour opérations complexes
- [ ] Resources pour JSON
- [ ] Soft deletes pour données sensibles
- [ ] Filtres et tri sur index()
- [ ] Pagination avec per_page
- [ ] Messages d'erreur explicites
- [ ] Statuts HTTP corrects
- [ ] Tests unitaires pour validations
- [ ] Tests d'intégration pour flux métier

---

**Dernière mise à jour** : 18 Janvier 2026
**Version** : 1.0
