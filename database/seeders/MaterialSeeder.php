<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            ['name' => 'OPC Cement (53 Grade)', 'unit' => 'Bags', 'description' => 'Ordinary Portland Cement for structural works'],
            ['name' => 'PPC Cement', 'unit' => 'Bags', 'description' => 'Portland Pozzolana Cement for plastering and brickwork'],
            ['name' => 'TMT Steel Bars (8mm - 32mm)', 'unit' => 'Tonnes', 'description' => 'Thermo Mechanically Treated bars for reinforcement'],
            ['name' => 'River Sand', 'unit' => 'Cu.Ft', 'description' => 'Fine aggregate for mortar and concrete'],
            ['name' => 'M-Sand (Manufactured Sand)', 'unit' => 'Cu.Ft', 'description' => 'Alternative to river sand for concrete'],
            ['name' => 'Coarse Aggregate (20mm)', 'unit' => 'Cu.Ft', 'description' => 'Crushed stone for RCC work'],
            ['name' => 'Coarse Aggregate (40mm)', 'unit' => 'Cu.Ft', 'description' => 'Crushed stone for PCC work'],
            ['name' => 'Red Clay Bricks (Class A)', 'unit' => 'Nos', 'description' => 'Standard burnt clay building bricks'],
            ['name' => 'Fly Ash Bricks', 'unit' => 'Nos', 'description' => 'Eco-friendly machine-made bricks'],
            ['name' => 'AAC Blocks', 'unit' => 'Nos', 'description' => 'Autoclaved Aerated Concrete lightweight blocks'],
            ['name' => 'Binding Wire', 'unit' => 'Kg', 'description' => 'For tying TMT reinforcement bars'],
            ['name' => 'Shuttering Plywood (12mm)', 'unit' => 'Sq.Ft', 'description' => 'Film faced plywood for formwork'],
            ['name' => 'Vitrified Tiles (2x2 ft)', 'unit' => 'Sq.Ft', 'description' => 'Standard flooring tiles'],
            ['name' => 'Ceramic Tiles (1x1 ft)', 'unit' => 'Sq.Ft', 'description' => 'Anti-skid tiles for bathrooms'],
            ['name' => 'Granite Slabs', 'unit' => 'Sq.Ft', 'description' => 'For kitchen counters and stairs'],
            ['name' => 'Wall Putty', 'unit' => 'Kg', 'description' => 'For smooth wall finishing before painting'],
            ['name' => 'Emulsion Paint (Interior)', 'unit' => 'Liters', 'description' => 'Interior acrylic emulsion'],
            ['name' => 'Weather Coat Paint (Exterior)', 'unit' => 'Liters', 'description' => 'Exterior weather-proof paint'],
            ['name' => 'PVC Pipes (4 inch)', 'unit' => 'Feet', 'description' => 'For drainage and sewage'],
            ['name' => 'CPVC Pipes (1 inch)', 'unit' => 'Feet', 'description' => 'For hot/cold water supply'],
            ['name' => 'Teak Wood', 'unit' => 'Cu.Ft', 'description' => 'For main doors and premium frames'],
            ['name' => 'Sal Wood', 'unit' => 'Cu.Ft', 'description' => 'For window and door frames'],
            ['name' => 'Aluminium Sections', 'unit' => 'Kg', 'description' => 'For sliding windows and partitions'],
            ['name' => 'Electrical Wires (2.5 sq.mm)', 'unit' => 'Coils', 'description' => 'Copper wires for power circuits'],
            ['name' => 'Waterproofing Chemical', 'unit' => 'Liters', 'description' => 'For terrace and bathroom waterproofing']
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate(
                ['name' => $material['name']],
                ['unit' => $material['unit'], 'description' => $material['description']]
            );
        }
    }
}
