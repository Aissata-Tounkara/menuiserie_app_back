<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFactureRequest;
use App\Http\Resources\FactureResource;
use App\Models\Facture;
use App\Models\Commande;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ClientService;
use App\Services\DeviceDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Facture::with(['client', 'articles', 'commande']);

        if ($request->has('statut') && $request->statut !== 'Tous') {
            $query->where('statut', '=', $request->statut);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_facture', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                  ->orWhere('telephone', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $factures = $query->paginate($perPage);

        return FactureResource::collection($factures);
    }

    // ❌ CRÉATION MANUELLE DÉSACTIVÉE
    public function store(): JsonResponse
    {
        return response()->json([
            'message' => 'Les factures sont créées automatiquement à partir des devis.'
        ], 403);
    }

    public function show(Facture $facture): JsonResponse
    {
        $facture->load(['client', 'articles', 'commande']);
        return response()->json(['data' => new FactureResource($facture)]);
    }

    public function update(UpdateFactureRequest $request, Facture $facture): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updateData = $request->except('articles');

            if ($request->has('articles')) {
                $montantHT = 0;
                foreach ($request->articles as $article) {
                    $montantHT += $article['prix_unitaire'] * $article['quantite'];
                }
                $updateData['montant_ht'] = $montantHT;
                $updateData['tva'] = 0;
                $updateData['montant_ttc'] = $montantHT;

                $facture->articles()->delete();
                foreach ($request->articles as $articleData) {
                    $articleData['total'] = ($articleData['prix_unitaire'] ?? 0) * ($articleData['quantite'] ?? 1);
                    $facture->articles()->create($articleData);
                }
            }

            $facture->update($updateData);
            DB::commit();

            return response()->json([
                'message' => 'Facture modifiée avec succès',
                'data' => new FactureResource($facture->load(['client', 'articles', 'commande']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Facture $facture): JsonResponse
    {
        $facture->delete();
        return response()->json(['message' => 'Facture supprimée']);
    }

    public function marquerPayee(Request $request, Facture $facture): JsonResponse
    {
        $request->validate([
            'montant_paye' => 'required|numeric|min:0|max:' . $facture->montant_ttc,
            'mode_paiement' => 'required|string',
            'date_paiement' => 'required|date',
        ]);

        $facture->update([
            'montant_paye' => $request->montant_paye,
            'mode_paiement' => $request->mode_paiement,
            'date_paiement' => $request->date_paiement,
            'statut' => $request->montant_paye >= $facture->montant_ttc ? 'Payée' : 'En attente',
        ]);

        return response()->json([
            'message' => 'Paiement enregistré',
            'data' => new FactureResource($facture->load(['client', 'articles', 'commande']))
        ]);
    }

    public function stats(): JsonResponse
    {
        $factures = Facture::all();
        $payees = $factures->filter(fn($f) => $f->montant_paye >= $f->montant_ttc)->count();

        $stats = [
            'total' => $factures->count(),
            'chiffre_affaires' => $factures->sum('montant_ttc'),
            'payees' => $payees,
            'non_payees' => $factures->count() - $payees,
            'encours' => $factures->sum(fn($f) => $f->montant_ttc - $f->montant_paye),
        ];

        return response()->json(['data' => $stats]);
    }

    public function telechargerPDF($id)
    {
        $facture = Facture::with(['client', 'articles', 'commande', 'paiements'])->findOrFail($id);
        $articleCount = $facture->articles->count();
        $montantEnLettres = $this->montantEnLettres((float) $facture->montant_ttc);

        if ($articleCount > 18) {
            $densityClass = 'ultra-compact';
        } elseif ($articleCount > 10) {
            $densityClass = 'compact';
        } else {
            $densityClass = 'normal';
        }

        $this->logPdfDownloadActivity($facture);

        $pdf = Pdf::loadView('factures.pdf', compact('facture', 'montantEnLettres', 'densityClass'));
        
        $numeroClean = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $facture->numero_facture);
        
        return $pdf->download('facture-' . $numeroClean . '.pdf');
    }

    private function montantEnLettres(float $montant): string
    {
        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::SPELLOUT);

        $partieEntiere = (int) floor($montant);
        $partieDecimale = (int) round(($montant - $partieEntiere) * 100);

        $texte = ucfirst($formatter->format($partieEntiere)) . ' francs CFA';

        if ($partieDecimale > 0) {
            $texte .= ' et ' . $formatter->format($partieDecimale) . ' centimes';
        }

        return $texte;
    }

    private function logPdfDownloadActivity(Facture $facture): void
    {
        try {
            $request = request();
            $user = auth('sanctum')->user();
            $detector = new DeviceDetector($request);

            ActivityLog::create([
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'action' => 'download',
                'module' => 'factures',
                'model_type' => Facture::class,
                'model_id' => $facture->id,
                'description' => "Téléchargement du PDF de la facture {$facture->numero_facture} (#{$facture->id})",
                'changes' => [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'format' => 'pdf',
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $detector->getDeviceType(),
                'device_name' => $detector->getDeviceName(),
                'session_id' => $request->bearerToken(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
