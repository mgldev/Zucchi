<?php
namespace Zucchi\Controller;

use Zend\Http\Request;
/**
 * RequestParserTrait.php
 *
 * @link      http://github.com/zucchifor the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 * @author Matt Cockayne <matt@zucchi.co.uk>
 */
trait RequestParserTrait
{

    protected $requestOperators = array(
        'is'    => 'is',
        'eq'    => '=',
        'gt'    => '>',
        'gte'   => '>=',
        'lt'    => '<',
        'lte'   => '<=',
        'neq'   => '!=',
        'between' => 'between',
        'fuzzy' => 'like',
        'regex' => 'regexp',
    );

    protected $requestModes = array(
        'or' => 'or',
        'and' => 'and',
    );

    /**
     * Processes the WHERE clauses and operators provided in the request into
     * a format usable by the getList() method of the services.
     * @return array
     */
    protected function parseWhere($where = false)
    {

        if (is_array($where)) {
            if (array_key_exists('expressions', $where)) {
                $where = $this->parseComplexWhere($where);
            } else {
                $where = $this->parseSimpleWhere($where);
            }
        } else if (!is_string($where)) {
            $where = false;
        }

        return $where;
    }


    /**
     * @param $where
     * @param $operators
     * @param $this->requestModes
     * @return array
     */
    protected function parseComplexWhere($where)
    {
        if (array_key_exists('expressions', $where)) {

            if (isset($where['mode']) && isset($this->requestModes[$where['mode']])) {
                $where['mode'] = $this->requestModes[$where['mode']];
            } else {
                $where['mode'] = $this->requestModes['and'];
            }

            foreach ($where['expressions'] as $index => &$expression) {
                if (isset($expression['mode']) && isset($this->requestModes[$expression['mode']])) {
                    $expression['mode'] = $this->requestModes[$expression['mode']];
                } else {
                    $expression['mode'] = $expression['and'];
                }
                $expression['fields'] = $this->parseSimpleWhere($expression['fields'], $operators, $this->requestModes);
            }
        }

        return $where;
    }

    /**
     * @param $where
     * @param $operators
     * @param $this->requestModes
     * @return array
     */
    protected function parseSimpleWhere($where)
    {
        // loop through and sanitize the where statement
        foreach ($where as $field => &$value) {
            if (is_array($value)) {
                if (isset($value['value']) && is_string($value['value']) && strlen($value['value'])) {
                    if (isset($value['operator']) && isset($this->requestOperators[$value['operator']])) {
                        $value['operator'] = $this->requestOperators[$value['operator']];
                    } else {
                        $value['operator'] = $this->requestOperators['eq'];
                    }

                    if (isset($value['mode']) && isset($this->requestModes[$value['mode']])) {
                        $value['mode'] = $this->requestModes[$value['mode']];
                    } else {
                        $value['mode'] = $this->requestModes['and'];
                    }
                }
            } else if (is_string($value) && strlen($value)) {
                $value = array(
                    'mode' => $modes['and'],
                    'operator' => $this->requestOperators['eq'],
                    'value' => $value
                );
            }
        }

        return $where;
    }
}
