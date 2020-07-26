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

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database;
use Contao\FilesModel;
use Contao\Image;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RszSteckbriefReaderModuleController
 * @package Markocupic\RszSteckbriefBundle\Controller\FrontendModule
 */
class RszSteckbriefReaderModuleController extends AbstractFrontendModuleController
{
    /** @var string */
    private $projectDir;

    /** @var string */
    private $strRszSteckbriefAvatarSrc;

    /**
     * RszSteckbriefReaderModuleController constructor.
     * @param string $projectDir
     * @param string $strRszSteckbriefAvatarSrc
     */
    public function __construct(string $projectDir, string $strRszSteckbriefAvatarSrc)
    {
        $this->projectDir = $projectDir;
        $this->strRszSteckbriefAvatarSrc = $strRszSteckbriefAvatarSrc;
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
        $services['database_connection'] = Connection::class;
        $services['contao.routing.scope_matcher'] = ScopeMatcher::class;
        $services['security.helper'] = Security::class;
        $services['translator'] = TranslatorInterface::class;

        return $services;
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->get('contao.framework')->getAdapter(Database::class);

        // Load language file
        Controller::loadLanguageFile('tl_rsz_steckbrief');

        if (!is_int((int) $inputAdapter->get('uid')))
        {
            throw new PageNotFoundException('Uuid param missing. Page not found.');
        }

        $objSteckbrief = $databaseAdapter->getInstance()
            ->prepare("SELECT * FROM tl_rsz_steckbrief WHERE pid = ?")
            ->execute($inputAdapter->get('uid'));

        $arrSteckbrief = $objSteckbrief->fetchAssoc();
        foreach ($arrSteckbrief as $key => $content)
        {
            $template->{$key} = stripslashes($content);
        }

        // Get name and city from tl_user
        $objUser = $databaseAdapter->getInstance()
            ->prepare("SELECT * FROM tl_user WHERE id = ?")
            ->execute($inputAdapter->get('uid'));
        $template->name = $objUser->name;
        $template->wohnort = $objUser->city;

        $template->arrVideos = [];
        if ($arrSteckbrief['video_integration'] != '')
        {
            $template->arrVideos = array_values(explode(',', $arrSteckbrief['video_integration']));
        }

        $multiSRC = unserialize($objSteckbrief->multiSRC);
        $orderSRC = unserialize($objSteckbrief->orderSRC);
        $images = [];
        

        // Return if there are no files
        if (!empty($multiSRC && is_array($multiSRC)))
        {
            // Get the file entries from the database
            $filesModel = FilesModel::findMultipleByUuids($multiSRC);
            if ($filesModel !== null)
            {
                while ($filesModel->next())
                {
                    $arrImage = [];
                    if (!file_exists($this->projectDir . '/' . $filesModel->path))
                    {
                        $filesModel->path = $this->strRszSteckbriefAvatarSrc;
                    }
                    $arrImage['uuid'] = $filesModel->uuid;
                    $arrImage['imageSrc'] = $filesModel->path;
                    $images[$filesModel->path] = $arrImage;
                    
                }
            }
        }

        // Custom sorting in the backend
        if (!empty($orderSRC) && is_array($orderSRC))
        {
            $tmp = $orderSRC;

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

        // Finally store the image captions in an array
        $imageCaption = explode('***', $objSteckbrief->image_description);
        foreach ($images as $k => $v)
        {
            $images[$k]['caption'] = $imageCaption[$k] ? htmlspecialchars(str_replace(chr(10), '', $imageCaption[$k])) : '';
        }

        $template->arrImages = $images;

        return $template->getResponse();
    }
}

