<?php

namespace Swissup\Firecheckout\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Page view helper
     *
     * @var \Swissup\Firecheckout\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Swissup\Firecheckout\Helper\Data $pageViewHelper
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Swissup\Firecheckout\Helper\Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    /**
     * Match firecheckout page
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->helper->isFirecheckoutEnabled() || $request->getParam('onepage')) {
            return null;
        }

        $currentPath = trim($request->getPathInfo(), '/');
        if (strpos($currentPath, 'firecheckout') === 0) {
            return null; // use standard router to prevent recursion
        }

        $firecheckoutPath = $this->helper->getFirecheckoutUrlPath();
        $firecheckoutPaths = [
            $firecheckoutPath,
            $firecheckoutPath . '/index',
            $firecheckoutPath . '/index/index',
        ];

        if (!in_array($currentPath, $firecheckoutPaths)) {
            return null;
        }

        $request->setAlias(
            \Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
            $currentPath
        );
        $request->setPathInfo('/firecheckout/index/index');

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class
        );
    }
}
