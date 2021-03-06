<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * @param string $name
     * @param string $displayName
     * @param array  $permissions
     * @param string $description
     * @return $this
     */
    protected function makeRole($name, $displayName, array $permissions = [], $description = '')
    {
        $role = \App\Models\Role::create([
            'name'         => $name,
            'display_name' => $displayName,
            'description'  => $description
        ]);
        foreach ($permissions as $permission) {
            $role->attachPermission(\App\Models\Permission::whereName($permission)->first());
        }
        return $this;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->makeRole('admin', 'Administrator', [
            'user-panels',
            'view-all-datasets',
            'use-all-datasets',
            'submit-dataset',
            'view-datasets',
            'delete-datasets',
            'select-from-datasets',
            'view-selections',
            'remove-selections',
            'download-selections',
            'use-tools',
            'integrate-datasets',
            'view-jobs',
            'view-all-jobs',
            'administer-system',
        ], 'WebApp Administrator')->makeRole('user', 'User', [
            'user-panels',
            'submit-dataset',
            'view-datasets',
            'delete-datasets',
            'select-from-datasets',
            'view-selections',
            'remove-selections',
            'download-selections',
            'use-tools',
            'integrate-datasets',
            'view-jobs',
        ], 'WebApp User');

    }
}
