<?php
namespace verbb\snipcart\models\shipstation;

use craft\base\Model;

class AdvancedOptions extends Model
{
    // Properties
    // =========================================================================

    public ?int $warehouseId = null;
    public ?bool $nonMachinable = null;
    public ?bool $saturdayDelivery = null;
    public ?bool $containsAlcohol = null;
    public ?int $storeId = null;
    public ?string $customField1 = null;
    public ?string $customField2 = null;
    public ?string $customField3 = null;
    public ?string $source = null;
    public bool $mergedOrSplit;
    public array $mergedIds = [];
    public ?int $parentId = null;
    public ?string $billToParty = null;
    public ?string $billToAccount = null;
    public ?string $billToPostalCode = null;
    public ?string $billToCountryCode = null;
    public ?string $billToMyOtherAccount = null;


    // Public Methods
    // =========================================================================

    public function rules(): array
    {
        return [
            [['warehouseId', 'storeId', 'parentId'], 'number', 'integerOnly' => true],
            [['customField1', 'customField2', 'customField3', 'source', 'billToParty', 'billToAccount', 'billToPostalCode', 'billToCountryCode', 'billToMyOtherAccount'], 'string'],
            [['nonMachinable', 'saturdayDelivery', 'containsAlcohol', 'mergedOrSplit'], 'boolean'],
            ['mergedIds', 'each', 'rule' => ['integer']],
            [['billToCountryCode'], 'string', 'length' => 2],
        ];
    }
}
