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
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['rsz_steckbrief_listing_module'] = '{title_legend},name,headline,type;{config_legend},rszSteckbriefReaderPage,numberOfItems;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['rsz_steckbrief_reader_module'] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['rszSteckbriefReaderPage'] = array
(
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('fieldType'=>'radio','tl_class' => 'clr'), // do not set mandatory (see #5453)
    'sql'                     => "int(10) unsigned NOT NULL default 0",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);
