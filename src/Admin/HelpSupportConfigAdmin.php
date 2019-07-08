<?php

namespace App\Admin;

use AppBundle\Schema\HelpSupportSchema;

/**
 * Class HelpSupportConfigAdmin
 */
class HelpSupportConfigAdmin extends BaseConfigAdmin
{

    /**
     * @var string
     */
    protected $baseRouteName = 'help_support';

    /**
     * @var bool
     */
    protected $isPrivate = true;

    /**
     * { @inheritdoc }
     */
    protected function getConfigSchema()
    {
        return HelpSupportSchema::class;
    }

}
