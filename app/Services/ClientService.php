<?php

namespace App\Services;

use App\Models\Client;
use Carbon\Carbon;

class ClientService
{
    /**
     * Met à jour les statistiques du client après la création d'une facture.
     *
     * @param  Client  $client
     * @param  float   $montantTTC      Montant total de la facture (TTC)
     * @param  string|null $dateFacture Date d'émission de la facture (format Y-m-d)
     * @return void
     */
    public function updateAfterFacture(Client $client, float $montantTTC, ?string $dateFacture = null): void
    {
        // 1. Incrémenter le nombre de commandes/factures
        $client->nombre_commandes += 1;

        // 2. Ajouter le montant TTC aux achats totaux
        $client->total_achats = (float) $client->total_achats + $montantTTC;

        // 3. Définir la date de dernière commande
        //    → On utilise la date d'émission de la facture si fournie, sinon "maintenant"
        $client->derniere_commande = $dateFacture 
            ? Carbon::parse($dateFacture)->startOfDay()
            : now()->startOfDay();

        // 4. Sauvegarder les modifications en base
        $client->save();

        // 5. Mettre à jour le statut (Actif / Inactif / VIP)
        $client->updateStatut();
    }
}