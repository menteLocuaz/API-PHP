<?php

declare(strict_types=1);

namespace Arancamon\ApiPhp\Database\Builders;

class JoinBuilder
{
    public static function build(array $tables, array $types): string
    {
        if (count($tables) < 2) {
            return '';
        }

        foreach ($tables as $t) {
            SelectBuilder::validateIdentifier($t);
        }
        foreach ($types as $t) {
            SelectBuilder::validateIdentifier($t);
        }

        $join = '';
        foreach ($tables as $key => $table) {
            if ($key === 0) {
                continue;
            }

            if ($key === 1) {
                $join .= "INNER JOIN {$table} ON {$tables[0]}.id_{$types[0]} = {$table}.id_{$types[0]}_{$types[1]} ";
            } else {
                $join .= "INNER JOIN {$table} ON {$tables[1]}.id_{$types[$key]}_{$types[1]} = {$table}.id_{$types[$key]} ";
            }
        }

        return $join;
    }
}
