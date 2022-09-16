<?php

namespace Swissup\Geoip\Model\Provider;

use GeoIp2\Database\Reader;
use Magento\Framework\Exception\NotFoundException;

class MaxmindDatabase extends MaxmindAbstract
{
    const FILENAME = 'maxmind.mmdb';
    const FILENAME_CONFIG = 'geoip/main/filename'; // deprecated

    /**
     * @return Client
     */
    protected function getReader()
    {
        return new Reader($this->getFilepath());
    }

    /**
     * @return boolean
     */
    public function isCacheable()
    {
        return false;
    }

    /**
     * @return string
     * @throws NotFoundException
     */
    private function getFilepath()
    {
        $filename = $this->getConfigValue(self::FILENAME_CONFIG);
        $filename = basename($filename);
        $filenames = [
            self::FILENAME,
            $filename,
        ];

        foreach ($filenames as $filename) {
            $path = BP . '/var/swissup/geoip/' . $filename;
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new NotFoundException(__('Maxmind database file was not found.'));
    }
}
