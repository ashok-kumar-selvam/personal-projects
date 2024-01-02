<?php
namespace App\Payments;
class Plans 
{
private $monthly = [
'basic' => [
'name' => 'Basic free plan',
'description' => 'You can create unlimitted exams and receive 151 responses each month.',
'price' => 0,
'credits' => 151,
'features' => ['Unlimited quizzes with responses', 'Unlimited exams and 151 responses', 'Bulk question upload word, txt', 'Unlimited members', '2 groups', 'Unlimited questions', 'more'],
'total_price' => 0,
],
'silver' => [
'name' => 'Silver plan ',
'description' => 'Enjoy the most innovative features of this website and receive 251 responses/results each month. ',
'price' => 599,
'credits' => 251,
'features' => ['Unlimited exams with 251 responses ', 'Email notifications', '10 groups', 'Result analyse', 'Unlimited quizzes with responses', 'Bulk question upload word, txt', 'All features of free plan'],
'total_price' => 7188,
],
'gold' => [
'name' => 'Gold plan',
'description' => 'Most recommended plan to enjoy most features of this website. Get 500 responses with many intrusting features',
'price' => 999,
'credits' => 501,
'features' => ['50 groups', '2 instructor accounts', '501 exam responses', 'Automatic result publish and notification', '100 temporary members', 'all features of Silver plan'],
'total_price' => 11988,
],

];

private $addons = [
[
'credits' => 101,
'price' => 299,
'vallidity' => 365,
'subscribed' => true,
],
[
'credits' => 251,
'price' => 699,
'validity' => 365,
'subscribed' => true,
],
[
'credits' => 501,
'price' => 1299,
'validity' => 365,
'subscribed' => true,
],
[
'credits' => 801,
'price' => 1919,
'validity' => 365,
'subscribed' => false,
],
[
'credits' => 1001,
'price' => 2199,
'validity' => 365,
'subscribed' => false,
],
];

public function getMonthly() {
return $this->monthly;
}

public function getAddon() {
return $this->addons;
}


}
