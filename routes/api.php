<?php

Route::get('customers', 'ApiController@getCustomers');
Route::post('customers', 'ApiController@postCustomer');
Route::get('customers/{customer_id}', 'ApiController@getCustomer');
Route::put('customers/{customer_id}', 'ApiController@putCustomer');
Route::delete('customers/{customer_id}', 'ApiController@deleteCustomer');
Route::get('reports', 'ApiController@getReports');
Route::post('reports', 'ApiController@postReport');
Route::get('reports/{report_id}', 'ApiController@getReport');
Route::put('reports/{report_id}', 'ApiController@putReport');
Route::delete('reports/{report_id}', 'ApiController@deleteReport');
