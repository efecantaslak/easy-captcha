<?php

require_once '../../EasyCaptcha.php';

$captcha = new \efecantaslak\EasyCaptcha\EasyCaptcha();

$captcha->setNoisePercentage(2);

$captcha->prepare();
$captcha->export('png');
