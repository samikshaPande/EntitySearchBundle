<?php
declare(strict_types=1);

/*
 * This file is part of the Stinger Entity Search package.
 *
 * (c) Oliver Kotte <oliver.kotte@stinger-soft.net>
 * (c) Florian Meyer <florian.meyer@stinger-soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StingerSoft\EntitySearchBundle\Services\Facet;

use StingerSoft\EntitySearchBundle\Model\Document;

class NoIconTypeFacet implements FacetServiceInterface {

    public const SERVICE_ID = 'stinger_soft_entity_search.facets.noIconType';

    public function getField(): string {
        return Document::FIELD_NO_ICON_TYPE;
    }

    public function getFormOptions() : array {
        return array(
            'label'              => 'stinger_soft_entity_search.forms.query.type.label',
            'translation_domain' => 'StingerSoftEntitySearchBundle'
        );
    }

    public function getFacetFormatter() : ?callable {
        return null;
    }

}
