@extends('crudbooster::admin_template')
@section('content')
    <!-- Your Page Content Here -->
    <table id='table-apikey' class='table table-striped table-bordered'>
        <thead>
        <tr>
            <th>Module Name</th>
            <th width="10%">Status</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>CMS Menu</td>
            <td>{!! ($cms_menu == 0)?"<span class='label label-success'>Synced</span>":"<span class='label label-default'>Not Synced</span>" !!}</td>
        </tr>
        <tr>
            <td>CMS Menus Privileges</td>
            <td>{!! ($cms_menus_privileges == 0)?"<span class='label label-success'>Synced</span>":"<span class='label label-default'>Not Synced</span>" !!}</td>
        </tr>
        <tr>
            <td>CMS Moduls</td>
            <td>{!! ($cms_moduls == 0)?"<span class='label label-success'>Synced</span>":"<span class='label label-default'>Not Synced</span>" !!}</td>
        </tr>
        <tr>
            <td>CMS Privileges</td>
            <td>{!! ($cms_privileges == 0)?"<span class='label label-success'>Synced</span>":"<span class='label label-default'>Not Synced</span>" !!}</td>
        </tr>
        <tr>
            <td>CMS Privileges Roles</td>
            <td>{!! ($cms_privileges_roles == 0)?"<span class='label label-success'>Synced</span>":"<span class='label label-default'>Not Synced</span>" !!}</td>
        </tr>
        </tbody>
    </table>
    <div class="text-right">
        <button type="button" class="btn btn-success" id="sync-to-file">Sync To File</button>
        <button type="button" class="btn btn-success" id="sync-to-db">Sync To Database</button>
    </div>
    <form action="{{ route('sync-to-file') }}" method="POST" style="display: none" id="post-sync-to-file">
        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
    </form>
    <form action="{{ route('sync-to-db') }}" method="POST" style="display: none" id="post-sync-to-db">
        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function (event) {
            var syncToFile = document.getElementById("post-sync-to-file");
            document.getElementById("sync-to-file").addEventListener("click", function () {
                swal({
                    title: 'Are you sure?',
                    text: "Once you sync to file it will replaced your existing file and cannot be revert!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Sync To file!'
                }, function (result) {
                    if (result) {
                        event.preventDefault();
                        syncToDb.submit();
                    }
                })
            });
            var syncToDb = document.getElementById("post-sync-to-db");
            document.getElementById("sync-to-db").addEventListener("click", function () {
                swal({
                    title: 'Are you sure?',
                    text: "Once you sync to database it will replaced your existing database table and cannot be revert!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Sync To Database!'
                }, function (result) {
                    if (result) {
                        event.preventDefault();
                        syncToDb.submit();
                    }
                })
            });
        });
    </script>
@endsection
