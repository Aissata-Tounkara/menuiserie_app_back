<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Récupérer toutes les factures avec l'ancien format F-XXX/YYYY
        $oldFactures = DB::table('factures')
            ->where('numero_facture', 'REGEXP', '^F-[0-9]{1,3}/[0-9]{4}$')
            ->orderBy('created_at')
            ->get();

        foreach ($oldFactures as $facture) {
            // Extraire le numéro et l'année
            if (preg_match('/^F-(\d+)\/(\d{4})$/', $facture->numero_facture, $matches)) {
                $numero = (int) $matches[1];
                $annee = $matches[2];

                // Générer le nouveau format
                $newNumero = "FAC-{$annee}-" . str_pad($numero, 3, '0', STR_PAD_LEFT);

                // Vérifier qu'il n'existe pas déjà (au cas où)
                $exists = DB::table('factures')->where('numero_facture', $newNumero)->exists();

                if (!$exists) {
                    DB::table('factures')
                        ->where('id', $facture->id)
                        ->update(['numero_facture' => $newNumero]);
                } else {
                    // Si collision, incrémenter jusqu'à trouver un numéro libre
                    $counter = $numero + 1;
                    do {
                        $newNumero = "FAC-{$annee}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
                        $exists = DB::table('factures')->where('numero_facture', $newNumero)->exists();
                        $counter++;
                    } while ($exists && $counter <= 999);

                    if ($counter <= 999) {
                        DB::table('factures')
                            ->where('id', $facture->id)
                            ->update(['numero_facture' => $newNumero]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Optionnel : impossible de revenir proprement, donc on laisse vide
        // ou on sauvegarde les anciens numéros dans une colonne temporaire si besoin
    }
};