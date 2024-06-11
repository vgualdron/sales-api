<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
                        AuthController,
                        ZoneController,
                        RoleController,
                        PermissionController,
                        YardController,
                        UserController,
                        MaterialController,
                        ThirdController,
                        AdjustmentController,
                        RateController,
                        TicketController,
                        SynchronizationController,
                        MaterialSettlementController,
                        FreightSettlementController,
                        ReportController,
                        MovementController,
                        BatterieController,
                        OvenController,
                    };

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', function (Request $request) {
    return 'Health...';
});

Route::group(["prefix" => "/auth"], function () {
    Route::get('/get-active-token', [AuthController::class, 'getActiveToken'])->name('auth.getActiveToken');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::middleware(['middleware' => 'auth:api'])->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/zone"], function () {
    Route::get('/list', [ZoneController::class, 'list'])->middleware('can:zone.list')->name('zone.list');
    Route::post('/create', [ZoneController::class, 'create'])->middleware('can:zone.create')->name('zone.create');
    Route::put('/update/{id}', [ZoneController::class, 'update'])->middleware('can:zone.update')->name('zone.update');
    Route::delete('/delete/{id}', [ZoneController::class, 'delete'])->middleware('can:zone.delete')->name('zone.delete');
    Route::get('/get/{id}', [ZoneController::class, 'get'])->middleware('can:zone.get')->name('zone.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/role"], function () {
    Route::get('/list', [RoleController::class, 'list'])->middleware('can:role.list')->name('role.list');
    Route::post('/create', [RoleController::class, 'create'])->middleware('can:role.create')->name('role.create');
    Route::put('/update/{id}', [RoleController::class, 'update'])->middleware('can:role.update')->name('role.update');
    Route::delete('/delete/{id}', [RoleController::class, 'delete'])->middleware('can:role.delete')->name('role.delete');
    Route::get('/get/{id}', [RoleController::class, 'get'])->middleware('can:role.get')->name('role.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/permission"], function () {
    Route::get('/list', [PermissionController::class, 'list'])->name('permission.list');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/yard"], function () {
    Route::get('/list/{yard}/{displayAll}', [YardController::class, 'list'])->middleware('can:yard.list')->name('yard.list');
    Route::post('/create', [YardController::class, 'create'])->middleware('can:yard.create')->name('yard.create');
    Route::put('/update/{id}', [YardController::class, 'update'])->middleware('can:yard.update')->name('yard.update');
    Route::delete('/delete/{id}', [YardController::class, 'delete'])->middleware('can:yard.delete')->name('yard.delete');
    Route::get('/get/{id}', [YardController::class, 'get'])->middleware('can:yard.get')->name('yard.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/user"], function () {
    Route::get('/list/{displayAll}', [UserController::class, 'list'])->middleware('can:user.list')->name('user.list');
    Route::post('/create', [UserController::class, 'create'])->middleware('can:user.create')->name('user.create');
    Route::put('/update/{id}', [UserController::class, 'update'])->middleware('can:user.update')->name('user.update');
    Route::delete('/delete/{id}', [UserController::class, 'delete'])->middleware('can:user.delete')->name('user.delete');
    Route::get('/get/{id}', [UserController::class, 'get'])->middleware('can:user.get')->name('user.get');
    Route::put('/updateProfile/{id}', [UserController::class, 'updateProfile'])->middleware('can:user.updateProfile')->name('user.updateProfile');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/material"], function () {
    Route::get('/list/{displayAll}/{material}', [MaterialController::class, 'list'])->middleware('can:material.list')->name('material.list');
    Route::post('/create', [MaterialController::class, 'create'])->middleware('can:material.create')->name('material.create');
    Route::put('/update/{id}', [MaterialController::class, 'update'])->middleware('can:material.update')->name('material.update');
    Route::delete('/delete/{id}', [MaterialController::class, 'delete'])->middleware('can:material.delete')->name('material.delete');
    Route::get('/get/{id}', [MaterialController::class, 'get'])->middleware('can:material.get')->name('material.get');
    Route::get('/getMaterialsByYard/{yard}', [MaterialController::class, 'getMaterialsByYard'])->name('material.getMaterialsByYard');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/third"], function () {
    Route::get('/list/{displayAll}/{type}/{third}/{origin}/{startDate}/{finalDate}', [ThirdController::class, 'list'])->middleware('can:third.list')->name('third.list');
    Route::post('/create', [ThirdController::class, 'create'])->middleware('can:third.create')->name('third.create');
    Route::post('/createInBatch', [ThirdController::class, 'createInBatch'])->middleware('can:third.createInBatch')->name('third.createInBatch');
    Route::put('/update/{id}', [ThirdController::class, 'update'])->middleware('can:third.update')->name('third.update');
    Route::delete('/delete/{id}', [ThirdController::class, 'delete'])->middleware('can:third.delete')->name('third.delete');
    Route::get('/get/{id}', [ThirdController::class, 'get'])->middleware('can:third.get')->name('third.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/adjustment"], function () {
    Route::get('/list', [AdjustmentController::class, 'list'])->middleware('can:adjustment.list')->name('adjustment.list');
    Route::post('/create', [AdjustmentController::class, 'create'])->middleware('can:adjustment.create')->name('adjustment.create');
    Route::put('/update/{id}', [AdjustmentController::class, 'update'])->middleware('can:adjustment.update')->name('adjustment.update');
    Route::delete('/delete/{id}', [AdjustmentController::class, 'delete'])->middleware('can:adjustment.delete')->name('adjustment.delete');
    Route::get('/get/{id}', [AdjustmentController::class, 'get'])->middleware('can:adjustment.get')->name('adjustment.get');
    Route::post('/createFromProccess', [AdjustmentController::class, 'createFromProccess'])->name('adjustment.create');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/rate"], function () {
    Route::get('/list', [RateController::class, 'list'])->middleware('can:rate.list')->name('rate.list');
    Route::post('/create', [RateController::class, 'create'])->middleware('can:rate.create')->name('rate.create');
    Route::put('/update/{id}', [RateController::class, 'update'])->middleware('can:rate.update')->name('rate.update');
    Route::delete('/delete/{id}', [RateController::class, 'delete'])->middleware('can:rate.delete')->name('rate.delete');
    Route::get('/get/{id}', [RateController::class, 'get'])->middleware('can:rate.get')->name('rate.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/ticket"], function () {
    Route::get('/list', [TicketController::class, 'list'])->middleware('can:ticket.list')->name('ticket.list');
    Route::post('/create', [TicketController::class, 'create'])->middleware('can:ticket.create')->name('ticket.create');
    Route::put('/update/{id}', [TicketController::class, 'update'])->middleware('can:ticket.update')->name('ticket.update');
    Route::delete('/delete/{id}', [TicketController::class, 'delete'])->middleware('can:ticket.delete')->name('ticket.delete');
    Route::get('/get/{id}', [TicketController::class, 'get'])->middleware('can:ticket.get')->name('ticket.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/synchronization"], function () {
    Route::post('/synchronize', [SynchronizationController::class, 'synchronize'])->middleware('can:synchronization.synchronize')->name('synchronization.synchronize');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/materialSettlement"], function () {
    Route::get('/list', [MaterialSettlementController::class, 'list'])->middleware('can:materialSettlement.list')->name('materialSettlement.list');
    Route::get('/getTickets/{type}/{startDate}/{finalDate}/{third}/{material}/{materialType}', [MaterialSettlementController::class, 'getTickets'])->middleware('can:materialSettlement.settle')->name('materialSettlement.getTickets');
    Route::post('/settle', [MaterialSettlementController::class, 'settle'])->middleware('can:materialSettlement.settle')->name('materialSettlement.settle');
    Route::get('/print/{id}', [MaterialSettlementController::class, 'print'])->middleware('can:materialSettlement.print')->name('materialSettlement.print');
    Route::get('/get/{id}', [MaterialSettlementController::class, 'get'])->middleware('can:materialSettlement.get')->name('materialSettlement.get');
    Route::put('/addInformation/{id}', [MaterialSettlementController::class, 'addInformation'])->middleware('can:materialSettlement.addInformation')->name('materialSettlement.addInformation');
    Route::get('/getTickets/{id}', [MaterialSettlementController::class, 'getData'])->name('materialSettlement.getData');
    Route::get('/validateMovements/{id}', [MaterialSettlementController::class, 'validateMovements'])->name('materialSettlement.validateMovements');
    Route::put('/update/{id}', [MaterialSettlementController::class, 'update'])->middleware('can:materialSettlement.update')->name('materialSettlement.update');
    Route::delete('/delete/{id}', [MaterialSettlementController::class, 'delete'])->middleware('can:materialSettlement.delete')->name('materialSettlement.delete');
    Route::get('/getSettledTickets/{id}', [MaterialSettlementController::class, 'getSettledTickets'])->name('materialSettlement.getSettledTickets');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/freightSettlement"], function () {
    Route::get('/list', [FreightSettlementController::class, 'list'])->middleware('can:freightSettlement.list')->name('freightSettlement.list');
    Route::get('/getTickets/{startDate}/{finalDate}/{convenyorCompany}', [FreightSettlementController::class, 'getTickets'])->middleware('can:freightSettlement.settle')->name('FreightSettlementController.getTickets');
    Route::post('/settle', [FreightSettlementController::class, 'settle'])->middleware('can:freightSettlement.settle')->name('freightSettlement.settle');
    Route::get('/print/{id}', [FreightSettlementController::class, 'print'])->middleware('can:freightSettlement.print')->name('freightSettlement.print');
    Route::get('/get/{id}', [FreightSettlementController::class, 'get'])->middleware('can:freightSettlement.get')->name('freightSettlement.get');
    Route::put('/addInformation/{id}', [FreightSettlementController::class, 'addInformation'])->middleware('can:freightSettlement.addInformation')->name('freightSettlement.addInformation');
    Route::get('/getTickets/{id}', [FreightSettlementController::class, 'getData'])->name('freightSettlement.getData');
    Route::get('/validateMovements/{id}', [FreightSettlementController::class, 'validateMovements'])->name('freightSettlement.validateMovements');
    Route::put('/update/{id}', [FreightSettlementController::class, 'update'])->middleware('can:freightSettlement.update')->name('freightSettlement.update');
    Route::delete('/delete/{id}', [FreightSettlementController::class, 'delete'])->middleware('can:freightSettlement.delete')->name('freightSettlement.delete');
    Route::get('/getSettledTickets/{id}', [FreightSettlementController::class, 'getSettledTickets'])->name('freightSettlement.getSettledTickets');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/report"], function () {
    Route::get('/movements/{movement}/{startDate}/{finalDate}/{originYard}/{destinyYard}/{material}', [ReportController::class, 'movements'])->middleware('can:report.movements')->name('report.movements');
    Route::get('/yardStock/{date}', [ReportController::class, 'yardStock'])->middleware('can:report.yardStock')->name('report.yardStock');
    Route::get('/completeTransfers/{startDate}/{finalDate}/{originYard}/{destinyYard}', [ReportController::class, 'completeTransfers'])->middleware('can:report.completeTransfers')->name('report.completeTransfers');
    Route::get('/uncompleteTransfers/{startDate}/{finalDate}/{originYard}/{destinyYard}', [ReportController::class, 'uncompleteTransfers'])->middleware('can:report.uncompleteTransfers')->name('report.uncompleteTransfers');
    Route::get('/unbilledPurchases/{startDate}/{finalDate}/{supplier}/{material}', [ReportController::class, 'unbilledPurchases'])->middleware('can:report.unbilledPurchases')->name('report.unbilledPurchases');
    Route::get('/unbilledSales/{startDate}/{finalDate}/{customer}/{material}', [ReportController::class, 'unbilledSales'])->middleware('can:report.unbilledSales')->name('report.unbilledSales');
    Route::get('/unbilledFreights/{startDate}/{finalDate}/{conveyorCompany}/{material}', [ReportController::class, 'unbilledFreights'])->middleware('can:report.unbilledFreights')->name('report.unbilledFreights');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/movement"], function () {
    Route::get('/list', [MovementController::class, 'list'])->middleware('can:movement.list')->name('movement.list');
    Route::get('/getTickets/{startDate}/{finalDate}', [MovementController::class, 'getTickets'])->middleware('can:movement.getTickets')->name('movement.getTickets');
    Route::get('/create/{startDate}/{finalDate}/{tickets}', [MovementController::class, 'create'])->middleware('can:movement.create')->name('movement.create');
    Route::delete('/delete/{id}', [MovementController::class, 'delete'])->middleware('can:movement.delete')->name('movement.delete');
    Route::get('/print/{id}', [MovementController::class, 'print'])->middleware('can:movement.print')->name('movement.print');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/batterie"], function () {
    Route::get('/list', [BatterieController::class, 'list'])->middleware('can:batterie.list')->name('batterie.list');
    Route::post('/create', [BatterieController::class, 'create'])->middleware('can:batterie.create')->name('batterie.create');
    Route::put('/update/{id}', [BatterieController::class, 'update'])->middleware('can:batterie.update')->name('batterie.update');
    Route::delete('/delete/{id}', [BatterieController::class, 'delete'])->middleware('can:batterie.delete')->name('batterie.delete');
    Route::get('/get/{id}', [BatterieController::class, 'get'])->middleware('can:batterie.get')->name('batterie.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/oven"], function () {
    Route::get('/list', [OvenController::class, 'list'])->middleware('can:oven.list')->name('oven.list');
    Route::post('/create', [OvenController::class, 'create'])->middleware('can:oven.create')->name('oven.create');
    Route::put('/update/{id}', [OvenController::class, 'update'])->middleware('can:oven.update')->name('oven.update');
    Route::delete('/delete/{id}', [OvenController::class, 'delete'])->middleware('can:oven.delete')->name('oven.delete');
    Route::get('/get/{id}', [OvenController::class, 'get'])->middleware('can:oven.get')->name('oven.get');
});