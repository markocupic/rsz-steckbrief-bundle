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

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\UserModel;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_rsz_steckbrief'] = [
    // Config
    'config'   => [
        'ptable'           => 'tl_user',
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback'  => [
            ['tl_rsz_steckbrief', 'createProfiles'],
            ['tl_rsz_steckbrief', 'filterList'],
        ],
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
    // List
    'list'     => [
        'sorting'           => [
            'mode'            => DataContainer::MODE_SORTED,
            // Do not permit access to foreign profiles (except admins)
            'filter'          => [['pid=?', BackendUser::getInstance()->id]],
            'fields'          => ['pid'],
            'flag'            => DataContainer::SORT_INITIAL_LETTER_ASC,
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields'         => ['pid'],
            'label_callback' => ['tl_rsz_steckbrief', 'labelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    // Palettes
    'palettes' => [
        'default' => '{publish_legend},aktiv;{gallery},multiSRC,image_description,video_integration;{competitions},klettert_seit,best_competition_results;{indoorleistungen},schwerste_rotpunktroute_indoor,schwerste_boulderroute_indoor; {outdoorleistungen_routen},schwerste_route_gebiet,schwerste_route_routenname,schwerste_route_difficulty;{outdoorleistungen_boulders},schwerster_boulder_gebiet,schwerster_boulder_routenname,schwerster_boulder_difficulty;{allgemeines},lieblingsklettergebiet,sponsoren,ziele,leitsatz,hobbies',
    ],
    // Fields
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

class tl_rsz_steckbrief extends Backend
{
    /**
     * Onload callback.
     */
    public function filterList(): void
    {
        // Nur Admins haben Zugriff auf fremde Profile
        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin) {
            unset($GLOBALS['TL_DCA']['tl_rsz_steckbrief']['list']['sorting']['filter']);
        }
    }

    /**
     * Onload callback
     * Create profiles.
     */
    public function createProfiles(): void
    {
        // Create a blanko profile if not exists.
        $objUser = $this->Database
            ->execute('SELECT id, username FROM tl_user');

        while ($objUser->next()) {
            $objSteckbriefe = $this->Database
                ->prepare('SELECT * FROM tl_rsz_steckbrief WHERE pid=?')
                ->execute($objUser->id);

            if (!$objSteckbriefe->numRows) {
                $set = [
                    'pid' => $objUser->id,
                ];

                $objSteckbriefe = $this->Database
                    ->prepare('INSERT INTO  tl_rsz_steckbrief %s')
                    ->set($set)
                    ->execute($objUser->id);

                if ($objSteckbriefe->affectedRows) {
                    $insertID = $objSteckbriefe->insertId;
                    $this->log('A new entry in table "tl_rsz_steckbrief" has been created (ID: '.$insertID.')', self::class.' '.__FUNCTION__.'()', TL_GENERAL);
                }
            }
        }
    }

    /**
     * Replace the pid with tl_user.name.
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $args): array
    {
        $args[0] = null !== UserModel::findByPk($args[0]) ? UserModel::findByPk($args[0])->name : 'Unbekannt';

        return $args;
    }
}
