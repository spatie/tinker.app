<?php

Route::get('/', 'HomeController@startSession');
Route::get('/{sessionId}', 'HomeController@joinSession');
