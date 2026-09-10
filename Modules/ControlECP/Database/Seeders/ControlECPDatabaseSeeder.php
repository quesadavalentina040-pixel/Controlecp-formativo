<?php

namespace Modules\ControlECP\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\SICA\Entities\App;
use Modules\SICA\Entities\Bloque;
use Modules\SICA\Entities\EPS;
use Modules\SICA\Entities\PensionEntity;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\PopulationGroup;
use Modules\SICA\Entities\Role;

class ControlECPDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener o crear Bloque de Apoyo
        $bloqueApoyo = Bloque::firstOrCreate(
            ['slug' => 'apoyos'],
            [
                'name' => 'Procesos de Apoyo',
                'description' => 'Soporte contable, financiero y gestión integral del capital humano.',
                'icon' => 'fas fa-handshake',
                'color' => '#e65100',
                'order_index' => 3
            ]
        );

        // 2. Registrar o actualizar la Aplicación Control ECP
        $app = App::updateOrCreate(
            ['name' => 'Control ECP'],
            [
                'bloque_id' => $bloqueApoyo->id,
                'url' => '/control-ecp',
                'color' => '#FF7F28',
                'icon' => 'fas fa-dove',
                'description' => 'Gestión de momentos, actividades, cronograma, repositorio e inventario para la Escuela Cultura de Paz.',
                'description_english' => 'Management of moments, activities, schedule, repository and inventory for the School of Peace Culture.'
            ]
        );

        // 3. Registrar o actualizar Roles para Control ECP
        $roleAdmin = Role::updateOrCreate(
            ['slug' => 'controlecp.admin'],
            [
                'name' => 'Administrador Control ECP',
                'description' => 'Rol Administrador del aplicativo Control ECP',
                'description_english' => 'Administrator Role of Control ECP application',
                'full_access' => 'No',
                'app_id' => $app->id
            ]
        );

        $roleInstructor = Role::updateOrCreate(
            ['slug' => 'controlecp.instructor'],
            [
                'name' => 'Instructor Control ECP',
                'description' => 'Rol Instructor del aplicativo Control ECP',
                'description_english' => 'Instructor Role of Control ECP application',
                'full_access' => 'No',
                'app_id' => $app->id
            ]
        );

        $roleAprendiz = Role::updateOrCreate(
            ['slug' => 'controlecp.apprentice'],
            [
                'name' => 'Aprendiz Control ECP',
                'description' => 'Rol Aprendiz del aplicativo Control ECP',
                'description_english' => 'Apprentice Role of Control ECP application',
                'full_access' => 'No',
                'app_id' => $app->id
            ]
        );

        // 4. Asegurar entidades base para Personas
        $populationGroup = PopulationGroup::firstOrCreate(['name' => 'NINGUNA']);
        $eps = EPS::firstOrCreate(['name' => 'NO REGISTRA']);
        $pensionEntity = PensionEntity::firstOrCreate(['name' => 'NO REGISTRA']);

        // 5. Registrar o actualizar Personas
        $personAdmin = Person::updateOrCreate(
            ['document_number' => 1000000011],
            [
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'ADMINISTRADOR',
                'first_last_name' => 'CONTROL',
                'second_last_name' => 'ECP',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
                'misena_email' => 'admin.controlecp@misena.edu.co'
            ]
        );

        $personInstructor = Person::updateOrCreate(
            ['document_number' => 1000000012],
            [
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'INSTRUCTOR',
                'first_last_name' => 'CONTROL',
                'second_last_name' => 'ECP',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
                'misena_email' => 'instructor.controlecp@misena.edu.co'
            ]
        );

        $personAprendiz = Person::updateOrCreate(
            ['document_number' => 1000000013],
            [
                'document_type' => 'Cédula de ciudadanía',
                'first_name' => 'APRENDIZ',
                'first_last_name' => 'CONTROL',
                'second_last_name' => 'ECP',
                'eps_id' => $eps->id,
                'population_group_id' => $populationGroup->id,
                'pension_entity_id' => $pensionEntity->id,
                'misena_email' => 'aprendiz.controlecp@misena.edu.co'
            ]
        );

        // 6. Registrar o actualizar Usuarios con sus respectivas contraseñas solicitadas
        // Admin: password 'admin321'
        $userAdmin = User::updateOrCreate(
            ['nickname' => 'admin_controlecp'],
            [
                'person_id' => $personAdmin->id,
                'email' => 'admin@controlecp.com',
                'password' => Hash::make('admin321')
            ]
        );
        $userAdmin->roles()->syncWithoutDetaching([$roleAdmin->id]);

        // Instructor: password 'instru321'
        $userInstructor = User::updateOrCreate(
            ['nickname' => 'instru_controlecp'],
            [
                'person_id' => $personInstructor->id,
                'email' => 'instru@controlecp.com',
                'password' => Hash::make('instru321')
            ]
        );
        $userInstructor->roles()->syncWithoutDetaching([$roleInstructor->id]);

        // Aprendiz: password 'apren321'
        $userAprendiz = User::updateOrCreate(
            ['nickname' => 'apren_controlecp'],
            [
                'person_id' => $personAprendiz->id,
                'email' => 'apren@controlecp.com',
                'password' => Hash::make('apren321')
            ]
        );
        $userAprendiz->roles()->syncWithoutDetaching([$roleAprendiz->id]);
    }
}
