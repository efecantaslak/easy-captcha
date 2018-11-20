<?php

require_once '../../EasyCaptcha.php';

$captcha = new \efecantaslak\EasyCaptcha\EasyCaptcha();

$captcha->setCanvasBackground([95, 76, 45]);
$captcha->setCanvasTextColor([255, 245, 200]);
$captcha->setNoiseColor([72, 142, 12]);

$captcha->prepare();
$captcha->export('png');
