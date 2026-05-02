<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DreamHomeSeeder extends Seeder
{
    public function run(): void
    {
        // ─── BRANCH OFFICES ──────────────────────────────────────────
        DB::table('branch_office')->insertOrIgnore([
            ['branch_no' => 1, 'street' => '163 Main Street', 'area' => 'Patrick',   'city' => 'Glasgow',   'postcode' => 'G11 9QX', 'telephone_no' => '0141-339-2178', 'fax_no' => '0141-339-2179'],
            ['branch_no' => 2, 'street' => '22 Deer Road',    'area' => 'Sidcup',    'city' => 'London',    'postcode' => 'DO1 2PP',  'telephone_no' => '0171-886-1212', 'fax_no' => '0171-886-1213'],
            ['branch_no' => 3, 'street' => '16 Argyll Street','area' => 'Dyce',      'city' => 'Aberdeen',  'postcode' => 'AB2 3SU',  'telephone_no' => '01224-67125',   'fax_no' => null],
        ]);

        // ─── STAFF ───────────────────────────────────────────────────
        DB::table('staff')->insertOrIgnore([
            // Glasgow branch – manager
            ['staff_no' => 1, 'first_name' => 'John',   'last_name' => 'White',  'address' => '19 Taylor St, Cranford, London', 'telephone_no' => '0171-884-5112', 'sex' => 'Male',   'date_of_birth' => '1945-10-01', 'NIN' => 'WK442011B', 'position' => 'Manager',    'salary' => 30000, 'date_joined' => '1988-10-24', 'branch_no' => 1, 'supervisor_staff_no' => null],
            // Glasgow branch – supervisor
            ['staff_no' => 2, 'first_name' => 'Susan',  'last_name' => 'Brand',  'address' => '5 Novar Drive, Glasgow',         'telephone_no' => '0141-339-5544', 'sex' => 'Female', 'date_of_birth' => '1975-03-12', 'NIN' => 'AB123456C', 'position' => 'Supervisor', 'salary' => 24000, 'date_joined' => '2000-06-01', 'branch_no' => 1, 'supervisor_staff_no' => 1],
            // Glasgow branch – staff (handles leases)
            ['staff_no' => 3, 'first_name' => 'Ann',    'last_name' => 'Beech',  'address' => '12 Dale Road, Hyndland, Glasgow','telephone_no' => '0141-334-7788', 'sex' => 'Female', 'date_of_birth' => '1980-07-22', 'NIN' => 'CD789012D', 'position' => 'Staff',      'salary' => 18000, 'date_joined' => '2005-02-14', 'branch_no' => 1, 'supervisor_staff_no' => 2],
            // London branch – manager
            ['staff_no' => 4, 'first_name' => 'David',  'last_name' => 'Ford',   'address' => '63 Park Road, Sidcup, London',   'telephone_no' => '0171-886-9900', 'sex' => 'Male',   'date_of_birth' => '1968-11-05', 'NIN' => 'EF345678E', 'position' => 'Manager',    'salary' => 32000, 'date_joined' => '1995-03-15', 'branch_no' => 2, 'supervisor_staff_no' => null],
            // London branch – staff
            ['staff_no' => 5, 'first_name' => 'Mary',   'last_name' => 'Howe',   'address' => '8 Baker Street, London',         'telephone_no' => '0171-550-3312', 'sex' => 'Female', 'date_of_birth' => '1990-05-30', 'NIN' => 'GH901234F', 'position' => 'Staff',      'salary' => 17000, 'date_joined' => '2018-08-01', 'branch_no' => 2, 'supervisor_staff_no' => 4],
        ]);

        // ─── OWNERS ──────────────────────────────────────────────────
        DB::table('owner')->insertOrIgnore([
            ['owner_no' => 1, 'full_name' => 'Claire Adams',  'address' => '47 Elm Street, Glasgow',          'telephone_no' => '0141-445-2211'],
            ['owner_no' => 2, 'full_name' => 'Robert Hughes', 'address' => '12 Oak Avenue, London',           'telephone_no' => '0171-556-8833'],
            ['owner_no' => 3, 'full_name' => 'Patricia Finn', 'address' => '3 Cedar Close, Aberdeen',         'telephone_no' => '01224-88990'],
            ['owner_no' => 4, 'full_name' => 'James Loch',    'address' => '29 Birch Road, Glasgow',          'telephone_no' => '0141-778-1100'],
        ]);

        // ─── PROPERTIES FOR RENT ─────────────────────────────────────
        DB::table('property_for_rent')->insertOrIgnore([
            // Glasgow properties
            ['property_no' => 1, 'street' => '6 Lawrence St',  'area' => 'Patrick',  'city' => 'Glasgow', 'postcode' => 'G11 9QX', 'property_type' => 'Flat',  'no_of_rooms' => 3, 'monthly_rent' => 350.00, 'rental_status' => 'available', 'owner_no' => 1, 'branch_no' => 1, 'staff_no' => 3],
            ['property_no' => 2, 'street' => '2 Manor Road',   'area' => 'Parkhead', 'city' => 'Glasgow', 'postcode' => 'G32 4QX', 'property_type' => 'Flat',  'no_of_rooms' => 3, 'monthly_rent' => 375.00, 'rental_status' => 'available', 'owner_no' => 1, 'branch_no' => 1, 'staff_no' => 3],
            ['property_no' => 3, 'street' => '18 Dale Road',   'area' => 'Hyndland', 'city' => 'Glasgow', 'postcode' => 'G12 0BT', 'property_type' => 'House', 'no_of_rooms' => 5, 'monthly_rent' => 600.00, 'rental_status' => 'rented',    'owner_no' => 4, 'branch_no' => 1, 'staff_no' => 2],
            ['property_no' => 4, 'street' => '5 Novar Drive',  'area' => 'Hyndland', 'city' => 'Glasgow', 'postcode' => 'G12 9AX', 'property_type' => 'Flat',  'no_of_rooms' => 4, 'monthly_rent' => 450.00, 'rental_status' => 'reserved',  'owner_no' => 4, 'branch_no' => 1, 'staff_no' => 2],
            // London properties
            ['property_no' => 5, 'street' => '14 Maple Close', 'area' => 'Sidcup',   'city' => 'London',  'postcode' => 'DO1 4AA', 'property_type' => 'Flat',  'no_of_rooms' => 2, 'monthly_rent' => 800.00, 'rental_status' => 'available', 'owner_no' => 2, 'branch_no' => 2, 'staff_no' => 5],
            ['property_no' => 6, 'street' => '9 Victoria Road','area' => 'Bromley',  'city' => 'London',  'postcode' => 'BR1 2PQ', 'property_type' => 'House', 'no_of_rooms' => 4, 'monthly_rent' => 1200.00,'rental_status' => 'available', 'owner_no' => 2, 'branch_no' => 2, 'staff_no' => 5],
            // Aberdeen property
            ['property_no' => 7, 'street' => '2 Argyll Street','area' => 'Dyce',     'city' => 'Aberdeen','postcode' => 'AB2 1XY', 'property_type' => 'Flat',  'no_of_rooms' => 2, 'monthly_rent' => 320.00, 'rental_status' => 'available', 'owner_no' => 3, 'branch_no' => 3, 'staff_no' => 1],
        ]);

        // ─── RENTERS ─────────────────────────────────────────────────
        DB::table('renter')->insertOrIgnore([
            ['renter_no' => 1, 'first_name' => 'Mike',    'last_name' => 'Ritchie',  'address' => '18 Tain Street, Gourock, PA16 1YQ', 'telephone_no' => '01475-392178', 'preferred_type' => 'House', 'preferred_location' => 'Glasgow', 'max_rent' => 750.00,  'staff_no' => 3, 'branch_no' => 1],
            ['renter_no' => 2, 'first_name' => 'Tina',    'last_name' => 'Murphy',   'address' => '33 High Street, Glasgow, G1 1AA',   'telephone_no' => '0141-330-4456', 'preferred_type' => 'Flat',  'preferred_location' => 'Glasgow', 'max_rent' => 400.00,  'staff_no' => 3, 'branch_no' => 1],
            ['renter_no' => 3, 'first_name' => 'Alan',    'last_name' => 'Stewart',  'address' => '21 Park Avenue, London, SE1 2BB',   'telephone_no' => '0171-229-3344', 'preferred_type' => 'Flat',  'preferred_location' => 'London',  'max_rent' => 900.00,  'staff_no' => 5, 'branch_no' => 2],
            ['renter_no' => 4, 'first_name' => 'Rachel',  'last_name' => 'Green',    'address' => '7 Central Park, London, W1 5CC',    'telephone_no' => '0171-555-7788', 'preferred_type' => 'House', 'preferred_location' => 'London',  'max_rent' => 1500.00, 'staff_no' => 5, 'branch_no' => 2],
            ['renter_no' => 5, 'first_name' => 'Carlos',  'last_name' => 'Diaz',     'address' => '4 Westfield Road, Aberdeen, AB1 9DD','telephone_no' => '01224-44556',  'preferred_type' => 'Flat',  'preferred_location' => 'Aberdeen', 'max_rent' => 350.00,  'staff_no' => 1, 'branch_no' => 3],
        ]);

        // ─── VIEWINGS ─────────────────────────────────────────────────
        DB::table('viewing')->insertOrIgnore([
            ['property_no' => 4, 'renter_no' => 2, 'viewing_date' => '2024-03-10', 'comments' => 'Very spacious, liked the kitchen layout.'],
            ['property_no' => 1, 'renter_no' => 2, 'viewing_date' => '2024-03-12', 'comments' => 'Convenient location, but needs repainting.'],
            ['property_no' => 5, 'renter_no' => 3, 'viewing_date' => '2024-04-01', 'comments' => 'Good size for a couple.'],
            ['property_no' => 6, 'renter_no' => 4, 'viewing_date' => '2024-04-03', 'comments' => 'Excellent garden, close to school.'],
            ['property_no' => 7, 'renter_no' => 5, 'viewing_date' => '2024-04-10', 'comments' => 'Quiet area, suits working from home.'],
        ]);

        // ─── LEASE AGREEMENTS ─────────────────────────────────────────
        DB::table('lease_agreement')->insertOrIgnore([
            // Property 3 is rented
            ['lease_no' => 1, 'start_date' => '2024-01-01', 'end_date' => '2024-12-31', 'duration' => 12, 'deposit' => 600.00, 'deposit_paid' => 'Yes', 'payment_method' => 'Bank Transfer', 'property_no' => 3, 'renter_no' => 1, 'staff_no' => 3],
        ]);

        $this->command->info('DreamHome seed data inserted successfully!');
    }
}