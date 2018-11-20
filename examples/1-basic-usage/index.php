<?php

require_once '../../EasyCaptcha.php';

$captcha = new \efecantaslak\EasyCaptcha\EasyCaptcha();

$captcha->prepare();
$captcha->export('png');
