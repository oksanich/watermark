<?php
class Watermark {
    protected static $_instance;

    private function __construct(){}

    private function __clone(){}

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function saveImg ($arResult)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/lib/wideimage/WideImage.php';
        $strError = '';

        $img = WideImage::load($arResult['imgurl']);
        $watermark = WideImage::load($arResult['wmurl']);
        $new = $img->merge($watermark, $arResult['posx'], $arResult['posy'], $arResult['alpha']);
        if ($arResult['multy']) {
            $wmW = $watermark->getWidth() + $arResult['horizontal_margin'];
            $wmH = $watermark->getHeight() + $arResult['vertical_margin'];
            $col = ceil($img->getWidth()/$wmW);
            $row = ceil($img->getHeight()/$wmH);

            for ($i=0; $i < $col; $i++) {
                for ($j = 0; $j < $row; $j++) {
                    if ($i == 0 && $j == 0) {
                        continue;
                    }

                    $new = $new->merge($watermark, $arResult['posx'] + $wmW * $i, $arResult['posy'] + $wmH * $j,
                        $arResult['alpha']);
                }
            }
        }
        header('Content-type: image/png');
        header('Content-Disposition: attachment; filename=water.png');
        header('Content-Transfer-Encoding: binary');

        echo($new);


        if (strlen(trim($strError)) > 0) {
            echo $strError;
            die();
        }
    }

    public function validateData ($objData) {
        if ( !isset($objData->files->watermark) || empty ($objData->files->watermark)
            || !isset($objData->files->image) || empty ($objData->files->image)
            || !isset($objData->gridType) || empty ($objData->gridType)
            || !isset($objData->alpha) || empty ($objData->alpha)
        ) {
            return false;
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/'.$objData->files->image)
            || !file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/'.$objData->files->watermark)) {
                return false;
            }

        $arResult = array(
            'alpha' => ((int)($objData->alpha * 100) < 100 ? (int)($objData->alpha * 100) : 100),
            'posx' => (int)$objData->coord->x,
            'posy' => (int)$objData->coord->y,
            'multy' => (($objData->gridType == 'multy')? 1 : 0),
            'vertical_margin' => (int)$objData->margins->y,
            'horizontal_margin' => (int)$objData->margins->x,
            'imgurl' => $_SERVER['DOCUMENT_ROOT'].'/upload/'.$objData->files->image,
            'wmurl' => $_SERVER['DOCUMENT_ROOT'].'/upload/'.$objData->files->watermark
        );

        return $arResult;
    }
}