<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    /** Product families managed from the admin catalog. */
    public const TYPES = [
        'electronics' => 'Electronics',
        'stationery'  => 'Stationery',
        'appliance'   => 'Appliance',
    ];

    public const SKU_PREFIX = ['electronics' => 'ELC', 'stationery' => 'STN', 'appliance' => 'APL'];

    /**
     * Type-specific specification inputs: field => [stored label, placeholder].
     * The form renders these and collectSpecs() stores them under the label.
     */
    public const SPEC_FIELDS = [
        'electronics' => [
            'wattage'   => ['Power',    '45W'],
            'voltage'   => ['Voltage',  '100-240V'],
            'battery'   => ['Battery',  '5000mAh'],
            'ports'     => ['Ports',    'USB-C, HDMI'],
            'guarantee' => ['Warranty', '1 year'],
            'color'     => ['Color',    'Black'],
        ],
        'stationery' => [
            'paper_gsm'  => ['GSM',     '80 GSM'],
            'pages'      => ['Pages',   '200'],
            'dimensions' => ['Size',    'A4'],
            'binding'    => ['Binding', 'Spiral'],
            'pack_unit'  => ['Pack',    '12 pcs'],
            'ink_color'  => ['Ink',     'Blue'],
        ],
        'appliance' => [
            'wattage'    => ['Power',        '1200W'],
            'voltage'    => ['Voltage',      '220V / 50Hz'],
            'capacity'   => ['Capacity',     '1.5 L / 7 kg / 12000 BTU'],
            'energy'     => ['Energy rating','5 Star'],
            'dimensions' => ['Dimensions',   '30 x 25 x 40 cm'],
            'weight'     => ['Weight',       '3.2 kg'],
            'color'      => ['Color',        'Silver'],
            'install'    => ['Installation', 'Free / Self'],
        ],
    ];


    /**
     * Extra inputs shown only for matching categories: type => [ 'keyword,keyword' => [field => [label, placeholder]] ].
     * A category matches when its lower-cased name contains any keyword.
     */
    public const CATEGORY_SPECS = [
        'electronics' => [
            'mobile,phone,tablet' => [
                'ram'     => ['RAM', '8 GB'],
                'storage' => ['Storage', '128 GB'],
                'display' => ['Display', '6.5" AMOLED'],
                'camera'  => ['Camera', '50 MP'],
                'os'      => ['OS', 'Android 14'],
            ],
            'laptop,computer,pc' => [
                'cpu'     => ['Processor', 'Core i5 12th Gen'],
                'ram'     => ['RAM', '16 GB'],
                'storage' => ['Storage', '512 GB SSD'],
                'display' => ['Display', '15.6" FHD'],
                'gpu'     => ['Graphics', 'Integrated'],
            ],
            'audio,headphone,earphone,speaker,earbud' => [
                'driver'    => ['Driver', '40 mm'],
                'bluetooth' => ['Bluetooth', '5.3'],
                'playtime'  => ['Playtime', '30 hours'],
                'mic'       => ['Mic', 'Built-in'],
            ],
            'power,charger,cable,adapter' => [
                'output'   => ['Output', '65W PD'],
                'capacity' => ['Capacity', '20000 mAh'],
                'length'   => ['Cable length', '1.5 m'],
            ],
        ],
        'stationery' => [
            'pen,pencil,marker,highlighter' => [
                'tip'      => ['Tip size', '0.7 mm'],
                'ink_type' => ['Ink type', 'Gel'],
                'refill'   => ['Refillable', 'Yes'],
            ],
            'notebook,diary,register,copy,paper' => [
                'ruling'   => ['Ruling', 'Ruled'],
                'cover'    => ['Cover', 'Hardbound'],
                'sheets'   => ['Sheets', '100'],
            ],
            'art,craft,paint,color,colour,sketch' => [
                'medium'   => ['Medium', 'Watercolor'],
                'shades'   => ['Shades', '24'],
                'surface'  => ['Surface', 'Paper'],
            ],
            'bag,box,file,folder,organizer' => [
                'material' => ['Material', 'Polyester'],
                'compart'  => ['Compartments', '3'],
                'capacity' => ['Capacity', '20 L'],
            ],
        ],
        'appliance' => [
            'cool,ac,air,fridge,refrigerator,freezer,fan' => [
                'cooling'  => ['Cooling capacity', '1.5 Ton / 12000 BTU'],
                'inverter' => ['Inverter', 'Yes'],
                'gas'      => ['Refrigerant', 'R32'],
                'noise'    => ['Noise level', '38 dB'],
            ],
            'laundry,wash,dryer' => [
                'load'     => ['Load capacity', '7 kg'],
                'spin'     => ['Spin speed', '1200 RPM'],
                'wtype'    => ['Machine type', 'Front load'],
                'programs' => ['Programs', '15'],
            ],
            'kitchen,cook,oven,blender,fryer,rice,microwave,toaster,kettle,juicer,mixer' => [
                'volume'   => ['Volume', '1.5 L'],
                'programs' => ['Programs', '8'],
                'material' => ['Material', 'Stainless steel'],
                'coating'  => ['Coating', 'Non-stick'],
            ],
            'clean,vacuum,iron,steam' => [
                'suction'  => ['Suction power', '18 kPa'],
                'dustcap'  => ['Dust capacity', '1.2 L'],
                'cordless' => ['Cordless', 'Yes'],
                'attach'   => ['Attachments', '5'],
            ],
            'care,hair,shaver,trimmer,dryer,groom' => [
                'heat'     => ['Heat settings', '3'],
                'attach'   => ['Attachments', '4'],
                'cordless' => ['Cordless', 'Yes'],
            ],
            'light,lamp,led,bulb' => [
                'lumens'   => ['Lumens', '1200 lm'],
                'ctemp'    => ['Color temperature', '4000K'],
                'lifespan' => ['Lifespan', '25000 hours'],
            ],
            'heat,heater,geyser,water' => [
                'heat'     => ['Heat settings', '3'],
                'tank'     => ['Tank size', '30 L'],
                'safety'   => ['Safety', 'Auto cut-off'],
            ],
        ],
    ];

    /** Flat [field => [label, placeholder]] of every category extra for a type. */
    public static function extraFields(string $type): array
    {
        $all = [];
        foreach (self::CATEGORY_SPECS[$type] ?? [] as $fields) {
            foreach ($fields as $k => $def) { $all['x_' . $k] = $def; }
        }
        return $all;
    }

    /** Starter categories created the first time a type is opened in admin. */
    public const STARTER_CATEGORIES = [
        'electronics' => ['Mobile', 'Laptop', 'Audio', 'Power', 'Smart Home', 'Accessories'],
        'stationery'  => ['Pen', 'Notebook', 'Paper', 'Art', 'Office', 'Bag'],
        'appliance'   => ['Kitchen', 'Cooling', 'Laundry', 'Cleaning', 'Personal Care', 'Lighting', 'Heating', 'Small Appliance'],
    ];

    public function getShopRouteAttribute(): string
    {
        return ['electronics' => 'products.electronics', 'stationery' => 'products.stationery', 'appliance' => 'products.appliance'][$this->type] ?? 'products.electronics';
    }

    public function getTypeLabelBnAttribute(): string
    {
        return ['electronics' => 'ইলেক্ট্রনিক্স', 'stationery' => 'স্টেশনারি', 'appliance' => 'হোম অ্যাপ্লায়েন্স'][$this->type] ?? 'পণ্য';
    }

    public static function skuPrefix(string $type): string
    {
        return self::SKU_PREFIX[$type] ?? 'PRD';
    }

    /** Build the specifications array from a request's spec_* inputs. */
    public static function collectSpecs(string $type, \Illuminate\Http\Request $request): array
    {
        $specs = [];
        foreach (self::SPEC_FIELDS[$type] ?? [] as $field => [$label]) {
            $v = trim((string) $request->input('spec_' . $field));
            if ($v !== '') {
                $specs[$label] = $v;
            }
        }
        foreach (self::extraFields($type) as $field => [$label]) {
            $v = trim((string) $request->input('spec_' . $field));
            if ($v !== '') {
                $specs[$label] = $v;
            }
        }
        return $specs;
    }

    public function scopeAppliance($query)
    {
        return $query->where('type', 'appliance');
    }

    protected $fillable = [
        'type',
        'category_id',
        'title',
        'slug',
        'sku',
        'brand',
        'model',
        'summary',
        'description',
        'specifications',
        'price',
        'discount_price',
        'stock',
        'stock_status',
        'cover_image',
        'gallery_images',
        'warranty',
        'badge',
        'rating',
        'reviews_count',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'specifications' => 'array',
        'gallery_images' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock' => 'integer',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'final_price',
        'discount_percent',
        'image_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->title);
                $product->slug = $baseSlug ?: 'item-' . time();
            }
            if (empty($product->sku)) {
                $prefix = self::skuPrefix((string) $product->type);
                $product->sku = $prefix . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function scopeElectronics($query)
    {
        return $query->where('type', 'electronics');
    }

    public function scopeStationery($query)
    {
        return $query->where('type', 'stationery');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->price > 0 && $this->discount_price && $this->discount_price < $this->price) {
            return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->cover_image)) {
            if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
                return $this->cover_image;
            }
            if (str_starts_with($this->cover_image, 'storage/')) {
                return asset($this->cover_image);
            }
            if (str_starts_with($this->cover_image, 'assets/')) {
                return asset($this->cover_image);
            }
            return asset('storage/' . ltrim($this->cover_image, '/'));
        }

        // Elegant default placeholder depending on type
        return asset('assets/images/placeholder-product.svg');
    }

    public function getGalleryListAttribute(): array
    {
        $gallery = is_array($this->gallery_images) ? $this->gallery_images : [];
        $urls = [];
        foreach ($gallery as $img) {
            if (empty($img)) continue;
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, 'assets/')) {
                $urls[] = $img;
            } else {
                $urls[] = asset('storage/' . ltrim($img, '/'));
            }
        }
        return $urls;
    }
}
