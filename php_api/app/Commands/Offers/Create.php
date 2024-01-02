<?php

namespace App\Commands\Offers;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Create extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'offers';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'offers:create';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'A command to create new offers.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'offers:create [promo code]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = ['promo code'];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
$required = [
'name' => 'Please provide a name',
'description' => 'Please provide a description', 
'product' => 'Provide the product id',
'target' => 'To whome do you want to provide the offer',
'discount' => 'How many number of percentages do you want to give as a discount? Please only enter in numbers',
'limit' => 'How many people can use this offer code? 0 = unlimited',
'expiry' => 'When the offer ends? 0 = never ends'];


$data = [];
$offers = new \App\Subscriptions\Offers;
$db = db_connect();
// promo code
while(true) {
$data['code'] = CLI::prompt(' Please create a new promo code ');

if($offers->check_code($data['code'])) {
CLI::error(' The promo code is already taken. Please try someother. ');
continue;
}

break;
}
$products = $offers->getProductNames();
$products['all'] = 'For all products. ';


$data['product'] = CLI::promptByKey('Select the product', $products);
$data['discount'] = CLI::prompt("What is the discount? Please provide as a number. You can provide up to $offers->discountLimit percentage", range(5, $offers->discountLimit, 5));
$data['limit'] = (int) CLI::prompt('Please provide how many people can use this offer. 0 = unlimited  ');

while(true) {
$data['expiry'] = CLI::prompt('When the offer closes', '2 days');
if(strtotime($data['expiry']) === false) {
CLI::error(' The time format is wrong. ');
continue;
}
$answer = CLI::prompt('The offer closes on '.date('jS F Y, G:i', strtotime($data['expiry'])).'. Is it right', ['y', 'n']);
if($answer == 'n')
continue;

break;
}
$data['expiry'] = strtotime($data['expiry']);

$data['target'] = CLI::promptByKey('Select the targeted audience', ['all' => 'For everyone', 'user' => 'For particular user']);
if($data['target'] == 'user') {
while(true) {
$email = CLI::prompt('Please provide the email id of the user.');
$user = $db->table('users')->where('email', $email)->get();
$user = $user->getRow();
if(!$user) {
CLI::error(' Unable to find the user with the particular email id. ');
continue;
}

if($user->type != 'admin') {
CLI::error(' You can provide offers only to admins. This seems to be an email id of a member. ');
continue;
}

$data['target'] = $user->id.'@users';
break;
}


}
$descriptions = [
'all' => 'Here is an offer for everyone. Use this promo code ('.$data['code'].' and get '.$data['discount'].'% discount for any product you buy. ',
'user' => 'Best offer just for you! Here is the offer for you. use the promo code '.$data['code'].' to get '.$data['discount'].'% discount on your first purchase of any package. Grab the deal before the offer ends. ',
];
$data['name'] = CLI::prompt('Provide the name for this offer');
$data['description'] = CLI::prompt('Provide the description for the offer', $data['target'] == 'all' ? $descriptions[$data['target']]: $descriptions['user']);

if(!$offers->new($data))
return CLI::error($offers->getError());


return CLI::write(" success");
    }
}
