<?php

namespace App;

/**
 * The centers of participation under the Philippine Youth Development Plan,
 * used to group CBYDP priorities.
 */
enum CenterOfParticipation: string
{
    case Health = 'health';
    case Education = 'education';
    case EconomicEmpowerment = 'economic_empowerment';
    case SocialInclusion = 'social_inclusion';
    case PeaceBuilding = 'peace_building';
    case Governance = 'governance';
    case ActiveCitizenship = 'active_citizenship';
    case Environment = 'environment';
    case GlobalMobility = 'global_mobility';
    case Agriculture = 'agriculture';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Health => 'Health',
            self::Education => 'Education',
            self::EconomicEmpowerment => 'Economic empowerment',
            self::SocialInclusion => 'Social inclusion and equity',
            self::PeaceBuilding => 'Peace-building and security',
            self::Governance => 'Governance',
            self::ActiveCitizenship => 'Active citizenship',
            self::Environment => 'Environment',
            self::GlobalMobility => 'Global mobility',
            self::Agriculture => 'Agriculture',
        };
    }
}
