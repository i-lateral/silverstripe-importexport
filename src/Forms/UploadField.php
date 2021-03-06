<?php
/**
 * Created by IntelliJ IDEA.
 * User: Werner
 * Date: 03.09.2018
 * Time: 19:40
 */

namespace ilateral\SilverStripe\ImportExport\Forms;


use SilverStripe\ORM\SS_List;

class UploadField extends \SilverStripe\AssetAdmin\Forms\UploadField
{
    /**
     * @var string link
     */
    private $link;

    public function __construct($name, $title = null, SS_List $items = null)
    {
        parent::__construct($name, $title, $items);

        $this->setAllowedMaxFileNumber(1);
        $this->setAllowedExtensions(['csv']);
        $this->setFolderName('csvImports');
        $this->addExtraClass("import-upload-csv-field");
    }

    public function getSchemaDataDefaults()
    {
        return parent::getSchemaDataDefaults(); // TODO: Change the autogenerated stub
    }

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function Link($action = null)
    {
        return $this->link ?: parent::Link($action);
    }

}
