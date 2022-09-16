<?php

namespace Swissup\Geoip\Model\Downloader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Swissup\Geoip\Model\Provider\MaxmindDatabase;

class Maxmind extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Framework\Archive
     */
    private $archive;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    private $curl;

    /**
     * @var string
     */
    private $folder;

    /**
     * @var Magento\Framework\Filesystem\Directory\Write
     */
    private $directory;

    /**
     * @param \Magento\Framework\Archive $archive
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param string $folder
     */
    public function __construct(
        \Magento\Framework\Archive $archive,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        $folder = 'swissup/geoip'
    ) {
        $this->archive = $archive;
        $this->curl = $curl;
        $this->folder = $folder;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @return void
     */
    public function download()
    {
        $this->validate()
            ->backup()
            ->fetch()
            ->unpack()
            ->cleanup();
    }

    /**
     * @return int|false
     */
    public function getUpdateDate()
    {
        $path = $this->getDatabasePath();

        if ($this->directory->isExist($path)) {
            $stat = $this->directory->stat($path);
            return $stat['mtime'] ?? false;
        }

        return false;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function validate()
    {
        try {
            $this->directory->openFile($this->getArchivePath());
        } catch (ValidatorException $e) {
            // Fix for magento 2.3.3 and older
            throw new FileSystemException(__(
                'The path "%1" is not writable.',
                $this->directory->getAbsolutePath($this->folder)
            ));
        }

        return $this;
    }

    /**
     * Download tar.gz arhive into working folder
     *
     * @return $this
     */
    private function fetch()
    {
        $url = 'https://download.maxmind.com/app/geoip_download'
            . '?edition_id=' . $this->getEdition()
            . '&license_key=' . $this->getLicense()
            . '&suffix=tar.gz';

        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $url);

        $response = $this->curl->read();
        $responseCode = (int) $this->curl->getInfo(CURLINFO_HTTP_CODE);

        $this->curl->close();

        if ($responseCode !== 200) {
            throw new \Exception($response);
        }

        $file = $this->directory->openFile($this->getArchivePath());

        if (!$file->lock()) {
            return;
        }

        try {
            $file->flush();
            $file->write($response);
        } catch (FileSystemException $e) {
            throw $e;
        } finally {
            $file->unlock();
        }

        return $this;
    }

    /**
     * Create copy of City.mmdb file: City.mmdb.bak
     *
     * @return $this
     */
    private function backup()
    {
        $path = $this->getDatabasePath();

        if ($this->directory->isExist($path)) {
            $this->directory->copyFile($path, $path . '.bak');
        }

        return $this;
    }

    /**
     * Unpack the archive and move *.mmdb file into working folder
     *
     * @return $this
     */
    private function unpack()
    {
        $this->directory->delete($this->getExtractedArchivePath());
        $this->directory->create($this->getExtractedArchivePath());

        $this->archive->unpack(
            $this->getArchivePath(true),
            $this->getExtractedArchivePath(true)
        );

        $files = $this->directory->search('*/*.mmdb', $this->getExtractedArchivePath());

        if (!$files) {
            throw new LocalizedException(__(
                'Archive successfully unpacked to %1, but *.mmdb database not found.',
                $this->getExtractedArchivePath()
            ));
        }

        $this->directory->copyFile($files[0], $this->getDatabasePath());

        return $this;
    }

    /**
     * @return Remove unpacked archive
     */
    private function cleanup()
    {
        $this->directory->delete($this->getExtractedArchivePath());

        return $this;
    }

    /**
     * @param boolean $absolute
     * @return string
     */
    private function getArchivePath($absolute = false)
    {
        $path = $this->folder . '/maxmind.tgz';
        if ($absolute) {
            $path = $this->directory->getAbsolutePath($path);
        }
        return $path;
    }

    /**
     * @param boolean $absolute
     * @return string
     */
    private function getExtractedArchivePath($absolute = false)
    {
        $path = $this->folder . '/tmp';
        if ($absolute) {
            $path = $this->directory->getAbsolutePath($path);
        }
        return $path;
    }

    /**
     * @param boolean $absolute
     * @return string
     */
    private function getDatabasePath($absolute = false)
    {
        $path = $this->folder . '/' . MaxmindDatabase::FILENAME;
        if ($absolute) {
            $path = $this->directory->getAbsolutePath($path);
        }
        return $path;
    }
}
