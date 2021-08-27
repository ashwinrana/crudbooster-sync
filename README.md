# Crudbooster Sync Package

This package will help you to sync your laravel Crudbooster used projects 
* CMS Menu, 
* CMS Menus Privileges,
* CMS Moduls,
* CMS Privileges, And
* CMS Privileges Roles

Between developer and team members and can use that as backup.

#### How to install the package
`composer require ashwinrana/crudbooster-sync`

After installing the package, you will have to publish the asset to your public directory

`php artisan vendor:publish --provider=Ashwinrana\CrudboosterSync\CrudboosterSyncServiceProvider`

After you have published the vendor then visit this url

`<URL>/app/sync-management`

Click **Sync to File** in the admin panel, it will download the json file to the published the file.
After you have published the file then you can share the synced file with your team-mates.

Click **Sync to Database** to sync the aboved mentioned tables with the shared sync file.

This file will be automatically tracked by the git but if you want to manually share this file,
it will be available in the given folder path
`app-folder/public/vendor/crudboostersync/data/sync.json`