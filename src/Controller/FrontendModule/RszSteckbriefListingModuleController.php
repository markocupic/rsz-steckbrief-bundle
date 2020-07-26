<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Mein Steckbrief
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-steckbrief-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\RszSteckbriefBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class RszSteckbriefListingModuleController
 * @package Markocupic\RszSteckbriefBundle\Controller\FrontendModule
 */
class RszSteckbriefListingModuleController extends AbstractFrontendModuleController
{

    /**
     * RszSteckbriefListingModuleController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary
     * @param Request $request
     * @param ModuleModel $model
     * @param string $section
     * @param array|null $classes
     * @param PageModel|null $page
     * @return Response
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Lazyload some services
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services['contao.framework'] = ContaoFramework::class;
        //$services['database_connection'] = Connection::class;
        //$services['contao.routing.scope_matcher'] = ScopeMatcher::class;
        //$services['security.helper'] = Security::class;
        //$services['translator'] = TranslatorInterface::class;

        return $services;
    }

    /**
     * @var string
     */
    protected $strAvatar = 'system/modules/steckbriefe/html/avatar.png';

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->get('contao.framework')->getAdapter(Database::class);

        $items = [];

        $objSteckbrief = $databaseAdapter->getInstance()
            ->prepare("SELECT * FROM tl_steckbriefe WHERE multiSRC != ''")
            ->execute();

        while ($objSteckbrief->next())
        {
            if (!$this->isAthlete($objSteckbrief->pid) || !$this->isRSZ($objSteckbrief->pid))
            {
                continue;
            }

            $objSteckbrief->multiSRC = unserialize($objSteckbrief->multiSRC);
            if (!empty($objSteckbrief->multiSRC && is_array($objSteckbrief->multiSRC)))
            {
                $images = [];
                foreach ($objSteckbrief->multiSRC as $uuid)
                {
                    $images[] = ['uuid' => $uuid];
                }
                // Custom order
                if ($objSteckbrief->orderSRC != '')
                {
                    $tmp = unserialize($objSteckbrief->orderSRC);

                    if (!empty($tmp) && is_array($tmp))
                    {
                        // Remove all values
                        $arrOrder = array_map(function () {
                        }, array_flip($tmp));

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k => $v)
                        {
                            if (array_key_exists($v['uuid'], $arrOrder))
                            {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images))
                        {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }
                }
                $image = $images[0];
                $items[] = [
                    'id'   => $objSteckbrief->id,
                    'pid'  => $objSteckbrief->pid,
                    'src'  => FilesModel::findByUuid($image['uuid'])->path,
                    'name' => UserModel::findByPk($objSteckbrief->pid)->name,
                ];
            }
        }

        shuffle($items);
        $portraits = [];
        foreach ($items as $item)
        {
            $portraits[] = $item;
        }
        $template->items = $portraits;

        return $template->getResponse();
    }

    /**
     * @param $id
     * @return bool
     */
    public function isAthlete($id): bool
    {
        $objUser = UserModel::findByPk($id);
        if ($objUser !== null)
        {
            $arrFunktionen = StringUtil::deserialize($objUser->funktion, true);
            if (in_array('Athlet', $arrFunktionen))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function isRSZ($id): bool
    {
        $objUser = UserModel::findByPk($id);
        if ($objUser !== null)
        {
            if ($objUser->isRSZ)
            {
                return true;
            }
        }
        return false;
    }
}

