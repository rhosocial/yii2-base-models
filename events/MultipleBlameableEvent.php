<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license http://vistart.me/license/
 */

namespace rhosocial\base\models\events;

/**
 * Description of MultipleBlameableEvent
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MultipleBlameableEvent extends \yii\base\Event
{

    public $blamesChanged = true;

}
