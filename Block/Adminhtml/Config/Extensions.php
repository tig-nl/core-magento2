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

namespace TIG\Core\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use TIG\Core\Model\ExtensionFactory;
use TIG\Core\Model\Extension;


class Extensions extends Template implements RendererInterface
{

    const MODULE_NAME = 'TIG_Core';

    /**
     * @var string
     */
    protected $_template = 'TIG_Core::adminhtml/config/extensions.phtml';

    /**
     * @var Extension
     */
    private $extension;

    /**
     * @var $extensionFactory
     */
    private $extensionFactory;

    /**
     * @var $fullModuleList
     */
    protected $fullModuleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * Extensions constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Extension $extension,
        ExtensionFactory $extensionFactory,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    )
    {
        $this->extensionFactory = $extensionFactory;
        $this->extension = $extension;
        $this->moduleManager = $moduleManager;
        $this->fullModuleList = $fullModuleList;
        parent::__construct($context, $data);

    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if(isset($element)){
            $this->setElement($element);
            return $this->toHtml();
        }

    }

    /**
     * @return array
     */
    public function generateExtensionsList()
    {
        try {
            $extensionList = $this->extension->generateModuleList();
            return $extensionList;

        } catch (\Exception $e) {
            return $e;
        }

    }
}
