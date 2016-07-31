<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @return $this
     */
    protected function makeUser($name, $email, $password, $role)
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
        ]);
        $user->attachRole(\App\Models\Role::whereName($role)->first());
        return $this;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->makeUser('admin', 'admin@tacitus', '1234', 'admin')
            ->makeUser('user', 'user@tacitus', '1234', 'user');

    }
}
