<?php

namespace Database\Seeders;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use Illuminate\Database\Seeder;

class VehicleMakeSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = [
            'Toyota' => [
                'Corolla'      => ['1.3 GLi', '1.6 Gli', '1.8 Altis', '1.8 Altis Grande', 'X'],
                'Yaris'        => ['1.3 GLi', '1.5 ATIV X'],
                'Camry'        => ['2.5 Hybrid', '3.5 V6'],
                'Fortuner'     => ['2.7 VVTi', '2.8 Sigma4', '2.8 Sigma4 AT', 'Legender'],
                'Prado'        => ['2.7 TX', '3.0 TXL', '4.0 VX'],
                'Land Cruiser' => ['4.0 GX', '4.0 VX', '4.5 ZX', 'GR Sport'],
                'Hilux'        => ['2.4 E', '2.4 G', '2.8 Revo G', '2.8 Revo V'],
                'Rush'         => ['1.5 G', '1.5 S'],
                'Raize'        => ['1.0 G', '1.0 Z'],
                'Vigo'         => ['2.5 E', '2.5 G', '2.7 G', '3.0 V'],
            ],
            'Honda' => [
                'Civic'   => ['1.5 Turbo Oriel', '1.5 Turbo RS', '1.8 i-VTEC Oriel', '1.8 i-VTEC VTi'],
                'City'    => ['1.2 Aspire', '1.5 Aspire', '1.5 RS'],
                'HR-V'    => ['1.8 i-VTEC'],
                'BR-V'    => ['1.5 i-VTEC S', '1.5 i-VTEC V'],
                'Accord'  => ['2.0 i-VTEC', '2.4 i-VTEC'],
                'Freed'   => ['1.5 G', '1.5 RS'],
                'N-Box'   => ['Custom G', 'Custom G Turbo'],
            ],
            'Suzuki' => [
                'Cultus'   => ['VXR', 'VXL', 'AGS'],
                'Swift'    => ['1.2 DLX', '1.2 GLX', '1.3 DLX', 'RS'],
                'Alto'     => ['VX', 'VXR', 'VXL', 'AGS'],
                'Wagon R'  => ['VX', 'VXR', 'VXL'],
                'Jimny'    => ['1.5 GL', '1.5 AT'],
                'Bolan'    => ['Standard'],
                'Ravi'     => ['Standard'],
                'APV'      => ['GL', 'GLX'],
            ],
            'Hyundai' => [
                'Tucson'    => ['1.6 AWD', '2.0 FWD', '2.0 AWD Executive'],
                'Santa Fe'  => ['2.4 AWD', '2.5 HTRAC'],
                'Elantra'   => ['1.6 GL', '2.0 GLS'],
                'Sonata'    => ['2.4 GLS', '2.5 Hybrid'],
                'Ioniq 5'   => ['Standard Range', 'Long Range AWD'],
                'Palisade'  => ['3.8 AWD'],
            ],
            'Kia' => [
                'Sportage'  => ['2.0 FWD Alpha', '2.0 AWD', '1.6 FWD', '1.6 AWD GT Line'],
                'Sorento'   => ['2.4 FWD', '2.5 AWD'],
                'Stonic'    => ['1.4 FWD'],
                'Picanto'   => ['1.0 MT', '1.0 AT'],
                'Carnival'  => ['2.2 SX', '3.5 SXL'],
                'EV6'       => ['Standard', 'Long Range AWD'],
            ],
            'MG' => [
                'HS'    => ['1.5T Excite', '1.5T Exclusive', '2.0T Luxury'],
                'ZS'    => ['1.5 Excite', '1.5 Exclusive'],
                'ZS EV' => ['Long Range'],
                'RX8'   => ['2.0T', '1.5T'],
                'VS'    => ['1.5T'],
            ],
            'Changan' => [
                'Alsvin'    => ['1.5 Comfort', '1.5 Lumiere'],
                'Oshan X7'  => ['1.5T FWD', '1.5T AWD'],
                'Hunter'    => ['2.0T'],
                'CS35 Plus' => ['1.4T'],
                'Karvaan'   => ['1.5 Standard', '1.5 Plus'],
            ],
            'Isuzu' => [
                'D-Max'   => ['1.9 LS', '3.0 LS-A', '3.0 V-Cross'],
                'MU-X'    => ['3.0 LS-U', '3.0 LS-U Premium'],
            ],
            'Mitsubishi' => [
                'Pajero'         => ['3.5 GLS', '3.5 Exceed'],
                'Outlander'      => ['2.4 MIVEC', '2.5 MIVEC AWD'],
                'Eclipse Cross'  => ['1.5 Turbo AWD'],
                'L200'           => ['2.4 Triton GL', '2.4 Triton GSR'],
            ],
            'Nissan' => [
                'Dayz'    => ['S', 'X', 'Highway Star'],
                'Note'    => ['e-Power X', 'e-Power S', 'Nismo'],
                'Leaf'    => ['S', 'SV', 'SL'],
                'Patrol'  => ['4.0 SE', '5.6 LE Titanium'],
                'Navara'  => ['2.5 SL', '2.5 VL'],
            ],
            'Daihatsu' => [
                'Mira'   => ['X', 'L', 'ES', 'Turbo'],
                'Move'   => ['L', 'X', 'Custom RS'],
                'Cast'   => ['Activa', 'Sport', 'Style'],
                'Tanto'  => ['L', 'X', 'Custom'],
                'Rocky'  => ['1.0 Turbo', '1.2'],
                'Hijet'  => ['Cargo', 'Jumbo'],
            ],
            'BMW' => [
                '3 Series' => ['318i', '320i', '330i', 'M340i'],
                '5 Series' => ['520i', '530i', '540i', 'M550i'],
                'X3'       => ['xDrive20i', 'xDrive30i', 'M40i'],
                'X5'       => ['xDrive40i', 'M50i'],
                'X7'       => ['xDrive40i', 'M60i'],
            ],
            'Mercedes-Benz' => [
                'C-Class'  => ['C180', 'C200', 'C300', 'AMG C43'],
                'E-Class'  => ['E200', 'E300', 'E350', 'AMG E53'],
                'GLC'      => ['GLC200', 'GLC300', 'AMG GLC43'],
                'GLE'      => ['GLE300d', 'GLE350d', 'AMG GLE53'],
                'S-Class'  => ['S400d', 'S500', 'S580', 'Maybach S580'],
            ],
            'Audi' => [
                'A3'  => ['1.4 TFSI', '1.8 TFSI', '35 TFSI'],
                'A4'  => ['1.4 TFSI', '35 TFSI', '40 TFSI'],
                'Q5'  => ['45 TFSI', '55 TFSI Quattro'],
                'Q7'  => ['3.0 TFSI Quattro', '55 TFSI Quattro'],
                'Q8'  => ['55 TFSI Quattro'],
            ],
            'Porsche' => [
                'Cayenne'   => ['2.9T', '3.0T', 'GTS', 'Turbo'],
                'Macan'     => ['2.0T', 'S', 'GTS', 'Turbo'],
                'Panamera'  => ['2.9 4S', '4.0 Turbo'],
            ],
            'Land Rover' => [
                'Range Rover'          => ['3.0 HSE', '4.4 Autobiography', '5.0 SVAutobiography'],
                'Range Rover Sport'    => ['3.0 HST', '5.0 SVR'],
                'Range Rover Evoque'   => ['2.0 S', '2.0 HSE', '2.0 R-Dynamic'],
                'Defender'             => ['110 X', '90 X', '130 Outbound'],
            ],
            'Other' => [
                'Other' => [],
            ],
        ];

        foreach ($catalogue as $makeName => $modelsData) {
            $make = VehicleMake::updateOrCreate(
                ['name' => $makeName],
                ['is_active' => true]
            );

            foreach ($modelsData as $modelName => $variantsList) {
                $model = VehicleModel::updateOrCreate(
                    ['make_id' => $make->id, 'name' => $modelName],
                    ['is_active' => true]
                );

                foreach ($variantsList as $variantName) {
                    VehicleVariant::updateOrCreate(
                        ['model_id' => $model->id, 'name' => $variantName],
                        ['is_active' => true]
                    );
                }
            }
        }

        $this->command->info('Vehicle makes, models and variants seeded successfully.');
    }
}
