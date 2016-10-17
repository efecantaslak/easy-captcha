<?php

/**
 * Created by PhpStorm.
 * User: Efecan Taslak
 * Date: 15.10.2016
 * Time: 19:53
 */

namespace Efot;

class ETCaptcha {

    protected $canvas;
    protected $canvasWidth = 120;
    protected $canvasHeight = 40;
    protected $canvasBackground;
    protected $canvasTextColor;
    protected $frontLinesCount = 2;
    protected $behindLinesCount = 2;
    protected $noisePercentage = 6;
    protected $noiseColor;
    protected $contentType = 'code';
    protected $characterList = 'ABCDEFGHIJKLMNOPRSTUWXVYZabcdefghijklmnoprstuwxvyz1234567890';
    protected $canvasText;
    protected $answer;
    protected $lastError;
    protected $errorHistory = array();
    protected $language = 'en';
    protected $availableLanguages = array(
        'en', 'tr'
    );
    protected $errorList = array(
        'en' => array(
            'Color is wrong type. Eg: array(255, 255, 255)',
            'Captcha content type is wrong type. Must be \'code\' or \'math\'.',
            'Invalid language selection.',
            'Noise percentage must be between 0 and 100',
            'Character list must be string type.',
            'Wrong export type. Only can be base64, png and jpeg.'
        ),
        'tr' => array(
            'Renk yanlış tipte. Ör: array(255, 255, 255)',
            'Yanlış captcha içerik tipi. Yalnızca \'code\' veya \'math\' olabilir.',
            'Geçersiz dil seçimi.',
            'Gürültü yüzdesi 0 ve 100 aralığında olmak zorundadır.',
            'Karakter listesi string tipinde olmak zorundadır.',
            'Hatalı dışarı çıktı tipi. Yalnızca base64, png ve jpeg olabilir.'
        )
    );

    public function setCanvasBackground($color = array()) {
        if($this->checkRGBValue($color)) {
            $this->canvasBackground = $color;
        } else {
            $this->getError(0);
        }
    }

    protected function setRandomCanvasBackground() {
        $this->canvasBackground = array(rand(0, 255), rand(0, 255), rand(0, 255));
    }

    public function setCanvasTextColor($color = array()) {
        if($this->checkRGBValue($color)) {
            $this->canvasTextColor = $color;
        } else {
            $this->getError(0);
        }
    }

    protected function setRandomCanvasTextColor() {
        if($this->isDark($this->canvasBackground)) {
            $this->canvasTextColor = array(rand(0, 64), rand(0, 64), rand(0, 64));
        } else {
            $this->canvasTextColor = array(rand(192, 255), rand(192, 255), rand(192, 255));
        }
    }

    protected function isDark($color = array()) {
        if(is_array($color) && count($color) == 3) {
            $total = $color[0] + $color[1] + $color[2];
            if(($total / 3) > 128) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->getError(0);
        }
    }

    protected function checkRGBValue($rgb) {
        if(is_array($rgb) && count($rgb) === 3) {
            for ($i = 0; $i < 3; $i++) {
                if(!(0 <= $rgb[$i] && $rgb[$i] <= 255)) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function setCaptchaContentType($contentType) {
        switch ($contentType) {
            case 'code': $this->contentType = $contentType; break;
            case 'math': $this->contentType = $contentType; break;
            default: $this->getError(1); break;
        }
    }

    public function setNoisePercentage($percentage) {
        if(0 <= $percentage && $percentage <= 100) {
            $this->noisePercentage = $percentage;
        } else {
            $this->getError(3);
        }
    }

    public function setNoiseColor($color = array()) {
        if($this->checkRGBValue($color)) {
            $this->noiseColor = $color;
        } else {
            $this->getError(0);
        }
    }

    protected function setRandomNoiseColor() {
        $this->noiseColor = array(rand(0, 255), rand(0, 255), rand(0, 255));
    }

    public function setCharacterList($characterList) {
        if(is_string($characterList)) {
            $this->characterList = $characterList;
        } else {
            $this->getError(4);
        }
    }

    public function setLanguage($language) {
        if(in_array($language, $this->availableLanguages)) {
            $this->language = $language;
        } else {
            $this->getError(2);
        }
    }

    public function getAnswer() {
        return $this->answer;
    }

    protected function getError($errorCode) {
        $this->errorHistory[] = $errorCode;
        $this->lastError = $errorCode;
    }

    public function getLastError() {
        return $this->errorList[$this->language][$this->lastError];
    }

    public function getErrorHistory() {
        return $this->errorHistory;
    }

    public function prepare() {
        if(is_null($this->lastError)) {
            if(is_null($this->canvasBackground)) {
                $this->setRandomCanvasBackground();
            }

            if(is_null($this->canvasTextColor)) {
                $this->setRandomCanvasTextColor();
            }

            if(is_null($this->noiseColor)) {
                $this->setRandomNoiseColor();
            }

            $this->canvas = imagecreate($this->canvasWidth, $this->canvasHeight);

            $this->canvasBackground = imagecolorallocate($this->canvas, $this->canvasBackground[0], $this->canvasBackground[1], $this->canvasBackground[2]);
            $this->canvasTextColor = imagecolorallocate($this->canvas, $this->canvasTextColor[0], $this->canvasTextColor[1], $this->canvasTextColor[2]);
            $this->noiseColor = imagecolorallocate($this->canvas, $this->noiseColor[0], $this->noiseColor[1], $this->noiseColor[2]);

            imagefill($this->canvas, 0, 0, $this->canvasBackground);
            for ($i = 0; $i < $this->behindLinesCount; $i++) {
                imageline($this->canvas, round(rand(0, $this->canvasWidth / 3)), round(rand(0, $this->canvasHeight)), round(rand(($this->canvasWidth / 3) * 2, $this->canvasWidth)), round(rand(0, $this->canvasHeight)), $this->canvasTextColor);
            }

            for ($i = 0; $i < ((($this->canvasWidth * $this->canvasHeight) / 100) * $this->noisePercentage) / 2; $i++) {
                imagesetpixel($this->canvas, rand(1, $this->canvasWidth), rand(1, $this->canvasHeight), $this->noiseColor);
            }

            switch ($this->contentType) {
                case 'code':
                    $this->canvasText = substr(str_shuffle($this->characterList), 0, 6);
                    $this->answer = $this->canvasText;
                    break;
                case 'math':
                    $n1 = rand(1, 9);
                    $n2 = rand(1, 9);
                    while ($n1 == $n2) {
                        $n2 = rand(1, 9);
                    }
                    $t = substr(str_shuffle('+-'), 1);
                    if($t == '+') {
                        $this->canvasText = $n1 . $t . $n2;
                        $this->answer = $n1 + $n2;
                    } else {
                        $this->canvasText = $n1 > $n2 ? $n1 . $t . $n2 : $n2 . $t . $n1;
                        $this->answer = $n1 > $n2 ? $n1 - $n2 : $n2 - $n1;
                    }
                    break;
                default:
                    $this->getError(1);
                    break;
            }

            $padding = $this->contentType == 'code' ? 15 : 35;

            for ($i = 0; $i < mb_strlen($this->canvasText, 'UTF-8'); $i++) {
                $font = rand(75, 100);
                $x = (15 * $i) + $padding;
                $y = rand(5, 25);
                imagestring($this->canvas, $font, $x, $y, $this->canvasText[$i], $this->canvasTextColor);
            }

            for ($i = 0; $i < ((($this->canvasWidth * $this->canvasHeight) / 100) * $this->noisePercentage) / 2; $i++) {
                imagesetpixel($this->canvas, rand(1, $this->canvasWidth), rand(1, $this->canvasHeight), $this->noiseColor);
            }

            for ($i = 0; $i < $this->frontLinesCount; $i++) {
                imageline($this->canvas, round(rand(0, $this->canvasWidth / 3)), round(rand(0, $this->canvasHeight)), round(rand(($this->canvasWidth / 3) * 2, $this->canvasWidth)), round(rand(0, $this->canvasHeight)), $this->canvasTextColor);
            }

            return true;
        } else {
            return false;
        }
    }

    public function export($type, $base64Type = null) {
        switch ($type) {
            case 'base64':
                ob_start();
                switch ($base64Type) {
                    case 'png':
                        imagepng($this->canvas);
                        break;
                    case 'jpeg':
                        imagejpeg($this->canvas);
                        break;
                    case 'gif':
                        imagegif($this->canvas);
                        break;
                    default:
                        return $this->getError(5);
                        break;
                }
                $data = ob_get_contents();
                ob_end_clean();
                return base64_encode($data);
                break;
            case 'png':
                header("Content-type: image/png");
                return imagepng($this->canvas);
                break;
            case 'jpeg':
                header('Content-type: image/jpeg');
                return imagejpeg($this->canvas);
                break;
            case 'gif':
                header('Content-type: image/gif');
                return imagegif($this->canvas);
                break;
            default:
                return $this->getError(5);
                break;
        }
    }

    public function __destruct() {
        if(is_resource($this->canvas)) {
            imagedestroy($this->canvas);
        }
    }

}