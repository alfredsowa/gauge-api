<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/link', function () {
    Artisan::call('storage:link');
});

Route::get('/test', function () {
    $testing = new DashboardController();
    $testing->saleChannelRatePlotValues('2024-07-01');
});
Route::get('/clear', function() {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
 
    return "Cleared!";
 
 });
 Route::get('/test-email', function () {
    $user = User::find(1);
    $user->notify(new UserWelcomeNotification($user));
    return "Done";
        // return $request->user()->notify(new ActivityErrorNotification($request->user(),'Error notification'));
});