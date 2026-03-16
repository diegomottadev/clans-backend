<?php

namespace Database\Seeders;

use App\Models\PaymentTeacher;
use Illuminate\Database\Seeder;

class PaymentTeacherSeeder extends Seeder
{
    public function run()
    {
        $pagos = [
            ['fecha' => '2026-03-10', 'id_profesor' => 5,  'monto' => 45000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 5,  'monto' => 45000.00],
            ['fecha' => '2026-05-10', 'id_profesor' => 5,  'monto' => 47500.00],
            ['fecha' => '2026-03-10', 'id_profesor' => 6,  'monto' => 52000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 6,  'monto' => 52000.00],
            ['fecha' => '2026-05-10', 'id_profesor' => 6,  'monto' => 55000.00],
            ['fecha' => '2026-03-10', 'id_profesor' => 7,  'monto' => 48000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 7,  'monto' => 48000.00],
            ['fecha' => '2026-03-10', 'id_profesor' => 9,  'monto' => 42000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 9,  'monto' => 42000.00],
            ['fecha' => '2026-05-10', 'id_profesor' => 9,  'monto' => 44000.00],
            ['fecha' => '2026-03-10', 'id_profesor' => 10, 'monto' => 50000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 10, 'monto' => 50000.00],
            ['fecha' => '2026-03-10', 'id_profesor' => 11, 'monto' => 38000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 11, 'monto' => 38000.00],
            ['fecha' => '2026-05-10', 'id_profesor' => 11, 'monto' => 40000.00],
            ['fecha' => '2026-03-10', 'id_profesor' => 8,  'monto' => 46000.00],
            ['fecha' => '2026-04-10', 'id_profesor' => 8,  'monto' => 46000.00],
        ];

        foreach ($pagos as $pago) {
            PaymentTeacher::create($pago);
        }
    }
}
