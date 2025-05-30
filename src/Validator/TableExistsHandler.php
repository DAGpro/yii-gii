<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;

/**
 * An inline validator that checks if the attribute value refers to an existing class name.
 */
final class TableExistsHandler implements RuleHandlerInterface
{
    public function __construct(
        private readonly ConnectionInterface $connection
    ) {
    }

    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof TableExistsRule) {
            throw new UnexpectedRuleException(TableExistsRule::class, $rule);
        }

        $result = new Result();
        if (!is_string($value)) {
            $result->addError(sprintf('Value must be a string, %s given.".', gettype($value)));
            return $result;
        }

        try {
            $tableSchema = $this->connection->getTableSchema($value);
        } catch (\Yiisoft\Db\Exception\Exception $e) {
            $result->addError(sprintf('The error occurred during fetching table schema: "%s".', $e));
            return $result;
        }

        if ($tableSchema === null) {
            $result->addError(sprintf('Table "%s" does not exist.', $value));
            return $result;
        }

        return $result;
    }
}
