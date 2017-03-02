<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('See homepage');
$I->amOnPage('/');
$I->see('The best place to tell people why they are here');
