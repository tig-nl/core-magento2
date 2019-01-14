<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Core\Model;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Dir\Reader as ComposerDir;
use Magento\Framework\Locale\ResolverInterface as ResolverInterface;
use Zend\Uri\Http as HttpUri;

class Extension
{
    /**
     * @var FullModuleList
     */
    protected $fullModuleList;

    /**
     * @var ComposerDir
     */
    protected $composerDir;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var string
     */
    const url_tig_extensions = 'http://dev.tig-grouped.local/tig_extensions.json';


    /**
     * @var ResolverInterface
     */
    protected $localeResolver;


    /**
     * Extension constructor.
     * @param FullModuleList $fullModuleList
     * @param ComposerDir $composerDir
     * @param HttpUri $HttpUri
     */
    public function __construct(
        FullModuleList $fullModuleList,
        ComposerDir $composerDir,
        HttpUri $HttpUri,
        ResolverInterface $localeResolver
    )
    {
        $this->fullModuleList = $fullModuleList;
        $this->composerDir = $composerDir;
        $this->localeResolver = $localeResolver;
        $this->HttpUri = $HttpUri;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function getComposerInformation($moduleName)
    {
        try {
            $extensionDir = $this->composerDir->getModuleDir("", strip_tags($moduleName));
            $filesList = scandir($extensionDir);
            $filesListComposerPosition = array_search("composer.json", $filesList);

            if ($filesList[$filesListComposerPosition] === 'composer.json') {
                return \Zend_Json::decode(file_get_contents($extensionDir . '/composer.json'))['version'];
            }


        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function checkModuleInstalled($moduleName)
    {
        $boolInstalled = $this->fullModuleList->has(strip_tags($moduleName));
        return $boolInstalled;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function checkModuleNeedsUpdate($moduleItem)
    {
        $composerVersion = $this->getComposerInformation($moduleItem['name']);

        if ($composerVersion < $this->getVersionFromExternalSource($moduleItem)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getFromExternalSource()
    {
        $json = file_get_contents(self::url_tig_extensions);
        $obj = json_decode($json, true);
        return $obj;
    }

    /**
     * @param $moduleItem
     * @return mixed
     */
    public function getVersionFromExternalSource($moduleItem)
    {
        return $moduleItem['version'];
    }


    /**
     * @return bool
     */
    public function checkIfBackendAccountIsDutch()
    {
        if ($this->localeResolver->getLocale() === 'nl_BE' || $this->localeResolver->getLocale() === 'nl_NL') {
            return true;
        }
        return false;
    }


    /**
     * @param $arr
     * @return array
     */
    public function generateTigFormatArray($arr)
    {
        $result = [];

        foreach ($arr as $item) {
            array_push($item, array(
                    'installed' => $this->checkModuleInstalled($item['name']),
                    'update_available' => $this->checkModuleInstalled($item['name']) ? $this->checkModuleNeedsUpdate($item) : false,
                    'version' => $this->getComposerInformation($item['name']),
                    'external_version' => $this->getVersionFromExternalSource($item)
                )
            );
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param $extensionList
     * @return mixed
     */
    public function generateEnglishList($extensionList)
    {

        $dutchArr = $extensionList['extensions']['english'];
        $result = $this->generateTigFormatArray($dutchArr);

        return $result;
    }

    /**
     * @param $extensionList
     * @return mixed
     */
    public function generateDutchList($extensionList)
    {
        $dutchArr = $extensionList['extensions']['dutch'];
        $result = $this->generateTigFormatArray($dutchArr);

        return $result;
    }

    /**
     * @return array|mixed
     */
    public function generateModuleList()
    {

        $result = [];
        $extensionList = $this->getFromExternalSource();

        if ($this->checkIfBackendAccountIsDutch()) {
            $result = $this->generateDutchList($extensionList);
        }
        if (!$this->checkIfBackendAccountIsDutch()) {
            $result = $this->generateEnglishList($extensionList);
        }

        return $result;
    }
}
