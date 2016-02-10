<?php

/**
 * André Felipe Silveira
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package    Silveira_Jadlog
 * @author     André Felipe Silveira (andrefelipesilveira@gmail.com.br)
 * @copyright  Copyright (c) 20015
 * @license    OSL 3.0
 */

class Silveira_Jadlog_Model_Source_Mode
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => 'EXPRESSO - Aéreo'),
            array('value' => '3', 'label' => '.PACKAGE - Rodoviário'),
            array('value' => '4', 'label' => 'RODOVIÁRIO - Rodoviário'),
            array('value' => '5', 'label' => 'ECONÔMICO - Rodoviário'),
            array('value' => '6', 'label' => 'DOC - Rodoviário'),
            array('value' => '7', 'label' => 'CORPORATE - Aéreo'),
            array('value' => '9', 'label' => '.COM - Aéreo'),
            array('value' => '10', 'label' => 'INTERNACIONAL - Aéreo'),
            array('value' => '12', 'label' => 'CARGO - Aéreo'),
            array('value' => '14', 'label' => 'EMERGENCIAL - Rodoviário'),
        );
    }
}