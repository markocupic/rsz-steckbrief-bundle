<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Steckbrief Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-steckbrief-bundle
 */

use Contao\System;
use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_rsz_steckbrief'] = [
    'config'   => [
        'ptable'           => 'tl_user',
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => true,
        'notDeletable'     => true,
        'notCopyable'      => true,
        'notSortable'      => true,
        'notCreatable'     => true,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'list'     => [
        'sorting'           => [
            'mode'            => DataContainer::MODE_SORTED,
            // Do not permit access to foreign profiles (except admins)
            'filter'          => [['pid = ?', System::getContainer()->get('security.helper')->getUser() ? System::getContainer()->get('security.helper')->getUser()->id : null]],
            'fields'          => ['pid'],
            'flag'            => DataContainer::SORT_INITIAL_LETTER_ASC,
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields' => ['pid'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
        ],
    ],
    'palettes' => [
        'default' => '
        {publish_legend},aktiv;
        {gallery},multiSRC,image_description,video_integration;
        {competitions},klettert_seit,best_competition_results;
        {indoorleistungen},schwerste_rotpunktroute_indoor,schwerste_boulderroute_indoor;
        {outdoorleistungen_routen},schwerste_route_gebiet,schwerste_route_routenname,schwerste_route_difficulty;
        {outdoorleistungen_boulders},schwerster_boulder_gebiet,schwerster_boulder_routenname,schwerster_boulder_difficulty;
        {allgemeines},lieblingsklettergebiet,sponsoren,ziele,leitsatz,hobbies
        ',
    ],
    'fields'   => [
        'id'                             => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid'                            => [
            'foreignKey' => 'tl_user.username',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp'                         => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'aktiv'                          => [
            'exclude'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'checkbox',
            'sql'       => "int(1) unsigned NOT NULL default '0'",
        ],
        'multiSRC'                       => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['isGallery' => true, 'multiple' => true, 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png', 'files' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderSRC'],
            'sql'       => 'blob NULL',
        ],
        'orderSRC'                       => [
            'sql' => 'blob NULL',
        ],
        'image_description'              => [
            'exclude'     => true,
            'flag'        => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType'   => 'textarea',
            'explanation' => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['image_description_explanation'],
            'eval'        => ['allowHtml' => false],
            'sql'         => 'mediumtext NULL',
        ],
        'klettert_seit'                  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'best_competition_results'       => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerste_rotpunktroute_indoor' => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerste_boulderroute_indoor'  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerste_route_gebiet'         => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerste_route_routenname'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerste_route_difficulty'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerster_boulder_gebiet'      => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerster_boulder_routenname'  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'schwerster_boulder_difficulty'  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
        'lieblingsklettergebiet'         => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'sponsoren'                      => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'ziele'                          => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'leitsatz'                       => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'hobbies'                        => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'video_integration'              => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => 'mediumtext NULL',
        ],
    ],
];
