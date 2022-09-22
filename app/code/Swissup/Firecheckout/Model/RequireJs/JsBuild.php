<?php

namespace Swissup\Firecheckout\Model\RequireJs;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;

class JsBuild
{
    const TEMPLATE = <<<template
(function(require){
    require.config({
        config: %build%,
        bundles: {
            'mage/requirejs/static': [
                'jsbuild',
                'buildTools',
                'text',
                'statistician'
            ]
        },
        deps: %buildkeys%
    });
})(require);
template;

    /**
     * @var array
     */
    private $types = [
        'js' => [
            'name' => 'jsbuild',
            'folder' => 'js/',
            'glob' => '{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/}*.js',
        ],
        'html' => [
            'name' => 'text',
            'folder' => 'template/',
            'glob' => '{,*/,*/*/,*/*/*/,*/*/*/*/,*/*/*/*/*/}*.html',
        ],
    ];

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\View\Asset\File\FallbackContext
     */
    private $staticContext;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $staticDir;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readDirFactory;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    private $moduleDir;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\View\Asset\Minification
     */
    private $minification;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    private $curlFactory;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $paths;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readDirFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\View\Asset\Minification $minification
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param Dir $moduleDir
     * @param string $name
     * @param array $paths
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readDirFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Asset\Minification $minification,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        Dir $moduleDir,
        $name,
        array $paths = []
    ) {
        $this->assetRepo = $assetRepo;
        $this->staticContext = $assetRepo->getStaticViewFileContext();
        $this->filesystem = $filesystem;
        $this->staticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $this->readDirFactory = $readDirFactory;
        $this->moduleDir = $moduleDir;
        $this->moduleManager = $moduleManager;
        $this->minification = $minification;
        $this->curlFactory = $curlFactory;
        $this->name = $name;
        $this->paths = $paths;
    }

    /**
     * @return $this
     */
    public function publishIfNotExist()
    {
        if (!$this->staticDir->isExist($this->getPath())) {
            $this->publish();
        }

        return $this;
    }

    /**
     * Collect jsbuilds and publish them into pub/static folder
     *
     * @return $this
     */
    public function publish()
    {
        $build = [];
        $staticPath = $this->staticContext->getPath();

        foreach ($this->paths as $path) {
            try {
                list($module, $relativePath, $excludedPaths, $requestedTypes) =
                    $this->extractVars($path);

                if (!$this->moduleManager->isEnabled($module)) {
                    continue;
                }

                $modulePath = $this->moduleDir->getDir($module, Dir::MODULE_VIEW_DIR);
                $moduleDir = $this->readDirFactory->create($modulePath);
            } catch (\Exception $e) {
                continue;
            }

            foreach (['frontend/web/', 'base/web/'] as $area) {
                foreach ($this->types as $type => $settings) {
                    if (!in_array($type, $requestedTypes)) {
                        continue;
                    }

                    $glob = $area . $settings['folder'] . $relativePath;

                    if (strpos($glob, '*') === false) {
                        $files = [$glob];
                    } else {
                        if (substr($glob, -3) === '***') {
                            $glob = str_replace('***', $settings['glob'], $glob);
                        }
                        $files = $moduleDir->search($glob);
                    }

                    foreach ($files as $filepath) {
                        foreach ($excludedPaths as $excludedPath) {
                            if (strpos($filepath, $excludedPath) !== false) {
                                continue 2;
                            }
                        }

                        $fileContents = '';

                        // try to read files from pub/static folder (minified and overriden by theme)
                        $fullFilepaths = [];
                        $fullFilepath = $staticPath . '/' . $module . '/' . str_replace($area, '', $filepath);
                        if ($type === 'js' && $this->minification->isEnabled('js')) {
                            $fullFilepaths[] = substr($fullFilepath, 0, -2) . 'min.js';
                        }
                        $fullFilepaths[] = $fullFilepath;

                        foreach ($fullFilepaths as $fullFilepath) {
                            if (!$this->staticDir->isExist($fullFilepath)) {
                                $fileContents = $this->deployAndRead($fullFilepath);

                                if ($fileContents) {
                                    break;
                                }

                                continue;
                            }

                            try {
                                $fileContents = $this->staticDir->readFile($fullFilepath);
                                break;
                            } catch (\Exception $e) {
                                continue;
                            }
                        }

                        // read directly from module sources
                        if (!$fileContents) {
                            try {
                                $fileContents = $moduleDir->readFile($filepath);
                            } catch (\Exception $e) {
                                continue;
                            }
                        }

                        if ($type === 'js') {
                            if (strpos($fileContents, 'define(') === false &&
                                strpos($fileContents, 'define.amd') === false
                            ) {
                                continue;
                            }

                            if ($this->minification->isEnabled('js')) {
                                $filepath = substr($filepath, 0, -2) . 'min.js';
                            }
                        }

                        $name = $module . '/' . str_replace($area, '', $filepath);
                        $build[$settings['name']][$name] = $fileContents;
                    }
                }
            }
        }

        $content = str_replace(
            ['%build%', '%buildkeys%'],
            [
                json_encode($build, JSON_UNESCAPED_SLASHES),
                json_encode(array_keys($build))
            ],
            self::TEMPLATE
        );

        $this->filesystem
            ->getDirectoryWrite(DirectoryList::STATIC_VIEW)
            ->writeFile($this->getPath(), $content);

        return $this;
    }

    /**
     * @return \Magento\Framework\View\Asset\File
     */
    public function getAsset()
    {
        return $this->assetRepo->createArbitrary($this->getPath(), '');
    }

    /**
     * @return \Magento\Framework\View\Asset\File
     */
    public function createStaticJsAsset()
    {
        return $this->assetRepo->createAsset(
            \Magento\Framework\RequireJs\Config::STATIC_FILE_NAME
        );
    }

    /**
     * Extract module name, path, excluded paths, and requested file types
     *
     * @param string $path
     * @return array
     */
    private function extractVars($path)
    {
        preg_match(
            "/(\w+_\w+)(\/([a-zA-Z\/\*]+))?(---([a-zA-Z\.,]+))?(\|([a-z,]+))?/",
            $path,
            $matches
        );

        if (!$matches || count($matches) < 2) {
            throw new \Exception("Unable to extract vars from given path. " . $path);
        }

        $module = $matches[1];
        $relativePath = empty($matches[3]) ? '***' : $matches[3];
        $excludedPaths = $matches[5] ?? '';
        $requestedTypes = empty($matches[7]) ? 'js,html' : $matches[7];

        $vars = [
            $module,
            $relativePath,
            array_filter(explode(',', $excludedPaths)),
            array_filter(explode(',', $requestedTypes)),
        ];

        return $vars;
    }

    /**
     * @return string
     */
    private function getPath()
    {
        return $this->staticContext->getConfigPath()
            . '/Swissup_Firecheckout/'
            . $this->getName()
            . ($this->minification->isEnabled('js') ? '.min.js' : '.js');
    }

    /**
     * @return string
     */
    private function getName()
    {
        return 'fcbuild-' . $this->name;
    }

    private function deployAndRead($path)
    {
        $path = str_replace($this->staticContext->getPath(), '', $path);
        $path = trim($path, '/');
        $url = $this->assetRepo->getUrl($path);

        $curl = $this->curlFactory->create()->setConfig([
            'header' => false,
            'verifypeer' => false,
        ]);
        $curl->write('GET', $url);

        $data = $curl->read();
        $responseCode = (int) $curl->getInfo(CURLINFO_HTTP_CODE);

        $curl->close();

        if ($responseCode !== 200) {
            return false;
        }

        return $data;
    }
}
