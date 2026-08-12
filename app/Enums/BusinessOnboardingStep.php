<?php

namespace App\Enums;

enum BusinessOnboardingStep: string
{
    case BasicDetails = 'basic_details';
    case OpeningHours = 'opening_hours';
    case OwnPhoto = 'own_photo';
    case ProductsServices = 'products_services';
    case InitialAction = 'initial_action';

    public function label(): string
    {
        return match ($this) {
            self::BasicDetails => 'Confirmar dados básicos',
            self::OpeningHours => 'Confirmar horários',
            self::OwnPhoto => 'Adicionar foto própria',
            self::ProductsServices => 'Produtos e serviços',
            self::InitialAction => 'Ação inicial',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BasicDetails => 'Confirme as informações do estabelecimento.',
            self::OpeningHours => 'Confirme ou ajuste os horários de funcionamento.',
            self::OwnPhoto => 'Adicione pelo menos uma foto enviada por você.',
            self::ProductsServices => 'Informe o que você oferece.',
            self::InitialAction => 'Realize uma ação para movimentar seu perfil.',
        };
    }

    /** @return list<BusinessOnboardingStep> */
    public static function ordered(): array
    {
        return [
            self::BasicDetails,
            self::OpeningHours,
            self::OwnPhoto,
            self::ProductsServices,
            self::InitialAction,
        ];
    }
}
