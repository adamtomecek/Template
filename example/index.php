<?php
require_once '../Template.php';

$template = new Template();

/* or
$template = new Template('index.phtml', '@layout.phtml');
*/

$template->title = "Variable example";

$template->array = array(
	'1' => "First array item",
	'2' => "Second array item",
	'n' => "N-th array item",
);

$template->j = 5;

//fluent interface
$template->setFile('index.phtml')->setLayout('@layout.phtml')->render();

/* or
$template->setup('index.phtml', '@layout.phtml')->render();
*/
?>