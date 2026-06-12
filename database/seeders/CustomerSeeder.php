<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Seed the customers table (master data).
     */
    public function run(): void
    {
        // Isian sesuai form: name, phone, email, address
        $customers = [
            [
                'name' => 'Hilmi Prasetya',
                'phone' => '089608089898',
                'email' => 'hilmi@gmail.com',
                'address' => 'Jalan Raya Cileungsi - Jonggol. Cileungsi Kidul, Kec. Cileungsi, Kabupaten Bogor, Jawa Barat, Indonesia',
            ],
            [
                'name' => 'Amir Hutapea',
                'phone' => '089545453434',
                'email' => 'amir@gmail.com',
                'address' => 'Jalan Raya Jakarta - Bogor, Cisalak, Kota Depok, Jawa Barat, Indonesia.',
            ],
            [
                'name' => 'Mail Arifin Ilham',
                'phone' => '089765657878',
                'email' => 'mail@gmail.com',
                'address' => 'Jalan Alternatif Cibubur (Jalan Transyogi), Harjamukti, Kecamatan Tapos, Kota Depok, Jawa Barat, Indonesia',
            ],
            [
                'name' => 'Sada Husein',
                'phone' => '089487656565',
                'email' => 'sada@gmail.com',
                'address' => 'Jalan Pengatin Ali, Ciracas, Jakarta Timur, Indonesia',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['phone' => $customer['phone']],
                $customer
            );
        }
    }
}
