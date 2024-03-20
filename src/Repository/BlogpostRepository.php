<?php

namespace App\Repository;

use Bolt\Repository\ContentRepository;
use Doctrine\Common\Collections\Criteria;

class BlogpostRepository extends ContentRepository
{
    public function findPostSortedByField(?string $fieldName = '')
    {
        if ($contentType && ($contentType->get('slug')) && ($contentType->get('slug') === 'blogpost')) {
            $sortableFieldNames = ['title', 'date'];
            if (!empty($fieldName) && in_array($fieldName, $sortableFieldNames)) {
                $this->getQueryBuilder()
                    ->addOrderBy($fieldName, Criteria::DESC);
            }
        }

        return $this->getQueryBuilder()->getQuery()->getArrayResult();
    }
}
