<?php

include 'LifeCell.php';

// mobile phone in format 380xxxxxxxxx and superPassword
$life = LifeCell::getInstance('380631234567','1234');

//return xml response

$life->getAvailableTariffs(); 

print_r($life->json); 
print_r($life->xml); 

$life->signOut();