<?php

namespace Anstech\Report\Report;

use Anstech\Crud\Form\Base;
use Anstech\Report\Entity\Criteria;
use Anstech\Template\Enqueue;
use Fuel\Core\Arr;
use Fuel\Core\Fieldset;
use Fuel\Core\Input;
use Parser\View;

trait Report
{
    // protected $report_criteria_fields = [];

    public function criteria()
    {
        $properties = $this->properties();

        $report_criteria_fields = $this->report_criteria_fields;

        // Replace string fields with values from model properties, where available
        foreach ($report_criteria_fields as $key => $value) {
            if (is_string($value) && isset($properties[$value])) {
                $report_criteria_fields[$value] = $properties[$value];
                unset($report_criteria_fields[$key]);
            }
        };

        return $report_criteria_fields;
    }


    public function criteriaRequired()
    {
        // Return only criteria that have the required flag
        return array_filter($this->criteria(), function ($options) {
            return Arr::get($options, 'required') === true;
        });
    }


    public function criteriaProvided($validateRequired = false)
    {
        $criteriaProvided = [];

        $criteria = $this->criteria();

        // Check input method
        if ($criteria && (Input::method() !== 'POST')) {
            // Criteria options but NOT POST method
            return [
                false,
                [],
            ];
        }

        // Read input for each criteria
        foreach ($criteria as $field => $options) {
            $criteriaFieldValue = Input::post($field, null);
            if ($criteriaFieldValue !== null) {
                $criteriaProvided[$field] = $criteriaFieldValue;
            }
        }

        // Check if validating criteria requirements
        if ($validateRequired && array_diff_key($this->criteriaRequired(), $criteriaProvided)) {
            return [
                false,
                $criteriaProvided,
            ];
        }

        return [
            true,
            $criteriaProvided,
        ];
    }


    /**
     * Generate criteria form
     *
     * @param mixed $provided
     * @param array $errors
     *
     * @return object
     */
    public function criteriaForm($provided, $errors = [])
    {
        // Queue plugins associated with form
        Enqueue::enqueuePlugins('report-criteria-form');

        // Build criteria entity
        Criteria::setProperties($this->criteria());
        $criteria = Criteria::forge();

        // Create fieldset
        $fieldset = Fieldset::forge('form');
        $fieldset->add_model($criteria);

        // Modify fieldset
        Base::helper($fieldset);
        $fieldset->populate($provided);

        // Create the criteria form
        return View::forge('criteria/form.mustache', [
            'fields' => $fieldset->build(),
        ], false);
    }

    public function run()
    {
        return [
            'content',
            ['data' => 'Default output'],
        ];
    }
}
