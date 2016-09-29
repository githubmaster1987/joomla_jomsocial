<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
if (!class_exists('S3')) {
    include_once( JPATH_ROOT . '/components/com_community/libraries/storage/s3_lib.php');
}

class S3_CStorage {

    public $accessKey = null;
    public $secretKey = null;
    public $s3 = null;
    public $bucket = null;
    public $useSSL = false;
    public $name = 's3';

    public function _init() {
        if ($this->s3 == null) {
            $config = CFactory::getConfig();
            $this->accessKey = $config->get('storages3accesskey');
            $this->secretKey = $config->get('storages3secretkey');
            $this->bucket = $config->get('storages3bucket');
            $this->s3 = new S3($this->accessKey, $this->secretKey, $this->useSSL);
            $this->s3->setExceptions();
        }
    }

    /**
     * Check if the given storage id exist. We perform local check via db since
     * checking remotely is time consuming
     *
     * @return true is file exits
     * */
    public function exists($storageid, $checkRemote = false) {
        $item = JTable::getInstance('StorageS3', 'CTable');
        return $item->load($storageid);
    }

    /**
     * Put the file into remote storage,
     * @return true if successful
     */
    public function put($storageid, $file) {
        $this->_init();
        $metaHeaders = array(
            "Cache-Control" => "max-age=94608000",
            "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+3 years"))
        );
        // Put our file (also with public read access)
        if ($this->s3->putObjectFile($file, $this->bucket, $storageid, S3::ACL_PUBLIC_READ, $metaHeaders)) {

            // Insert into our s3 database
            $item = JTable::getInstance('StorageS3', 'CTable');
            $item->storageid = $storageid;
            $item->resource_path = $storageid;
            try {
                $item->store();
            } catch (Exception $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function delete($storageid) {

        //if there is nothing, do not delete
        if(!$storageid){
            return;
        }

        if (is_array($storageid)) {
            $storageids = $storageid;
        } else {
            $storageids[] = $storageid;
        }
        $this->_init();
        foreach ($storageids as $storageid) {
            $this->s3->deleteObject($this->bucket, $storageid);
            $item = JTable::getInstance('StorageS3', 'CTable');
            $item->load($storageid);

            if(!is_null($item->storageid)){
                $item->delete();
            }

        }
        return true;
    }

    /**
     * Retrive the file from remote location and store it locally
     * @param storageid The unique file we want to retrive
     * @param file String	filename where we want to save the file
     */
    public function get($storageid, $file) {
        $this->_init();

        // Put our file (also with public read access)
        if ($this->s3->getObject($this->bucket, $storageid, $file)) {
            return true;
        }
        return false;
    }

    /**
     * Return the absolute URI path to the resource
     */
    public function getURI($storageId) {
        $item = JTable::getInstance('StorageS3', 'CTable');
        $item->load($storageId);

        if (isset($item->resource_path)) {
            $uri = JUri::getInstance();
            /* Get S3 URL format */
            $s3Url = CFactory::getConfig()->get('storages3bucket_url', 's3.amazonaws.com/<bucket>');
            /* Replace <bucket> by real bucketname */
            $s3Url = str_replace('<bucket>', $this->bucket, $s3Url);
            /* General final path */
            if ($uri->isSSL()) {
                return 'https://' . $s3Url . '/' . $item->resource_path;
            } else {
                return 'http://' . $s3Url . '/' . $item->resource_path;
            }
        }

        return $storageId;
    }

    /**
     * @since 4.2 to retrieve all the files within the folder
     * @param string $prefix - path of the folder that needs to be iterated
     * @return array|false
     */
    public function getFileList($prefix = ''){
        //@todo there are some limitation here, it can only retrieve 1k results, a marker need to be set to retrieve the next page
        return $this->s3->getBucket($this->bucket,'images/avatar/');
    }

}

class CTableStorageS3 extends JTable {

    var $storageid = null;
    var $resource_path = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_storage_s3', 'storageid', $db);
    }

    public function store($updateNulls = null) {
        $k = $this->_tbl_key;

        if (empty($this->$k)) {
            return false;
        }

        $db = $this->getDBO();

        $query = 'SELECT count(*)'
                . ' FROM ' . $this->_tbl
                . ' WHERE ' . $this->_tbl_key . ' = ' . $db->Quote($this->storageid);
        $db->setQuery($query);
        $isExist = $db->loadResult();

        if (!$isExist) {
            $query = 'INSERT INTO ' . $this->_tbl
                    . ' SET ' . $db->quoteName('storageid') . '=' . $db->Quote($this->storageid)
                    . ' , ' . $db->quoteName('resource_path') . '= ' . $db->Quote($this->resource_path);
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        } else {
            $query = 'UPDATE ' . $this->_tbl
                    . ' SET ' . $db->quoteName('resource_path') . '= ' . $db->Quote($this->resource_path)
                    . ' WHERE ' . $db->quoteName('storageid') . '=' . $db->Quote($this->storageid);
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return true;
    }

}
