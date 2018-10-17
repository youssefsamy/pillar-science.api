<?php

namespace App\Functional\Services\MimeTypes;

trait MimeTypesDataProviderTrait
{
    public function csvPathProvider()
    {
        return $this->nameDataProviderTests($this->getFiles('tests/Data/MimeTypes/CSV/*'));
    }

    public function tabsPathProvider()
    {
        return $this->nameDataProviderTests($this->getFiles('tests/Data/MimeTypes/Tabs/*'));
    }

    public function singleColumnProvider()
    {
        return $this->nameDataProviderTests($this->getFiles('tests/Data/MimeTypes/SingleColumn/*'));
    }

    public function textPlainProvider()
    {
        return $this->nameDataProviderTests($this->getFiles('tests/Data/MimeTypes/TextPlain/*'));
    }

    protected function getFiles($path)
    {
        return array_map(function ($path) {
            return [$path];
        }, glob($path));
    }
}