<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * @param string $name
     * @param string $displayName
     * @param string $description
     * @return $this
     */
    protected function makePermission($name, $displayName, $description = '')
    {
        \App\Models\Permission::create([
            'name'         => $name,
            'display_name' => $displayName,
            'description'  => $description
        ]);
        return $this;
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
    }
}
