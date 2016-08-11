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
        $this->makePermission('user-panels', 'View user panels')
            ->makePermission('view-all-datasets', 'View all datasets')
            ->makePermission('use-all-datasets', 'Use all datasets (Delete, Select)')
            ->makePermission('submit-dataset', 'Submit dataset request')
            ->makePermission('view-datasets', 'View owned datasets')
            ->makePermission('delete-datasets', 'Delete datasets')
            ->makePermission('select-from-datasets', 'Run selection tool on dataset')
            ->makePermission('view-selections', 'View selections on dataset')
            ->makePermission('remove-selections', 'Remove selections on dataset')
            ->makePermission('download-selections', 'Download selections on dataset')
            ->makePermission('use-tools', 'Use tools')
            ->makePermission('integrate-datasets', 'Use integration tool')
            ->makePermission('view-jobs', 'View jobs')
            ->makePermission('view-all-jobs', 'View all jobs')
            ->makePermission('administer-system', 'Administer the system');
    }
}
