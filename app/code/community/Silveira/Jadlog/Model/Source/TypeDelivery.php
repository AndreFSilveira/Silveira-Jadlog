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

class Silveira_Jadlog_Model_Source_TypeDelivery
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'R', 'label' => 'Retirada na unidade Jadlog'),
            array('value' => 'D', 'label' => 'Entraga no domicílio do cliente'),
        );
    }
}