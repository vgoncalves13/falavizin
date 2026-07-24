<?php

namespace App\Actions;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;

class ImportBusinessFromGoogleAction
{
    /**
     * Maps Google Places types to our category slugs.
     * First match wins (order matters — more specific first).
     *
     * @var array<string, string>
     */
    private const TYPE_TO_CATEGORY = [
        // Beleza
        'beauty_salon' => 'beleza',
        'hair_care' => 'beleza',
        'hair_salon' => 'beleza',
        'barber_shop' => 'beleza',
        'nail_salon' => 'beleza',
        'spa' => 'beleza',

        // Pet
        'veterinary_care' => 'pet',
        'pet_store' => 'pet',
        'animal_shelter' => 'pet',

        // Elétrica
        'electrician' => 'eletrica',

        // Encanamento
        'plumber' => 'encanamento',

        // Pintura
        'painter' => 'pintura',

        // Internet / telecom
        'internet_cafe' => 'internet',
        'telecommunications' => 'internet',
        'cell_phone_store' => 'internet',

        // Saúde
        'pharmacy' => 'saude',
        'drugstore' => 'saude',
        'hospital' => 'saude',
        'doctor' => 'saude',
        'dentist' => 'saude',
        'physiotherapist' => 'saude',
        'medical_clinic' => 'saude',
        'urgent_care_center' => 'saude',
        'medical_lab' => 'saude',
        'skin_care_clinic' => 'saude',
        'wellness_center' => 'saude',
        'health' => 'saude',

        // Educação
        'school' => 'educacao',
        'university' => 'educacao',
        'secondary_school' => 'educacao',
        'primary_school' => 'educacao',
        'educational_institution' => 'educacao',
        'tutoring_service' => 'educacao',
        'library' => 'educacao',

        // Mercado
        'supermarket' => 'mercado',
        'grocery_store' => 'mercado',
        'convenience_store' => 'mercado',
        'department_store' => 'mercado',
        'shopping_mall' => 'mercado',
        'liquor_store' => 'mercado',
        'warehouse_store' => 'mercado',

        // Alimentação
        'restaurant' => 'alimentacao',
        'cafe' => 'alimentacao',
        'bar' => 'alimentacao',
        'bakery' => 'alimentacao',
        'meal_delivery' => 'alimentacao',
        'meal_takeaway' => 'alimentacao',
        'fast_food_restaurant' => 'alimentacao',
        'coffee_shop' => 'alimentacao',
        'ice_cream_shop' => 'alimentacao',
        'pizza_restaurant' => 'alimentacao',
        'hamburger_restaurant' => 'alimentacao',
        'sandwich_shop' => 'alimentacao',
        'sushi_restaurant' => 'alimentacao',
        'steak_house' => 'alimentacao',
        'seafood_restaurant' => 'alimentacao',
        'vegetarian_restaurant' => 'alimentacao',
        'food' => 'alimentacao',

        // Academia
        'gym' => 'academia',
        'fitness_center' => 'academia',
        'sports_club' => 'academia',
        'sports_complex' => 'academia',
        'stadium' => 'academia',
        'sports_school' => 'academia',
        'swimming_pool' => 'academia',
        'yoga_studio' => 'academia',
        'pilates_studio' => 'academia',

        // Automotivo
        'car_repair' => 'automotivo',
        'car_dealer' => 'automotivo',
        'car_wash' => 'automotivo',
        'gas_station' => 'automotivo',
        'parking' => 'automotivo',
        'auto_parts_store' => 'automotivo',
        'car_rental' => 'automotivo',
        'tire_shop' => 'automotivo',
        'motorcycle_dealer' => 'automotivo',
        'motorcycle_repair' => 'automotivo',

        // Banco & Finanças
        'bank' => 'banco',
        'atm' => 'banco',
        'finance' => 'banco',
        'insurance_agency' => 'banco',
        'accounting' => 'banco',
        'money_transfer' => 'banco',
        'financial_institution' => 'banco',

        // Casa & Construção
        'furniture_store' => 'casa',
        'hardware_store' => 'casa',
        'home_goods_store' => 'casa',
        'carpenter' => 'casa',
        'roofing_contractor' => 'casa',
        'general_contractor' => 'casa',
        'home_improvement_store' => 'casa',
        'locksmith' => 'casa',

        // Moda
        'clothing_store' => 'moda',
        'shoe_store' => 'moda',
        'jewelry_store' => 'moda',
        'tailor' => 'moda',
        'boutique' => 'moda',

        // Serviços
        'laundry' => 'servicos',
        'laundry_service' => 'servicos',
        'moving_company' => 'servicos',
        'cleaning_service' => 'servicos',
        'pest_control' => 'servicos',
        'storage' => 'servicos',
        'courier_service' => 'servicos',
        'notary_public' => 'servicos',
        'lawyer' => 'servicos',
        'real_estate_agency' => 'servicos',
        'photographer' => 'servicos',
        'travel_agency' => 'servicos',
        'florist' => 'servicos',
        'print_shop' => 'servicos',

        // Transporte
        'transit_station' => 'transporte',
        'bus_station' => 'transporte',
        'taxi_stand' => 'transporte',
        'subway_station' => 'transporte',
        'train_station' => 'transporte',
        'airport' => 'transporte',
        'ferry_terminal' => 'transporte',

        // Entretenimento
        'movie_theater' => 'entretenimento',
        'night_club' => 'entretenimento',
        'bowling_alley' => 'entretenimento',
        'amusement_park' => 'entretenimento',
        'casino' => 'entretenimento',
        'event_venue' => 'entretenimento',
        'comedy_club' => 'entretenimento',
        'karaoke' => 'entretenimento',
        'performing_arts_theater' => 'entretenimento',
        'museum' => 'entretenimento',
    ];

    /** @var array<string, int> */
    private array $categoryCache = [];

    public function execute(array $place, Neighborhood $neighborhood, int $fallbackCategoryId): ?Business
    {
        if (Business::where('google_place_id', $place['place_id'])->exists()) {
            return null;
        }

        $categoryId = $this->resolveCategoryId($place['types'] ?? [], $fallbackCategoryId);

        return Business::create([
            'category_id' => $categoryId,
            'neighborhood_id' => $neighborhood->id,
            'name' => $place['name'],
            'address' => $place['address'] ?? null,
            'neighborhood' => $neighborhood->name,
            'city' => $neighborhood->city,
            'lat' => $place['lat'] ?? null,
            'lng' => $place['lng'] ?? null,
            'phone' => $place['phone'] ?? null,
            'website' => $place['website'] ?? null,
            'google_place_id' => $place['place_id'],
            'status' => BusinessStatus::Approved,
            'claimed' => false,
            'user_id' => null,
        ]);
    }

    private function resolveCategoryId(array $types, int $fallbackCategoryId): int
    {
        foreach ($types as $type) {
            $slug = self::TYPE_TO_CATEGORY[$type] ?? null;

            if ($slug === null) {
                continue;
            }

            if (! isset($this->categoryCache[$slug])) {
                $id = Category::where('slug', $slug)->value('id');

                if ($id) {
                    $this->categoryCache[$slug] = $id;
                }
            }

            if (isset($this->categoryCache[$slug])) {
                return $this->categoryCache[$slug];
            }
        }

        // Fallback: category selected in form, or "outros" if none selected
        if ($fallbackCategoryId > 0) {
            return $fallbackCategoryId;
        }

        if (! isset($this->categoryCache['outros'])) {
            $id = Category::where('slug', 'outros')->value('id');

            if ($id) {
                $this->categoryCache['outros'] = $id;
            }
        }

        return $this->categoryCache['outros'] ?? $fallbackCategoryId;
    }
}
