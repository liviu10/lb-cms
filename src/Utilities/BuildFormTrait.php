<?php

namespace LiviuVoica\LbCms\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LiviuVoica\LbCms\DTO\FormFieldDTO;

trait BuildFormTrait
{
    /**
     * Build a form representation from a model.
     *
     * @param  array<int, string>  $formFields  List of field names to include
     * @return FormFieldDTO[] Array of FormFieldDTO
     *
     * @see FormFieldDTO
     */
    public function buildForm(Model $instance, array $formFields): array
    {
        $tableName = $instance->getTable();
        $form = [];

        // Get the columns from the database
        $columns = DB::select('
            SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ', [$tableName]);

        foreach ($columns as $column) {
            $name = $column->COLUMN_NAME;

            if (! in_array($name, $formFields)) {
                continue;
            }

            $type = $column->DATA_TYPE;
            $columnType = $column->COLUMN_TYPE;

            $options = null;

            if ($type === 'enum') {
                preg_match("/^enum\((.*)\)$/", $columnType, $matches);
                if (isset($matches[1])) {
                    $vals = explode(',', $matches[1]);
                    $options = array_map(fn ($val) => [
                        'value' => $val,
                        'label' => $val,
                    ], $vals);
                }
                $fieldType = 'select';
            } else {
                switch ($type) {
                    case 'varchar':
                    case 'text':
                        $fieldType = 'text';
                        break;
                    case 'int':
                    case 'bigint':
                    case 'smallint':
                        $fieldType = 'number';
                        break;
                    case 'tinyint':
                        $fieldType = 'checkbox';
                        break;
                    case 'date':
                        $fieldType = 'date';
                        break;
                    case 'datetime':
                    case 'timestamp':
                        $fieldType = 'datetime-local';
                        break;
                    case 'decimal':
                    case 'float':
                    case 'double':
                        $fieldType = 'number';
                        break;
                    default:
                        $fieldType = 'text';
                }
            }

            $form[] = FormFieldDTO::fromArray([
                'key' => (string) $name,
                'type' => (string) $fieldType,
                'value' => '',
                'options' => $options,
            ]);
        }

        return $form;
    }

    /**
     * Attach anti-bot hidden fields to the form array.
     *
     * @param  FormFieldDTO[]  $form  Array of FormFieldDTO
     * @return FormFieldDTO[] Array of FormFieldDTO including anti-bot fields
     *
     * @see FormFieldDTO
     */
    public function attachAntiBotFields(array $form): array
    {
        $antiBotFields = [
            FormFieldDTO::fromArray([
                'key' => 'bd',
                'type' => 'hidden',
                'value' => '',
            ]),
            FormFieldDTO::fromArray([
                'key' => 'fst',
                'type' => 'hidden',
                'value' => (string) time(),
            ]),
        ];

        return array_merge($form, $antiBotFields);
    }
}
