<?php

namespace Sup7\Mailchimp\Domain\Model\Dto;

use Sup7\Mailchimp\Exception\ApiKeyMissingException;

class ExtensionConfiguration
{

    /** @var string */
    protected $apiKey;

    public function __construct()
    {
        $settings = (array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mailchimp']);
        foreach ($settings as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return string
     * @throws ApiKeyMissingException
     */
    public function getApiKey()
    {
        if (empty($this->apiKey)) {
            throw new ApiKeyMissingException('API key is missing');
        }
        return $this->apiKey;
    }

}