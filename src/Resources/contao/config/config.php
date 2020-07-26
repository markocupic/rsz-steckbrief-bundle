<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Mein Steckbrief
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-steckbrief-bundle
 *
 */

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['rsz_tools']['rsz_steckbrief'] = array(
    'tables' => ['tl_rsz_steckbrief']
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_rsz_steckbrief'] = \Markocupic\RszSteckbriefBundle\Model\RszSteckbriefModel::class;


