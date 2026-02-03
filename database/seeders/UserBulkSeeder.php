<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class UserBulkSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Anton','Renaldi','Aan','Novita','Kikik','Happy','Firdausin','Angga',
            'Lamidi','Syahrul','Heni','Devig','Heri','Robit','Syafiudin',
            'Fida','Joko','Agus','Dana','Wahyu','Irfan','Erly','Ainur','Afif',
            'Firman','Mely','Asroful','Teduh','Khoirul','Training','Shoim',
            'Vita','Rizal','Aris','Rafidah','Asep','Irvan','Eko','Matrudin',
            'Yogi','Deni','Saifudin','Agung','Yunip','Anam','Hermanto','Ayu',
            'Yuwono','Denny','Annasta','Rizki','Zainul','Habib','Ndaru','Bagus',
            'Taufik','Syahril','Khoirudin','Antony','Doni','Rohman','Refangga',
            'Zaini','Musa','Burhanis','Amanda','Dimas','Putut','Dwi','Presca',
            'Alfi','Violita','Melyna','Hendrik','Adel','Ridho','Kholil','Rahmad',
            'Bima','Nanang','Putri','Andik','Qanifah','Masrukin','Sugianto',
            'Misbahudin','Daniel','Ilham','Aldy','Habib','Bintang','Fauzan'
        ];

        $counter = [];

        foreach ($names as $name) {
            $baseUsername = Str::slug(strtolower($name), '_');

            $counter[$baseUsername] = ($counter[$baseUsername] ?? 0) + 1;
            $username = $counter[$baseUsername] > 1
                ? $baseUsername . '_' . $counter[$baseUsername]
                : $baseUsername;

            User::firstOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $username . '@inkalum.com',
                    'password' => Hash::make('admin123'),
                    'role' => 'admin',
                    'department_id' => null
                ]
            );
        }
    }
}
