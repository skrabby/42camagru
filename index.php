<?php
include('router.php');

Route::add('/',function(){
    require_once 'pages/main.php';
});

Route::add('/login',function(){
    require_once 'pages/login.php';
});

Route::add('/id([0-9]*)',function($id){
    require_once 'pages/profile.php';
});

Route::add('/add',function(){
    require_once 'pages/add.php';
});

// Post route example
Route::add('/contact-form',function(){
    echo 'Hey! The form has been sent:<br/>';
    print_r($_POST);
},'post');


Route::run('/');