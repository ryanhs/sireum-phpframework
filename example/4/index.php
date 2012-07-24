<?php
require '../../sireum.php';


$sireum->add('index', function() use($sireum){
	$sireum->form->addRule('test', 'test', 'required', $method = 'get');
	var_dump($sireum->form->validation(), $sireum->form->getError());
});