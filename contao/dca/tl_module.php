<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Steckbrief Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-steckbrief-bundle
 */



use Markocupic\RszSteckbriefBundle\Controller\FrontendModule\RszSteckbriefListingModuleController;
use Markocupic\RszSteckbriefBundle\Controller\FrontendModule\RszSteckbriefReaderModuleController;

/*
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes'][RszSteckbriefListingModuleController::TYPE] = '{title_legend},name,headline,type;{config_legend},rszSteckbriefReaderPage,numberOfItems;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][RszSteckbriefReaderModuleController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['rszSteckbriefReaderPage'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];
