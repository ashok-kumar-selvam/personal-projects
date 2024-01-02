<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

$routes->environment('development', function($routes) {

$routes->get('test', 'Home::test');
$routes->get('/commands', 'Home::commands');
$routes->get('/emails', 'Home::getEmails');
$routes->get('/emails/(:any)', 'Home::getEmail/$1');
});

$routes->group('payments', function($routes) {
$routes->post('success', 'Admin\Plans::success');
$routes->post('failure', 'Admin\Plans::failure');
$routes->post('webhook', 'Admin\Plans::webhook');
$routes->get('status', 'Admin\Plans::status');
});


// auth routes
$routes->group('', function($routes) {
$routes->post('/login', 'Users::login');
$routes->post('/forgot', 'Users::forgot');
$routes->get('/forgot', 'Users::forgot');
$routes->post('/otp', 'Users::otp');
$routes->post('/newpassword', 'Users::new_password');
$routes->post('/register', 'Users::register');
$routes->post('/verifyemail', 'Users::verify_email');
$routes->get('/resendemail/(:any)', 'Users::resend_email/$1');
$routes->post('/users/anonymous', 'Users\AnonymousUsers::create');


});

// anonymous routes
$routes->group('anonymous', function($routes) {
$routes->post('checkemail', 'Accounts\Anonymous::checkEmail');
$routes->post('checkotp', 'Accounts\Anonymous::checkOtp');
$routes->get('results', 'Common\Common::anonymous_results');
});

//common routes
$routes->get('seterror', 'Common\Common::createError');
$routes->get('/files/(:any)', 'Common\Files::show/$1');
$routes->post('/files', 'Common\Files::create');
$routes->post('/contact-us', 'Home::contact_us');
$routes->get('/results/(:any)', 'Common\Common::showResult/$1');
$routes->post('/unsubscribe', 'Common\Common::unsubscribe');

// quizzes 
$routes->group('quizzes', function($routes) {
$routes->get('checkinfo/(:any)', 'Common\Quizzes::checkinfo/$1');
$routes->post('save', 'Common\Quizzes::save');
$routes->get('result/(:any)', 'Common\Quizzes::result/$1');
$routes->get('(:any)', 'Common\Quizzes::show/$1');
}); // end of quizzes 

// exams 
$routes->group('exams', function($routes) {
$routes->get('checkinfo/(:any)', 'Common\Exams::checkinfo/$1');
$routes->get('start/(:any)', 'Common\Exams::start/$1');
$routes->get('resume/(:any)', 'Common\Exams::resume/$1');
$routes->post('finish', 'Common\Exams::finish');
$routes->get('evaluate/(:any)', 'Common\Exams::setResult/$1');
$routes->get('(:any)', 'Common\Exams::show/$1');
}); 

// answers 
$routes->group('answers', function($routes) {
$routes->get('marked/(:any)', 'Common\Answers::marked/$1');
$routes->get('all/(:any)', 'Common\Answers::all/$1');
$routes->put('/', 'Common\Answers::update');
$routes->get('(:any)', 'Common\Answers::show/$1');
}); 

// end of all common routes


// member routes
$routes->group('', ['filter' => 'member'], function($routes) {
$routes->get('/dashboard', 'Home::dashboard');
$routes->resource('join', ['controller' => '\App\Controllers\Member\Join']);
$routes->get('/check_pending', 'Users::check_pending');
$routes->get('/quizzes', 'Member\All::quizzes');
$routes->get('/exams', 'Member\All::exams');
$routes->get('/results', 'Member\All::results');
$routes->put('/notifications/(:any)', 'Member\Member::updateNotification/$1');
});
// end of member routes 

$routes->group('',  ['filter' => 'admin'], function($routes) {

$routes->get('/admin', 'Home::admin');

// Assignments
$routes->get('/admin/assignments/(:any)/results', 'Admin\Assignments::results/$1');
$routes->get('/admin/assignments/assignees', 'Admin\Assignments::assignees');
$routes->post('/admin/assignments/activate', 'Admin\Assignments::activate');
$routes->post('/admin/assignments/publish', 'Admin\Assignments::publish');
$routes->resource('admin/assignments');


// results
$routes->get('/admin/results/reevaluate/(:any)', 'Admin\Results::reevaluate/$1');
$routes->put('/admin/results/point', 'Admin\Results::point');
$routes->put('/admin/results/additional', 'Admin\Results::additional');
$routes->post('/admin/results/setcomplete/(:any)', 'Admin\Results::setComplete/$1');
$routes->post('/admin/results/review', 'Admin\Results::add_review');
$routes->resource('admin/results');

// quizzes
$routes->get('/admin/quizzes/results/(:any)', 'Admin\Quizzes::view_result/$1');
$routes->get('/admin/quizzes/(:any)/results', 'Admin\Quizzes::all_results/$1');
$routes->put('/admin/quizzes/publish/(:any)', 'Admin\Quizzes::publish/$1');
$routes->resource('admin/quizzes');

$routes->get('/admin/groups/members/(:any)', 'Admin\Groups::get_members/$1');
$routes->post('/admin/groups/members', 'Admin\Groups::add_members');
$routes->delete('/admin/groups/members/(:any)/(:any)', 'Admin\Groups::remove_member/$1/$2');
$routes->resource('admin/groups');
$routes->get('admin/members/getinvite', 'Admin\Members::getInvite');
$routes->post('admin/members/upload', 'Admin\Members::upload');
$routes->resource('admin/members');

$routes->post('/admin/questions/upload', 'Admin\Questions::upload');
$routes->get('/admin/questions/bank/(:any)', 'Admin\Questions::bank/$1');
$routes->delete('/admin/questions/clear/(:any)', 'Admin\Questions::clear/$1');
$routes->post('/admin/questions/remove', 'Admin\Questions::remove');
$routes->post('/admin/questions/add', 'Admin\Questions::add');
$routes->get('/admin/questions/getcount', 'Admin\Questions::getCount');
$routes->resource('admin/questions');


//exams 
$routes->delete('/admin/exams/clearquestions/(:any)', 'Admin\Exams::clear_questions/$1');
$routes->resource('admin/exams');
$routes->resource('admin/notifications');

//$routes->resource('admin/payments');

$routes->get('/admin/plans/premium', 'Admin\Plans::getPremiumPlans');
$routes->get('/admin/plans/premium/(:any)', 'Admin\Plans::getPremium/$1');
$routes->post('/admin/plans/coupon', 'Admin\Plans::applyCoupon');
$routes->post('/admin/plans/checkout', 'Admin\Plans::checkout');

$routes->post('/admin/instructors/activate', 'Admin\Instructors::activate');
$routes->resource('admin/instructors');

$routes->group('/admin/profile', function($routes) {
$routes->get('account', 'Admin\Profile::getAccount');
$routes->post('account', 'Admin\Profile::setAccount');
$routes->post('password', 'Admin\Profile::setPassword');
$routes->get('plans', 'Admin\Profile::getPlans');
$routes->post('plans/(:any)', 'Admin\Profile::setPlan/$1');
$routes->get('offers', 'Admin\Profile::getOffers');
$routes->get('preferences', 'Admin\Profile::getPreferences');
$routes->post('preferences', 'Admin\Profile::setPreferences');

});

});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
