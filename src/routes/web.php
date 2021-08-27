<?php

use Ashwinrana\CrudboosterSync\Http\Controllers\CrudboosterSynController;

Route::group(['middleware' => ['web']], function () {
    Route::get('app/sync-management', [CrudboosterSynController::class, 'index'])->name('crudboostersync');
    Route::post('app/sync-management/sync-to-file', [CrudboosterSynController::class, 'syncToFile'])->name('sync-to-file');
    Route::post('app/sync-management/sync-to-db', [CrudboosterSynController::class, 'syncToDb'])->name('sync-to-db');
});
