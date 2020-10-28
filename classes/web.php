<?php

class Web
{
    private $cms;

    const PAGE_DOCUMENTATION = "documentation";
    const NAVIGATION = "navigation";
    const MAIL = "Mail";
    const NEW_PASSWORD = "newPassword";

    public function __construct(Cms $cms) {
        $this->cms = $cms;
    }

    private function getPageToDataMethodMapping() {
        return array(
            self::PAGE_DOCUMENTATION => "getDocumentationData",
            self::NAVIGATION => "getNavigationData",
            self::MAIL => "getMailData",
            self::NEW_PASSWORD => "getNewPasswordData",
        );
    }

    public function getPageData(string $page) {
        if(!array_key_exists($page, $this->getPageToDataMethodMapping())) {
            throw new Exception("Page not found", HttpCodes::HTTP_NOT_FOUND);
        }

        return call_user_func([$this, $this->getPageToDataMethodMapping()[$page]]);
    }

    private function getNewPasswordData() {
        $pageCmsData = $this->getCmsData(Cms::PAGE_DATA_FILTER_PAGE, self::NEW_PASSWORD);
        return $this->basicCmsDataFormatting($pageCmsData);
    }

    private function getNavigationData() {
        $pageCmsData = $this->getCmsData(Cms::PAGE_DATA_FILTER_PAGE, self::NAVIGATION);
        $returnData  = array();
        array_walk($pageCmsData, function($value) use(&$returnData) {
            $returnData[$value["placeholder"]] = $value["content"];
        });

        return $returnData;
    }

    private function getDocumentationData() {
        $pageCmsData = $this->getCmsData(Cms::PAGE_DATA_FILTER_PAGE, self::PAGE_DOCUMENTATION);
        $returnData = array("statuses" => array(), "parameters" => array());
        array_walk($pageCmsData, function ($value) use(&$returnData, $pageCmsData) {
           $returnData[$value["placeholder"]] = $value["content"];
           $this->documentationPairValues($pageCmsData,$returnData, "status", "statuses", $value);
           $this->documentationPairValues($pageCmsData,$returnData, "params", "parameters", $value);
            $this->documentationPairValues($pageCmsData,$returnData, "rate-limit", "limit_headers", $value);
        });

        return $returnData;
    }

    private function getMailData() {
        $pageCmsData = $this->getCmsData(Cms::PAGE_DATA_FILTER_PAGE, self::MAIL);
        $returnData  = array();
        array_walk($pageCmsData, function($value) use(&$returnData) {
            $returnData[$value["placeholder"]] = $value["content"];
        });

        return $returnData;
    }

    private function basicCmsDataFormatting(array $pageCmsData) {
        $returnData  = array();
        array_walk($pageCmsData, function($value) use(&$returnData) {
            $returnData[$value["placeholder"]] = $value["content"];
        });

        return $returnData;
    }

    private function getCmsData(string $filterKey, string $filterValue) {
        return $this->cms->getPageDataForLocalWithFilter("", "en", $filterKey, $filterValue);
    }

    private function documentationPairValues(array $pageCmsData, &$returnData, $prefix, $returnArrayKey, $cmsDataValue) {
        if(substr($cmsDataValue["placeholder"], 0, strlen($prefix)) === $prefix && strpos($cmsDataValue["placeholder"], "_label")) {
            $nextStatuses = array("label" => $cmsDataValue["placeholder"]);
            $forValueSearch = substr($cmsDataValue["placeholder"], 0, strlen($cmsDataValue["placeholder"]) - strlen("_label"));
            $statusValue = array_values(array_filter($pageCmsData, function ($value) use($forValueSearch) {
                return $value["placeholder"] === $forValueSearch;
            }));

            if(!empty($statusValue)) {
                $nextStatuses["content"] = $statusValue[0]["placeholder"];
            } else {
                $nextStatuses["content"] = "no_placeholder";
            }

            $returnData[$returnArrayKey][] = $nextStatuses;
        }
    }
}