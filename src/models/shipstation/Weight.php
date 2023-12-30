<?php
namespace verbb\snipcart\models\shipstation;

use craft\base\Model;

class Weight extends Model
{
    // Constants
    // =========================================================================

    public const UNIT_POUNDS = 'pounds';
    public const UNIT_OUNCES = 'ounces';
    public const UNIT_GRAMS = 'grams';


    // Properties
    // =========================================================================

    public ?int $value = null;
    public ?string $units = null;
    public ?int $weightUnits = null;
    

    // Public Methods
    // =========================================================================

    public static function populateFromSnipcartItem($item): self
    {
        return new self([
            'value' => $item->weight ?? 0,
            'units' => self::UNIT_GRAMS,
        ]);
    }
    

    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        
        $rules[] = [['value'], 'number', 'integerOnly' => true];
        $rules[] = [['units'], 'in', 'range' => [self::UNIT_POUNDS, self::UNIT_OUNCES, self::UNIT_GRAMS]];

        return $rules;
    }
}
