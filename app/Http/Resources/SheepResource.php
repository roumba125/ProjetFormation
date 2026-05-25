<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforme un modèle Sheep en JSON propre pour l'API
 * Utilisé par Vue.js pour afficher les fiches
 */
class SheepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // --- Identité ---
            'id'          => $this->id,
            'name'        => $this->name,
            'reference'   => $this->reference,
            'ear_tag'     => $this->ear_tag,
            'gender'      => $this->gender,
            'gender_label'=> $this->gender_label,   // "Bélier" / "Brebis"

            // --- Race ---
            'breed' => $this->whenLoaded('breed', fn() => [
                'id'     => $this->breed->id,
                'name'   => $this->breed->name,
                'slug'   => $this->breed->slug,
                'origin' => $this->breed->origin,
            ]),

            // --- Âge ---
            'birth_date'     => $this->birth_date->format('Y-m-d'),
            'age_months'     => $this->age_in_months,
            'age_formatted'  => $this->age_formatted,    // "18 mois" / "2 ans 3 mois"

            // --- Physique ---
            'current_weight'      => (float) $this->current_weight,
            'height'              => $this->height ? (float) $this->height : null,
            'coat_color'          => $this->coat_color,
            'physical_description'=> $this->physical_description,

            // --- Santé ---
            'health_condition' => $this->health_condition,
            'health_label'     => $this->health_label,   // "Excellent" / "Bon"...
            'last_vet_checkup' => $this->last_vet_checkup?->format('d/m/Y'),
            'last_daily_gain'  => $this->last_daily_gain, // GMQ en g/j

            // --- Alimentation ---
            'current_diet' => $this->current_diet,

            // --- Vente ---
            'price'           => (float) $this->price,
            'price_formatted' => number_format($this->price, 0, ',', ' ') . ' FCFA',
            'negotiable_price'=> $this->negotiable_price ? (float) $this->negotiable_price : null,
            'status'          => $this->status,
            'status_label'    => $this->status_label,    // "Disponible" / "Réservé"...
            'is_featured'     => $this->is_featured,

            // --- Photos ---
            'primary_photo_url' => $this->primary_photo_url,
            'photos' => $this->whenLoaded('photos', fn() =>
                $this->photos->map(fn($p) => [
                    'id'         => $p->id,
                    'url'        => $p->url,
                    'caption'    => $p->caption,
                    'is_primary' => $p->is_primary,
                ])
            ),

            // --- Vaccinations (détail complet) ---
            'vaccinations' => $this->whenLoaded('vaccinations', fn() =>
                $this->vaccinations->map(fn($v) => [
                    'id'              => $v->id,
                    'vaccine_name'    => $v->vaccine_name,
                    'vaccine_brand'   => $v->vaccine_brand,
                    'administered_at' => $v->administered_at->format('d/m/Y'),
                    'next_due_at'     => $v->next_due_at?->format('d/m/Y'),
                    'administered_by' => $v->administered_by,
                    'route_label'     => $v->route_label,
                    'status'          => $v->status,
                    'status_label'    => $v->status_label,
                ])->values()->all()
            ),

            // --- Résumé vaccins (pour la carte catalogue) ---
            'vaccinations_summary' => $this->when(
                !$this->relationLoaded('vaccinations'),
                null,
                fn() => [
                    'total' => $this->vaccinations->count(),
                    'valid' => $this->vaccinations->where('status', 'valid')->count(),
                    'names' => $this->vaccinations->where('status', 'valid')
                                    ->pluck('vaccine_name')
                                    ->take(3)
                                    ->values()
                                    ->all(),
                ]
            ),

            // --- Historique des poids ---
            'weight_records' => $this->whenLoaded('weightRecords', fn() =>
                $this->weightRecords->map(fn($w) => [
                    'id'          => $w->id,
                    'recorded_at' => $w->recorded_at->format('d/m/Y'),
                    'weight'      => (float) $w->weight,
                    'daily_gain'  => $w->daily_gain ? (float) $w->daily_gain : null,
                    'diet'        => $w->diet,
                ])->values()->all()
            ),

            // --- Parents ---
            'mother' => $this->whenLoaded('mother', fn() => $this->mother ? [
                'id'        => $this->mother->id,
                'name'      => $this->mother->name,
                'reference' => $this->mother->reference,
            ] : null),

            'father' => $this->whenLoaded('father', fn() => $this->father ? [
                'id'        => $this->father->id,
                'name'      => $this->father->name,
                'reference' => $this->father->reference,
            ] : null),

            'notes'      => $this->notes,
            'created_at' => $this->created_at->format('d/m/Y'),
        ];
    }
}
