<?php

// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require_once LIBS_DIR . '/Nette096/loader.php';


//IMPORTANT - uncomment next line on production server if it has database problems
//Environment::setMode('production', true);
//Environment::setMode('production', false);



// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable();

// 2b) enable RobotLoader - this allows load all classes automatically
$loader = new /*Nette\Loaders\*/RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->addDirectory(LIBS_DIR);
$loader->register();


// Step 3: Configure application
$application = Environment::getApplication();
Environment::loadConfig();

//RoutingDebugger::enable();

// mod_rewrite detection
//this is the magic working like .htacces, but much better
$router = $application->getRouter();

$router[] = new Route('index.php', array(
    'presenter' => 'SuggesterTest',
    'action' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<action>/<id>', array(
    'presenter' => 'SuggesterTest',
    'action' => 'default',
    'id' => NULL,
));

header('Content-Type: text/html;charset=utf-8');

Form::extensionMethod('Form::addSuggestInput', 'SuggestInput::addSuggestInput');
//Form::extensionMethod('addSuggestInput', 'SuggestInput::addSuggestInput'); since PHP 5.3

// Step 4: Run the application!
$application->run();
