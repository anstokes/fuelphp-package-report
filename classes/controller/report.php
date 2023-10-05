<?php

namespace Anstech\Report\Controller;

use Fuel\Core\Request;
use Fuel\Core\Security;
use Parser\View;

/**
 * Report Controller
 */
trait Report
{
    /**
     * The report class
     * NOTE: guessed if not provided
     *
     * @var string
     * @access protected
     */
    protected static $report_class = null;

    /**
     * The title to be used
     * NOTE: guessed if not provided
     *
     * @var string
     * @access protected
     */
    protected $title = null;


    public function __construct(Request $request)
    {
        $class_name = get_called_class();

        // Guess (populate) report class, if not provided
        if (! static::$report_class) {
            static::$report_class = '\\' . str_replace('Controller\\', 'Report\\', $class_name);
        }

        // Guess title, if not provided
        if (! $this->title && $this->report()) {
            $this->title = $this->report()->getTitle(true);
        }

        // Call parent constructor
        parent::__construct($request);
    }


    /**
     * Returns name of associated report class
     *
     * @return null|string
     */
    protected function reportClass()
    {
        return static::$report_class;
    }


    /**
     * Returns an instance of the associated report
     *
     * @return null|object
     */
    protected function report()
    {
        if (class_exists(static::$report_class)) {
            return static::$report_class::forge();
        }
        return null;
    }


    /**
     * Get the title
     *
     * @return string
     */
    protected function getTitle()
    {
        return $this->title;
    }


    /**
     * Set the title
     *
     * @param string $title
     *
     * @return Report
     */
    protected function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    public function action_index()
    {
        // Make sure report class exists
        if ($report = $this->report()) {
            // Check whether report has criteria
            list ($valid, $provided) = $report->criteriaProvided(true);
            if (! $valid) {
                // Show criteria input
                $this->showCriteria($provided);
            } else {
                // Run report
                // if (Security::check_token()) {
                if (1 === 1) {
                    list($type, $output) = $report->run($provided);

                    switch ($type) {
                        default:
                        case 'content':
                        case 'html':
                            $this->template->content = View::forge('output/content.mustache', $output, false);
                            // Deliberate fall-through...

                        case 'response':
                            return $output;
                    }
                } else {
                    // Invalid CSRF token
                    $this->showCriteria();
                }
            }
        } else {
            // Report not found
            $this->template->content = View::forge('error.mustache', [
                'title'   => 'Error!',
                'message' => "Report class not found: {$this->reportClass()}",
            ], false);
        }
    }


    protected function showCriteria($provided = [])
    {
        $report = $this->report();

        // Report not found
        $this->template->content = View::forge('criteria.mustache', [
            'title' => $report->getTitle(),
            'form'  => $report->criteriaForm($provided),
        ], false);
    }
}
