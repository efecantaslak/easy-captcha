<?php

require_once '../../EasyCaptcha.php';

$captcha = new \efecantaslak\EasyCaptcha\EasyCaptcha();

$captcha->setCaptchaContentType('math');

$captcha->prepare();
$captcha->export('png');
