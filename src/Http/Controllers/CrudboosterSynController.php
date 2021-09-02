<?php

namespace Ashwinrana\CrudboosterSync\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class CrudboosterSynController extends Controller
{
    public $file_path = '';

    public function __construct($file_path = '')
    {
        $this->file_path = (!empty($file_path)) ? $file_path : public_path('vendor/crudboostersync/data/sync.json');
    }

    public function index()
    {
        if (!\crocodicstudio\crudbooster\helpers\CRUDBooster::isSuperadmin()) {
            return abort(403, 'Unauthorized action.');
        }
        $file = File::exists($this->file_path);
        if ($file) {
            $content = File::get($this->file_path);
            $data = json_decode($content, true);
            $db_response = $this->loadData();
            if (count($db_response['cms_menu']) !== count($data['cms_menu']) ||
                count($db_response['cms_menus_privileges']) !== count($data['cms_menus_privileges']) ||
                count($db_response['cms_moduls']) !== count($data['cms_moduls']) ||
                count($db_response['cms_privileges']) !== count($data['cms_privileges']) ||
                count($db_response['cms_privileges_roles']) !== count($data['cms_privileges_roles'])) {
                $cms_menu = (count($db_response['cms_menu']) !== count($data['cms_menu'])) ? max(count($db_response['cms_menu']), count($data['cms_menu'])) : 0;
                $cms_menus_privileges = (count($db_response['cms_menus_privileges']) !== count($data['cms_menus_privileges'])) ? max(count($db_response['cms_menus_privileges']), count($data['cms_menus_privileges'])) : 0;
                $cms_moduls = (count($db_response['cms_moduls']) !== count($data['cms_moduls'])) ? max(count($db_response['cms_moduls']), count($data['cms_moduls'])) : 0;
                $cms_privileges = (count($db_response['cms_privileges']) !== count($data['cms_privileges'])) ? max(count($db_response['cms_privileges']), count($data['cms_privileges'])) : 0;
                $cms_privileges_roles = (count($db_response['cms_privileges_roles']) !== count($data['cms_privileges_roles'])) ? max(count($db_response['cms_privileges_roles']), count($data['cms_privileges_roles'])) : 0;
            }
        }

        return view('crudboostersync::index', compact('cms_menu', 'cms_menus_privileges', 'cms_moduls', 'cms_privileges', 'cms_privileges_roles'));
    }

    public function syncToFile(Request $request)
    {
        if (!\crocodicstudio\crudbooster\helpers\CRUDBooster::isSuperadmin()) {
            return abort(403, 'Unauthorized action.');
        }
        $file = File::exists($this->file_path);;
        if ($file) {
            $db_response = $this->loadData();
            try {
                File::put($this->file_path, json_encode($db_response));

                return redirect()->route('crudboostersync')->with('message', 'Synced to file successfully');

            } catch (\Exception $exception) {

                return redirect()->route('crudboostersync')->with('error', $exception->getMessage());

            }

        }
    }

    public function syncToDb(Request $request)
    {
        if (!\crocodicstudio\crudbooster\helpers\CRUDBooster::isSuperadmin()) {
            return abort(403, 'Unauthorized action.');
        }
        $file = File::exists($this->file_path);
        $database_connection = (config('app.default') == null) ? env('DB_CONNECTION') : config('app.default');
        if ($file) {
            $content = File::get($this->file_path);
            $data = json_decode($content, true);
            $db_response = $this->loadData();
            if (array_diff($db_response['cms_menu'], $data['cms_menu']) ||
                array_diff($db_response['cms_menus_privileges'], $data['cms_menus_privileges']) ||
                array_diff($db_response['cms_moduls'], $data['cms_moduls']) ||
                array_diff($db_response['cms_privileges'], $data['cms_privileges']) ||
                array_diff($db_response['cms_privileges_roles'], $data['cms_privileges_roles'])) {
            }

            try {
                DB::beginTransaction();

                //TODO Check if need to run this query or not.
                foreach ($data['cms_menu'] as $menu) {
                    if (DB::table('cms_menus')->where('id', $menu['id'])->exists()) {
                        DB::table('cms_menus')
                            ->where('id', $menu['id'])
                            ->update([
                                'name' => $menu['name'],
                                'type' => $menu['type'],
                                'path' => $menu['path'],
                                'color' => $menu['color'],
                                'icon' => $menu['icon'],
                                'parent_id' => $menu['parent_id'],
                                'is_active' => $menu['is_active'],
                                'is_dashboard' => $menu['is_dashboard'],
                                'id_cms_privileges' => $menu['id_cms_privileges'],
                                'sorting' => $menu['sorting'],
                                'created_at' => $menu['created_at'],
                                'updated_at' => Carbon::now()
                            ]);
                    } else {
                        DB::insert('INSERT INTO cms_menus (id, name, type, path, color, icon, parent_id, is_active, is_dashboard, id_cms_privileges, sorting, created_at, updated_at )
                                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                            $menu['id'], $menu['name'], $menu['type'], $menu['path'], $menu['color'], $menu['icon'], $menu['parent_id'], $menu['is_active'], $menu['is_dashboard'], $menu['id_cms_privileges'], $menu['sorting'], $menu['created_at'], Carbon::now()
                        ]);
                    }
                }

                //TODO Check if need to run this query or not.
                foreach ($data['cms_menus_privileges'] as $cms_menus_privilege) {
                    if (DB::table('cms_menus_privileges')->where('id', $cms_menus_privilege['id'])->exists()) {
                        DB::table('cms_menus_privileges')
                            ->where('id', $cms_menus_privilege['id'])
                            ->update([
                                'id_cms_menus' => $cms_menus_privilege['id_cms_menus'],
                                'id_cms_privileges' => $cms_menus_privilege['id_cms_privileges']
                            ]);
                    } else {
                        DB::insert('INSERT INTO cms_menus_privileges (id, id_cms_menus, id_cms_privileges )
                                        VALUES (?,?,?)', [
                            $cms_menus_privilege['id'], $cms_menus_privilege['id_cms_menus'], $cms_menus_privilege['id_cms_privileges']
                        ]);
                    }
                }

                //TODO Check if need to run this query or not.
                foreach ($data['cms_moduls'] as $cms_modul) {
                    if (DB::table('cms_moduls')->where('id', $cms_modul['id'])->exists()) {
                        DB::table('cms_moduls')
                            ->where('id', $cms_modul['id'])
                            ->update([
                                'name' => $cms_modul['name'],
                                'icon' => $cms_modul['icon'],
                                'path' => $cms_modul['path'],
                                'table_name' => $cms_modul['table_name'],
                                'controller' => $cms_modul['controller'],
                                'is_protected' => $cms_modul['is_protected'],
                                'is_active' => $cms_modul['is_active'],
                                'created_at' => $cms_modul['created_at'],
                                'updated_at' => Carbon::now()
                            ]);
                    } else {
                        DB::insert('INSERT INTO cms_moduls (id, name, icon, path, table_name, controller, is_protected, is_active, created_at, updated_at )
                                        VALUES (?,?,?,?,?,?,?,?,?,?)', [
                            $cms_modul['id'], $cms_modul['name'], $cms_modul['icon'], $cms_modul['path'], $cms_modul['table_name'], $cms_modul['controller'], $cms_modul['is_protected'], $cms_modul['is_active'], $cms_modul['created_at'], Carbon::now()
                        ]);
                    }
                }

                //TODO Check if need to run this query or not.
                foreach ($data['cms_privileges'] as $cms_privilege) {
                    if (DB::table('cms_privileges')->where('id', $cms_privilege['id'])->exists()) {
                        DB::table('cms_privileges')
                            ->where('id', $cms_privilege['id'])
                            ->update([
                                'name' => $cms_privilege['name'],
                                'is_superadmin' => $cms_privilege['is_superadmin'],
                                'theme_color' => $cms_privilege['theme_color'],
                                'created_at' => $cms_privilege['created_at'],
                                'updated_at' => Carbon::now()
                            ]);
                    } else {
                        DB::insert('INSERT INTO cms_privileges (id, name, is_superadmin, theme_color, created_at, updated_at )
                                        VALUES (?,?,?,?,?,?)', [
                            $cms_privilege['id'], $cms_privilege['name'], $cms_privilege['is_superadmin'], $cms_privilege['theme_color'], $cms_privilege['created_at'], Carbon::now()
                        ]);
                    }
                }

                //TODO Check if need to run this query or not.
                foreach ($data['cms_privileges_roles'] as $cms_privileges_role) {
                    if (DB::table('cms_privileges_roles')->where('id', $cms_privileges_role['id'])->exists()) {
                        DB::table('cms_privileges_roles')
                            ->where('id', $cms_privileges_role['id'])
                            ->update([
                                'is_visible' => $cms_privileges_role['is_visible'],
                                'is_create' => $cms_privileges_role['is_create'],
                                'is_read' => $cms_privileges_role['is_read'],
                                'is_edit' => $cms_privileges_role['is_edit'],
                                'is_delete' => $cms_privileges_role['is_delete'],
                                'id_cms_privileges' => $cms_privileges_role['id_cms_privileges'],
                                'id_cms_moduls' => $cms_privileges_role['id_cms_moduls'],
                                'created_at' => empty($cms_privileges_role['created_at']) ? $cms_privileges_role['created_at'] : Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                    } else {
                        DB::insert('INSERT INTO cms_privileges_roles (id, is_visible, is_create, is_read, is_edit, is_delete, id_cms_privileges, id_cms_moduls, created_at, updated_at )
                                        VALUES (?,?,?,?,?,?,?,?,?,?)', [
                            $cms_privileges_role['id'], $cms_privileges_role['is_visible'], $cms_privileges_role['is_create'], $cms_privileges_role['is_read'], $cms_privileges_role['is_edit'], $cms_privileges_role['is_delete'], $cms_privileges_role['id_cms_privileges'], $cms_privileges_role['id_cms_moduls'], $cms_privileges_role['created_at'], Carbon::now()
                        ]);
                    }
                }

                // Update the Auto Increment value for pgsql
                if ($database_connection == "pgsql") {
                    DB::statement("SELECT setval('cms_menus_id_seq', COALESCE((SELECT MAX(id)+1 FROM cms_menus), 1), false)");
                    DB::statement("SELECT setval('cms_menus_privileges_id_seq', COALESCE((SELECT MAX(id)+1 FROM cms_menus_privileges), 1), false)");
                    DB::statement("SELECT setval('cms_moduls_id_seq', COALESCE((SELECT MAX(id)+1 FROM cms_moduls), 1), false)");
                    DB::statement("SELECT setval('cms_privileges_id_seq', COALESCE((SELECT MAX(id)+1 FROM cms_privileges), 1), false)");
                    DB::statement("SELECT setval('cms_privileges_roles_id_seq', COALESCE((SELECT MAX(id)+1 FROM cms_privileges_roles), 1), false)");
                }

                DB::commit();

                return redirect()->route('crudboostersync')->with('message', 'Synced to database successfully');

            } catch (\Exception $exception) {
                DB::rollBack();

                return redirect()->route('crudboostersync')->with('error', $exception->getMessage());

            }
        } else {
            return redirect()->route('crudboostersync')->with('message', 'File Not Found');
        }
    }

    public function loadData()
    {
        if (!\crocodicstudio\crudbooster\helpers\CRUDBooster::isSuperadmin()) {
            return abort(403, 'Unauthorized action.');
        }
        $cms_menus_database = DB::table('cms_menus')->get();
        $cms_menu_json = [];

        foreach ($cms_menus_database as $cms_menu) {
            $cms_menus['id'] = $cms_menu->id;
            $cms_menus['name'] = $cms_menu->name;
            $cms_menus['type'] = $cms_menu->type;
            $cms_menus['path'] = $cms_menu->path;
            $cms_menus['color'] = $cms_menu->color;
            $cms_menus['icon'] = $cms_menu->icon;
            $cms_menus['parent_id'] = $cms_menu->parent_id;
            $cms_menus['is_active'] = $cms_menu->is_active;
            $cms_menus['is_dashboard'] = $cms_menu->is_dashboard;
            $cms_menus['id_cms_privileges'] = $cms_menu->id_cms_privileges;
            $cms_menus['sorting'] = $cms_menu->sorting;
            $cms_menus['created_at'] = $cms_menu->created_at;
            $cms_menus['updated_at'] = $cms_menu->updated_at;
            array_push($cms_menu_json, $cms_menus);
        }

        $cms_menus_privileges = DB::table('cms_menus_privileges')->get();
        $cms_menus_privileges_json = [];

        foreach ($cms_menus_privileges as $cms_menus_privilege) {
            $cms_menus_privileges_array['id'] = $cms_menus_privilege->id;
            $cms_menus_privileges_array['id_cms_menus'] = $cms_menus_privilege->id_cms_menus;
            $cms_menus_privileges_array['id_cms_privileges'] = $cms_menus_privilege->id_cms_privileges;
            array_push($cms_menus_privileges_json, $cms_menus_privileges_array);
        }

        $cms_moduls = DB::table('cms_moduls')->get();
        $cms_moduls_json = [];

        foreach ($cms_moduls as $cms_modul) {
            $cms_moduls_array['id'] = $cms_modul->id;
            $cms_moduls_array['name'] = $cms_modul->name;
            $cms_moduls_array['icon'] = $cms_modul->icon;
            $cms_moduls_array['path'] = $cms_modul->path;
            $cms_moduls_array['table_name'] = $cms_modul->table_name;
            $cms_moduls_array['controller'] = $cms_modul->controller;
            $cms_moduls_array['is_protected'] = $cms_modul->is_protected;
            $cms_moduls_array['is_active'] = $cms_modul->is_active;
            $cms_moduls_array['created_at'] = $cms_modul->created_at;
            array_push($cms_moduls_json, $cms_moduls_array);
        }

        $cms_privileges = DB::table('cms_privileges')->get();
        $cms_privilege_json = [];

        foreach ($cms_privileges as $cms_privilege) {
            $cms_privilege_array['id'] = $cms_privilege->id;
            $cms_privilege_array['name'] = $cms_privilege->name;
            $cms_privilege_array['is_superadmin'] = $cms_privilege->is_superadmin;
            $cms_privilege_array['theme_color'] = $cms_privilege->theme_color;
            $cms_privilege_array['created_at'] = $cms_privilege->created_at;
            array_push($cms_privilege_json, $cms_privilege_array);
        }

        $cms_privileges_roles = DB::table('cms_privileges_roles')->get();
        $cms_privileges_roles_array = [];

        foreach ($cms_privileges_roles as $cms_privileges_role) {
            $cms_menus_privileges_roles_array['id'] = $cms_privileges_role->id;
            $cms_menus_privileges_roles_array['is_visible'] = $cms_privileges_role->is_visible;
            $cms_menus_privileges_roles_array['is_create'] = $cms_privileges_role->is_create;
            $cms_menus_privileges_roles_array['is_read'] = $cms_privileges_role->is_read;
            $cms_menus_privileges_roles_array['is_edit'] = $cms_privileges_role->is_edit;
            $cms_menus_privileges_roles_array['is_delete'] = $cms_privileges_role->is_delete;
            $cms_menus_privileges_roles_array['id_cms_privileges'] = $cms_privileges_role->id_cms_privileges;
            $cms_menus_privileges_roles_array['id_cms_moduls'] = $cms_privileges_role->id_cms_moduls;
            $cms_menus_privileges_roles_array['created_at'] = $cms_privileges_role->created_at;
            array_push($cms_privileges_roles_array, $cms_menus_privileges_roles_array);
        }

        return [
            'cms_menu' => $cms_menu_json,
            'cms_menus_privileges' => $cms_menus_privileges_json,
            'cms_moduls' => $cms_moduls_json,
            'cms_privileges' => $cms_privilege_json,
            'cms_privileges_roles' => $cms_privileges_roles_array,
        ];
    }

}
