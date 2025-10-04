<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1️⃣ Créer les rôles
        $roles = [
            ['name' => 'Admin', 'description' => 'Admin d’un tenant (propriétaire de supermarché)'],
            ['name' => 'Gérant', 'description' => 'Gestion des ventes et produits'],
            ['name' => 'Caissier', 'description' => 'Gestion des ventes'],
            ['name' => 'Super Admin', 'description' => 'Gestion globale du SaaS'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // 2️⃣ Créer ton compte Super Admin (global, sans tenant_id)
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        User::create([
            'tenant_id' => null, // ⚠️ pas de tenant
            'role_id' => $superAdminRole->id,
            'name' => 'Super Admin SaaS',
            'email' => 'superadmin@quira.com',
            'password' => Hash::make('password'), // ⚠️ à changer après installation
            'is_active' => true,
        ]);

        // 3️⃣ Paramètres globaux de la plateforme
        CompanySetting::create([
            'tenant_id' => null, // ⚠️ pas lié à un tenant
            'name'    => 'QUIRA POS',
            'address' => 'Bukavu, RDC',
            'email'   => 'contact@quira.com',
            'phone'   => '099999999',
            'rccm'    => 'RC-RDC-0034A',
            'id_nat'  => '7483945',
            'devise'  => '$',
        ]);
    }
}
