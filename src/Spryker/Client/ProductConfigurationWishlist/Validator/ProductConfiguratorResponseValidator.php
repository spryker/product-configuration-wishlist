<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductConfigurationWishlist\Validator;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer;
use Spryker\Client\ProductConfigurationWishlist\Dependency\Client\ProductConfigurationWishlistToProductConfigurationClientInterface;
use Spryker\Shared\Kernel\Transfer\Exception\RequiredTransferPropertyException;
use Spryker\Shared\Log\LoggerTrait;

class ProductConfiguratorResponseValidator implements ProductConfiguratorResponseValidatorInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_RESPONSE_VALIDATION_ERROR = 'product_configuration.response.validation.error';

    /**
     * @var \Spryker\Client\ProductConfigurationWishlist\Dependency\Client\ProductConfigurationWishlistToProductConfigurationClientInterface
     */
    protected $productConfigurationClient;

    /**
     * @param \Spryker\Client\ProductConfigurationWishlist\Dependency\Client\ProductConfigurationWishlistToProductConfigurationClientInterface $productConfigurationClient
     */
    public function __construct(ProductConfigurationWishlistToProductConfigurationClientInterface $productConfigurationClient)
    {
        $this->productConfigurationClient = $productConfigurationClient;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer $productConfiguratorResponseProcessorResponseTransfer
     * @param array $configuratorResponseData
     *
     * @return \Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer
     */
    public function validateProductConfiguratorCheckSumResponse(
        ProductConfiguratorResponseProcessorResponseTransfer $productConfiguratorResponseProcessorResponseTransfer,
        array $configuratorResponseData
    ): ProductConfiguratorResponseProcessorResponseTransfer {
        $productConfiguratorResponseProcessorResponseTransfer = $this->productConfigurationClient
            ->validateProductConfiguratorCheckSumResponse(
                $productConfiguratorResponseProcessorResponseTransfer,
                $configuratorResponseData,
            );

        if (!$productConfiguratorResponseProcessorResponseTransfer->getIsSuccessful()) {
            return $productConfiguratorResponseProcessorResponseTransfer;
        }

        return $this->validateMandatoryFields($productConfiguratorResponseProcessorResponseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer $productConfiguratorResponseProcessorResponseTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer
     */
    protected function validateMandatoryFields(
        ProductConfiguratorResponseProcessorResponseTransfer $productConfiguratorResponseProcessorResponseTransfer
    ): ProductConfiguratorResponseProcessorResponseTransfer {
        $productConfiguratorResponseTransfer = $productConfiguratorResponseProcessorResponseTransfer
            ->getProductConfiguratorResponseOrFail();

        try {
            $productConfiguratorResponseTransfer
                ->requireIdWishlistItem()
                ->requireSku();
        } catch (RequiredTransferPropertyException $requiredTransferPropertyException) {
            $this->getLogger()->error(
                static::GLOSSARY_KEY_RESPONSE_VALIDATION_ERROR,
                ['exception' => $requiredTransferPropertyException],
            );

            return $this->addErrorToResponse(
                $productConfiguratorResponseProcessorResponseTransfer,
                static::GLOSSARY_KEY_RESPONSE_VALIDATION_ERROR,
            );
        }

        return $productConfiguratorResponseProcessorResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer $productConfiguratorResponseProcessorResponseTransfer
     * @param string $errorMessage
     *
     * @return \Generated\Shared\Transfer\ProductConfiguratorResponseProcessorResponseTransfer
     */
    protected function addErrorToResponse(
        ProductConfiguratorResponseProcessorResponseTransfer $productConfiguratorResponseProcessorResponseTransfer,
        string $errorMessage
    ): ProductConfiguratorResponseProcessorResponseTransfer {
        $messageTransfer = (new MessageTransfer())
            ->setValue($errorMessage);

        return $productConfiguratorResponseProcessorResponseTransfer
            ->addMessage($messageTransfer)
            ->setIsSuccessful(false);
    }
}
