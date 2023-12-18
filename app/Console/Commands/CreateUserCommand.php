<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // TODO
        $user['name'] = $this->ask('Enter the name of the new user');
        $user['email'] = $this->ask('Enter the email of the new user');
        $user['password'] = $this->secret('Enter the password of the new user');

        $roleName = $this->choice('Role of the new user', ['admin', 'editor'], 1);

        $role = Role::where('name', $roleName)->first();

        $validator = Validator::make( $user, [
            'name' => ['required', 'string','max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', Password::defaults()]
        ]);

        if( $validator->fails() ) {
            foreach( $validator->errors()->all() as $error ) {
                $this->error( $error );
            }

            return -1;
        }

        if( !$role ) {
            $this->error('Role not found');

            return -1;
        }

        DB::transaction( function() use ($user, $role) {
            $new_user = User::create( $user );
            $new_user->roles()->attach( $role->id );
        });


        $this->info('User has been created');

        return 0;

    }
}
