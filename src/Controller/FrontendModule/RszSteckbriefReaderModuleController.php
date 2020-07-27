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

use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Contao\UserModel;
use Markocupic\RszSteckbriefBundle\Model\RszSteckbriefModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

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

    /** @var RequestStack */
    private $requestStack;

    /** @var ScopeMatcher */
    private $scopeMatcher;

    /** @var RszSteckbriefModel */
    private $objRszSteckbrief;

    /**
     * RszSteckbriefReaderModuleController constructor.
     * @param string $projectDir
     * @param string $strRszSteckbriefAvatarSrc
     * @param RequestStack $requestStack
     * @param ScopeMatcher $scopeMatcher
     */
    public function __construct(string $projectDir, string $strRszSteckbriefAvatarSrc, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->projectDir = $projectDir;
        $this->strRszSteckbriefAvatarSrc = $strRszSteckbriefAvatarSrc;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
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
        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        /** @var Config $configAdapter */
        $configAdapter = $this->get('contao.framework')->getAdapter(Config::class);

        /** @var UserModel $userModelAdapter */
        $userModelAdapter = $this->get('contao.framework')->getAdapter(UserModel::class);

        /** @var RszSteckbriefModel $rszSteckbriefModelAdapter */
        $rszSteckbriefModelAdapter = $this->get('contao.framework')->getAdapter(RszSteckbriefModel::class);

        /** @var Environment $environmentAdapter */
        $environmentAdapter = $this->get('contao.framework')->getAdapter(Environment::class);

        if ($this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest()))
        {
            $blnShow = false;

            // Set the item from the auto_item parameter
            if (!isset($_GET['person']) && $configAdapter->get('useAutoItem') && isset($_GET['auto_item']))
            {
                $inputAdapter->setGet('person', $inputAdapter->get('auto_item'));
            }

            if ($inputAdapter->get('person') != '')
            {
                $objUser = $userModelAdapter->findByUsername($inputAdapter->get('person'));
                if ($objUser !== null)
                {
                    if (($this->objRszSteckbrief = $rszSteckbriefModelAdapter->findByPid($objUser->id)) !== null)
                    {
                        $blnShow = true;
                    }
                }
            }

            if (!$blnShow)
            {
                throw new PageNotFoundException('Page not found: ' . $environmentAdapter->get('uri'));
            }
        }

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
        return $services;
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var System $systemAdapter */
        $systemAdapter = $this->get('contao.framework')->getAdapter(System::class);

        /** @var FilesModel $filesModelAdapter */
        $filesModelAdapter = $this->get('contao.framework')->getAdapter(FilesModel::class);

        // Load language file
        $systemAdapter->loadLanguageFile('tl_rsz_steckbrief');

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->get('contao.framework')->getAdapter(StringUtil::class);

        // Get name and city from tl_user
        $objUser = $this->objRszSteckbrief->getRelated('pid');

        // Get user model
        $template->userModel = $objUser;

        $arrSteckbrief = $this->objRszSteckbrief->row();
        $arrSteckbrief['city'] = $objUser->city;

        foreach ($arrSteckbrief as $key => $content)
        {
            $template->{$key} = stripslashes($content);
        }

        $template->arrVideos = [];
        if ($arrSteckbrief['video_integration'] != '')
        {
            $template->arrVideos = array_values(explode(',', $arrSteckbrief['video_integration']));
        }

        $multiSRC = $stringUtilAdapter->deserialize($this->objRszSteckbrief->multiSRC);
        $orderSRC = $stringUtilAdapter->deserialize($this->objRszSteckbrief->orderSRC);
        $images = [];

        // Return if there are no files
        if (!empty($multiSRC) && is_array($multiSRC))
        {
            // Get the file entries from the database
            $filesModel = $filesModelAdapter->findMultipleByUuids($multiSRC);
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
        $imageCaption = explode('***', $this->objRszSteckbrief->image_description);
        foreach ($images as $k => $v)
        {
            $images[$k]['caption'] = $imageCaption[$k] ? htmlspecialchars(str_replace(chr(10), '', $imageCaption[$k])) : '';
        }

        $template->arrImages = $images;

        return $template->getResponse();
    }
}

