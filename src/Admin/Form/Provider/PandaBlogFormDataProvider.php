<?php

declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Provider;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

final class PandaBlogFormDataProvider implements FormDataProviderInterface
{
    private $dataConfiguration;

    public function __construct(DataConfigurationInterface $dataConfiguration)
    {
        $this->dataConfiguration = $dataConfiguration;
    }

    public function getData(): array
    {
        return $this->dataConfiguration->getConfiguration();
    }

    public function setData(array $data): array
    {
        return $this->dataConfiguration->updateConfiguration($data);
    }
}