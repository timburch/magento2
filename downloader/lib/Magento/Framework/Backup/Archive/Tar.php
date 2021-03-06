<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category     Magento
 * @package      Magento_Backup
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Backup\Archive;

/**
 * Extended version of \Magento\Framework\Archive\Tar that supports filtering
 *
 * @category    Magento
 * @package     Magento_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tar extends \Magento\Framework\Archive\Tar
{
    /**
     * Filenames or filename parts that are used for filtering files
     *
     * @var array
     */
    protected $_skipFiles = array();

    /**
     * Overridden \Magento\Framework\Archive\Tar::_createTar method that does the same actions as it's parent but filters
     * files using \Magento\Framework\Backup\Filesystem\Iterator\Filter
     *
     * @param bool $skipRoot
     * @param bool $finalize
     * @return void
     * @see \Magento\Framework\Archive\Tar::_createTar()
     */
    protected function _createTar($skipRoot = false, $finalize = false)
    {
        $path = $this->_getCurrentFile();

        $filesystemIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new \Magento\Framework\Backup\Filesystem\Iterator\Filter($filesystemIterator, $this->_skipFiles);

        foreach ($iterator as $item) {
            $this->_setCurrentFile($item->getPathname());
            $this->_packAndWriteCurrentFile();
        }

        if ($finalize) {
            $this->_getWriter()->write(str_repeat("\0", self::TAR_BLOCK_SIZE * 12));
        }
    }

    /**
     * Set files that shouldn't be added to tarball
     *
     * @param array $skipFiles
     * @return \Magento\Framework\Backup\Archive\Tar
     */
    public function setSkipFiles(array $skipFiles)
    {
        $this->_skipFiles = $skipFiles;
        return $this;
    }
}
