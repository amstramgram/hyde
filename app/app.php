<?php
require dirname(__DIR__) .'/vendor/autoload.php';

setlocale(LC_ALL, 'fr_FR.UTF-8', 'fr_FR');
ini_set( 'date.timezone', 'Europe/Paris' );
date_default_timezone_set('Europe/Paris');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Slim\Views\PhpRenderer;

use \App\Page;

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH .'/public');

define('LAYOUTS_PATH', PUBLIC_PATH .'/layouts');
define('CONTENT_PATH', PUBLIC_PATH .'/content');

define('DARFTS_PATH', CONTENT_PATH .'/drafts');
define('POSTS_PATH', CONTENT_PATH .'/posts');
define('UPLOADS_PATH', CONTENT_PATH .'/uploads');

define('ROOT_URI', (isset($_SERVER['HTTPS']) ? "https" : "http").'://'.$_SERVER['SERVER_NAME']);

// Instantiate the app
$settings = [
	'settings' => [
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true
	]
];
$app = new \Slim\App($settings);

$container = $app->getContainer();

$container['notFoundHandler'] = function($c) {
	return function ($request, $response) use ($c) {
		$c->phpView->addAttribute('page_title', '404 not found');
		return $c->phpView->render($response, "404.php");
	};
};

$container['page'] = function($c) {
	return new Page($c, ['', 'contact']);
};

$container['phpView'] = function($c) {

	$templateVariables = array(
		"page_title" => "Julien Miclo",
		"baseUrl" => ROOT_URI,
		"posts" => $c->page->getPosts()
	);

	return new PhpRenderer(LAYOUTS_PATH, $templateVariables);
};

$app->add(function(Request $request, Response $response, $next){
	$page = $this->page;
	if( !$page->isPage() ){
		$this->phpView->addAttribute('page_title', $page->getTitle());
	}

	$response = $this->phpView->render($response, "header.php");

	if( $page->isNotFound() ){
		$notFoundHandler = $this->notFoundHandler;
		$response = $notFoundHandler($request, $response);
	}

	if( $page->isPage() ) {
		$response = $next($request, $response);
	}else {
		if( !$page->isNotFound() ){
			$this->phpView->addAttribute('content', $page->getContent());
			if($page->getLayout() === 'gallery') {
				$this->phpView->addAttribute('images', $page->getImages());
			}
			$response = $this->phpView->render($response,  '/types/'. $page->getLayout() .'.php');
		}
	}

	$response = $this->phpView->render($response, 'footer.php');

	return $response;
});

$app->get('/', function ($request, $response, $args) {
	$response = $this->phpView->render($response, 'pages/homepage.php');

	return $response;
})->setName('homepage');

$app->get('/contact', function ($request, $response, $args) {
	$response = $this->phpView->render($response, 'pages/contact.php');

	return $response;
})->setName('contact');

$app->run();