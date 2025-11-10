php artisan migrate
php artisan make:filament-user
php artisan tinker 

use Spatie\Permission\Models\Role;

Role::create(['name' => 'admin']);
Role::create(['name' => 'organizer']);
Role::create(['name' => 'verifier']);
Role::create(['name' => 'attender']);

use App\Models\User;
$user = User::find(1);
$user->assignRole('admin');