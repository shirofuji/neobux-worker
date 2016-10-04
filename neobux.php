<?php

require(dirname(__FILE__).'/vendor/autoload.php');
$capabilities = [
		'browserName'       => 'firefox',
		'platform'          => 'ANY',
		'browserVersion'    => '9',
		'browser'           => 'firefox',
		'name'              => 'Automation',
		'deviceOrientation' => 'portrait',
		'acceptSslCerts'	=> true,
		'proxy'				=> ['proxyType'=>'manual','httpProxy'=>'161.68.250.139:80']
];
$driver = new \Behat\Mink\Driver\Selenium2Driver('firefox');
$session = new \Behat\Mink\Session($driver);

$session->start();

$start_time = time();

$session->visit('https://www.neobux.com/m/l');
$page = $session->getPage();

file_put_contents('sample1.png',$session->getScreenshot());

while($page->find('css','#Kf1') != null){
	$test = [];
	$page->find('css','#Kf1')->setValue('username');
	$page->find('css','#Kf2')->setValue('password');
	if($page->find('css','img[height=24]')){
		$captcha_field = $page->find('css','#Kf3');
		$captcha_answer = '';
		// write captcha to file
		$base64_image = $page->find('css','img[height=24]')->getAttribute('src');
		$file = fopen('captcha.png','wb');
		$data = explode(',',$base64_image);
		fwrite($file,base64_decode($data[1]));
		fclose($file);
		// convert image to grayscale
		$im = imagecreatefrompng('captcha.png');
		if($im  && imagefilter($im,IMG_FILTER_GRAYSCALE)){
			imagepng($im,'captcha_gray.png');
		}else{
			die('Unable to convert image to grayscale.');
		}
		imagedestroy($im);
		// execute ocr
		sleep(1);
		$answer = exec('sh /var/www/html/test/improve.sh',$test);
		sleep(1);
		print_r($test);
		$page->find('css','#Kf3')->setValue(str_replace(' ','',$test[0]));
	}
	$page->find('css','#botao_login')->click();
	
	$session->visit('https://www.neobux.com/m/l');
	$page= $session->getPage();
}


$session->visit('https://www.neobux.com/m/v');
$page = $session->getPage();

if($page->find('css','#Kf1') != null){
	// restart script	
	exit();
}else{
	echo 'Starting adClick',PHP_EOL;
	for($i = 1; $i<30; $i++){
		echo 'ad #',$i,PHP_EOL;
		if($page->find('css','#da'.$i.'a')){
			$page->find('css','#da'.$i.'a')->click();
			$page->find('css','#da'.$i.'c a')->click();
			sleep(15);
			$windowNames = $session->getWindowNames();
			if(count($windowNames) > 1){
				$session->switchToWindow($windowNames[1]);
				$session->executeScript('window.close()');
				$session->switchToWindow($windowNames[0]);
			}
		}else{
			break;
		}
	}
	
	echo 'Click adPrize',PHP_EOL;
	$session->visit('https://www.neobux.com/m/v');
	$page = $session->getPage();
	
	$page->find('css','#ap_ctr div')->click();
	$windowNames = $session->getWindowNames();
	if(count($windowNames) > 1){
		$session->switchToWindow($windowNames[1]);
		$page = $session->getPage();
		sleep(15);
		$ads = $page->find('css','#rmnDv')->getText();
		for($i = $ads; $i>0; $i--){
			echo 'Remaining ads (',$i,')',PHP_EOL;
			$page->find('css','#nxt_bt_a')->click();
			sleep(15);
		}
	}
	
	echo 'Logging out',PHP_EOL;
	$session->switchToWindow($windowNames[0]);
	$page = $session->getPage();
	
	//$page->find('css','#ubar_wl td:nth-child(3) a')->click();
}

file_put_contents('sample2.png',$session->getScreenshot());

$session->stop();

$end_time = time();

echo 'Cycle completed within ',($end_time - $start_time),' seconds.',PHP_EOL;
sleep(300);
exit();
