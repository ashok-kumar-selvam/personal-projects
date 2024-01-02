<?php

namespace App\Commands\Offers;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Getredeemed extends BaseCommand
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
    protected $name = 'offers:getredeemed';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'get the redeemed offers ';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'offers:getredeemed [code] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = ['code'];

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
$db = db_connect();
if(!isset($params[0])) {
$redeemed = $db->table('redeemed_coupons')->join('users', 'users.id = redeemed_coupons.user_id')->get();
$redeemed = $redeemed->getResult();
$string = '';
foreach($redeemed as $offer) {
$string .= $offer->first_name.' redeemed '.$offer->code.' on '.$offer->created_at;
}
CLI::write($string);
}

    }
}
