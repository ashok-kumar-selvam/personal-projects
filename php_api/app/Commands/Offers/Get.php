<?php

namespace App\Commands\Offers;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Get extends BaseCommand
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
    protected $name = 'offers:get';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Get all available offers or particular offer. ';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'offers:get [promo code] [options]';

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
$offers = new \App\Subscriptions\Offers;

if(!isset($params[0])) {
$all = $offers->all();
if(count($all) <= 0)
return CLI::write(' There are no promo codes available. ');

$code = CLI::prompt('Which promo code to show', array_column($all, 'code'));
} else {
$code = $params[0];
}

$plan = $offers->getOffer($code);
if(!$plan)
return CLI::error($offers->getError());
$string = "";
foreach($plan as $key => $value) 
$string .= "$key : $value".PHP_EOL;


return CLI::write($string);

    }
}
