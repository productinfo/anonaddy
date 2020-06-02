<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait CheckUserRules
{
    public function checkRules()
    {
        $this->user->activeRulesOrdered()->each(function ($rule) {
            // Check if the conditions of the rule are satisfied
            if ($this->ruleConditionsSatisfied($rule->conditions, $rule->operator)) {
                // Apply actions for that rule
                collect($rule->actions)->each(function ($action) {
                    $this->applyAction($action);
                });
            };
        });
    }

    protected function ruleConditionsSatisfied($conditions, $logicalOperator)
    {
        $results = collect();

        collect($conditions)->each(function ($condition) use ($results) {
            $results->push($this->lookupConditionType($condition));
        });

        $result = $results->unique();

        if ($logicalOperator == 'OR') {
            return $result->contains(true);
        }

        // Logical operator is AND so return false if any conditions are not met
        return ! $result->contains(false);
    }

    protected function lookupConditionType($condition)
    {
        switch ($condition['type']) {
            case 'sender':
                return $this->conditionSatisfied($this->sender, $condition);
                break;
            case 'subject':
                return $this->conditionSatisfied($this->subject, $condition);
                break;
            case 'alias':
                return $this->conditionSatisfied($this->alias->email, $condition);
                break;
            case 'displayFrom':
                return $this->conditionSatisfied($this->displayFrom, $condition);
                break;
        }
    }

    protected function conditionSatisfied($variable, $condition)
    {
        $values = collect($condition['values']);

        switch ($condition['match']) {
            case 'is exactly':
                return $values->contains(function ($value) use ($variable) {
                    return $variable === $value;
                });
                break;
            case 'is not':
                return $values->contains(function ($value) use ($variable) {
                    return $variable !== $value;
                });
                break;
            case 'contains':
                return $values->contains(function ($value) use ($variable) {
                    return Str::contains($variable, $value);
                });
                break;
            case 'does not contain':
                return $values->contains(function ($value) use ($variable) {
                    return ! Str::contains($variable, $value);
                });
                break;
            case 'starts with':
                return $values->contains(function ($value) use ($variable) {
                    return Str::startsWith($variable, $value);
                });
                break;
            case 'does not start with':
                return $values->contains(function ($value) use ($variable) {
                    return ! Str::startsWith($variable, $value);
                });
                break;
            case 'ends with':
                return $values->contains(function ($value) use ($variable) {
                    return Str::endsWith($variable, $value);
                });
                break;
            case 'does not end with':
                return $values->contains(function ($value) use ($variable) {
                    return ! Str::endsWith($variable, $value);
                });
                break;
            // regex preg_match?
        }
    }

    protected function applyAction($action)
    {
        switch ($action['type']) {
            case 'subject':
                $this->email->subject = $action['value'];
                break;
            case 'displayFrom':
                $this->email->from($this->fromEmail, $action['value']);
                break;
            case 'encryption':
                if ($action['value'] == false) {
                    // detach the openpgpsigner from the email...
                }
                break;
            case 'banner':
                $this->email->location = $action['value'];
                break;
            case 'block':
                $this->alias->increment('emails_blocked');
                exit(0);
                break;
            case 'webhook':
                // http payload to url
                break;
        }
    }
}
