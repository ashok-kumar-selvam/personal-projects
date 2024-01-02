<?php
function primary_key($data, $model) {
helper('text');
$key = 0;
while(true) {
$key = random_string('alnum', 64);
if(!$model->find($key))
break;

}
$data['data']['id'] = $key;
return $data;
}
