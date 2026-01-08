<?php

namespace LiviuVoica\LbCms\Utilities;

use Illuminate\Database\Eloquent\Model;

trait DesiredFieldsTrait
{
    /**
     * @param  array<string>  $desiredFields
     * @return array<string> The desired fields.
     */
    private function getFields(Model $instance, array $desiredFields): array
    {
        $fields = [];

        foreach ($instance->getFillable() as $field) {
            if (! in_array($field, $desiredFields, true)) {
                continue;
            }

            $fields[] = $field;
        }

        array_unshift($fields, $instance->getKeyName());

        return $fields;
    }
}
