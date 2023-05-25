<?php

namespace Buten\Viatec\Model\Inventory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Xml\Parser;

class GetXml
{
    const FILE_URL = 'https://viatec.ua/files/product_info_uk.xml';

    private File $file;

    private DirectoryList $directoryList;

    private Parser $parser;

    public function __construct(
        File          $file,
        DirectoryList $directoryList,
        Parser        $parser
    ) {
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->parser = $parser;
    }

    public function getFileArray()
    {
        $filePath = $this->getFile();
        $parsedArray = $this->parser->load($filePath)->xmlToArray();
        return $parsedArray['sitedata'];
    }





    /**
     * @return false|string
     * @throws LocalizedException
     */
    public function getFile()
    {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        $this->file->checkAndCreateFolder($tmpDir);
        if ($this->file->fileExists($tmpDir . baseName(self::FILE_URL))) {
            $this->file->rm($tmpDir . baseName(self::FILE_URL));
        }
        $newFileName = $tmpDir . baseName(self::FILE_URL);
        $result = $this->file->read(self::FILE_URL, $newFileName);
        if (!$result) {
            return false;
        }
        return $newFileName;
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp/';
    }
}
