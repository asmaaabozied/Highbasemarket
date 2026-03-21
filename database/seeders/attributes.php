<?php

return [
    [
        'sub_category' => 'Fresh Milk',
        'attributes'   => [
            [
                'name'         => 'Fat Content',
                'values'       => null,
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Package Size',
                'values'       => null,
                'units'        => ['mli liters', 'liters', 'gallons'],
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Shelf Life',
                'type' => 'number',
            ],
            [
                'name'         => 'Source',
                'values'       => ['cows', 'goats', 'sheep'],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
    ],
    [
        'sub_category' => 'Long Life Milk',
        'attributes'   => [
            [
                'name'   => 'UHT Treatment',
                'values' => [
                    'ultra-pasteurized',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'         => 'Fat Content',
                'values'       => null,
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Sterilization Process',
            'Fortification Details',
        ],
    ],
    [
        'sub_category' => 'Dairy Alternatives',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fortified Nutrients',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
            [
                'name' => 'Added Sugar Content',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Laban & Flavoured Milk',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fat Content',
                'values'       => [],
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Ingredients List',
            'Additives',
        ],
    ],
    [
        'sub_category' => 'Yoghurt & Chilled Desserts',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fat Percentage',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Probiotic Content',
            'Sweetness Level',
        ],
    ],
    [
        'sub_category' => 'Fresh Cream',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fat Percentage',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Whipping Compatibility',
            'Cooking Suitability',
        ],
    ],
    [
        'sub_category' => 'Cheese',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Aging Period',
                'values'       => null,
                'units'        => ['day', 'month', 'week'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Melting Properties',
            'Storage Tips',
        ],
    ],
    [
        'sub_category' => 'Butter & Margarine',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fat Content',
                'values'       => [],
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Salt Content',
            'Plant-based Certification',
        ],
    ],
    [
        'sub_category' => 'Eggs',
        'attributes'   => [
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['pieces', 'cartons', 'cases', 'pallets'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Farming Method',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Batch Number',
            'Packaging Eco-certifications',
        ],
    ],
    [
        'sub_category' => 'Bread Basket',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['g', 'kg', 'metric tons (MT)', 'oz', 'lb; pieces', 'cases', 'pallets'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Packaging Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Shelf Life',
                'type' => 'number',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Cake House',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['g', ' kg', ' oz', ' lb; cm', ' inches; pieces', ' cases'],
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Shelf Life',
                'type' => 'number',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Arabic Bakery',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => null,
                'units'        => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Shelf Life',
                'type' => 'number',
            ],
            [
                'name' => 'Production Date',
                'type' => 'date',
            ],
        ],
    ],
    [
        'sub_category' => 'Asian Bakery',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
            'Shelf Life',
        ],
    ],
    [
        'sub_category' => 'Croissant and Savories Corner',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Filling',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Baking Instructions',
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Healthy Bake Shop',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name' => [
                    'name' => 'Ingredients',
                    'type' => 'long text',
                ],
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Dietary Features',
                'values' => [
                    'almond flour',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Nutritional Information',
                'type' => 'long text',
            ],
            'Certifications',
        ],
    ],
    [
        'sub_category' => 'Cold Cuts & Prepacked Cooked Meats',
        'attributes'   => [
            [
                'name'         => 'Meat Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
            'Halal Certification',
        ],
    ],
    [
        'sub_category' => 'Olives & Pickles',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Preservation Method',
        ],
    ],
    [
        'sub_category' => 'Oriental Food',
        'attributes'   => [
            [
                'name'   => 'Cuisine Type',
                'values' => [
                    'Middle Eastern',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'   => 'Dish Name',
                'values' => [
                    'hummus',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Serving Suggestions',
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Hummus, Labneh & Other Prepacked Deli',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fat Content',
                'values'       => [],
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Shelf Life',
        ],
    ],
    [
        'sub_category' => 'Fruits',
        'attributes'   => [
            [
                'name'         => 'Fruit Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Grade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Harvest Date',
                'type' => 'date',
            ],
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Vegetables',
        'attributes'   => [
            [
                'name'         => 'Vegetable Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Grade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Harvest Date',
                'type' => 'date',
            ],
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Leaves',
        'attributes'   => [
            [
                'name'         => 'Leaf Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Harvest Date',
                'type' => 'date',
            ],
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Beef & Veal',
        'attributes'   => [
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Grade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Halal Certification',
            'Grass-fed Status',
        ],
    ],
    [
        'sub_category' => 'Lamb & Mutton',
        'attributes'   => [
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Grade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Halal Certification',
            'Grass-fed Status',
        ],
    ],
    [
        'sub_category' => 'Chicken',
        'attributes'   => [
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Weight',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Farming Method',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Halal Certification',
            'Antibiotic-Free Status',
        ],
    ],
    [
        'sub_category' => 'Fresh Fish',
        'attributes'   => [
            [
                'name'         => 'Species',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Weight',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Catch Method',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Sustainability Certification',
            'Fishing Area',
        ],
    ],
    [
        'sub_category' => 'Smoked Fish & Dried Fish',
        'attributes'   => [
            [
                'name'         => 'Species',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Processing Method',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Salt Content',
        ],
    ],
    [
        'sub_category' => 'Bakes & Grills',
        'attributes'   => [
            [
                'name'         => 'Dish Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Serving Size',
                'values'       => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'   => 'Spice Level',
                'values' => [
                    'serves 2',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Cooking Instructions',
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Chilled Meals',
        'attributes'   => [
            [
                'name'         => 'Dish Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Serving Size',
                'values'       => [],
                'units'        => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'   => 'Spice Level',
                'values' => [
                    'serves 1',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Fresh Juice',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Pulp Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Cold-Pressed Status',
        ],
    ],
    [
        'sub_category' => 'Cereals',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Whole Grain Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Nutritional Information',
                'type' => 'long text',
            ],
            'Fortification',
        ],
    ],
    [
        'sub_category' => 'Oats & Bars',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'   => 'Dietary Features',
                'values' => [
                    'apple cinnamon',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Nutritional Information',
                'type' => 'long text',
            ],
            'Certifications',
        ],
    ],
    [
        'sub_category' => 'Jams & Spreads',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Sugar Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Honey',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Source Flower',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'UMF Rating',
        ],
    ],
    [
        'sub_category' => 'Water',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Mineral Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'pH Level',
            'Source',
        ],
    ],
    [
        'sub_category' => 'Tea',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Packaging Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'Caffeine Content',
        ],
    ],
    [
        'sub_category' => 'Coffee',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Roast Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Fair Trade Certification',
            'Caffeine Content',
        ],
    ],
    [
        'sub_category' => 'Soft Drinks & Juices',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Sugar Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Carbonation Level',
        ],
    ],
    [
        'sub_category' => 'Sports & Energy Drinks',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Sugar Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Caffeine Content',
            'Added Vitamins',
        ],
    ],
    [
        'sub_category' => 'Powdered Drinks',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Nutritional Information',
                'type' => 'long text',
            ],
            'Allergen Info',
        ],
    ],
    [
        'sub_category' => 'Long Life Milk',
        'attributes'   => [
            [
                'name'   => 'UHT Treatment',
                'values' => [
                    'ultra-pasteurized',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'         => 'Fat Content',
                'values'       => null,
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Fortification Details',
            'Shelf Life',
        ],
    ],
    [
        'sub_category' => 'Dairy Alternatives',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fortified Nutrients',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
            [
                'name' => 'Added Sugar Content',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Milk Powder',
        'attributes'   => [
            [
                'name'         => 'Fat Content',
                'values'       => null,
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Fortification',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Preparation Instructions',
            'Allergen Info',
        ],
    ],
    [
        'sub_category' => 'Arabic Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Dietary Certifications',
        ],
    ],
    [
        'sub_category' => 'Filipino Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Cooking Instructions',
        ],
    ],
    [
        'sub_category' => 'Indian Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Dietary Certifications',
        ],
    ],
    [
        'sub_category' => 'Korean Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Fermentation Status',
        ],
    ],
    [
        'sub_category' => 'Chinese Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Cooking Instructions',
        ],
    ],
    [
        'sub_category' => 'Japanese Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Thai Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Dietary Certifications',
        ],
    ],
    [
        'sub_category' => 'Sri Lankan Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Dietary Certifications',
        ],
    ],
    [
        'sub_category' => 'Mexican Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Spice Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Italian Food',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Cooking Instructions',
        ],
    ],
    [
        'sub_category' => 'Other Ethnic Foods',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Regional Origin',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Ice Cream',
        'attributes'   => [
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Fat Content',
                'values'       => [],
                'units'        => '%',
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Ready Meals & Snacks',
        'attributes'   => [
            [
                'name'         => 'Dish Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Serving Size',
                'values'       => [],
                'units'        => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'   => 'Cooking Time',
                'values' => [
                    'serves 2',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Cooking Instructions',
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Burgers',
        'attributes'   => [
            [
                'name'         => 'Meat Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Patty Size',
                'values'       => null,
                'units'        => ['cm', 'inches', 'pieces', ' cases', ' kg', ' metric tons (MT) pallets'],
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Cooking Instructions',
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Pastry Sheets & Dough',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Dough Thickness',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Baking Instructions',
            'Allergen Info',
        ],
    ],
    [
        'sub_category' => 'Meat & Poultry',
        'attributes'   => [
            [
                'name'         => 'Meat Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Halal Certification',
            'Cooking Instructions',
        ],
    ],
    [
        'sub_category' => 'Fish & Seafood',
        'attributes'   => [
            [
                'name'         => 'Species',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Country of Origin',
            'Sustainability Certification',
        ],
    ],
    [
        'sub_category' => 'Frozen Fruits & Vegetables',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Country of Origin',
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Frozen Dairy',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
            'Fat Content',
        ],
    ],
    [
        'sub_category' => 'Canned Vegetables',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Canned Beans',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Sauce Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Canned Meat',
        'attributes'   => [
            [
                'name'         => 'Meat Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Cut Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Halal Certification',
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Canned Fish',
        'attributes'   => [
            [
                'name'         => 'Fish Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Sauce/Oil Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Sustainability Certification',
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Canned Fruits',
        'attributes'   => [
            [
                'name'         => 'Fruit Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Syrup Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Sugar Content',
        ],
    ],
    [
        'sub_category' => 'Baking Essentials',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Sugar & Other Sweeteners',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Source',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'Glycemic Index',
        ],
    ],
    [
        'sub_category' => 'Salad Dressing & Table Sauces',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Bottled Olives & Pickles',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Preservation Method',
        ],
    ],
    [
        'sub_category' => 'Cooking Sauces and Cream',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Oils & Ghee',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Source',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Smoke Point',
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Salt & Pepper',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Source Region',
            'Organic Certification',
        ],
    ],
    [
        'sub_category' => 'Pulses',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'Cooking Time',
        ],
    ],
    [
        'sub_category' => 'Spices & Herbs',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Form',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'Source Region',
        ],
    ],
    [
        'sub_category' => 'Condiments',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Spice Level',
        ],
    ],
    [
        'sub_category' => 'Rice',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Grain Length',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'Cooking Time',
        ],
    ],
    [
        'sub_category' => 'Pasta & Noodles',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Shape',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Cooking Time',
        ],
    ],
    [
        'sub_category' => 'Soups',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Biscuits',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Chocolates',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['g', 'kg', 'oz', 'lb'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Cocoa Content',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Candy',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Sugar Content',
        ],
    ],
    [
        'sub_category' => 'Gums & Mints',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Sugar Content',
        ],
    ],
    [
        'sub_category' => 'Chips',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Snacks',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Popcorn',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Nuts & Dates',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Variety',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Roasting Status',
        ],
    ],
    [
        'sub_category' => 'Organic',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Certification',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Gluten Free',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Certification',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Sugar Free',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Sweetener Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Vegan',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Certifications',
        ],
    ],
    [
        'sub_category' => 'Lactose Free',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Plant Based',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Certifications',
        ],
    ],
    [
        'sub_category' => 'Baby Shower & Bath',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Age Group',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Baby Cream & Lotion',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Age Group',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Diapers & Wipes',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['pieces', ' packs', ' cases', ' pallets'],
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material Type',
            'Absorbency Level',
        ],
    ],
    [
        'sub_category' => 'Feeding Bottles & Accessories',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Age Group',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'BPA-Free Status',
            'Safety Certifications',
        ],
    ],
    [
        'sub_category' => 'Other Baby Care',
        'attributes'   => [
            [
                'name'         => 'Product Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Age Group',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material Type',
            'Safety Certifications',
        ],
    ],
    [
        'sub_category' => 'Baby Food',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Age Group',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Flavor',
                'values' => [
                    '6+ months',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Baby Milk Powder & Formulas',
        'attributes'   => [
            [
                'name'         => 'Stage',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Formula Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Preparation Instructions',
            'DHA Content',
        ],
    ],
    [
        'sub_category' => 'Body Cream & Lotion',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Skin Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Facial Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Skin Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'SPF Level',
        ],
    ],
    [
        'sub_category' => 'Eye Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Skin Concerns',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Foot Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Skin Concerns',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Talc',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Cotton Wool & Buds',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Quantity per Pack',
                'values' => [
                    '100% cotton',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Organic Certification',
            'Sterilization Status',
        ],
    ],
    [
        'sub_category' => 'Bath & Wash Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'pH Balance',
        ],
    ],
    [
        'sub_category' => 'Bar Soaps',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name' => 'Allergen Information',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Shower Accessories',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Reusability',
            'Anti-Bacterial Treatment',
        ],
    ],
    [
        'sub_category' => 'Shampoo & Conditioners',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Hair Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Sulfate-Free Status',
        ],
    ],
    [
        'sub_category' => 'Hair Treatment & Masks',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Hair Concerns',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Usage Instructions',
        ],
    ],
    [
        'sub_category' => 'Hair Oil & Spray',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Hair Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hold Level',
        ],
    ],
    [
        'sub_category' => 'Hair Cream & Gel',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Hair Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hold Level',
        ],
    ],
    [
        'sub_category' => 'Colorants',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Color Shade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Ammonia-Free Status',
        ],
    ],
    [
        'sub_category' => 'Toothpaste',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Fluoride Content',
        ],
    ],
    [
        'sub_category' => 'Toothbrush',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Bristle Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Replaceable Head',
        ],
    ],
    [
        'sub_category' => 'Mouthwash',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Alcohol-Free Status',
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
        ],
    ],
    [
        'sub_category' => 'Floss & Other Dental Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Waxed Status',
        ],
    ],
    [
        'sub_category' => 'Feminine Hygiene',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['Regular', 'Super', 'Super Plus'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Absorbency Level',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Fragrance-Free Status',
        ],
    ],
    [
        'sub_category' => "Women's Fragrance & Deodorants",
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Alcohol-Free Status',
        ],
    ],
    [
        'sub_category' => 'Blades & Razors',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Blade Count',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Compatibility',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Replaceable Blades',
        ],
    ],
    [
        'sub_category' => 'Shave Prep & After Shave',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Skin Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Alcohol-Free Status',
        ],
    ],
    [
        'sub_category' => "Men's Fragrance & Deodorants",
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Long-Lasting Formula',
        ],
    ],
    [
        'sub_category' => 'Eyes',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Shade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Waterproof Status',
        ],
    ],
    [
        'sub_category' => 'Face',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Shade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'SPF Level',
        ],
    ],
    [
        'sub_category' => 'Lips',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Shade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Long-Lasting Formula',
        ],
    ],
    [
        'sub_category' => 'Accessories',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Cleaning Instructions',
            'Reusability',
        ],
    ],
    [
        'sub_category' => 'Nails',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Shade',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Quick-Dry Formula',
        ],
    ],
    [
        'sub_category' => 'Makeup Gift Sets',
        'attributes'   => [
            [
                'name'   => 'Set Contents',
                'values' => [
                    'lipstick and mascara',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Limited Edition Status',
            'Gift Packaging',
        ],
    ],
    [
        'sub_category' => "Men's Perfume",
        'attributes'   => [
            [
                'name'         => 'Scent Notes',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Concentration',
            'Limited Edition Status',
        ],
    ],
    [
        'sub_category' => "Women's Perfume",
        'attributes'   => [
            [
                'name'         => 'Scent Notes',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Concentration',
            'Limited Edition Status',
        ],
    ],
    [
        'sub_category' => "Kid's Perfume",
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Alcohol-Free Status',
            'Dermatologist-Tested',
        ],
    ],
    [
        'sub_category' => "Kid's Body Care",
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Hypoallergenic Status',
        ],
    ],
    [
        'sub_category' => 'Home Pharmacy',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Dosage',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Active Ingredients',
            [
                'name' => 'Expiration Date',
                'type' => 'date',
            ],
        ],
    ],
    [
        'sub_category' => 'Sexual Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Features',
            'Certifications',
        ],
    ],
    [
        'sub_category' => 'Hand Wash & Sanitizer',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Alcohol Content',
        ],
    ],
    [
        'sub_category' => 'Bleach, Disinfectants & Wipes',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Kill Rate Percentage',
        ],
    ],
    [
        'sub_category' => 'Glass, Carpet & Polish',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Application Surface',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Scent',
        ],
    ],
    [
        'sub_category' => 'Kitchen Cleaners',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Antibacterial Properties',
        ],
    ],
    [
        'sub_category' => 'Scrubbers & Cloths',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['pieces', 'packs', 'cases'],
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Usage Instructions',
            'Reusability',
        ],
    ],
    [
        'sub_category' => 'Gloves',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Powdered Status',
            'Allergens',
        ],
    ],
    [
        'sub_category' => 'Brushes, Mops & Buckets',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Size',
                'values'       => null,
                'units'        => ['pcs', 'sets'],
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Handle Type',
            'Compatibility',
        ],
    ],
    [
        'sub_category' => 'Toilet & Bathroom Cleaners',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Disinfectant Properties',
        ],
    ],
    [
        'sub_category' => 'Toilet Blocks',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Duration',
            'Colorant',
        ],
    ],
    [
        'sub_category' => 'Dishwashing Liquids',
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Concentration',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Biodegradable Status',
        ],
    ],
    [
        'sub_category' => 'Dishwasher Liquid & Tablet',
        'attributes'   => [
            [
                'name'         => 'Form',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Function',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Phosphate-Free Status',
        ],
    ],
    [
        'sub_category' => 'Food Storage Bags',
        'attributes'   => [
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['Small', 'Medium', 'Large'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Closure Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Freezer-Safe Status',
        ],
    ],
    [
        'sub_category' => 'Aluminium Tray',
        'attributes'   => [
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['pcs', 'packs', 'cases'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Shape',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Oven-Safe Temperature',
            'Lid Included',
        ],
    ],
    [
        'sub_category' => 'Disposables',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Compostable Status',
            'Microwavable',
        ],
    ],
    [
        'sub_category' => 'Cling Film & Wrap',
        'attributes'   => [
            [
                'name'         => 'Width',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Length',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Packaging Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Microwave-Safe Status',
        ],
    ],
    [
        'sub_category' => 'Aluminium Foil',
        'attributes'   => [
            [
                'name'         => 'Width',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Length',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Thickness',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Oven-Safe Temperature',
            'Non-Stick Coating',
        ],
    ],
    [
        'sub_category' => 'Facial Tissue & Wipes',
        'attributes'   => [
            [
                'name'         => 'Ply',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Lotion-Infused Status',
        ],
    ],
    [
        'sub_category' => 'Kitchen Rolls',
        'attributes'   => [
            [
                'name'         => 'Ply',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Sheet Size',
                'values'       => [],
                'units'        => ['sheets', 'rolls', 'packs'],
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Absorbency Level',
            'Recycled Content',
        ],
    ],
    [
        'sub_category' => 'Toilet Rolls',
        'attributes'   => [
            [
                'name'         => 'Ply',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Roll Length',
                'values'       => [],
                'units'        => ['cm', 'm', 'ft'],
                'customizable' => true,
                'type'         => 'number',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Biodegradable Status',
            'Scent',
        ],
    ],
    [
        'sub_category' => 'Garbage Bags & Liners',
        'attributes'   => [
            [
                'name'         => 'Size',
                'values'       => ['Small', 'Medium', 'Large'],
                'units'        => null,
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Thickness',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Material',
            'Biodegradable Status',
        ],
    ],
    [
        'sub_category' => 'Candles',
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Burn Time',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Wick Type',
        ],
    ],
    [
        'sub_category' => 'Spray & Aerosols',
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Function',
                'values' => [
                    '300ml',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Propellant Type',
        ],
    ],
    [
        'sub_category' => 'Car Fresheners',
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Form',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Duration',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Adjustable Intensity',
        ],
    ],
    [
        'sub_category' => 'Air Freshener Gels & Crystals',
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Duration',
                'values' => [
                    '180g',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Refillable Status',
        ],
    ],
    [
        'sub_category' => 'Lights & Bulbs',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Wattage',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Base Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Lumens',
            'Color Temperature',
        ],
    ],
    [
        'sub_category' => 'Batteries',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => ['pcs', 'packs', 'cases'],
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Rechargeable Status',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Capacity',
            'Shelf Life',
        ],
    ],
    [
        'sub_category' => 'Plugs & Extensions',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Number of Outlets',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Cord Length',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Surge Protection',
            'Plug Type',
        ],
    ],
    [
        'sub_category' => 'Power Tools',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Power Source',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Voltage',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Included Accessories',
            'Warranty',
        ],
    ],
    [
        'sub_category' => 'Torches & Emergency Lantern',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Lumens',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Battery Type',
                'values' => [
                    '300 lm',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Runtime',
            'Water Resistance Level',
        ],
    ],
    [
        'sub_category' => 'Washing Powder',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Number of Washes',
        ],
    ],
    [
        'sub_category' => 'Liquids & Concentrates',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Number of Washes',
        ],
    ],
    [
        'sub_category' => 'Fabric Conditioners',
        'attributes'   => [
            [
                'name'         => 'Scent',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'   => 'Concentration',
                'values' => [
                    '1L',
                ],
                'units'        => null,
                'customizable' => true,
                'type'         => 'select',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Softening Technology',
        ],
    ],
    [
        'sub_category' => 'Stain Removers',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Form',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Suitable Fabrics',
        ],
    ],
    [
        'sub_category' => 'Laundry Accessories',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Usage Instructions',
            'reusable',
        ],
    ],
    [
        'sub_category' => 'Insecticides',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Target Pest',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            [
                'name'  => 'Indoor/Outdoor Use',
                'type'  => 'select',
                'value' => ['indoor', 'outdoor'],
            ],
        ],
    ],
    [
        'sub_category' => 'Shoe Polish',
        'attributes'   => [
            [
                'name'         => 'Color',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
            [
                'name'         => 'Form',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Applicator Included',
        ],
    ],
    [
        'sub_category' => 'Plastic Storage & Baskets',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => ['Small', 'Medium', 'Large'],
                'customizable' => true,
                'type'         => 'select',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Stackable Status',
            'Lid Included',
        ],
    ],
    [
        'sub_category' => 'Other Household',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Material',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Usage Instructions',
            'Special Features',
        ],
    ],
    [
        'sub_category' => 'Dog Food',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Life Stage',
            'Special Diet',
        ],
    ],
    [
        'sub_category' => 'Cat Food',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Flavor',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Life Stage',
            'Special Diet',
        ],
    ],
    [
        'sub_category' => 'Other Pet Food',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Species',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Special Diet',
        ],
    ],
    [
        'sub_category' => 'Pet Care',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Pet Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Ingredients',
                'type' => 'long text',
            ],
            'Usage Instructions',
        ],
    ],
    [
        'sub_category' => 'Plants',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Size',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'number',
            ],
            [
                'name'         => 'Pot Included',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            [
                'name' => 'Care Instructions',
                'type' => 'long text',
            ],
            [
                'name'  => 'Indoor/Outdoor Use',
                'type'  => 'select',
                'value' => ['indoor', 'outdoor'],
            ],
        ],
    ],
    [
        'sub_category' => 'Flowers',
        'attributes'   => [
            [
                'name'         => 'Type',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'text',
            ],
            [
                'name'         => 'Stem Count',
                'values'       => [],
                'units'        => null,
                'customizable' => true,
                'type'         => 'long text',
            ],
        ],
        'displayed_attributes' => [],
        'custom_fields'        => [
            'Freshness Guarantee',
            'Vase Included',
        ],
    ],
];
