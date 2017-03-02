<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Login on page');
$I->amOnPage('/user/login');

$I->fillField("Login", "admin");
$I->fillField("Password", "admin");

$I->click("Login");

$I->see("Logout(admin)");


