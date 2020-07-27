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
 * Table tl_rsz_steckbrief
 */
$GLOBALS['TL_DCA']['tl_rsz_steckbrief'] = [
    // Config
    'config'   => [
        'ptable'           => 'tl_user',
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => [
            ["tl_rsz_steckbrief", "createProfiles"],
            ["tl_rsz_steckbrief", "filterList"]
        ],
        'notDeletable'     => true,
        'notSortable'      => true,
        'notCopyable'      => true,
        'notSortable'      => true,
        'notCreatable'     => true,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ]
        ]
    ],
    // List
    'list'     => [
        'sorting'           => [
            'mode'            => 1,
            // Do not permit access to foreign profiles (except admins)
            'filter'          => [['pid=?', \Contao\BackendUser::getInstance()->id]],
            'fields'          => ['pid'],
            'flag'            => 1,
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields'         => ['pid'],
            'label_callback' => ['tl_rsz_steckbrief', 'labelCallback']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ]
        ]
    ],
    // Palettes
    'palettes' => [
        'default' => '{publish_legend},aktiv;{gallery},multiSRC,image_description,video_integration;{competitions},klettert_seit,best_competition_results;{indoorleistungen},schwerste_rotpunktroute_indoor,schwerste_boulderroute_indoor; {outdoorleistungen_routen},schwerste_route_gebiet,schwerste_route_routenname,schwerste_route_difficulty;{outdoorleistungen_boulders},schwerster_boulder_gebiet,schwerster_boulder_routenname,schwerster_boulder_difficulty;{allgemeines},lieblingsklettergebiet,sponsoren,ziele,leitsatz,hobbies'
    ],
    // Fields
    'fields'   => [
        'id'                             => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'                            => [
            'foreignKey' => 'tl_user.username',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'tstamp'                         => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'aktiv'                          => [
            'exclude'   => true,
            'flag'      => 1,
            'inputType' => 'checkbox',
            'sql'       => "int(1) unsigned NOT NULL default '0'"
        ],
        'multiSRC'                       => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['multiple' => true, 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png', 'files' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderSRC'],
            'sql'       => "blob NULL",
        ],
        'orderSRC'                       => [
            'sql'   => "blob NULL"
        ],
        'image_description'              => [
            'exclude'   => true,
            'flag'      => 1,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'klettert_seit'                  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'best_competition_results'       => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => "text NOT NULL",
        ],
        'schwerste_rotpunktroute_indoor' => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerste_boulderroute_indoor'  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerste_route_gebiet'         => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerste_route_routenname'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerste_route_difficulty'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerster_boulder_gebiet'      => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerster_boulder_routenname'  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'schwerster_boulder_difficulty'  => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ],
        'lieblingsklettergebiet'         => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => "text NOT NULL",
        ],
        'sponsoren'                      => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['style' => 'allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => "text NOT NULL",
        ],
        'ziele'                          => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => "text NOT NULL",
        ],
        'leitsatz'                       => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => "text NOT NULL",
        ],
        'hobbies'                        => [
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['allowHtml' => false, 'tl_class' => 'clr'],
            'sql'       => "text NOT NULL",
        ],
        'video_integration'              => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['allowHtml' => false],
            'sql'       => "text NOT NULL",
        ]
    ]
];

/**
 * Class tl_rsz_steckbrief
 */
class tl_rsz_steckbrief extends Backend
{

    /**
     * tl_rsz_steckbrief constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Onload callback
     */
    public function filterList()
    {
        // Nur Admins haben Zugriff auf fremde Profile
        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin)
        {
            unset($GLOBALS['TL_DCA']['tl_rsz_steckbrief']['list']['sorting']['filter']);
        }
    }

    /**
     * Onload callback
     * Create profiles
     */
    public function createProfiles()
    {
        // erstellt von allen Benutzern ein Blanko-Profil, wenn noch keines vorhanden ist.
        $objUser = $this->Database->execute("SELECT id, username FROM tl_user");
        while ($objUser->next())
        {
            $objSteckbriefe = $this->Database->prepare("SELECT * FROM tl_rsz_steckbrief WHERE pid=?")
                ->execute($objUser->id);
            if (!$objSteckbriefe->numRows)
            {
                $objSteckbriefe = $this->Database->prepare("INSERT INTO  tl_rsz_steckbrief (pid) VALUES (?)")
                    ->execute($objUser->id);
                if ($objSteckbriefe->affectedRows)
                {
                    $insertID = $objSteckbriefe->insertId;
                    $this->log('A new entry in table "tl_rsz_steckbrief" has been created (ID: ' . $insertID . ')', __CLASS__ . " " . __FUNCTION__ . "()", TL_GENERAL);
                }
            }
        }
    }

    /**
     * Replace pid with tl_user.name
     *
     * @param array $row
     * @param string $label
     * @param Contao\DataContainer $dc
     * @param array $args
     *
     * @return array
     */
    public function labelCallback($row, $label, Contao\DataContainer $dc, $args)
    {
        $args[0] = \Contao\UserModel::findByPk($args[0]) !== null ? \Contao\UserModel::findByPk($args[0])->name : 'Unbekannt';
        return $args;
    }

}
