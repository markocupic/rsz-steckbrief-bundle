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

namespace Markocupic\RszSteckbriefBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\UserModel;
use Markocupic\RszSteckbriefBundle\Model\RszSteckbriefModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(RszSteckbriefReaderModuleController::TYPE, category:'rsz_frontend_modules', template: 'mod_rsz_steckbrief_reader')]
class RszSteckbriefReaderModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'rsz_steckbrief_reader_module';

    private ContaoFramework $framework;
    private RequestStack $requestStack;
    private ScopeMatcher $scopeMatcher;
    private RszSteckbriefModel|null $objRszSteckbrief = null;
    private string $projectDir;
    private string $strRszSteckbriefAvatarSrc;

    public function __construct(ContaoFramework $framework, RequestStack $requestStack, ScopeMatcher $scopeMatcher, string $projectDir, string $strRszSteckbriefAvatarSrc)
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
        $this->projectDir = $projectDir;
        $this->strRszSteckbriefAvatarSrc = $strRszSteckbriefAvatarSrc;
    }

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        $inputAdapter = $this->framework->getAdapter(Input::class);
        $configAdapter = $this->framework->getAdapter(Config::class);
        $userModelAdapter = $this->framework->getAdapter(UserModel::class);
        $rszSteckbriefModelAdapter = $this->framework->getAdapter(RszSteckbriefModel::class);
        $environmentAdapter = $this->framework->getAdapter(Environment::class);

        if ($this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest())) {
            $blnShow = false;

            // Set the item from the auto_item parameter
            if (!isset($_GET['person']) && $configAdapter->get('useAutoItem') && isset($_GET['auto_item'])) {
                $inputAdapter->setGet('person', $inputAdapter->get('auto_item'));
            }

            if ('' !== $inputAdapter->get('person')) {
                $objUser = $userModelAdapter->findByUsername($inputAdapter->get('person'));

                if (null !== $objUser) {
                    if (null !== ($this->objRszSteckbrief = $rszSteckbriefModelAdapter->findOneByPid($objUser->id))) {
                        $blnShow = true;
                    }
                }
            }

            if (!$blnShow) {
                throw new PageNotFoundException('Page not found: '.$environmentAdapter->get('uri'));
            }
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $filesModelAdapter = $this->framework->getAdapter(FilesModel::class);
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        // Get user model and add it to the template
        $objUser = $this->objRszSteckbrief->getRelated('pid');
        $template->user_model = $objUser->row();

        $arrSteckbrief = $this->objRszSteckbrief->row();

        foreach ($arrSteckbrief as $key => $content) {
            $template->{$key} = stripslashes((string) $content);
        }

        // Youtube videos
        $template->arr_videos = [];

        if (!empty($arrSteckbrief['video_integration'])) {
            $template->arr_videos = array_values(explode(',', $arrSteckbrief['video_integration']));
        }

        // Images
        $multiSRC = $stringUtilAdapter->deserialize($this->objRszSteckbrief->multiSRC);
        $orderSRC = $stringUtilAdapter->deserialize($this->objRszSteckbrief->orderSRC);
        $images = [];

        // Return if there are no files
        if (!empty($multiSRC) && \is_array($multiSRC)) {
            // Get the file entries from the database
            $filesModel = $filesModelAdapter->findMultipleByUuids($multiSRC);

            if (null !== $filesModel) {
                while ($filesModel->next()) {
                    $arrImage = [];

                    if (!file_exists($this->projectDir.'/'.$filesModel->path)) {
                        $filesModel->path = $this->strRszSteckbriefAvatarSrc;
                    }
                    $arrImage['uuid'] = $filesModel->uuid;
                    $arrImage['imageSrc'] = $filesModel->path;
                    $arrImage['caption'] = '';
                    $images[$filesModel->path] = $arrImage;
                }
            }
        }

        // Custom sorting in the backend
        if (!empty($orderSRC) && \is_array($orderSRC)) {
            $tmp = $orderSRC;

            // Remove all values
            $arrOrder = array_map(
                static function (): void {
                },
                array_flip($tmp)
            );

            // Move the matching elements to its position in $arrOrder
            foreach ($images as $k => $v) {
                if (\array_key_exists($v['uuid'], $arrOrder)) {
                    $arrOrder[$v['uuid']] = $v;
                    unset($images[$k]);
                }
            }

            // Append the left-over images at the end
            if (!empty($images)) {
                $arrOrder = array_merge($arrOrder, array_values($images));
            }

            // Remove empty (not replaced) entries
            $images = array_values(array_filter($arrOrder));
            unset($arrOrder);
        }

        // Finally store the image captions in an array
        if (\is_array($images) && !empty($this->objRszSteckbrief->image_description)) {
            $imageCaption = explode('***', $this->objRszSteckbrief->image_description);

            if (!empty($imageCaption) && \is_array($imageCaption)) {
                foreach (array_keys($images) as $k) {
                    $images[$k]['caption'] = isset($imageCaption[$k]) ? htmlspecialchars(str_replace(\chr(10), '', $imageCaption[$k])) : '';
                }
            }
        }

        $template->arr_images = $images;

        return $template->getResponse();
    }
}
