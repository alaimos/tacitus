<?php

namespace App\Utils;


class Bench
{
    protected $benches = [];

    protected $repeats;

    /**
     * Bench constructor.
     *
     * @param int $repeats
     */
    public function __construct($repeats = 10)
    {
        $this->repeats = $repeats;
    }

    /**
     * Add a benchmark
     *
     * @param string   $name
     * @param callable $callback
     *
     * @return $this
     */
    public function addBench($name, callable $callback)
    {
        $this->benches[$name] = [
            'callback' => $callback,
            'measures' => [],
            'started'  => false,
        ];
        return $this;
    }

    /**
     * Run all benchmarks
     *
     * @return $this
     */
    public function run()
    {
        foreach ($this->benches as $bench => $info) {
            echo "Starting bench $bench";
            for ($i = 0; $i < $this->repeats; $i++) {
                $start = microtime(true);
                call_user_func($info['callback']);
                $end = microtime(true);
                $this->benches[$bench]['measures'][] = round($end - $start, 2);
                echo '...' . ($i + 1);
            }
            echo "...OK\n";
        }
        return $this;
    }

    /**
     * Returns a string with the results
     *
     * @return string
     */
    public function printResults()
    {
        $res = '';
        foreach ($this->benches as $bench => $info) {
            $mean = array_sum($info['measures']) / count($info['measures']);
            $res .= $bench . ": " . $mean . "\n";
        }
        return $res;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     */
    function __toString()
    {
        return $this->printResults();
    }


}